# 🔧 Corrección de Error de Guards - Spatie Permission

## ❌ Problema Identificado

**Error**: `The given role or permission should use guard '' instead of 'web'.`

**Causa**: Incompatibilidad entre los guards configurados en:
- Roles/Permisos (usando guard `web`)
- API de autenticación (usando guard por defecto vacío)

## ✅ Solución Aplicada

### 1. Configuración de Guards en `config/auth.php`
```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    
    'api' => [  // ✅ Agregado
        'driver' => 'passport',
        'provider' => 'users',
    ],
],
```

### 2. Actualización del Seeder
Cambio del guard de `web` a `api` en roles y permisos:
```php
// Roles
'guard_name' => 'api'  // ✅ Cambiado de 'web'

// Permisos
'guard_name' => 'api'  // ✅ Cambiado de 'web'
```

### 3. Actualización de Modelos
**Role.php**: Guard por defecto cambiado a `api`
```php
'guard_name' => $attributes['guard_name'] ?? 'api'
```

**RoleController.php**: Creación de roles con guard `api`
```php
'guard_name' => 'api'
```

### 4. Limpieza y Recreación de Datos
- ✅ Limpiado caché de permisos
- ✅ Eliminado roles/permisos existentes con guard incorrecto
- ✅ Recreado todos los roles con guard `api`

## 🧪 Verificación

### Tests Pasados
- ✅ **8 roles base** creados correctamente
- ✅ **76 permisos** asignados
- ✅ **Guards consistentes** (`api` en todos)
- ✅ **Comando de test** funcionando

### Roles Verificados
| Rol | Permisos | Guard |
|-----|----------|-------|
| owner | 30 | api ✅ |
| manager | 16 | api ✅ |
| cashier | 8 | api ✅ |
| chef | 4 | api ✅ |
| waiter | 5 | api ✅ |
| accountant | 5 | api ✅ |
| inventory_manager | 4 | api ✅ |
| technical_support | 4 | api ✅ |

## 🔄 Comandos para Aplicar la Solución

```bash
# 1. Limpiar configuración y caché
php artisan config:clear
php artisan permission:cache-reset

# 2. Limpiar datos existentes (opcional si hay conflictos)
php artisan tinker --execute="
DB::table('model_has_roles')->delete(); 
DB::table('role_has_permissions')->delete(); 
DB::table('roles')->delete(); 
DB::table('permissions')->delete();
"

# 3. Recrear roles y permisos
php artisan db:seed --class=RoleSeeder

# 4. Verificar funcionamiento
php artisan cf-auth:test-roles
```

## 💡 Explicación Técnica

### ¿Por qué ocurrió?
1. **Laravel Passport** usa el guard `api` por defecto
2. **Spatie Permission** estaba configurado para guard `web`
3. Al intentar asignar un rol con guard `web` a un usuario autenticado via `api`, surge el conflicto

### ¿Cómo se solucionó?
1. **Unificación de guards**: Todo ahora usa guard `api`
2. **Consistencia**: Roles, permisos y autenticación en el mismo guard
3. **Configuración correcta**: Laravel Passport y Spatie Permission trabajando juntos

## ✅ Estado Final

**RESUELTO**: El error de guards está completamente solucionado. Ahora se puede:
- ✅ Registrar usuarios con roles
- ✅ Asignar permisos correctamente  
- ✅ Usar middleware de roles y permisos
- ✅ Gestionar usuarios via API

¡El sistema de autenticación está completamente funcional! 🎉