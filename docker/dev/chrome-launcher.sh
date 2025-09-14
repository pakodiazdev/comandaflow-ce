#!/bin/bash

# Script optimizado para lanzar Google Chrome en el contenedor Docker
export DISPLAY=:1

# Configurar variables de entorno
export DBUS_SESSION_BUS_ADDRESS="unix:path=/dev/null"
export XDG_RUNTIME_DIR="/tmp"
export XDG_CONFIG_HOME="/root/.config"
export XDG_CACHE_HOME="/tmp/chrome-cache"

# Crear directorios necesarios
mkdir -p /root/.config/google-chrome
mkdir -p /tmp/chrome-cache
mkdir -p /tmp/chrome-tmp

# Verificar que X11 esté disponible
if ! xdpyinfo >/dev/null 2>&1; then
    echo "Error: No se puede conectar al servidor X en DISPLAY=$DISPLAY"
    exit 1
fi

echo "Iniciando Google Chrome..."

# Lanzar Chrome con configuración completa para contenedor
google-chrome \
    --no-sandbox \
    --disable-dev-shm-usage \
    --disable-gpu \
    --disable-gpu-sandbox \
    --disable-software-rasterizer \
    --disable-background-timer-throttling \
    --disable-backgrounding-occluded-windows \
    --disable-renderer-backgrounding \
    --disable-features=TranslateUI,VizDisplayCompositor \
    --disable-ipc-flooding-protection \
    --disable-dbus \
    --no-first-run \
    --no-default-browser-check \
    --disable-default-apps \
    --disable-component-extensions-with-background-pages \
    --disable-sync \
    --disable-background-networking \
    --disable-web-security \
    --disable-features=VizDisplayCompositor \
    --user-data-dir=/root/.config/google-chrome \
    --disk-cache-dir=/tmp/chrome-cache \
    --crash-dumps-dir=/tmp \
    --log-level=3 \
    --silent-debugger-extension-api \
    --disable-logging \
    --disable-gpu-process-crash-limit \
    "$@" > /dev/null 2>&1 &

echo "Chrome iniciado exitosamente"