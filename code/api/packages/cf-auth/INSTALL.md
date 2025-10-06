# 🚀 CF Auth Installation Guide

This guide will help you install and configure the CF Auth package in your Laravel application.

## 📋 Prerequisites

- Laravel 12.x
- PHP 8.2+
- PostgreSQL (recommended) or MySQL
- Composer

## 🔧 Installation Steps

### 1. Install Dependencies

The package dependencies are already included in the main `composer.json`, but you can install them manually:

```bash
composer require laravel/passport
composer require spatie/laravel-permission
```

### 2. Install CF Auth Package

```bash
# Install the package (if not already done)
composer install

# Run the installation command
php artisan cf-auth:install --seed
```

This command will:
- ✅ Publish migrations
- ✅ Publish configuration
- ✅ Run migrations
- ✅ Install Laravel Passport
- ✅ Seed base roles and permissions

### 3. Manual Installation (Alternative)

If you prefer manual installation:

```bash
# Publish migrations
php artisan vendor:publish --tag=cf-auth-migrations

# Publish config
php artisan vendor:publish --tag=cf-auth-config

# Run migrations
php artisan migrate

# Install Passport
php artisan passport:install

# Seed base roles
php artisan db:seed --class="CF\CE\Auth\Database\Seeders\RoleSeeder"
```

## 🧪 Testing Installation

### Test Roles and Permissions

```bash
php artisan cf-auth:test-roles
```

This command will verify that all base roles exist and show their permissions.

### Manual Verification

```php
use CF\CE\Auth\Models\Role;

// Check if all base roles exist
$baseRoles = ['owner', 'manager', 'cashier', 'chef', 'waiter', 'accountant', 'inventory_manager', 'technical_support'];

foreach ($baseRoles as $code) {
    $role = Role::findByCode($code);
    if ($role) {
        echo "✅ Role '{$code}' exists with " . $role->permissions->count() . " permissions\n";
    } else {
        echo "❌ Role '{$code}' not found\n";
    }
}
```

## 🔑 OAuth2 Endpoints

After installation, the following OAuth2 endpoints will be available:

- `POST /oauth/token` - Get access token
- `POST /oauth/refresh` - Refresh access token  
- `GET /oauth/user` - Get authenticated user
- `POST /oauth/revoke` - Revoke token

## 👥 User Management

### Create User with Role

```php
use App\Models\User;
use CF\CE\Auth\Models\Role;

// Create user
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => bcrypt('password'),
]);

// Assign role by code
$user->assignRoleByCode('manager');

// Check role
if ($user->hasRoleByCode('manager')) {
    echo "User is a manager";
}
```

### Role Management

```php
use CF\CE\Auth\Models\Role;

// Find role by code
$role = Role::findByCode('owner');

// Create custom role
$role = Role::createWithCode([
    'name' => 'Custom Role',
    'code' => 'custom_role',
    'description' => 'Custom role description',
    'guard_name' => 'web'
]);
```

## 🛡️ Middleware Usage

### API Routes with Role Protection

```php
// In routes/api.php
Route::middleware(['auth:api'])->group(function () {
    // Admin only
    Route::get('/admin/users', function () {
        return response()->json(['message' => 'Admin access granted']);
    })->middleware('role:owner');
    
    // Manager or Owner
    Route::get('/management/reports', function () {
        return response()->json(['message' => 'Management access granted']);
    })->middleware('role:owner|manager');
    
    // Cashier operations
    Route::post('/cashier/orders', function () {
        return response()->json(['message' => 'Order created']);
    })->middleware('role:cashier');
});
```

## 🔧 Configuration

### Passport Configuration

The package automatically configures Laravel Passport with these defaults:

- **Token Expiration**: 15 days
- **Refresh Token Expiration**: 30 days  
- **Personal Access Token Expiration**: 6 months

### Custom Configuration

You can customize the configuration by editing `config/cf-auth.php`:

```php
return [
    'passport' => [
        'token_expires_in' => 30, // days
        'refresh_token_expires_in' => 60, // days
        'personal_access_token_expires_in' => 12, // months
    ],
    // ... other configuration
];
```

## 🎭 Base Roles Overview

| Code | Role | Description | Key Permissions |
|------|------|-------------|-----------------|
| `owner` | Owner/Admin | Full system access | All permissions |
| `manager` | Manager | Daily operations | Tables, sales, cash, stock |
| `cashier` | Cashier | Payment handling | Orders, payments, cash |
| `chef` | Chef/Kitchen | Order preparation | Receive orders, mark ready |
| `waiter` | Waiter | Table service | Create orders, assign tables |
| `accountant` | Accountant | Finance management | Reports, sales, invoicing |
| `inventory_manager` | Inventory | Stock management | Inputs/outputs, costs, alerts |
| `technical_support` | Tech Support | System maintenance | Hardware, printing, network |

## 🚨 Troubleshooting

### Common Issues

1. **Migration errors**: Make sure to run `php artisan migrate` after publishing migrations
2. **Passport not working**: Ensure `php artisan passport:install` was run
3. **Roles not found**: Run the seeder: `php artisan db:seed --class="CF\CE\Auth\Database\Seeders\RoleSeeder"`

### Reset Installation

If you need to reset the installation:

```bash
# Rollback migrations
php artisan migrate:rollback

# Re-run installation
php artisan cf-auth:install --seed
```

## 📚 Next Steps

1. **Configure API routes** with role-based middleware
2. **Set up frontend authentication** using OAuth2 tokens
3. **Customize permissions** for your specific use case
4. **Implement user management** interface
5. **Add role-based UI components**

## 🆘 Support

For issues or questions:
- Check the [README.md](README.md) for detailed documentation
- Run `php artisan cf-auth:test-roles` to verify installation
- Review Laravel Passport and Spatie Permission documentation
