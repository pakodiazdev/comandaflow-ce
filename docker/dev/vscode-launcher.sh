#!/bin/bash

# VS Code Launcher optimizado para contenedores como root

# Configurar directorios de usuario para VS Code
VSCODE_USER_DATA_DIR="/root/.vscode-container"
VSCODE_EXTENSIONS_DIR="/root/.vscode-container/extensions"

# Crear directorios necesarios
mkdir -p "$VSCODE_USER_DATA_DIR"
mkdir -p "$VSCODE_EXTENSIONS_DIR"

# Variables de entorno para VS Code en contenedor
export ELECTRON_DISABLE_SANDBOX=1

# Argumentos mínimos necesarios para contenedor como root
VSCODE_ARGS=(
    "--no-sandbox"
    "--user-data-dir=$VSCODE_USER_DATA_DIR"
    "--extensions-dir=$VSCODE_EXTENSIONS_DIR"
    "--disable-dev-shm-usage"
    "--no-first-run"
)

# Agregar argumentos pasados al script
VSCODE_ARGS+=("$@")

# Si no se pasaron argumentos, abrir directorio workspace
if [ $# -eq 0 ]; then
    VSCODE_ARGS+=("/workspace")
fi

# Ejecutar VS Code
exec /usr/bin/code-original "${VSCODE_ARGS[@]}"