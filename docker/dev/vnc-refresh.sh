#!/bin/bash

# Script mejorado para manejar redimensionamiento y limpiar artifacts visuales
export DISPLAY=:1

echo "=== Iniciando vnc-refresh script mejorado ===" 
echo "Fecha: $(date)"

# Variables para detectar cambios de resolución
LAST_RES=""
COUNTER=0

while true; do
    COUNTER=$((COUNTER + 1))
    
    # Verificar que el display esté disponible
    if xdpyinfo >/dev/null 2>&1; then
        # Obtener la resolución actual
        CURRENT_RES=$(xdpyinfo | grep dimensions | awk '{print $2}' 2>/dev/null)
        
        if [ ! -z "$CURRENT_RES" ]; then
            # Si la resolución cambió, hacer limpieza completa
            if [ "$CURRENT_RES" != "$LAST_RES" ]; then
                echo "$(date): CAMBIO DE RESOLUCIÓN detectado: $LAST_RES -> $CURRENT_RES"
                
                # Esperar un momento para que se estabilice
                sleep 1
                
                # Reconfigurar el fondo completamente
                xsetroot -solid "#2F4F4F" 2>/dev/null || true
                
                # Refrescar toda la pantalla
                xrefresh -display :1 2>/dev/null || true
                
                # Forzar reconfiguración del escritorio XFCE
                if command -v xfdesktop >/dev/null 2>&1; then
                    pkill -USR1 xfdesktop 2>/dev/null || true
                    sleep 0.5
                    xfdesktop --reload 2>/dev/null &
                fi
                
                # Actualizar configuración del fondo en XFCE
                xfconf-query -c xfce4-desktop -p /backdrop/screen0/monitor0/workspace0/color1 -t uint -t uint -t uint -t uint -s 47 -s 79 -s 79 -s 255 2>/dev/null || true
                xfconf-query -c xfce4-desktop -p /backdrop/screen0/monitor0/workspace0/color-style -s 1 2>/dev/null || true
                
                LAST_RES="$CURRENT_RES"
                echo "$(date): Limpieza completa realizada para resolución $CURRENT_RES"
                
            else
                # Limpieza ligera cada 10 iteraciones (30 segundos)
                if [ $((COUNTER % 10)) -eq 0 ]; then
                    xsetroot -solid "#2F4F4F" 2>/dev/null || true
                    echo "$(date): Limpieza ligera - Resolución: $CURRENT_RES (Contador: $COUNTER)"
                fi
            fi
        fi
    else
        echo "$(date): Display :1 no disponible, esperando..."
        sleep 2
    fi
    
    sleep 3
done