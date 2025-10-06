# 🚀 CF Auth Package - Quick Start Guide

## 📋 Installation Checklist

### ✅ Prerequisites Completed
- [x] Package `cf-auth` created and configured
- [x] Laravel Passport installed and configured  
- [x] Spatie Laravel Permission integrated
- [x] Service Provider registered in `bootstrap/providers.php`
- [x] User model extends `CF\CE\Auth\Models\User`
- [x] L5-Swagger configured to include `packages/cf-auth/src`
- [x] Complete API documentation with Swagger annotations

## 🛠️ Setup Commands

### 1. Install Package (Automated)
```bash
# Install everything automatically
php artisan cf-auth:install --seed

# Or install without seeding roles
php artisan cf-auth:install
```

### 2. Manual Installation Steps
```bash
# 1. Publish migrations
php artisan vendor:publish --tag="cf-auth-migrations"

# 2. Run migrations
php artisan migrate

# 3. Install Passport
php artisan passport:install --force

# 4. Seed base roles
php artisan db:seed --class="CF\\CE\\Auth\\Database\\Seeders\\RoleSeeder"

# 5. Generate Swagger documentation
php artisan l5-swagger:generate
```

## 🔑 API Authentication Flow

### 1. User Registration
```bash
curl -X POST http://localhost/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Admin User",
    "email": "admin@comandaflow.com",
    "password": "password123",
    "password_confirmation": "password123",
    "role": "owner"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@comandaflow.com",
      "roles": ["owner"]
    },
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "token_type": "Bearer",
    "expires_in": 1296000
  }
}
```

### 2. User Login
```bash
curl -X POST http://localhost/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@comandaflow.com",
    "password": "password123"
  }'
```

### 3. Access Protected Endpoints
```bash
# Get current user info
curl -X GET http://localhost/auth/me \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"

# List all users (requires owner/manager role)
curl -X GET http://localhost/users \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"

# Get all roles (requires owner role)
curl -X GET http://localhost/roles \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

## 🔐 Available Endpoints

### Public Endpoints
- `POST /auth/login` - Authenticate user
- `POST /auth/register` - Register new user

### Protected Endpoints (Requires Authentication)
- `POST /auth/logout` - Logout user
- `GET /auth/me` - Get current user info
- `POST /auth/refresh` - Refresh access token

### User Management (Owner/Manager Role Required)
- `GET /users` - List users with pagination and search
- `GET /users/{id}` - Get user details
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user
- `POST /users/{id}/roles` - Assign roles to user

### Role Management (Owner Role Required)
- `GET /roles` - List all roles with permissions
- `GET /roles/{code}` - Get role by code
- `POST /roles` - Create new role
- `PUT /roles/{code}` - Update role
- `DELETE /roles/{code}` - Delete role
- `GET /permissions` - List all permissions

## 👑 Built-in Roles & Permissions

| Role Code | Name | Description | Key Permissions |
|-----------|------|-------------|-----------------|
| `owner` | Owner / Admin | Full system access | All permissions |
| `manager` | Manager / Supervisor | Operations management | Most operational permissions |
| `cashier` | Cashier | Payment handling | Orders, payments, cash management |
| `chef` | Chef / Kitchen | Order preparation | Kitchen operations |
| `waiter` | Waiter | Table service | Orders, tables, customer service |
| `accountant` | Accountant | Financial reports | Reporting, invoicing, taxes |
| `inventory_manager` | Inventory Manager | Stock management | Inventory, costs, supplies |
| `technical_support` | Technical Support | System support | Hardware, configuration |

## 🛡️ Middleware Usage

### Route Protection Examples
```php
// Require specific roles
Route::middleware('cf-role:owner,manager')->group(function () {
    Route::get('/admin-panel', [AdminController::class, 'index']);
});

// Require specific permissions
Route::middleware('cf-permission:manage_users')->group(function () {
    Route::post('/users', [UserController::class, 'store']);
});

// Combine authentication and role
Route::middleware(['auth:api', 'cf-role:owner'])->group(function () {
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
```

### User Role Checking in Code
```php
// Check if user has role
if ($user->hasRoleByCode('owner')) {
    // Owner-specific logic
}

// Get user's role codes
$roles = $user->getRoleCodes(); // ['owner', 'manager']

// Assign role to user
$user->assignRoleByCode('cashier');

// Remove role from user
$user->removeRoleByCode('waiter');
```

## 📚 Swagger Documentation

### Access Points
- **Swagger UI**: `http://localhost/api/v1/doc`
- **JSON Schema**: `http://localhost/api/v1/doc/docs.json`
- **YAML Schema**: `http://localhost/api/v1/doc/api-docs.yaml`

### Generate Documentation
```bash
# Generate/regenerate Swagger docs
php artisan l5-swagger:generate

# Clear and regenerate
php artisan l5-swagger:generate --force
```

## 🧪 Testing Examples

### Create Manager User
```bash
curl -X POST http://localhost/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Restaurant Manager",
    "email": "manager@restaurant.com",
    "password": "manager123",
    "password_confirmation": "manager123",
    "role": "manager"
  }'
```

### Assign Multiple Roles
```bash
curl -X POST http://localhost/users/2/roles \
  -H "Authorization: Bearer YOUR_OWNER_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "roles": ["manager", "cashier"]
  }'
```

### Search Users
```bash
curl -X GET "http://localhost/users?search=manager&per_page=10" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 📁 Project Structure Summary

```
packages/cf-auth/
├── src/
│   ├── AuthServiceProvider.php          # Main service provider
│   ├── Models/
│   │   ├── User.php                     # Extended user model
│   │   ├── Role.php                     # Custom role model  
│   │   └── Permission.php               # Custom permission model
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php       # Authentication endpoints
│   │   │   ├── UserController.php       # User management
│   │   │   └── RoleController.php       # Role management
│   │   └── Middleware/
│   │       ├── RoleMiddleware.php       # Role checking
│   │       └── PermissionMiddleware.php # Permission checking
│   └── Commands/
│       ├── InstallAuthCommand.php       # Installation command
│       └── TestRolesCommand.php         # Testing command
├── routes/
│   └── api.php                          # API routes
├── database/
│   ├── migrations/                      # Custom migrations
│   └── seeders/
│       └── RoleSeeder.php              # Role seeding
├── config/
│   └── cf-auth.php                     # Package configuration
└── composer.json                       # Package definition
```

## ✨ Key Features Implemented

- ✅ **OAuth2 Authentication** with Laravel Passport
- ✅ **Role-Based Access Control** with Spatie Permission
- ✅ **RESTful API Design** with consistent responses
- ✅ **Complete Swagger Documentation** with examples
- ✅ **Middleware Protection** for routes and controllers
- ✅ **Immutable Role Seeding** with predefined roles
- ✅ **Token Management** with refresh capabilities
- ✅ **User Management** with role assignment
- ✅ **Search and Pagination** for user listings
- ✅ **Automated Installation** with artisan commands

## 🎯 Ready for Production!

The CF Auth package is fully implemented and ready to use. All endpoints are documented, tested, and follow Laravel best practices. The authentication system is secure, scalable, and provides all the functionality needed for a restaurant management system.

Start by creating your first owner user and explore the API through Swagger UI! 🚀