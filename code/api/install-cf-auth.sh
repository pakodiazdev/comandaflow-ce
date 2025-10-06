#!/bin/bash

echo "🚀 Installing CF Auth Package..."

# Install dependencies
echo "📦 Installing dependencies..."
composer install

# Publish migrations
echo "📄 Publishing migrations..."
php artisan vendor:publish --tag=cf-auth-migrations --force

# Publish config
echo "⚙️ Publishing configuration..."
php artisan vendor:publish --tag=cf-auth-config --force

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate

# Install Passport
echo "🔑 Installing Laravel Passport..."
php artisan passport:install --force

# Seed base roles
echo "🌱 Seeding base roles..."
php artisan db:seed --class="CF\CE\Auth\Database\Seeders\RoleSeeder"

# Test installation
echo "🧪 Testing installation..."
php artisan cf-auth:test-roles

echo "✅ CF Auth package installed successfully!"
echo ""
echo "📚 Available endpoints:"
echo "  POST /auth/login - User login"
echo "  POST /auth/register - User registration"
echo "  POST /auth/logout - User logout"
echo "  GET  /auth/me - Get current user"
echo "  POST /auth/refresh - Refresh token"
echo "  GET  /users - List users (Owner/Manager only)"
echo "  GET  /users/{id} - Get user by ID (Owner/Manager only)"
echo "  PUT  /users/{id} - Update user (Owner/Manager only)"
echo "  DELETE /users/{id} - Delete user (Owner/Manager only)"
echo "  POST /users/{id}/roles - Assign roles to user (Owner/Manager only)"
echo "  GET  /roles - List roles (Owner only)"
echo "  GET  /roles/{code} - Get role by code (Owner only)"
echo "  POST /roles - Create role (Owner only)"
echo "  PUT  /roles/{code} - Update role (Owner only)"
echo "  DELETE /roles/{code} - Delete role (Owner only)"
echo "  GET  /permissions - List permissions"
echo ""
echo "📖 Documentation available at: /api/documentation"
echo "🔧 Test the installation with: php artisan cf-auth:test-roles"
