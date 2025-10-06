# 🔗 CF Auth API Endpoints

This document describes all available API endpoints provided by the CF Auth package.

## 🔐 Authentication Endpoints

### Login
- **POST** `/auth/login`
- **Description**: Authenticate user and return access token
- **Body**: `{ "email": "user@example.com", "password": "password123" }`
- **Response**: User data with access token

### Register
- **POST** `/auth/register`
- **Description**: Register a new user account
- **Body**: `{ "name": "John Doe", "email": "user@example.com", "password": "password123", "password_confirmation": "password123", "role": "cashier" }`
- **Response**: User data with access token

### Logout
- **POST** `/auth/logout`
- **Description**: Revoke the current access token
- **Headers**: `Authorization: Bearer {token}`
- **Response**: Success message

### Get Current User
- **GET** `/auth/me`
- **Description**: Get authenticated user's information
- **Headers**: `Authorization: Bearer {token}`
- **Response**: User data with roles and permissions

### Refresh Token
- **POST** `/auth/refresh`
- **Description**: Refresh the current access token
- **Headers**: `Authorization: Bearer {token}`
- **Response**: New access token

## 👥 User Management Endpoints

*All user management endpoints require Owner or Manager role*

### List Users
- **GET** `/users`
- **Description**: Get paginated list of all users
- **Headers**: `Authorization: Bearer {token}`
- **Query Parameters**: `page`, `per_page`, `search`
- **Response**: Paginated list of users with roles

### Get User by ID
- **GET** `/users/{id}`
- **Description**: Get specific user with roles and permissions
- **Headers**: `Authorization: Bearer {token}`
- **Response**: User data with roles and permissions

### Update User
- **PUT** `/users/{id}`
- **Description**: Update user information and roles
- **Headers**: `Authorization: Bearer {token}`
- **Body**: `{ "name": "Updated Name", "email": "new@email.com", "password": "newpassword", "roles": ["manager", "cashier"] }`
- **Response**: Updated user data

### Delete User
- **DELETE** `/users/{id}`
- **Description**: Delete a user account
- **Headers**: `Authorization: Bearer {token}`
- **Response**: Success message

### Assign Roles to User
- **POST** `/users/{id}/roles`
- **Description**: Assign one or more roles to a user
- **Headers**: `Authorization: Bearer {token}`
- **Body**: `{ "roles": ["manager", "cashier"] }`
- **Response**: User data with updated roles

## 🎭 Role Management Endpoints

*All role management endpoints require Owner role*

### List Roles
- **GET** `/roles`
- **Description**: Get all available roles with permissions
- **Headers**: `Authorization: Bearer {token}`
- **Response**: List of roles with permissions

### Get Role by Code
- **GET** `/roles/{code}`
- **Description**: Get specific role by code
- **Headers**: `Authorization: Bearer {token}`
- **Response**: Role data with permissions

### Create Role
- **POST** `/roles`
- **Description**: Create a new role with permissions
- **Headers**: `Authorization: Bearer {token}`
- **Body**: `{ "name": "Custom Role", "code": "custom_role", "description": "Custom role description", "permissions": ["manage_tables", "process_payments"] }`
- **Response**: Created role data

### Update Role
- **PUT** `/roles/{code}`
- **Description**: Update role information and permissions
- **Headers**: `Authorization: Bearer {token}`
- **Body**: `{ "name": "Updated Role", "description": "Updated description", "permissions": ["manage_tables"] }`
- **Response**: Updated role data

### Delete Role
- **DELETE** `/roles/{code}`
- **Description**: Delete a role (only if no users assigned)
- **Headers**: `Authorization: Bearer {token}`
- **Response**: Success message

## 🔑 Permission Endpoints

### List Permissions
- **GET** `/permissions`
- **Description**: Get all available permissions
- **Headers**: `Authorization: Bearer {token}`
- **Response**: List of permissions

## 🛡️ Middleware Usage

### Role-based Access
Use the `cf-role` middleware to restrict access by roles:

```php
Route::middleware(['cf-role:owner,manager'])->group(function () {
    // Only owners and managers can access these routes
});
```

### Permission-based Access
Use the `cf-permission` middleware to restrict access by permissions:

```php
Route::middleware(['cf-permission:manage_users,manage_roles'])->group(function () {
    // Only users with these permissions can access
});
```

## 📝 Example Usage

### 1. User Login
```bash
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com", "password": "password123"}'
```

### 2. Get Current User
```bash
curl -X GET http://localhost/api/auth/me \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### 3. List Users (Owner/Manager only)
```bash
curl -X GET http://localhost/api/users \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN"
```

### 4. Create Role (Owner only)
```bash
curl -X POST http://localhost/api/roles \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name": "Custom Role", "code": "custom_role", "description": "Custom role", "permissions": ["manage_tables"]}'
```

## 🔧 Configuration

### Token Expiration
- **Access Token**: 15 days
- **Refresh Token**: 30 days
- **Personal Access Token**: 6 months

### Base Roles Available
- `owner` - Full system access
- `manager` - Daily operations management
- `cashier` - Payment and order handling
- `chef` - Order preparation
- `waiter` - Table service
- `accountant` - Finance and reports
- `inventory_manager` - Stock management
- `technical_support` - System maintenance

## 📚 Swagger Documentation

The API includes complete Swagger/OpenAPI documentation. Access it at:
- **Swagger UI**: `/api/documentation`
- **OpenAPI JSON**: `/api/documentation.json`

All endpoints include detailed request/response examples and parameter descriptions.
