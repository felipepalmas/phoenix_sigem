<?php

namespace Tests\Feature;

use App\Models\CategoriaZeladoria;
use App\Models\OrdemServico;
use App\Models\User;
use App\Support\Numerador;
use App\Support\StatusZeladoria;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ZeladoriaEAcessoTest extends TestCase
{
    private function demandaPeloPortal(): OrdemServico
    {
        $semosp = $this->unidade('SEMOSP');
        $cat = CategoriaZeladoria::create(['nome' => 'Iluminação Pública', 'secretaria_padrao_id' => $semosp->id, 'prazo_padrao_dias' => 3, 'ativo' => true, 'ordem' => 1]);

        $this->post(route('portal.zeladoria.store'), [
            'nome' => 'João Cidadão', 'cpf' => '529.982.247-25', 'telefone' => '54999990000',
            'categoria_zeladoria_id' => $cat->id, 'logradouro' => 'Rua das Flores', 'bairro' => 'Centro',
            'relato_cliente' => 'Poste apagado há três noites',
            'latitude' => -27.36, 'longitude' => -52.77,
        ])->assertRedirect();

        return OrdemServico::latest('id')->firstOrFail();
    }

    public function test_portal_gera_codigo_zel_sequencial_e_unico(): void
    {
        $a = $this->demandaPeloPortal();
        $b = $this->demandaPeloPortal();
        $this->assertSame(sprintf('ZEL-%d/%05d', date('Y'), 1), $a->codigo_interno);
        $this->assertSame(sprintf('ZEL-%d/%05d', date('Y'), 2), $b->codigo_interno);
        $this->assertNull($a->numero_sequencial);
        $this->assertSame(StatusZeladoria::REGISTRADA, $a->status);
        $this->assertNotNull($a->latitude);
    }

    public function test_portal_sem_chave_mostra_so_o_andamento(): void
    {
        $os = $this->demandaPeloPortal();
        $this->assertNotEmpty($os->chave_acesso);

        $this->get(route('portal.zeladoria.show', ['codigo' => $os->codigo_interno]))
            ->assertOk()->assertSee('Em triagem')->assertDontSee('Poste apagado')->assertDontSee('Rua das Flores');
        $this->get(route('portal.zeladoria.show', ['codigo' => $os->codigo_interno, 'chave' => $os->chave_acesso]))
            ->assertOk()->assertSee('Poste apagado')->assertSee('Rua das Flores');
        // OS interna (sem categoria de zeladoria) não aparece no portal
        $interna = \App\Models\OrdemServico::create(['codigo_interno' => 'OS-000099', 'status' => 'Aberta', 'relato_cliente' => 'Conserto interno', 'cliente_id' => $os->cliente_id]);
        $this->get(route('portal.zeladoria.show', ['codigo' => 'OS-000099']))->assertOk()->assertDontSee('Conserto interno');
    }

    public function test_chefia_nao_se_promove_nem_muda_de_secretaria(): void
    {
        $semad = $this->unidade('SEMAD');
        $sefaz = $this->unidade('SEFAZ');
        $chefe = $this->servidor($semad, 'manager');
        $adminRole = \App\Models\Role::where('slug', 'admin')->value('id');
        $managerRole = \App\Models\Role::where('slug', 'manager')->value('id');

        $this->actingAs($chefe)->put(route('admin.users.update', $chefe), [
            'name' => $chefe->name, 'email' => $chefe->email, 'ativo' => 1,
            'unidade_administrativa_id' => $sefaz->id, 'roles' => [$adminRole, $managerRole],
        ]);
        $chefe->refresh();
        $this->assertSame($semad->id, (int) $chefe->unidade_administrativa_id);
        $this->assertFalse($chefe->ehGestorGeral());
    }

    public function test_numerador_continua_da_maior_numeracao_existente(): void
    {
        $n = Numerador::proximo('TESTE', 2026, fn () => 41);
        $this->assertSame(42, $n);
        $this->assertSame(43, Numerador::proximo('TESTE', 2026, fn () => 999));
    }

    public function test_conclusao_so_pela_secretaria_responsavel_e_uma_unica_vez(): void
    {
        Storage::fake('public');
        $os = $this->demandaPeloPortal();
        $foto = fn () => UploadedFile::fake()->image('depois.jpg');

        $outro = $this->servidor($this->unidade('SEMED'), 'technician');
        $this->actingAs($outro)->post(route('zeladoria.concluir', $os), ['foto_depois' => $foto(), 'servicos_realizados' => 'Troca de lâmpada'])
            ->assertSessionHas('error');

        $tecnico = $this->servidor($this->unidade('SEMOSP'), 'technician', ['cargo' => 'Eletricista']);
        $this->actingAs($tecnico)->post(route('zeladoria.concluir', $os), ['foto_depois' => $foto(), 'servicos_realizados' => 'Troca de lâmpada por LED'])
            ->assertSessionHas('success');
        $os->refresh();
        $this->assertSame(StatusZeladoria::CONCLUIDA, $os->status);
        $this->assertSame($tecnico->name, $os->atesto_responsavel);

        $this->actingAs($tecnico)->post(route('zeladoria.concluir', $os), ['foto_depois' => $foto(), 'servicos_realizados' => 'De novo'])
            ->assertSessionHas('error');
    }

    public function test_usuario_desativado_nao_entra_e_cadastro_publico_fechado(): void
    {
        $user = $this->servidor($this->unidade('SEMAD'), 'user', ['ativo' => false]);
        $this->get(route('signin'));
        $this->post(route('login'), [
            'email' => $user->email, 'password' => 'senha-de-teste-123', 'login_form_token' => session('auth.login.form_token'),
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->get('/signup')->assertRedirect(route('signin'));
    }

    public function test_telas_principais_abrem_para_servidor_lotado(): void
    {
        $user = $this->servidor($this->unidade('SEMAD'), 'manager');
        foreach (['/', '/protocolo', '/protocolo/novo', '/zeladoria', '/zeladoria/nova', '/almoxarifado/requisicoes', '/almoxarifado/movimentacoes', '/frota', '/frota/diario-bordo', '/frota/motoristas', '/secretarias', '/busca?q=a', '/portal'] as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
    }
}
