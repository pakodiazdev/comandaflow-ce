#!/bin/bash
set -e

echo "🚀 Iniciando configuración del entorno de desarrollo..."

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para imprimir mensajes con color
print_status() {
    echo -e "${BLUE}[INIT]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Cambiar al directorio de trabajo correcto
cd /workspace

# Función para instalar dependencias de Composer (API Laravel)
install_composer_dependencies() {
    if [ -f "code/api/composer.json" ]; then
        print_status "Verificando dependencias de Composer para API Laravel..."
        
        cd /workspace/code/api
        
        # Verificar si vendor está vacío o no existe autoload.php
        if [ ! -f "vendor/autoload.php" ] || [ -z "$(ls -A vendor 2>/dev/null)" ]; then
            print_status "El directorio vendor está vacío o no existe autoload.php, instalando dependencias..."
            composer install --no-interaction --prefer-dist --optimize-autoloader
            print_success "Dependencias de Composer instaladas correctamente"
        else
            print_success "Dependencias de Composer ya están instaladas"
        fi
        cd /workspace
    else
        print_warning "No se encontró code/api/composer.json, omitiendo instalación de dependencias PHP"
    fi
}

# Función para instalar dependencias de Node.js (Frontend React)
install_node_dependencies() {
    if [ -f "code/app/package.json" ]; then
        print_status "Verificando dependencias de Node.js para aplicación React..."
        
        cd /workspace/code/app
        if [ ! -d "node_modules" ]; then
            print_status "Instalando dependencias de Node.js..."
            yarn
            print_success "Dependencias de Node.js instaladas correctamente"
        else
            print_success "Dependencias de Node.js ya están instaladas"
        fi
        cd /workspace
    else
        print_warning "No se encontró code/app/package.json, omitiendo instalación de dependencias Node.js"
    fi
}

# Función para configurar Laravel
setup_laravel() {
    if [ -f "code/api/artisan" ]; then
        print_status "Configurando Laravel..."
        
        cd /workspace/code/api
        
        # Verificar si existe .env
        if [ ! -f ".env" ]; then
            if [ -f ".env.example" ]; then
                print_status "Copiando .env.example a .env..."
                cp .env.example .env
            else
                print_warning "No se encontró .env.example"
            fi
        fi
        
        # Generar key de aplicación si no existe
        if [ -f ".env" ] && ! grep -q "APP_KEY=base64:" .env; then
            print_status "Generando clave de aplicación Laravel..."
            php artisan key:generate --no-interaction
        fi
        
        # Esperar a que PostgreSQL esté disponible usando php
        print_status "Esperando a que PostgreSQL esté disponible..."
        max_attempts=30
        attempt=0
        while [ $attempt -lt $max_attempts ]; do
            if php -r "
                try {
                    new PDO('pgsql:host=postgres_db;port=5432;dbname=devbase', 'devbase', 'devbase123');
                    exit(0);
                } catch (Exception \$e) {
                    exit(1);
                }
            " 2>/dev/null; then
                break
            fi
            attempt=$((attempt + 1))
            sleep 2
        done
        
        if [ $attempt -eq $max_attempts ]; then
            print_warning "No se pudo conectar a PostgreSQL después de $max_attempts intentos"
        else
            print_success "PostgreSQL está disponible"
        fi
        
        # Ejecutar migraciones si existen
        if [ -d "database/migrations" ] && [ "$(ls -A database/migrations)" ]; then
            print_status "Ejecutando migraciones de base de datos..."
            php artisan migrate --force || print_warning "No se pudieron ejecutar las migraciones"
            print_success "Migraciones ejecutadas correctamente"
        fi
        
        # Generar certificados de Passport si no existen
        print_status "Ejecutando setup_passport_keys..."
        setup_passport_keys
        
        # Ejecutar seeders
        print_status "Ejecutando run_seeders..."
        run_seeders
        
        # Limpiar cache (sin comandos que requieran DB)
        print_status "Limpiando cache de Laravel..."
        php artisan config:clear || true
        php artisan route:clear || true
        
        cd /workspace
        print_success "Laravel configurado correctamente"
    else
        print_warning "No se detectó proyecto Laravel (artisan no encontrado)"
    fi
}

# Función para configurar las claves de Passport
setup_passport_keys() {
    if [ -f "/workspace/code/api/artisan" ]; then
        print_status "Verificando certificados de Passport..."
        
        cd /workspace/code/api
        
        # Verificar si las claves ya existen
        if [ ! -f "storage/oauth-private.key" ] || [ ! -f "storage/oauth-public.key" ]; then
            print_status "Generando certificados de Passport..."
            php artisan passport:keys --force
            print_success "Certificados de Passport generados correctamente"
        else
            print_success "Certificados de Passport ya existen"
        fi
        
        cd /workspace
    fi
}

# Función para ejecutar seeders
run_seeders() {
    if [ -f "/workspace/code/api/artisan" ]; then
        print_status "Ejecutando seeders..."
        
        cd /workspace/code/api
        
        # Ejecutar DatabaseSeeder que maneja la lógica condicional
        # - Siempre ejecuta ProductionSeeders (roles, permisos, usuarios base)
        # - Ejecuta DevSeeders solo en entornos de desarrollo/testing
        # - Ejecuta FakeSeeders solo si SEED_FAKE=true
        print_status "Ejecutando DatabaseSeeder..."
        php artisan db:seed --force || print_warning "No se pudo ejecutar DatabaseSeeder"
        
        print_success "Seeders ejecutados correctamente"
        cd /workspace
        print_success "run_seeders completado"
    else
        print_warning "No se encontró artisan para run_seeders"
    fi
}

# Función para configurar permisos
setup_permissions() {
    print_status "Configurando permisos..."
    
    # Permisos para Laravel
    if [ -d "code/api/storage" ]; then
        chmod -R 775 code/api/storage
        print_status "Permisos configurados para storage/"
    fi
    
    if [ -d "code/api/bootstrap/cache" ]; then
        chmod -R 775 code/api/bootstrap/cache
        print_status "Permisos configurados para bootstrap/cache/"
    fi
    
    print_success "Permisos configurados correctamente"
}

# Función principal
main() {
    print_status "🔧 Inicializando entorno de desarrollo..."
    
    # Instalar dependencias
    install_composer_dependencies
    install_node_dependencies
    
    # Configurar aplicaciones
    setup_laravel
    setup_permissions
    
    print_success "✅ Inicialización completada exitosamente!"
    print_status "🚀 Iniciando servicios..."
    
    # Iniciar servicios originales del contenedor
    # start_original_services
    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
}

# Ejecutar función principal
main "$@"