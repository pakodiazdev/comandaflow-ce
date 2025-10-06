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
            ['name' => 'users.view', 'description' => 'View users list'],
            ['name' => 'users.show', 'description' => 'View specific user details'],
            ['name' => 'users.create', 'description' => 'Create new users'],
            ['name' => 'users.update', 'description' => 'Update user information'],
            ['name' => 'users.delete', 'description' => 'Delete users'],
            ['name' => 'users.assign_roles', 'description' => 'Assign roles to users'],
            ['name' => 'users.revoke_roles', 'description' => 'Revoke roles from users'],
            ['name' => 'users.assign_permissions', 'description' => 'Assign direct permissions to users'],
            ['name' => 'users.revoke_permissions', 'description' => 'Revoke direct permissions from users'],
            ['name' => 'users.change_password', 'description' => 'Change user passwords'],
            ['name' => 'users.export', 'description' => 'Export users data'],
            ['name' => 'users.import', 'description' => 'Import users data'],

            // Role Management
            ['name' => 'roles.view', 'description' => 'View roles list'],
            ['name' => 'roles.show', 'description' => 'View specific role details'],
            ['name' => 'roles.create', 'description' => 'Create new roles'],
            ['name' => 'roles.update', 'description' => 'Update role information'],
            ['name' => 'roles.delete', 'description' => 'Delete roles'],
            ['name' => 'roles.assign_permissions', 'description' => 'Assign permissions to roles'],
            ['name' => 'roles.revoke_permissions', 'description' => 'Revoke permissions from roles'],

            // Permission Management
            ['name' => 'permissions.view', 'description' => 'View permissions list'],
            ['name' => 'permissions.show', 'description' => 'View specific permission details'],
            ['name' => 'permissions.create', 'description' => 'Create new permissions'],
            ['name' => 'permissions.update', 'description' => 'Update permission information'],
            ['name' => 'permissions.delete', 'description' => 'Delete permissions'],

            // Profile Management
            ['name' => 'profile.view', 'description' => 'View own profile'],
            ['name' => 'profile.update', 'description' => 'Update own profile'],
            ['name' => 'profile.change_password', 'description' => 'Change own password'],

            // Dashboard & Reports
            ['name' => 'dashboard.view', 'description' => 'Access main dashboard'],
            ['name' => 'dashboard.admin', 'description' => 'Access admin dashboard'],
            ['name' => 'reports.view', 'description' => 'View reports'],
            ['name' => 'reports.create', 'description' => 'Create reports'],
            ['name' => 'reports.export', 'description' => 'Export reports'],

            // System Settings
            ['name' => 'settings.view', 'description' => 'View system settings'],
            ['name' => 'settings.update', 'description' => 'Update system settings'],
            ['name' => 'settings.advanced', 'description' => 'Access advanced settings'],

            // API Management
            ['name' => 'api.access', 'description' => 'Access API endpoints'],
            ['name' => 'api.admin', 'description' => 'Admin-level API access'],

            // File Management
            ['name' => 'files.view', 'description' => 'View files'],
            ['name' => 'files.upload', 'description' => 'Upload files'],
            ['name' => 'files.download', 'description' => 'Download files'],
            ['name' => 'files.delete', 'description' => 'Delete files'],

            // Audit & Logs
            ['name' => 'logs.view', 'description' => 'View system logs'],
            ['name' => 'audit.view', 'description' => 'View audit trail'],

            // Content Management
            ['name' => 'content.view', 'description' => 'View content'],
            ['name' => 'content.create', 'description' => 'Create content'],
            ['name' => 'content.update', 'description' => 'Update content'],
            ['name' => 'content.delete', 'description' => 'Delete content'],
            ['name' => 'content.publish', 'description' => 'Publish content'],

            // Communication
            ['name' => 'notifications.view', 'description' => 'View notifications'],
            ['name' => 'notifications.send', 'description' => 'Send notifications'],
            ['name' => 'messages.view', 'description' => 'View messages'],
            ['name' => 'messages.send', 'description' => 'Send messages'],

            // Restaurant Business Specific
            ['name' => 'orders.view', 'description' => 'View orders'],
            ['name' => 'orders.create', 'description' => 'Create orders'],
            ['name' => 'orders.update', 'description' => 'Update orders'],
            ['name' => 'orders.delete', 'description' => 'Delete orders'],
            ['name' => 'orders.approve', 'description' => 'Approve orders'],
            ['name' => 'orders.send_to_kitchen', 'description' => 'Send orders to kitchen'],
            ['name' => 'orders.mark_ready', 'description' => 'Mark orders as ready'],
            ['name' => 'orders.mark_delivered', 'description' => 'Mark orders as delivered'],

            ['name' => 'products.view', 'description' => 'View products/menu items'],
            ['name' => 'products.create', 'description' => 'Create products/menu items'],
            ['name' => 'products.update', 'description' => 'Update products/menu items'],
            ['name' => 'products.delete', 'description' => 'Delete products/menu items'],

            ['name' => 'customers.view', 'description' => 'View customers'],
            ['name' => 'customers.create', 'description' => 'Create customers'],
            ['name' => 'customers.update', 'description' => 'Update customers'],
            ['name' => 'customers.delete', 'description' => 'Delete customers'],

            ['name' => 'inventory.view', 'description' => 'View inventory'],
            ['name' => 'inventory.update', 'description' => 'Update inventory'],
            ['name' => 'inventory.reports', 'description' => 'View inventory reports'],
            ['name' => 'inventory.alerts', 'description' => 'Manage stock alerts'],

            // Tables Management
            ['name' => 'tables.view', 'description' => 'View restaurant tables'],
            ['name' => 'tables.create', 'description' => 'Create restaurant tables'],
            ['name' => 'tables.update', 'description' => 'Update restaurant tables'],
            ['name' => 'tables.delete', 'description' => 'Delete restaurant tables'],
            ['name' => 'tables.assign', 'description' => 'Assign tables to orders'],

            // Financial
            ['name' => 'finances.view', 'description' => 'View financial data'],
            ['name' => 'finances.manage', 'description' => 'Manage financial data'],
            ['name' => 'finances.reports', 'description' => 'View financial reports'],
            ['name' => 'cash.open_close', 'description' => 'Open and close cash register'],
            ['name' => 'payments.process', 'description' => 'Process payments'],
            ['name' => 'discounts.approve', 'description' => 'Approve discounts'],

            // Kitchen Management
            ['name' => 'kitchen.view_orders', 'description' => 'View kitchen orders'],
            ['name' => 'kitchen.manage_preparation', 'description' => 'Manage order preparation'],
            ['name' => 'kitchen.mark_ready', 'description' => 'Mark orders as ready in kitchen'],

            // Printing & Hardware
            ['name' => 'printing.tickets', 'description' => 'Print order tickets'],
            ['name' => 'printing.configure', 'description' => 'Configure printers'],
            ['name' => 'hardware.manage', 'description' => 'Manage hardware devices'],
            ['name' => 'network.configure', 'description' => 'Configure network settings'],

            // Team Management
            ['name' => 'teams.view', 'description' => 'View teams'],
            ['name' => 'teams.create', 'description' => 'Create teams'],
            ['name' => 'teams.update', 'description' => 'Update teams'],
            ['name' => 'teams.delete', 'description' => 'Delete teams'],
            ['name' => 'teams.assign_members', 'description' => 'Assign team members'],
        ];

        foreach ($permissions as $permission) {
            Permission::getByCodeOrCreate([
                'name' => $permission['name'],
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
                'name' => 'super_admin',
                'name' => 'Super Administrator',
                'description' => 'Has access to all system features and settings',
                'permissions' => 'all' // Special case for all permissions
            ],
            [
                'name' => 'owner',
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
                'name' => 'manager',
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
                'name' => 'cashier',
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
                'name' => 'chef',
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
                'name' => 'waiter',
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
                'name' => 'accountant',
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
                'name' => 'inventory_manager',
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
                'name' => 'technical_support',
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
                'name' => $roleData['name'],
                'guard_name' => 'api',
                'description' => $roleData['description'],
            ]);

            // Assign permissions to role
            if ($roleData['permissions'] === 'all') {
                // Super admin gets all permissions
                $role->givePermissionTo(Permission::where('guard_name', 'api')->get());
            } else {
                // Get permissions by name
                $permissions = Permission::whereIn('name', $roleData['permissions'])
                    ->where('guard_name', 'api')
                    ->get();
                
                $role->syncPermissions($permissions);
            }

            $this->command->line("   ✅ Created role '{$roleData['name']}' with " . 
                (is_array($roleData['permissions']) ? count($roleData['permissions']) : 'all') . " permissions");
        }
    }
}
