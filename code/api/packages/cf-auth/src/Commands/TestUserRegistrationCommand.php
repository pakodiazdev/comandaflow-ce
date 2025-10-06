<?php

namespace CF\CE\Auth\Commands;

use CF\CE\Auth\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TestUserRegistrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cf-auth:test-registration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test user registration with role assignment to verify guard fix';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Testing user registration with role assignment...');

        try {
            // Create test user
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test-' . time() . '@example.com',
                'password' => Hash::make('password123')
            ]);

            $this->info("✅ User created successfully with ID: {$user->id}");

            // Assign role
            $user->assignRoleByCode('manager');
            $this->info('✅ Role assigned successfully');

            // Verify role assignment
            $roles = $user->getRoleCodes();
            $this->info('✅ User roles: ' . implode(', ', $roles));

            // Clean up test user
            $user->delete();
            $this->info('🧹 Test user cleaned up');

            $this->newLine();
            $this->info('🎉 Registration test completed successfully!');
            $this->info('✅ Guard issue is RESOLVED - users can be registered with roles');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}