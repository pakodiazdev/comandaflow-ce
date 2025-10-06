<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CF Auth Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the CF Auth package
    |
    */

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model class to use for authentication
    |
    */
    'user_model' => \CF\CE\Auth\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Role Model
    |--------------------------------------------------------------------------
    |
    | The role model class to use for permissions
    |
    */
    'role_model' => \CF\CE\Auth\Models\Role::class,

    /*
    |--------------------------------------------------------------------------
    | Permission Model
    |--------------------------------------------------------------------------
    |
    | The permission model class to use for permissions
    |
    */
    'permission_model' => \CF\CE\Auth\Models\Permission::class,

    /*
    |--------------------------------------------------------------------------
    | Passport Configuration
    |--------------------------------------------------------------------------
    |
    | Laravel Passport configuration
    |
    */
    'passport' => [
        'token_expires_in' => 15, // days
        'refresh_token_expires_in' => 30, // days
        'personal_access_token_expires_in' => 6, // months
    ],

    /*
    |--------------------------------------------------------------------------
    | Base Roles
    |--------------------------------------------------------------------------
    |
    | Base roles that will be seeded automatically
    |
    */
    'base_roles' => [
        'owner',
        'manager',
        'cashier',
        'chef',
        'waiter',
        'accountant',
        'inventory_manager',
        'technical_support',
    ],

    /*
    |--------------------------------------------------------------------------
    | Guard Configuration
    |--------------------------------------------------------------------------
    |
    | Default guard to use for authentication
    |
    */
    'guard' => 'web',
];
