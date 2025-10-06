<?php

namespace Database\Seeders;

use CF\CE\Auth\Models\Permission;
use CF\CE\Auth\Models\Role;
use CF\CE\Auth\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🔧 Creando usuarios por defecto...');

        // Obtener todos los permisos y roles para asignación
        $allPermissions = Permission::all();
        $roles = Role::with('permissions')->get();

        // Crear SuperAdmin con todos los permisos
        $this->createUser(
            'SuperAdmin System',
            'superadmin@comandaflow.com',
            'SuperAdmin123!',
            $allPermissions->pluck('name')->toArray(),
            'Usuario superadministrador del sistema con acceso total'
        );

        // Crear Owner con permisos de owner
        $ownerRole = $roles->firstWhere('name', 'owner');
        if ($ownerRole) {
            $this->createUser(
                'Restaurant Owner',
                'owner@comandaflow.com',
                'Owner123!',
                $ownerRole->permissions->pluck('name')->toArray(),
                'Propietario del restaurante con acceso completo al negocio'
            );
        }

        // Crear Manager con permisos de manager
        $managerRole = $roles->firstWhere('name', 'manager');
        if ($managerRole) {
            $this->createUser(
                'Restaurant Manager',
                'manager@comandaflow.com',
                'Manager123!',
                $managerRole->permissions->pluck('name')->toArray(),
                'Gerente del restaurante con acceso administrativo'
            );
        }

        // Crear Head Chef con permisos de chef
        $chefRole = $roles->firstWhere('name', 'chef');
        if ($chefRole) {
            $this->createUser(
                'Head Chef',
                'headchef@comandaflow.com',
                'HeadChef123!',
                $chefRole->permissions->pluck('name')->toArray(),
                'Chef principal con acceso completo a la cocina'
            );
        }

        // Crear Waiter con permisos de waiter
        $waiterRole = $roles->firstWhere('name', 'waiter');
        if ($waiterRole) {
            $this->createUser(
                'Main Waiter',
                'waiter@comandaflow.com',
                'Waiter123!',
                $waiterRole->permissions->pluck('name')->toArray(),
                'Mesero principal del restaurante'
            );
        }

        // Crear Cashier con permisos de cashier
        $cashierRole = $roles->firstWhere('name', 'cashier');
        if ($cashierRole) {
            $this->createUser(
                'Main Cashier',
                'cashier@comandaflow.com',
                'Cashier123!',
                $cashierRole->permissions->pluck('name')->toArray(),
                'Cajero principal del restaurante'
            );
        }

        // Crear Kitchen Staff con permisos de accountant
        $kitchenRole = $roles->firstWhere('name', 'accountant');
        if ($kitchenRole) {
            $this->createUser(
                'Kitchen Staff',
                'kitchen@comandaflow.com',
                'Kitchen123!',
                $kitchenRole->permissions->pluck('name')->toArray(),
                'Personal de cocina'
            );
        }

        // Crear Host con permisos de waiter
        $hostRole = $roles->firstWhere('name', 'waiter');
        if ($hostRole) {
            $this->createUser(
                'Restaurant Host',
                'host@comandaflow.com',
                'Host123!',
                $hostRole->permissions->pluck('name')->toArray(),
                'Anfitrión del restaurante'
            );
        }

        // Crear Delivery con permisos de waiter
        $deliveryRole = $roles->firstWhere('name', 'waiter');
        if ($deliveryRole) {
            $this->createUser(
                'Delivery Person',
                'delivery@comandaflow.com',
                'Delivery123!',
                $deliveryRole->permissions->pluck('name')->toArray(),
                'Personal de delivery'
            );
        }

        // Crear Technical Support con permisos básicos
        $techRole = $roles->firstWhere('name', 'technical-support');
        if ($techRole) {
            $this->createUser(
                'Tech Support',
                'support@comandaflow.com',
                'Support123!',
                $techRole->permissions->pluck('name')->toArray(),
                'Soporte técnico del sistema'
            );
        }

        $this->command->info('✅ Usuarios por defecto creados exitosamente');
        $this->command->info('');
        $this->command->warn('📧 Credenciales de acceso:');
        $this->command->warn('SuperAdmin: superadmin@comandaflow.com / SuperAdmin123!');
        $this->command->warn('Owner: owner@comandaflow.com / Owner123!');
        $this->command->warn('Manager: manager@comandaflow.com / Manager123!');
        $this->command->warn('Head Chef: headchef@comandaflow.com / HeadChef123!');
        $this->command->warn('Waiter: waiter@comandaflow.com / Waiter123!');
        $this->command->warn('Cashier: cashier@comandaflow.com / Cashier123!');
        $this->command->warn('Kitchen: kitchen@comandaflow.com / Kitchen123!');
        $this->command->warn('Host: host@comandaflow.com / Host123!');
        $this->command->warn('Delivery: delivery@comandaflow.com / Delivery123!');
        $this->command->warn('Support: support@comandaflow.com / Support123!');
        $this->command->warn('');
    }

    /**
     * Crear un usuario con permisos específicos
     */
    private function createUser(string $name, string $email, string $password, array $permissions, string $description)
    {
        // Verificar si el usuario ya existe
        $existingUser = User::where('email', $email)->first();
        
        if ($existingUser) {
            $this->command->warn("⚠️  Usuario {$email} ya existe, actualizando permisos...");
            $user = $existingUser;
        } else {
            $this->command->info("👤 Creando usuario: {$email}");
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
        }

        // Asignar permisos directamente al usuario (no roles)
        if (!empty($permissions)) {
            // Limpiar permisos existentes
            $user->permissions()->detach();
            
            // Asignar nuevos permisos
            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $user->givePermissionTo($permission);
                }
            }
            
            $this->command->info("✅ Asignados " . count($permissions) . " permisos a {$name}");
        }

        // Agregar descripción como atributo personalizado si es necesario
        $user->update(['description' => $description]);
    }
}