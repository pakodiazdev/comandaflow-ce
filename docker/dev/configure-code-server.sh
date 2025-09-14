#!/bin/bash

# Script para configurar code-server (VS Code Web)
export DISPLAY=:1

# Crear directorio de configuración
mkdir -p /root/.config/code-server

# Crear archivo de configuración
cat > /root/.config/code-server/config.yaml << EOF
bind-addr: 0.0.0.0:8080
auth: none
cert: false
user-data-dir: /root/.vscode-web
extensions-dir: /root/.vscode-web/extensions
EOF

# Crear directorios necesarios
mkdir -p /root/.vscode-web
mkdir -p /root/.vscode-web/extensions

echo "Configuración de code-server completada"