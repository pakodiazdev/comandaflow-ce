# 📚 Swagger API Documentation - Configuración Completa

## 🔧 Configuración Aplicada

### URLs Configuradas
- **Servidor API**: `http://localhost:8088`
- **Swagger UI**: `http://localhost:8088/api/v1/doc`
- **JSON Schema**: `http://localhost:8088/api/v1/doc/docs.json`

### Variables de Entorno
```bash
# En .env
APP_URL=http://localhost:8088
L5_SWAGGER_CONST_HOST=http://localhost:8088
L5_SWAGGER_GENERATE_ALWAYS=true
```

## 🚀 Acceso a la Documentación

### 1. Swagger UI Interactiva
```
http://localhost:8088/api/v1/doc
```

### 2. Esquema JSON
```
http://localhost:8088/api/v1/doc/docs.json
```

## 🔑 Cómo Probar los Endpoints

### 1. Registro de Usuario
1. Ve a Swagger UI: `http://localhost:8088/api/v1/doc`
2. Busca `POST /auth/register`
3. Haz clic en "Try it out"
4. Usa este ejemplo:
```json
{
  "name": "Admin User",
  "email": "admin@comandaflow.com", 
  "password": "password123",
  "password_confirmation": "password123",
  "role": "owner"
}
```

### 2. Login
1. Busca `POST /auth/login`
2. Usa las credenciales del usuario creado:
```json
{
  "email": "admin@comandaflow.com",
  "password": "password123"
}
```

### 3. Autenticación Bearer
1. Copia el `access_token` del response del login
2. Haz clic en el botón "Authorize" en la parte superior
3. Ingresa: `Bearer [tu-token-aquí]`
4. Ahora puedes probar todos los endpoints protegidos

## 📋 Endpoints Disponibles

### Autenticación (Público)
- `POST /auth/register` - Registro de usuario
- `POST /auth/login` - Iniciar sesión

### Autenticación (Protegido)
- `GET /auth/me` - Información del usuario actual
- `POST /auth/logout` - Cerrar sesión  
- `POST /auth/refresh` - Renovar token

### Gestión de Usuarios (Owner/Manager)
- `GET /users` - Listar usuarios
- `GET /users/{id}` - Ver usuario específico
- `PUT /users/{id}` - Actualizar usuario
- `DELETE /users/{id}` - Eliminar usuario
- `POST /users/{id}/roles` - Asignar roles

### Gestión de Roles (Owner)
- `GET /roles` - Listar roles
- `GET /roles/{code}` - Ver rol específico
- `POST /roles` - Crear rol
- `PUT /roles/{code}` - Actualizar rol
- `DELETE /roles/{code}` - Eliminar rol
- `GET /permissions` - Listar permisos

## 🔄 Regenerar Documentación

```bash
# Limpiar caché y regenerar
php artisan config:clear
php artisan l5-swagger:generate

# O desde Docker
docker-compose exec dev bash -c "cd /workspace/code/api && php artisan l5-swagger:generate"
```

## 🌐 Servidor Local

Asegúrate de que el servidor esté corriendo en el puerto 8088:
- **Nginx**: Puerto 8088 configurado en docker-compose
- **API Laravel**: Disponible a través de Nginx

## ✅ Verificación

1. **Swagger UI funcional**: ✅ `http://localhost:8088/api/v1/doc`
2. **Servidor correcto**: ✅ `http://localhost:8088`
3. **Endpoints interactivos**: ✅ Todos los endpoints de cf-auth
4. **Autenticación Bearer**: ✅ Configurada en Swagger
5. **Roles y permisos**: ✅ Sistema completo implementado

¡La documentación Swagger está completamente configurada y lista para usar! 🎉