# 🔐 CF Auth Package

Centralized authentication and user management package for ComandaFlow with Laravel Passport and Spatie Laravel Permission.

## 📦 Features

- **Laravel Passport** for OAuth2 authentication
- **Spatie Laravel Permission** for roles and permissions
- **Immutable seeders** for base roles
- **Code-based role identification** for easy management
- **Extensible User model** with role management methods

## 🚀 Installation

### 1. Install Dependencies

```bash
composer require laravel/passport
composer require spatie/laravel-permission
```

### 2. Publish and Run Migrations

```bash
# Publish CF Auth migrations
php artisan vendor:publish --tag=cf-auth-migrations

# Run migrations
php artisan migrate

# Install Passport
php artisan passport:install
```

### 3. Publish Configuration

```bash
php artisan vendor:publish --tag=cf-auth-config
```

### 4. Seed Base Roles

```bash
php artisan db:seed --class="CF\CE\Auth\Database\Seeders\RoleSeeder"
```

## 🏗️ Usage

### User Model

The package provides an extended User model with role management capabilities:

```php
use CF\CE\Auth\Models\User;

// Check if user has role by code
$user->hasRoleByCode('owner');

// Get user's role codes
$user->getRoleCodes(); // ['owner', 'manager']

// Assign role by code
$user->assignRoleByCode('cashier');

// Remove role by code
$user->removeRoleByCode('waiter');
```

### Role Management

```php
use CF\CE\Auth\Models\Role;

// Find role by code
$role = Role::findByCode('owner');

// Create role with code
$role = Role::createWithCode([
    'name' => 'Custom Role',
    'code' => 'custom_role',
    'description' => 'Custom role description',
    'guard_name' => 'web'
]);

// Get or create role
$role = Role::getByCodeOrCreate([
    'name' => 'New Role',
    'code' => 'new_role',
    'description' => 'New role description'
]);
```

### Permission Management

```php
use CF\CE\Auth\Models\Permission;

// Find permission by code
$permission = Permission::findByCode('manage_users');

// Create permission with code
$permission = Permission::createWithCode([
    'name' => 'custom_permission',
    'code' => 'custom_permission',
    'description' => 'Custom permission description',
    'guard_name' => 'web'
]);
```

## 🎭 Base Roles

The package includes 8 base roles with their respective permissions:

| Code | Role Name | Description | Key Permissions |
|------|-----------|-------------|-----------------|
| `owner` | Owner / Admin | Company owner or general manager | All permissions |
| `manager` | Manager / Supervisor | Daily operations manager | Manage tables, sales, cash, stock |
| `cashier` | Cashier | Payment and order handling | Register orders, process payments |
| `chef` | Chef / Kitchen | Order preparation | Receive orders, mark as ready |
| `waiter` | Waiter | Table order management | Create orders, assign tables |
| `accountant` | Accountant | Finance and reports | Access reports, view sales |
| `inventory_manager` | Inventory Manager | Stock and supplies | Register inputs/outputs, track costs |
| `technical_support` | Technical Support | Hardware and network | Handle hardware, configure systems |

## 🔧 Configuration

### Passport Configuration

The package automatically configures Laravel Passport with sensible defaults:

- **Token Expiration**: 15 days
- **Refresh Token Expiration**: 30 days
- **Personal Access Token Expiration**: 6 months

### Custom Configuration

You can customize the configuration by publishing the config file:

```bash
php artisan vendor:publish --tag=cf-auth-config
```

Then modify `config/cf-auth.php` as needed.

## 🔗 API Endpoints

The package provides a complete REST API with the following endpoints:

### Authentication
- `POST /auth/login` - User login
- `POST /auth/register` - User registration  
- `POST /auth/logout` - User logout
- `GET /auth/me` - Get current user
- `POST /auth/refresh` - Refresh access token

### User Management (Owner/Manager only)
- `GET /users` - List users
- `GET /users/{id}` - Get user by ID
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user
- `POST /users/{id}/roles` - Assign roles to user

### Role Management (Owner only)
- `GET /roles` - List roles
- `GET /roles/{code}` - Get role by code
- `POST /roles` - Create role
- `PUT /roles/{code}` - Update role
- `DELETE /roles/{code}` - Delete role

### Permissions
- `GET /permissions` - List permissions

All endpoints include complete Swagger documentation. See [ENDPOINTS.md](ENDPOINTS.md) for detailed usage examples.

## 🧪 Testing

### Test Seeder Execution

```bash
php artisan db:seed --class="CF\CE\Auth\Database\Seeders\RoleSeeder"
```

### Verify Roles

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

## 🔄 Idempotent Seeding

The RoleSeeder is designed to be **idempotent** - it can be run multiple times without creating duplicates:

- Checks for existing roles by `code` before creation
- Only creates roles that don't already exist
- Safe to run in production environments

## 📝 API Integration

### OAuth2 Endpoints

The package provides standard OAuth2 endpoints through Laravel Passport:

- `POST /oauth/token` - Get access token
- `POST /oauth/refresh` - Refresh access token
- `GET /oauth/user` - Get authenticated user
- `POST /oauth/revoke` - Revoke token

### Role-based Access Control

```php
// In your API routes
Route::middleware(['auth:api'])->group(function () {
    Route::get('/admin/users', function () {
        // Only users with 'owner' or 'manager' role can access
    })->middleware('role:owner|manager');
    
    Route::get('/cashier/orders', function () {
        // Only cashiers can access
    })->middleware('role:cashier');
});
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

For support, please open an issue in the repository or contact the development team.
