<?php

namespace CF\CE\Auth\Commands;

use CF\CE\Auth\Models\Role;
use Illuminate\Console\Command;

class TestRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cf-auth:test-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test CF Auth roles and permissions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Testing CF Auth roles and permissions...');

        $baseRoles = [
            'owner',
            'manager', 
            'cashier',
            'chef',
            'waiter',
            'accountant',
            'inventory_manager',
            'technical_support'
        ];

        $allRolesExist = true;
        $totalPermissions = 0;

        foreach ($baseRoles as $code) {
            $role = Role::findByCode($code);
            
            if ($role) {
                $permissionCount = $role->permissions->count();
                $totalPermissions += $permissionCount;
                
                $this->line("   ✅ Role '{$code}' exists with {$permissionCount} permissions");
                
                // Show first few permissions
                $permissions = $role->permissions->take(3)->pluck('name')->toArray();
                if (!empty($permissions)) {
                    $this->line("      📋 Sample permissions: " . implode(', ', $permissions));
                    if ($permissionCount > 3) {
                        $this->line("      ... and " . ($permissionCount - 3) . " more");
                    }
                }
            } else {
                $this->error("   ❌ Role '{$code}' not found");
                $allRolesExist = false;
            }
        }

        $this->newLine();
        
        if ($allRolesExist) {
            $this->info("✅ All base roles exist with a total of {$totalPermissions} permissions");
        } else {
            $this->error("❌ Some roles are missing. Run the seeder first:");
            $this->line("   php artisan db:seed --class=CF\\CE\\Auth\\Database\\Seeders\\RoleSeeder");
        }

        // Test role creation
        $this->newLine();
        $this->info("🔧 Testing role creation methods...");
        
        try {
            $testRole = Role::createWithCode([
                'name' => 'Test Role',
                'code' => 'test_role_' . time(),
                'description' => 'Test role for validation',
                'guard_name' => 'web'
            ]);
            
            $this->line("   ✅ Role creation works");
            
            // Clean up test role
            $testRole->delete();
            $this->line("   🧹 Test role cleaned up");
            
        } catch (\Exception $e) {
            $this->error("   ❌ Role creation failed: " . $e->getMessage());
        }

        return $allRolesExist ? Command::SUCCESS : Command::FAILURE;
    }
}
