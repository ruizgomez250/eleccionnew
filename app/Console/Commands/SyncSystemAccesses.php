<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncSystemAccesses extends Command
{
    protected $signature = 'sync:system-accesses';
    protected $description = 'Sincroniza todos los accesos a sistemas para todos los usuarios';

    public function handle()
    {
        $this->info('Iniciando sincronización de accesos...');
        
        $users = User::all();
        $bar = $this->output->createProgressBar(count($users));
        
        foreach ($users as $user) {
            $user->syncSystemAccesses();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Sincronización completada.');
        
        return Command::SUCCESS;
    }
}