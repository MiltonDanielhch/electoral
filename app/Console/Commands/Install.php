<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Install extends Command
{
    protected $signature = 'example:install';
    protected $description = 'Install Laravel Example';

    public function handle()
    {
        $this->info('Iniciando instalación...');

        if (!file_exists('.env')) {
            copy('.env.example', '.env');
            $this->info('.env creado desde .env.example');
        }

        $this->call('key:generate');

        if ($this->confirm('¿Eliminar y recrear la base de datos?')) {
            $this->call('migrate:fresh');
            $this->call('db:seed');
        }

        $this->call('storage:link');

        // Descomenta si usas Voyager
        // $this->call('vendor:publish', [
        //     '--provider' => 'TCG\\Voyager\\VoyagerServiceProvider',
        //     '--tag' => ['config', 'voyager_avatar']
        // ]);

        $this->info('✅ Instalación completada. ¡Gracias por usar LaravelTemplate!');
    }
}
