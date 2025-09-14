#!/bin/bash

# Script para configurar VS Code con extensiones básicas para desarrollo
export DISPLAY=:1

# Esperar a que VS Code esté disponible
sleep 5

# Definir directorios
VSCODE_USER_DATA_DIR="/tmp/vscode-user-data"
VSCODE_EXTENSIONS_DIR="/tmp/vscode-extensions"

# Crear directorios si no existen
mkdir -p "$VSCODE_USER_DATA_DIR"
mkdir -p "$VSCODE_EXTENSIONS_DIR"

# Lista de extensiones básicas para desarrollo
EXTENSIONS=(
    "ms-vscode.vscode-typescript-next"
    "bradlc.vscode-tailwindcss"
    "esbenp.prettier-vscode"
    "ms-python.python"
    "formulahendry.auto-rename-tag"
    "ms-vscode.vscode-json"
    "ms-vscode-remote.remote-containers"
    "ms-vscode.hexeditor"
    "github.github-vscode-theme"
    "PKief.material-icon-theme"
)

# Instalar extensiones
echo "Instalando extensiones básicas de VS Code..."
for ext in "${EXTENSIONS[@]}"; do
    echo "Instalando: $ext"
    code --user-data-dir="$VSCODE_USER_DATA_DIR" --extensions-dir="$VSCODE_EXTENSIONS_DIR" --install-extension "$ext" --force
done

# Crear configuración básica de settings.json
SETTINGS_DIR="$VSCODE_USER_DATA_DIR/User"
mkdir -p "$SETTINGS_DIR"

cat > "$SETTINGS_DIR/settings.json" << EOF
{
    "workbench.colorTheme": "GitHub Dark",
    "workbench.iconTheme": "material-icon-theme",
    "editor.fontSize": 14,
    "editor.fontFamily": "'DejaVu Sans Mono', 'Courier New', monospace",
    "editor.tabSize": 4,
    "editor.insertSpaces": true,
    "editor.wordWrap": "on",
    "editor.minimap.enabled": true,
    "editor.formatOnSave": true,
    "files.autoSave": "afterDelay",
    "files.autoSaveDelay": 1000,
    "terminal.integrated.fontSize": 12,
    "window.zoomLevel": 0,
    "workbench.startupEditor": "welcomePage",
    "extensions.autoUpdate": false,
    "telemetry.telemetryLevel": "off",
    "update.mode": "none"
}
EOF

echo "Configuración de VS Code completada"