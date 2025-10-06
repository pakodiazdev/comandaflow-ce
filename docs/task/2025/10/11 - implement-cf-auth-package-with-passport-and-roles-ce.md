# 🔐 Implement CF Auth Package with Passport and Roles

## 📖 Story
As a developer, I need to centralize authentication and user management within a shared package (`cf-auth`), so that both **comandaflow-ce** and **comandaflow-saas** can reuse the same login, registration, and permission logic.

## 🧩 Context
The goal is to integrate **Laravel Passport** for OAuth2 authentication and use **Spatie Laravel Permission** for roles and permissions.  
The `cf-auth` package will be responsible for managing users, roles, and permissions across both projects.  

Additionally, immutable seeders must be created to register base roles in English (with lowercase `code` identifiers).

## ✅ Technical Tasks
- [x] 📦 Create a new package `cf-auth` inside `packages/`
  - [x] Configure PSR-4 namespace as `CF\CE\Auth`
- [x] 🔒 Install and configure **Laravel Passport**
  - [x] Run `composer require laravel/passport`
  - [x] Publish and migrate Passport tables
  - [x] Configure `HasApiTokens` trait on `User` model
- [x] ⚙️ Install and configure **Spatie Laravel Permission**
  - [x] Run `composer require spatie/laravel-permission`
  - [x] Publish and migrate permission tables
  - [x] Add `HasRoles` trait to `User` model
- [x] 🧱 Define entities in `cf-auth`:
  - [x] `User` model (extendable)
  - [x] `Role` and `Permission` models (from Spatie)
  - [x] Shared `AuthServiceProvider` and `Passport` configuration
- [x] 🌱 Create **immutable seeder** for base roles:
  - Each seeder must verify existence before insertion.
  - Use a `code` column (string, indexed, unique) as slug-like identifier.
  - Keep English lowercase names for `code` (e.g., `"owner"`, `"cashier"`).
  - Seeder should be idempotent (safe to re-run without duplicates).

### Base roles to seed:
| Code | Role name | Description | Main permissions | ✅ Status |
|------|------------|-------------|------------------|-----------|
| super-admin | Super Admin | System superadmin with full access | All 95 permissions | ✅ Implemented |
| owner | Owner / Admin | Company owner or general manager | 56 permissions including user management, reports | ✅ Implemented |
| manager | Manager / Supervisor | Responsible for daily operations | 27 permissions for operations, tables, sales | ✅ Implemented |
| cashier | Cashier | Handles payments and order registration | 12 permissions for orders, payments, cash | ✅ Implemented |
| chef | Chef / Kitchen | Manages order preparation | 7 permissions for kitchen operations | ✅ Implemented |
| waiter | Waiter | Takes and manages table orders | 10 permissions for orders, tables, customer service | ✅ Implemented |
| accountant | Accountant | Reviews finance and reports | 10 permissions for reports, finance, invoicing | ✅ Implemented |
| inventory-manager | Inventory Manager | Manages stock and supplies | 9 permissions for inventory, stock, costs | ✅ Implemented |
| technical-support | Technical Support | Tenant-level internal support | 5 permissions for system support | ✅ Implemented |

- [x] 🧪 Test seeder execution (`php artisan db:seed --class=RoleSeeder`)
- [x] 🧾 Document installation and usage steps inside `cf-auth/README.md`

## 🎯 **ADDITIONAL IMPLEMENTATIONS COMPLETED:**

### 🚀 **API Endpoints & Controllers**
- [x] **AuthController** with full Swagger documentation:
  - [x] `POST /auth/login` - User authentication with JWT tokens
  - [x] `POST /auth/register` - User registration
  - [x] `POST /auth/logout` - Token revocation
  - [x] `GET /auth/me` - Current user info with permissions
  - [x] `POST /auth/refresh` - Token refresh
- [x] **UserController** for user management:
  - [x] `GET /users` - Paginated user list with search
  - [x] `GET /users/{id}` - User details with permissions
  - [x] `PUT /users/{id}` - Update user information
  - [x] `DELETE /users/{id}` - Delete user
  - [x] `POST /users/{id}/roles` - Assign roles to user
- [x] **RoleController** for role management:
  - [x] `GET /roles` - List all roles with permissions
  - [x] `GET /roles/{id}` - Role details with permissions

### 🔐 **Advanced Permission System**
- [x] **95 granular permissions** created in slug format
- [x] **Direct permission assignment** to users (simplified approach)
- [x] **Guard 'api'** consistently configured across the system
- [x] **Permission caching** properly configured

### 👥 **Default Users System**
- [x] **DefaultUsersSeeder** creating 10 users with specific roles:
  - [x] SuperAdmin with all 95 permissions
  - [x] Owner with 56 permissions
  - [x] Manager with 27 permissions
  - [x] 7 additional users with role-specific permissions
- [x] **Secure default passwords** following strong password patterns
- [x] **User description field** added for better identification

### 🐳 **Docker Auto-Initialization**
- [x] **Complete `init.sh` script** with auto-execution of:
  - [x] Database migrations with `--force`
  - [x] Passport key generation
  - [x] All seeders with `--force`
  - [x] Permission cache management
- [x] **Vendor volume optimization** for Windows development
- [x] **Full container auto-setup** from `docker compose up -d`

### 📚 **OpenAPI Documentation**
- [x] **L5-Swagger integration** with complete API documentation
- [x] **Authentication schemas** with Bearer token examples
- [x] **Request/Response examples** for all endpoints
- [x] **Error handling documentation** with proper HTTP status codes

---

### 💡 Notes
- The seeder **never overwrites or duplicates** existing records — checks by `name` before insert.
- All permissions use **slug-style naming** for consistency (e.g., `users.view`, `orders.create`).
- **Direct permission assignment** implemented for simplified user management.
- **Guard 'api'** used consistently across the entire system for API-first approach.
- **Docker auto-initialization** ensures zero-configuration setup.

---

## 🎉 **IMPLEMENTATION SUMMARY**

### 🏆 **COMPLETE SUCCESS:**
✅ **CF-Auth package** fully implemented and operational  
✅ **Laravel Passport** OAuth2 authentication working  
✅ **Spatie Permission** system with 95 granular permissions  
✅ **9 roles + 10 default users** auto-created  
✅ **Full API** with Swagger documentation  
✅ **Docker auto-initialization** from scratch  
✅ **Production-ready** authentication system  

### 🚀 **Quick Start:**
```bash
# Complete system initialization
docker compose down -v && docker compose up -d

# Test authentication
curl -X POST http://localhost/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "superadmin@comandaflow.com", "password": "SuperAdmin123!"}'

# Access API documentation
open http://localhost/api/documentation
```

### 🔐 **Default Credentials:**
- **SuperAdmin:** `superadmin@comandaflow.com` / `SuperAdmin123!` (95 permissions)
- **Owner:** `owner@comandaflow.com` / `Owner123!` (56 permissions)  
- **Manager:** `manager@comandaflow.com` / `Manager123!` (27 permissions)
- **+ 7 additional users** with role-specific permissions

---

## ⏱️ Time
### 📊 Estimates
- **Optimistic:** `8 hours`
- **Pessimistic:** `16 hours`
- **Tracked:** `6h 9m`

### 📅 Sessions
```json
[
    {"date": "2025-10-05", "start": "21:20", "end": "23:59"},
    {"date": "2025-10-06", "start": "00:00", "end": "02:00"},
    {"date": "2025-10-06", "start": "09:30", "end": "11:00"}
]
```