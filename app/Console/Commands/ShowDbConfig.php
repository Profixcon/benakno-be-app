<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ShowDbConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:config';

    protected $description = 'Show database connection configuration';

    public function handle()
    {
        $config = config('database.connections.' . config('database.default'));
        $this->info('Database Connection Configuration:');
        $this->info('Driver: ' . $config['driver']);
        $this->info('Host: ' . $config['host']);
        $this->info('Database: ' . $config['database']);
        $this->info('Username: ' . $config['username']);
        $this->info('Password: ' . $config['password']);
    }
}
