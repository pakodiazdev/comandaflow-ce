<?php

namespace CF\CE\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallAuthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cf-auth:install 
                            {--force : Force publish files even if they exist}
                            {--seed : Run role seeder after installation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install CF Auth package with Passport and Spatie Permission';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔐 Installing CF Auth package...');

        // Publish migrations
        $this->info('📦 Publishing migrations...');
        $this->call('vendor:publish', [
            '--tag' => 'cf-auth-migrations',
            '--force' => $this->option('force')
        ]);

        // Publish config
        $this->info('⚙️ Publishing configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'cf-auth-config',
            '--force' => $this->option('force')
        ]);

        // Run migrations
        $this->info('🗄️ Running migrations...');
        $this->call('migrate');

        // Install Passport
        $this->info('🔑 Installing Laravel Passport...');
        $this->call('passport:install', [
            '--force' => true
        ]);

        // Create personal access client
        $this->info('🔑 Creating personal access client...');
        $this->call('passport:client', [
            '--personal' => true,
            '--name' => 'CF Auth Personal Access Client'
        ]);

        // Seed roles if requested
        if ($this->option('seed')) {
            $this->info('🌱 Seeding base roles...');
            $this->call('db:seed', [
                '--class' => 'CF\CE\Auth\Database\Seeders\RoleSeeder'
            ]);
        }

        $this->info('✅ CF Auth package installed successfully!');
        
        if (!$this->option('seed')) {
            $this->warn('💡 Run "php artisan db:seed --class=CF\\CE\\Auth\\Database\\Seeders\\RoleSeeder" to seed base roles');
        }

        return Command::SUCCESS;
    }
}
