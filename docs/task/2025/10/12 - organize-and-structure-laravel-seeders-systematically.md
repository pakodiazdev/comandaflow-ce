# 📌 Organize and structure Laravel seeders systematically 🌱

---

## 📖 Story
As a backend developer, I need to organize and separate all Laravel seeders by purpose and environment, so that seeding can be controlled, reproducible, and consistent across production, development, and testing setups.

---

## 🧭 Context
We need to organize the Laravel seeders to prevent unnecessary or duplicate data creation when restarting the system.  
There will be **three distinct seeding scenarios**:

### 🏗️ Setup / Production
Used to populate **initial catalogs or core data**, such as permissions, roles, and a default user.  
These are **immutable** — once created, they should never be updated or recreated.

### 🧪 Development and Testing
Used to generate data that helps with feature development and testing, e.g., one user per role or sample items in various states.  
These should **only run once** and avoid reseeding if data already exists.

### 🎭 Fake Data
Used to **fill collections with mock data** for UI/UX or performance testing (e.g., many users for pagination or overflow tests).  
They can be **executed repeatedly** on demand using:

```bash
SEED_FAKE=true php artisan migrate --seed
# or
SEED_FAKE=true php artisan db:seed
```

Seeders will be executed through `DatabaseSeeder.php` using this logic:
- ✅ Always run `ProductionSeeder`
- 🧩 Run `DevSeeder` only if environment is `development` or `testing`
- 🎭 Run `FakeSeeder` only if `SEED_FAKE=true`

Seeders must also be invoked automatically from `init.sh` to:
- Initialize or update base data automatically at container startup.
- Maintain consistency when containers restart or schema changes occur.

### 📂 Folder Organization
```
database/seeders/
├── ProductionSeeders/
├── DevSeeders/
└── FakeSeeders/
```

---

## ✅ Technical Tasks
- [x] 📂 Create directory structure for seeders (`ProductionSeeders`, `DevSeeders`, `FakeSeeders`)
- [x] 🔧 Implement main logic in `DatabaseSeeder.php` to handle conditional seeding
- [x] 🔧 Move existing seeders into the appropriate subfolders
- [x] 🧩 Implement environment-based conditional logic (`APP_ENV`, `SEED_FAKE`)
- [x] 🧪 Test seeding in `development`, `testing`, and `production` modes
- [x] 🧪 Validate idempotence (no duplicate data insertion)
- [x] 📝 Update `init.sh` to trigger seeding automatically during container setup
- [x] 📝 Document usage and environment variable configuration in project README

---

## ⏱️ Time
### 📊 Estimates
- **Optimistic:** `2.5h`
- **Pessimistic:** `5h`
- **Tracked:** `1.5h`

### 📅 Sessions
```json
[
    {"date": "2025-10-06", "start": "10:57", "end": "12:27"}
]
```

---

## 📝 Implementation Notes

### ✅ Completed Work

1. **Directory Structure Created**
   - Created `ProductionSeeders/`, `DevSeeders/`, and `FakeSeeders/` directories
   - Moved existing seeders to `ProductionSeeders/`
   - Updated namespaces for all moved seeders

2. **DatabaseSeeder.php Logic**
   - Implemented conditional seeding based on `APP_ENV`
   - Added support for `SEED_FAKE` environment variable
   - Production seeders always run (idempotent)
   - Dev seeders run only in local/development/testing
   - Fake seeders run only when explicitly requested

3. **Seeders Organized**
   - Moved to `ProductionSeeders/`:
     - `RoleSeeder.php` - 9 roles, 95 permissions
     - `PassportClientSeeder.php` - OAuth2 clients
     - `PassportSeeder.php` - Personal access client
     - `DefaultUsersSeeder.php` - 10 default users
   
4. **Init.sh Updated**
   - Simplified seeder execution to use `DatabaseSeeder`
   - Automatic execution during container startup
   - Proper error handling and logging

5. **Testing Completed**
   - ✅ Tested production mode seeding
   - ✅ Tested with `SEED_FAKE=true`
   - ✅ Validated idempotence (running seeders twice)
   - ✅ Verified environment detection logic

6. **Documentation Created**
   - Comprehensive `README.md` in `database/seeders/`
   - Usage examples for all scenarios
   - Best practices guide
   - Troubleshooting section
   - Default user credentials table