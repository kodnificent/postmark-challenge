<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PreDeployment extends Command
{
    protected $signature = 'app:pre-deployment';

    protected $description = 'Commands that run before deployments';

    public function handle()
    {
        $this->call('migrate', [
            '--force' => true,
        ]);
    }
}
