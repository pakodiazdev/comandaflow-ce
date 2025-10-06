# 🌱 Database Seeders Documentation

## Overview

The seeding system is organized into three distinct categories, each serving a specific purpose in the application lifecycle.

## 📂 Directory Structure

```
database/seeders/
├── DatabaseSeeder.php          # Main orchestrator
├── ProductionSeeders/          # Immutable base data
│   ├── RoleSeeder.php
│   ├── PassportClientSeeder.php
│   ├── PassportSeeder.php
│   └── DefaultUsersSeeder.php
├── DevSeeders/                 # Development/testing data
│   └── (future dev seeders)
└── FakeSeeders/                # Mass fake data
    └── (future fake data seeders)
```

## 🎯 Seeder Categories

### 1. Production Seeders (`ProductionSeeders/`)

**Purpose:** Populate essential base data required for the application to function.

**Characteristics:**
- ✅ **Always executed** in all environments
- 🔒 **Idempotent** - safe to run multiple times
- 🏗️ **Immutable** - creates core catalogs and configurations
- 🚀 **Auto-executed** during Docker container initialization

**Current Seeders:**
- `RoleSeeder.php` - Creates 9 roles and 95 permissions
- `PassportClientSeeder.php` - Creates OAuth2 clients
- `PassportSeeder.php` - Sets up Passport personal access client
- `DefaultUsersSeeder.php` - Creates 10 default users with permissions

**When to use:**
- Core roles and permissions
- System configurations
- Essential default users
- OAuth clients
- Base catalogs required for the app to work

### 2. Development Seeders (`DevSeeders/`)

**Purpose:** Generate sample data to facilitate development and testing.

**Characteristics:**
- 🧪 **Only executed** in `local`, `development`, or `testing` environments
- 📝 **Idempotent** - checks if data exists before creating
- 🛠️ **One-time** - designed to run once per environment setup
- ⏭️ **Skipped** in production

**When to use:**
- Sample orders for testing workflows
- Test customers with various states
- Example products or menu items
- Mock data for specific features under development

**Example usage:**
```bash
# Automatically runs in dev/testing environments
php artisan db:seed
```

### 3. Fake Data Seeders (`FakeSeeders/`)

**Purpose:** Generate large volumes of realistic fake data for UI/UX and performance testing.

**Characteristics:**
- 🎭 **Only executed** when explicitly requested via `SEED_FAKE=true`
- 🔄 **Can be run repeatedly** to generate more data
- 📊 **High volume** - designed for pagination, overflow, and stress testing
- ⚠️ **Never auto-executed** - requires explicit flag

**When to use:**
- Testing pagination with 1000+ records
- UI overflow scenarios
- Performance and load testing
- Stress testing database queries
- Visual testing with realistic data volumes

**Example usage:**
```bash
# Explicit execution with environment variable
SEED_FAKE=true php artisan db:seed

# Or in Docker
docker-compose exec -e SEED_FAKE=true dev php /workspace/code/api/artisan db:seed
```

## 🚀 Usage

### Running All Seeders

```bash
# Run all seeders (Production + Dev if in dev/testing env)
php artisan db:seed --force

# Run with fresh migrations
php artisan migrate:fresh --seed
```

### Running Specific Categories

```bash
# Run only production seeders
php artisan db:seed --class=Database\\Seeders\\ProductionSeeders\\RoleSeeder

# Run with fake data
SEED_FAKE=true php artisan db:seed
```

### Docker Auto-Initialization

The `init.sh` script automatically runs `DatabaseSeeder` during container startup:

```bash
# In docker/dev/init.sh
php artisan db:seed --force
```

This ensures:
- Fresh containers have all base data
- Permissions and roles are always up to date
- No manual intervention needed after container restart

## 📋 Environment Detection

The `DatabaseSeeder.php` uses the following logic:

```php
// Always run Production seeders
$this->runProductionSeeders();

// Run Dev seeders only if in development/testing
if (in_array(App::environment(), ['local', 'development', 'testing'])) {
    $this->runDevSeeders();
}

// Run Fake seeders only if SEED_FAKE=true
if (env('SEED_FAKE', false) === true) {
    $this->runFakeSeeders();
}
```

## ✅ Best Practices

### Creating New Seeders

1. **Determine the category** based on purpose:
   - Base data needed for app to work → `ProductionSeeders/`
   - Sample data for development → `DevSeeders/`
   - Mass fake data for testing → `FakeSeeders/`

2. **Make seeders idempotent**:
   ```php
   // Use firstOrCreate to avoid duplicates
   Role::firstOrCreate(
       ['name' => 'admin'],
       ['description' => 'Administrator']
   );
   ```

3. **Add clear output messages**:
   ```php
   $this->command->info('✅ Created 10 sample orders');
   $this->command->warn('⚠️  Data already exists, skipping...');
   ```

4. **Register in DatabaseSeeder.php**:
   ```php
   protected function runDevSeeders(): void
   {
       $this->call([
           DevSeeders\SampleOrdersSeeder::class,
           DevSeeders\TestCustomersSeeder::class,
       ]);
   }
   ```

### Testing Seeders

Always test your seeders in all scenarios:

```bash
# Test production mode (always runs)
php artisan db:seed

# Test with fake data
SEED_FAKE=true php artisan db:seed

# Test idempotence (run twice)
php artisan db:seed
php artisan db:seed
```

## 🔍 Troubleshooting

### Seeders not running in container

1. Check `docker/dev/init.sh` has the correct path:
   ```bash
   php artisan db:seed --force
   ```

2. Verify the database connection is ready before seeding

### Duplicate data being created

1. Ensure seeders use `firstOrCreate` or existence checks
2. Run seeders with `--force` flag in production
3. Check idempotence by running seeder twice

### Dev seeders running in production

1. Verify `APP_ENV` is set correctly in `.env`
2. Check `DatabaseSeeder.php` environment detection logic

## 📚 Related Documentation

- [Laravel Seeding Documentation](https://laravel.com/docs/seeding)
- [Spatie Permission Package](https://spatie.be/docs/laravel-permission)
- [Laravel Passport Documentation](https://laravel.com/docs/passport)

## 🎉 Default Users

After running seeders, these users are available:

| Email | Password | Role/Permissions |
|-------|----------|------------------|
| superadmin@comandaflow.com | SuperAdmin123! | All 95 permissions |
| owner@comandaflow.com | Owner123! | 56 permissions |
| manager@comandaflow.com | Manager123! | 27 permissions |
| headchef@comandaflow.com | HeadChef123! | 7 permissions |
| waiter@comandaflow.com | Waiter123! | 10 permissions |
| cashier@comandaflow.com | Cashier123! | 12 permissions |
| kitchen@comandaflow.com | Kitchen123! | 10 permissions |
| host@comandaflow.com | Host123! | 10 permissions |
| delivery@comandaflow.com | Delivery123! | 10 permissions |
| support@comandaflow.com | Support123! | 5 permissions |

**Note:** These credentials are for development only. In production, change all default passwords immediately.
