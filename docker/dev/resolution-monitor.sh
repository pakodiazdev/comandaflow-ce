#!/bin/bash

# Script para detectar cambios de resolución y limpiar la pantalla inmediatamente
export DISPLAY=:1

echo "=== Monitor de resolución iniciado ==="

LAST_SIZE=""

while true; do
    if xdpyinfo >/dev/null 2>&1; then
        CURRENT_SIZE=$(xdpyinfo | grep "dimensions:" | awk '{print $2}')
        
        if [ "$CURRENT_SIZE" != "$LAST_SIZE" ] && [ ! -z "$CURRENT_SIZE" ]; then
            echo "$(date): Cambio de resolución detectado: $LAST_SIZE -> $CURRENT_SIZE"
            
            # Limpiar inmediatamente
            xsetroot -solid "#2F4F4F" 2>/dev/null
            xrefresh -display :1 2>/dev/null
            
            # Forzar recarga del escritorio
            pkill -USR1 xfdesktop 2>/dev/null || true
            
            # Reconfigurar XFCE desktop
            xfconf-query -c xfce4-desktop -p /backdrop/screen0/monitor0/workspace0/color-style -s 1 2>/dev/null || true
            
            LAST_SIZE="$CURRENT_SIZE"
            
            # Esperar un poco más después de un cambio
            sleep 2
        fi
    fi
    
    sleep 1
done