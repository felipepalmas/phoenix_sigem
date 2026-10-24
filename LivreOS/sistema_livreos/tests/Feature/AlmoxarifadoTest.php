<?php

namespace Tests\Feature;

use App\Models\Deposito;
use App\Models\Produto;
use App\Models\RequisicaoMaterial;
use Tests\TestCase;

class AlmoxarifadoTest extends TestCase
{
    private function cenario(float $saldo = 10): array
    {
        $sems = $this->unidade('SEMS');
        $semad = $this->unidade('SEMAD');
        $deposito = Deposito::create(['nome' => 'Almoxarifado Central', 'ativo' => true, 'unidade_administrativa_id' => $semad->id]);
        $produto = Produto::create(['nome' => 'Papel A4', 'unidade' => 'CX', 'estoque_quantidade' => $saldo, 'estoque_deposito_id' => $deposito->id]);
        $solicitante = $this->servidor($sems);

        $this->actingAs($solicitante)->post(route('almoxarifado.requisicoes.store'), [
            'unidade_solicitante_id' => $sems->id,
            'almoxarifado_id' => $deposito->id,
            'prioridade' => 'normal',
            'justificativa' => 'Uso no posto de saúde',
            'itens' => [['produto_id' => $produto->id, 'quantidade' => 8]],
        ])->assertRedirect();

        return [
            'req' => RequisicaoMaterial::latest('id')->firstOrFail(),
            'produto' => $produto,
            'solicitante' => $solicitante,
            'chefeSems' => $this->servidor($sems, 'manager'),
            'chefeOutra' => $this->servidor($this->unidade('SEMOSP'), 'manager'),
            'almoxarife' => $this->servidor($semad, 'manager'),
        ];
    }

    public function test_servidor_so_requisita_pela_propria_secretaria(): void
    {
        $sems = $this->unidade('SEMS');
        $outra = $this->unidade('SEFAZ');
        $deposito = Deposito::create(['nome' => 'Central', 'ativo' => true]);
        $produto = Produto::create(['nome' => 'Caneta', 'unidade' => 'UN', 'estoque_quantidade' => 5]);

        $this->actingAs($this->servidor($sems))->post(route('almoxarifado.requisicoes.store'), [
            'unidade_solicitante_id' => $outra->id, 'almoxarifado_id' => $deposito->id, 'prioridade' => 'normal',
            'justificativa' => 'Teste indevido', 'itens' => [['produto_id' => $produto->id, 'quantidade' => 1]],
        ])->assertSessionHasErrors('unidade_solicitante_id');
        $this->assertSame(0, RequisicaoMaterial::count());
    }

    public function test_aprovacao_respeita_lotacao_e_segregacao(): void
    {
        $c = $this->cenario();

        $r = $this->actingAs($c['solicitante'])->post(route('almoxarifado.requisicoes.aprovar', $c['req']));
        $r->assertSessionHas('error');
        $this->actingAs($c['chefeOutra'])->post(route('almoxarifado.requisicoes.aprovar', $c['req']))->assertSessionHas("error");
        $this->assertSame('pendente_aprovacao', $c['req']->fresh()->situacao);

        $this->actingAs($c['chefeSems'])->post(route('almoxarifado.requisicoes.aprovar', $c['req']))->assertSessionHas('success');
        $this->assertSame('aprovada', $c['req']->fresh()->situacao);
    }

    public function test_atendimento_parcial_nunca_entrega_mais_que_o_pedido(): void
    {
        $c = $this->cenario(20);
        $this->actingAs($c['chefeSems'])->post(route('almoxarifado.requisicoes.aprovar', $c['req']));
        $item = $c['req']->itens()->first();

        $this->actingAs($c['almoxarife'])->post(route('almoxarifado.requisicoes.atender', $c['req']), ['itens' => [$item->id => 5]])
            ->assertSessionHas('success');
        $this->assertSame('atendida_parcial', $c['req']->fresh()->situacao);

        // Tentar entregar 8 de novo (só faltam 3) é recusado
        $this->actingAs($c['almoxarife'])->post(route('almoxarifado.requisicoes.atender', $c['req']), ['itens' => [$item->id => 8]])
            ->assertSessionHas('error');

        $this->actingAs($c['almoxarife'])->post(route('almoxarifado.requisicoes.atender', $c['req']), ['itens' => [$item->id => 3]])
            ->assertSessionHas('success');

        $this->assertEquals(8.0, (float) $item->fresh()->quantidade_atendida);
        $this->assertEquals(12.0, (float) $c['produto']->fresh()->estoque_quantidade);
        $this->assertSame('atendida', $c['req']->fresh()->situacao);
    }

    public function test_saida_maior_que_saldo_e_recusada(): void
    {
        $c = $this->cenario(3);
        $this->actingAs($c['chefeSems'])->post(route('almoxarifado.requisicoes.aprovar', $c['req']));
        $item = $c['req']->itens()->first();

        $this->actingAs($c['almoxarife'])->post(route('almoxarifado.requisicoes.atender', $c['req']), ['itens' => [$item->id => 8]])
            ->assertSessionHas('error');
        $this->assertEquals(3.0, (float) $c['produto']->fresh()->estoque_quantidade);
        $this->assertSame('aprovada', $c['req']->fresh()->situacao);
    }
}
