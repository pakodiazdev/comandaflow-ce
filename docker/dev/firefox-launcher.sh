#!/bin/bash

# Firefox Launcher optimizado para contenedores
# Configuración específica para entornos Docker sin D-Bus

# Configurar directorio de perfil personalizado
export FIREFOX_PROFILE_DIR="/tmp/firefox-profile-$(whoami)"
mkdir -p "$FIREFOX_PROFILE_DIR"

# Variables de entorno para Firefox en contenedor
export MOZ_NO_REMOTE=1
export MOZ_DISABLE_CONTENT_SANDBOX=1
export MOZ_DISABLE_GMP_SANDBOX=1
export MOZ_DISABLE_RDD_SANDBOX=1
export MOZ_DISABLE_SOCKET_PROCESS_SANDBOX=1

# Configurar directorio temporal
export TMPDIR="/tmp/firefox-tmp"
mkdir -p "$TMPDIR"

# Configurar cache
export MOZ_CACHE_DIR="/tmp/firefox-cache"
mkdir -p "$MOZ_CACHE_DIR"

# Limpiar procesos Firefox existentes
pkill -f firefox || true
sleep 1

# Argumentos específicos para contenedor
FIREFOX_ARGS=(
    "--no-remote"
    "--profile" "$FIREFOX_PROFILE_DIR"
    "--new-instance" 
    "--disable-dev-shm-usage"
    "--disable-background-timer-throttling"
    "--disable-backgrounding-occluded-windows"
    "--disable-renderer-backgrounding"
    "--disable-features=TranslateUI"
    "--disable-ipc-flooding-protection"
    "--headless=new" # Cambiar a --no-headless para modo gráfico
)

# Si se proporciona una URL como argumento, agregarla
if [ $# -gt 0 ]; then
    # Quitar --headless=new y agregar la URL
    FIREFOX_ARGS=("${FIREFOX_ARGS[@]/--headless=new}")
    FIREFOX_ARGS+=("$@")
fi

# Ejecutar Firefox
exec firefox-esr "${FIREFOX_ARGS[@]}"