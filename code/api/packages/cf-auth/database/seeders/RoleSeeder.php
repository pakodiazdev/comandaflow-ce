<?php

namespace CF\CE\Auth\Database\Seeders;

use CF\CE\Auth\Models\Role;
use CF\CE\Auth\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding CF Auth roles and permissions...');
        
        // Clear cache before seeding
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        
        DB::transaction(function () {
            // Create permissions first
            $this->createPermissions();
            
            // Create roles with permissions
            $this->createRoles();
        });

        $this->command->info('✅ CF Auth roles and permissions seeded successfully!');
    }

    /**
     * Create all permissions
     */
    private function createPermissions(): void
    {
        $permissions = [
            // User Management
            ['code' => 'users.view', 'description' => 'View users list'],
            ['code' => 'users.show', 'description' => 'View specific user details'],
            ['code' => 'users.create', 'description' => 'Create new users'],
            ['code' => 'users.update', 'description' => 'Update user information'],
            ['code' => 'users.delete', 'description' => 'Delete users'],
            ['code' => 'users.assign_roles', 'description' => 'Assign roles to users'],
            ['code' => 'users.revoke_roles', 'description' => 'Revoke roles from users'],
            ['code' => 'users.assign_permissions', 'description' => 'Assign direct permissions to users'],
            ['code' => 'users.revoke_permissions', 'description' => 'Revoke direct permissions from users'],
            ['code' => 'users.change_password', 'description' => 'Change user passwords'],
            ['code' => 'users.export', 'description' => 'Export users data'],
            ['code' => 'users.import', 'description' => 'Import users data'],

            // Role Management
            ['code' => 'roles.view', 'description' => 'View roles list'],
            ['code' => 'roles.show', 'description' => 'View specific role details'],
            ['code' => 'roles.create', 'description' => 'Create new roles'],
            ['code' => 'roles.update', 'description' => 'Update role information'],
            ['code' => 'roles.delete', 'description' => 'Delete roles'],
            ['code' => 'roles.assign_permissions', 'description' => 'Assign permissions to roles'],
            ['code' => 'roles.revoke_permissions', 'description' => 'Revoke permissions from roles'],

            // Permission Management
            ['code' => 'permissions.view', 'description' => 'View permissions list'],
            ['code' => 'permissions.show', 'description' => 'View specific permission details'],
            ['code' => 'permissions.create', 'description' => 'Create new permissions'],
            ['code' => 'permissions.update', 'description' => 'Update permission information'],
            ['code' => 'permissions.delete', 'description' => 'Delete permissions'],

            // Profile Management
            ['code' => 'profile.view', 'description' => 'View own profile'],
            ['code' => 'profile.update', 'description' => 'Update own profile'],
            ['code' => 'profile.change_password', 'description' => 'Change own password'],

            // Dashboard & Reports
            ['code' => 'dashboard.view', 'description' => 'Access main dashboard'],
            ['code' => 'dashboard.admin', 'description' => 'Access admin dashboard'],
            ['code' => 'reports.view', 'description' => 'View reports'],
            ['code' => 'reports.create', 'description' => 'Create reports'],
            ['code' => 'reports.export', 'description' => 'Export reports'],

            // System Settings
            ['code' => 'settings.view', 'description' => 'View system settings'],
            ['code' => 'settings.update', 'description' => 'Update system settings'],
            ['code' => 'settings.advanced', 'description' => 'Access advanced settings'],

            // API Management
            ['code' => 'api.access', 'description' => 'Access API endpoints'],
            ['code' => 'api.admin', 'description' => 'Admin-level API access'],

            // File Management
            ['code' => 'files.view', 'description' => 'View files'],
            ['code' => 'files.upload', 'description' => 'Upload files'],
            ['code' => 'files.download', 'description' => 'Download files'],
            ['code' => 'files.delete', 'description' => 'Delete files'],

            // Audit & Logs
            ['code' => 'logs.view', 'description' => 'View system logs'],
            ['code' => 'audit.view', 'description' => 'View audit trail'],

            // Content Management
            ['code' => 'content.view', 'description' => 'View content'],
            ['code' => 'content.create', 'description' => 'Create content'],
            ['code' => 'content.update', 'description' => 'Update content'],
            ['code' => 'content.delete', 'description' => 'Delete content'],
            ['code' => 'content.publish', 'description' => 'Publish content'],

            // Communication
            ['code' => 'notifications.view', 'description' => 'View notifications'],
            ['code' => 'notifications.send', 'description' => 'Send notifications'],
            ['code' => 'messages.view', 'description' => 'View messages'],
            ['code' => 'messages.send', 'description' => 'Send messages'],

            // Restaurant Business Specific
            ['code' => 'orders.view', 'description' => 'View orders'],
            ['code' => 'orders.create', 'description' => 'Create orders'],
            ['code' => 'orders.update', 'description' => 'Update orders'],
            ['code' => 'orders.delete', 'description' => 'Delete orders'],
            ['code' => 'orders.approve', 'description' => 'Approve orders'],
            ['code' => 'orders.send_to_kitchen', 'description' => 'Send orders to kitchen'],
            ['code' => 'orders.mark_ready', 'description' => 'Mark orders as ready'],
            ['code' => 'orders.mark_delivered', 'description' => 'Mark orders as delivered'],

            ['code' => 'products.view', 'description' => 'View products/menu items'],
            ['code' => 'products.create', 'description' => 'Create products/menu items'],
            ['code' => 'products.update', 'description' => 'Update products/menu items'],
            ['code' => 'products.delete', 'description' => 'Delete products/menu items'],

            ['code' => 'customers.view', 'description' => 'View customers'],
            ['code' => 'customers.create', 'description' => 'Create customers'],
            ['code' => 'customers.update', 'description' => 'Update customers'],
            ['code' => 'customers.delete', 'description' => 'Delete customers'],

            ['code' => 'inventory.view', 'description' => 'View inventory'],
            ['code' => 'inventory.update', 'description' => 'Update inventory'],
            ['code' => 'inventory.reports', 'description' => 'View inventory reports'],
            ['code' => 'inventory.alerts', 'description' => 'Manage stock alerts'],

            // Tables Management
            ['code' => 'tables.view', 'description' => 'View restaurant tables'],
            ['code' => 'tables.create', 'description' => 'Create restaurant tables'],
            ['code' => 'tables.update', 'description' => 'Update restaurant tables'],
            ['code' => 'tables.delete', 'description' => 'Delete restaurant tables'],
            ['code' => 'tables.assign', 'description' => 'Assign tables to orders'],

            // Financial
            ['code' => 'finances.view', 'description' => 'View financial data'],
            ['code' => 'finances.manage', 'description' => 'Manage financial data'],
            ['code' => 'finances.reports', 'description' => 'View financial reports'],
            ['code' => 'cash.open_close', 'description' => 'Open and close cash register'],
            ['code' => 'payments.process', 'description' => 'Process payments'],
            ['code' => 'discounts.approve', 'description' => 'Approve discounts'],

            // Kitchen Management
            ['code' => 'kitchen.view_orders', 'description' => 'View kitchen orders'],
            ['code' => 'kitchen.manage_preparation', 'description' => 'Manage order preparation'],
            ['code' => 'kitchen.mark_ready', 'description' => 'Mark orders as ready in kitchen'],

            // Printing & Hardware
            ['code' => 'printing.tickets', 'description' => 'Print order tickets'],
            ['code' => 'printing.configure', 'description' => 'Configure printers'],
            ['code' => 'hardware.manage', 'description' => 'Manage hardware devices'],
            ['code' => 'network.configure', 'description' => 'Configure network settings'],

            // Team Management
            ['code' => 'teams.view', 'description' => 'View teams'],
            ['code' => 'teams.create', 'description' => 'Create teams'],
            ['code' => 'teams.update', 'description' => 'Update teams'],
            ['code' => 'teams.delete', 'description' => 'Delete teams'],
            ['code' => 'teams.assign_members', 'description' => 'Assign team members'],
        ];

        foreach ($permissions as $permission) {
            Permission::getByCodeOrCreate([
                'code' => $permission['code'],
                'guard_name' => 'api',
                'description' => $permission['description'],
            ]);
        }

        $this->command->line("   ✅ Created " . count($permissions) . " permissions");
    }

    /**
     * Create all roles with their permissions
     */
    private function createRoles(): void
    {
        $roles = [
            [
                'code' => 'super_admin',
                'name' => 'Super Administrator',
                'description' => 'Has access to all system features and settings',
                'permissions' => 'all' // Special case for all permissions
            ],
            [
                'code' => 'owner',
                'name' => 'Owner / Admin',
                'description' => 'Company owner or general manager',
                'permissions' => [
                    'users.view', 'users.show', 'users.create', 'users.update', 'users.assign_roles',
                    'roles.view', 'roles.show', 'roles.create', 'roles.update', 'roles.assign_permissions',
                    'permissions.view', 'permissions.show',
                    'dashboard.view', 'dashboard.admin',
                    'reports.view', 'reports.create', 'reports.export',
                    'settings.view', 'settings.update', 'settings.advanced',
                    'api.access', 'api.admin',
                    'orders.view', 'orders.create', 'orders.update', 'orders.approve',
                    'products.view', 'products.create', 'products.update', 'products.delete',
                    'customers.view', 'customers.create', 'customers.update',
                    'inventory.view', 'inventory.update', 'inventory.reports', 'inventory.alerts',
                    'tables.view', 'tables.create', 'tables.update', 'tables.delete', 'tables.assign',
                    'finances.view', 'finances.manage', 'finances.reports',
                    'cash.open_close', 'payments.process', 'discounts.approve',
                    'printing.tickets', 'printing.configure',
                    'hardware.manage', 'network.configure',
                    'teams.view', 'teams.create', 'teams.update', 'teams.assign_members',
                ]
            ],
            [
                'code' => 'manager',
                'name' => 'Manager / Supervisor',
                'description' => 'Responsible for daily operations',
                'permissions' => [
                    'users.view', 'users.show',
                    'dashboard.view',
                    'reports.view', 'reports.create', 'reports.export',
                    'api.access',
                    'orders.view', 'orders.create', 'orders.update', 'orders.approve',
                    'products.view', 'products.update',
                    'customers.view', 'customers.create', 'customers.update',
                    'inventory.view', 'inventory.update', 'inventory.reports',
                    'tables.view', 'tables.assign',
                    'finances.view', 'finances.reports',
                    'cash.open_close', 'payments.process', 'discounts.approve',
                    'printing.tickets',
                ]
            ],
            [
                'code' => 'cashier',
                'name' => 'Cashier',
                'description' => 'Handles payments and order registration',
                'permissions' => [
                    'dashboard.view',
                    'api.access',
                    'orders.view', 'orders.create', 'orders.send_to_kitchen',
                    'products.view',
                    'customers.view',
                    'tables.view', 'tables.assign',
                    'cash.open_close', 'payments.process',
                    'printing.tickets',
                ]
            ],
            [
                'code' => 'chef',
                'name' => 'Chef / Kitchen',
                'description' => 'Manages order preparation',
                'permissions' => [
                    'dashboard.view',
                    'api.access',
                    'kitchen.view_orders', 'kitchen.manage_preparation', 'kitchen.mark_ready',
                    'orders.mark_ready', 'orders.mark_delivered',
                ]
            ],
            [
                'code' => 'waiter',
                'name' => 'Waiter',
                'description' => 'Takes and manages table orders',
                'permissions' => [
                    'dashboard.view',
                    'api.access',
                    'orders.view', 'orders.create', 'orders.send_to_kitchen',
                    'products.view',
                    'customers.view',
                    'tables.view', 'tables.assign',
                    'printing.tickets',
                ]
            ],
            [
                'code' => 'accountant',
                'name' => 'Accountant',
                'description' => 'Reviews finance and reports',
                'permissions' => [
                    'dashboard.view',
                    'reports.view', 'reports.create', 'reports.export',
                    'api.access',
                    'orders.view',
                    'finances.view', 'finances.manage', 'finances.reports',
                    'inventory.reports',
                ]
            ],
            [
                'code' => 'inventory_manager',
                'name' => 'Inventory Manager',
                'description' => 'Manages stock and supplies',
                'permissions' => [
                    'dashboard.view',
                    'api.access',
                    'products.view', 'products.create', 'products.update',
                    'inventory.view', 'inventory.update', 'inventory.reports', 'inventory.alerts',
                ]
            ],
            [
                'code' => 'technical_support',
                'name' => 'Technical Support',
                'description' => 'Tenant-level internal support',
                'permissions' => [
                    'dashboard.view',
                    'api.access',
                    'printing.configure',
                    'hardware.manage',
                    'network.configure',
                ]
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::getByCodeOrCreate([
                'code' => $roleData['code'],
                'name' => $roleData['name'],
                'guard_name' => 'api',
                'description' => $roleData['description'],
            ]);

            // Assign permissions to role
            if ($roleData['permissions'] === 'all') {
                // Super admin gets all permissions
                $role->givePermissionTo(Permission::where('guard_name', 'api')->get());
            } else {
                // Get permissions by code
                $permissions = Permission::whereIn('code', $roleData['permissions'])
                    ->where('guard_name', 'api')
                    ->get();
                
                $role->syncPermissions($permissions);
            }

            $this->command->line("   ✅ Created role '{$roleData['code']}' with " . 
                (is_array($roleData['permissions']) ? count($roleData['permissions']) : 'all') . " permissions");
        }
    }
}
