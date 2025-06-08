<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PostDeployment extends Command
{
    protected $signature = 'app:post-deployment';

    protected $description = 'Commands that run after successful deployments';

    public function handle()
    {
        $this->call('config:clear');
        $this->call('event:clear');
        $this->call('route:clear');
        $this->call('view:clear');

        $this->call('filament:clear-cached-components');
        $this->call('icons:cache');

        if (config('app.env') === 'production') {
            $this->call('config:cache');
            $this->call('event:cache');
            $this->call('route:cache');
            $this->call('view:cache');

            $this->call('filament:cache-components');
        }

        $this->call('storage:link');
    }
}
