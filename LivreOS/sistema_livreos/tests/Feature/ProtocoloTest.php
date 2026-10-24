<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Processo;
use Tests\TestCase;

class ProtocoloTest extends TestCase
{
    private function autuar($autor, $unidade, array $dados = []): Processo
    {
        $cliente = Cliente::create(['tipo_pessoa' => 'F', 'nome' => 'Maria da Silva', 'cpf' => '52998224725']);
        $this->actingAs($autor)->post(route('protocolo.store'), array_merge([
            'interessado_id' => $cliente->id,
            'assunto' => 'Pedido de alvará',
            'unidade_atual_id' => $unidade->id,
            'prioridade' => 'normal',
            'sigilo' => 'publico',
            'prazo_dias' => 10,
        ], $dados))->assertRedirect();

        return Processo::latest('id')->firstOrFail();
    }

    public function test_numeracao_anual_sequencial_e_chave_de_acesso(): void
    {
        $semad = $this->unidade('SEMAD');
        $servidor = $this->servidor($semad);
        $p1 = $this->autuar($servidor, $semad);
        $p2 = $this->autuar($servidor, $semad);

        $this->assertSame(date('Y') . '/000001', $p1->numero);
        $this->assertSame(date('Y') . '/000002', $p2->numero);
        $this->assertMatchesRegularExpression('/^[A-Z2-9]{8}$/', $p1->chave_acesso);
    }

    public function test_so_quem_esta_lotado_na_unidade_recebe_e_tramita(): void
    {
        $semad = $this->unidade('SEMAD');
        $sefaz = $this->unidade('SEFAZ');
        $doSemad = $this->servidor($semad);
        $daSefaz = $this->servidor($sefaz);

        $p = $this->autuar($doSemad, $semad);

        // Servidor da SEFAZ não tramita processo que está na SEMAD
        $this->actingAs($daSefaz)->post(route('protocolo.tramitar', $p), [
            'unidade_destino_id' => $sefaz->id, 'despacho' => 'Envio indevido', 'parecer_tipo' => 'informativo',
        ])->assertSessionHas('error');
        $this->assertSame($semad->id, $p->fresh()->unidade_atual_id);

        // SEMAD recebe e tramita para a SEFAZ
        $this->actingAs($doSemad)->post(route('protocolo.receber', $p))->assertSessionHas('success');
        $this->actingAs($doSemad)->post(route('protocolo.tramitar', $p), [
            'unidade_destino_id' => $sefaz->id, 'despacho' => 'Para análise fiscal', 'parecer_tipo' => 'informativo',
        ])->assertSessionHas('success');
        $this->assertSame($sefaz->id, $p->fresh()->unidade_atual_id);
        $this->assertTrue($p->fresh()->aguardaRecebimento());

        // Agora a SEMAD não age mais; a SEFAZ recebe
        $this->actingAs($doSemad)->post(route('protocolo.receber', $p))->assertSessionHas('error');
        $this->actingAs($daSefaz)->post(route('protocolo.receber', $p))->assertSessionHas('success');
        $this->assertSame($daSefaz->id, $p->fresh()->usuario_responsavel_id);
    }

    public function test_processo_encerrado_nao_volta_a_tramitar(): void
    {
        $semad = $this->unidade('SEMAD');
        $sefaz = $this->unidade('SEFAZ');
        $servidor = $this->servidor($semad);
        $p = $this->autuar($servidor, $semad);

        $this->actingAs($servidor)->post(route('protocolo.concluir', $p), ['status' => 'deferido', 'despacho_final' => 'Deferido conforme parecer'])
            ->assertSessionHas('success');
        $this->actingAs($servidor)->post(route('protocolo.tramitar', $p), [
            'unidade_destino_id' => $sefaz->id, 'despacho' => 'Tentativa', 'parecer_tipo' => 'informativo',
        ])->assertSessionHas('error');
        $this->assertSame('deferido', $p->fresh()->status);
    }

    public function test_sigiloso_bloqueado_para_outra_secretaria_e_auditado(): void
    {
        $semad = $this->unidade('SEMAD');
        $sefaz = $this->unidade('SEFAZ');
        $p = $this->autuar($this->servidor($semad), $semad, ['sigilo' => 'sigiloso']);

        $this->actingAs($this->servidor($sefaz))->get(route('protocolo.show', $p))->assertForbidden();
        $this->assertDatabaseHas('audit_processos_sigilosos', ['processo_id' => $p->id, 'acao' => 'bloqueio_acesso_negado']);
    }

    public function test_portal_exige_numero_e_chave(): void
    {
        $semad = $this->unidade('SEMAD');
        $p = $this->autuar($this->servidor($semad), $semad);
        auth()->logout();

        $this->get(route('portal.processos', ['numero' => $p->numero, 'chave' => 'ERRADA12']))
            ->assertOk()->assertDontSee('Pedido de alvará');
        $this->get(route('portal.processos', ['numero' => $p->numero, 'chave' => $p->chave_acesso]))
            ->assertOk()->assertSee('Pedido de alvará')->assertDontSee('Maria da Silva');
    }
}
