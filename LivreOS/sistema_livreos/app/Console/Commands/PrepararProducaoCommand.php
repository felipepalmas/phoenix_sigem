<?php

/**
 * Componente da aplicação Phoenix - SIGEM
 *
 * @license AGPL-3.0
 */

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Checklist de segurança antes de colocar o SIGEM em uso real:
 *  - desativa os usuários de demonstração do LivreOS (@erp.test);
 *  - troca a senha padrão do admin@admin.com;
 *  - avisa sobre APP_DEBUG/APP_ENV.
 */
class PrepararProducaoCommand extends Command
{
    protected $signature = 'sigem:preparar-producao {--senha-admin= : Nova senha do admin@admin.com (se omitida, é perguntada)}';

    protected $description = 'Desativa usuários de demonstração, troca a senha padrão do admin e confere o .env';

    public function handle(): int
    {
        $demo = User::where('email', 'like', '%@erp.test')->where('ativo', true)->get();
        foreach ($demo as $u) {
            $u->ativo = false;
            $u->save();
        }
        $this->info("Usuários de demonstração desativados: {$demo->count()}");

        $admin = User::where('email', 'admin@admin.com')->first();
        if ($admin && Hash::check('password', $admin->password)) {
            $senha = $this->option('senha-admin') ?: $this->secret('Nova senha para admin@admin.com (mín. 12 caracteres)');
            if (! $senha || Str::length($senha) < 12) {
                $this->error('Senha não alterada: informe pelo menos 12 caracteres.');

                return self::FAILURE;
            }
            $admin->password = Hash::make($senha);
            $admin->save();
            $this->info('Senha padrão do admin@admin.com substituída.');
        } else {
            $this->line('admin@admin.com não usa a senha padrão. OK.');
        }

        if (config('app.debug')) {
            $this->warn('APP_DEBUG=true no .env — mude para false fora do ambiente de desenvolvimento.');
        }
        if (config('app.env') !== 'production') {
            $this->warn('APP_ENV=' . config('app.env') . ' — use APP_ENV=production no servidor da Prefeitura.');
        }

        return self::SUCCESS;
    }
}
