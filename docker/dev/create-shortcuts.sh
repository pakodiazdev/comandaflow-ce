#!/bin/bash

# Script para crear accesos directos en el escritorio XFCE
export DISPLAY=:1

# Esperar a que XFCE esté completamente cargado
sleep 10

# Crear directorio del escritorio si no existe
mkdir -p /root/Desktop

# Crear acceso directo para Chrome
cat > /root/Desktop/Chrome.desktop << EOF
[Desktop Entry]
Version=1.0
Type=Application
Name=Google Chrome
Comment=Navegador web Google Chrome
Exec=chrome-launcher.sh
Icon=google-chrome
Terminal=false
Categories=Network;WebBrowser;
MimeType=text/html;text/xml;application/xhtml+xml;application/xml;application/vnd.mozilla.xul+xml;application/rss+xml;application/rdf+xml;image/gif;image/jpeg;image/png;x-scheme-handler/http;x-scheme-handler/https;x-scheme-handler/ftp;x-scheme-handler/chrome;video/webm;application/x-xpinstall;
EOF

chmod +x /root/Desktop/Chrome.desktop

# Crear acceso directo para Firefox
cat > /root/Desktop/Firefox.desktop << EOF
[Desktop Entry]
Version=1.0
Type=Application
Name=Firefox
Comment=Navegador web Firefox
Exec=firefox-launcher.sh
Icon=firefox-esr
Terminal=false
Categories=Network;WebBrowser;
MimeType=text/html;text/xml;application/xhtml+xml;application/xml;application/vnd.mozilla.xul+xml;application/rss+xml;application/rdf+xml;image/gif;image/jpeg;image/png;x-scheme-handler/http;x-scheme-handler/https;x-scheme-handler/ftp;video/webm;application/x-xpinstall;
EOF

chmod +x /root/Desktop/Firefox.desktop

# Crear acceso directo para VS Code
cat > /root/Desktop/VSCode.desktop << EOF
[Desktop Entry]
Version=1.0
Type=Application
Name=VS Code
Comment=Editor de código Visual Studio Code
Exec=vscode-launcher.sh
Icon=com.visualstudio.code
Terminal=false
Categories=Development;IDE;
MimeType=text/plain;inode/directory;
EOF

chmod +x /root/Desktop/VSCode.desktop

# Crear acceso directo para Terminal
cat > /root/Desktop/Terminal.desktop << EOF
[Desktop Entry]
Version=1.0
Type=Application
Name=Terminal
Comment=Terminal Emulator
Exec=xfce4-terminal
Icon=utilities-terminal
Terminal=false
Categories=System;TerminalEmulator;
EOF

chmod +x /root/Desktop/Terminal.desktop

# Crear acceso directo para File Manager
cat > /root/Desktop/Files.desktop << EOF
[Desktop Entry]
Version=1.0
Type=Application
Name=Files
Comment=File Manager
Exec=thunar
Icon=file-manager
Terminal=false
Categories=System;FileTools;FileManager;
EOF

chmod +x /root/Desktop/Files.desktop

echo "Accesos directos creados en el escritorio"