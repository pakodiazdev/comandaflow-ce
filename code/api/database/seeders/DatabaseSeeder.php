<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

/**
 * Main Database Seeder
 * 
 * Orchestrates seeding based on environment and configuration:
 * - Production seeders: Always run (roles, permissions, default users)
 * - Development seeders: Run only in development/testing environments
 * - Fake seeders: Run only when SEED_FAKE=true
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');
        $this->command->info('');

        // ✅ Always run Production seeders (immutable base data)
        $this->command->info('📦 Running Production Seeders...');
        $this->runProductionSeeders();
        $this->command->info('');

        // 🧪 Run Development seeders only in dev/testing environments
        if ($this->shouldRunDevSeeders()) {
            $this->command->info('🧪 Running Development Seeders...');
            $this->runDevSeeders();
            $this->command->info('');
        } else {
            $this->command->info('⏭️  Skipping Development Seeders (not in dev/testing environment)');
            $this->command->info('');
        }

        // 🎭 Run Fake seeders only when explicitly requested
        if ($this->shouldRunFakeSeeders()) {
            $this->command->info('🎭 Running Fake Data Seeders...');
            $this->runFakeSeeders();
            $this->command->info('');
        } else {
            $this->command->info('⏭️  Skipping Fake Seeders (SEED_FAKE not set)');
            $this->command->info('');
        }

        $this->command->info('✅ Database seeding completed successfully!');
    }

    /**
     * Run production seeders (always executed)
     */
    protected function runProductionSeeders(): void
    {
        $this->call([
            ProductionSeeders\RoleSeeder::class,
            ProductionSeeders\PassportClientSeeder::class,
            ProductionSeeders\PassportSeeder::class,
            ProductionSeeders\DefaultUsersSeeder::class,
        ]);
    }

    /**
     * Run development seeders (only in dev/testing)
     */
    protected function runDevSeeders(): void
    {
        // Add development seeders here when created
        // Example:
        // $this->call([
        //     DevSeeders\SampleOrdersSeeder::class,
        //     DevSeeders\TestCustomersSeeder::class,
        // ]);
        
        $this->command->warn('   ⚠️  No development seeders configured yet');
    }

    /**
     * Run fake data seeders (only when SEED_FAKE=true)
     */
    protected function runFakeSeeders(): void
    {
        // Add fake data seeders here when created
        // Example:
        // $this->call([
        //     FakeSeeders\MassUsersSeeder::class,
        //     FakeSeeders\MassOrdersSeeder::class,
        // ]);
        
        $this->command->warn('   ⚠️  No fake data seeders configured yet');
    }

    /**
     * Determine if development seeders should run
     */
    protected function shouldRunDevSeeders(): bool
    {
        $env = App::environment();
        return in_array($env, ['local', 'development', 'testing']);
    }

    /**
     * Determine if fake seeders should run
     */
    protected function shouldRunFakeSeeders(): bool
    {
        return env('SEED_FAKE', false) === true || 
               env('SEED_FAKE', 'false') === 'true' ||
               env('SEED_FAKE', '0') === '1';
    }
}
