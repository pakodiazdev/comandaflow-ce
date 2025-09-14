#!/bin/bash

# Script para configurar XFCE optimizado para VNC
export DISPLAY=:1

# Esperar a que XFCE esté disponible
sleep 5

# Deshabilitar el compositor para evitar problemas de rendering
xfconf-query -c xfwm4 -p /general/use_compositing -s false 2>/dev/null || true

# Configurar el fondo de pantalla para que se adapte automáticamente
xfconf-query -c xfce4-desktop -p /backdrop/screen0/monitor0/workspace0/last-image -s "" 2>/dev/null || true
xfconf-query -c xfce4-desktop -p /backdrop/screen0/monitor0/workspace0/image-style -s 0 2>/dev/null || true
xfconf-query -c xfce4-desktop -p /backdrop/screen0/monitor0/workspace0/color-style -s 1 2>/dev/null || true
xfconf-query -c xfce4-desktop -p /backdrop/screen0/monitor0/workspace0/color1 -t uint -t uint -t uint -t uint -s 47 -s 79 -s 79 -s 255 2>/dev/null || true

# Deshabilitar el protector de pantalla
xfconf-query -c xfce4-screensaver -p /saver/enabled -s false 2>/dev/null || true
xfconf-query -c xfce4-power-manager -p /xfce4-power-manager/presentation-mode -s true 2>/dev/null || true

# Configurar el administrador de ventanas para mejor redimensionamiento
xfconf-query -c xfwm4 -p /general/snap_to_border -s true 2>/dev/null || true
xfconf-query -c xfwm4 -p /general/snap_to_windows -s true 2>/dev/null || true
xfconf-query -c xfwm4 -p /general/wrap_windows -s false 2>/dev/null || true

# Configurar el panel para que se adapte al redimensionamiento
xfconf-query -c xfce4-panel -p /panels/panel-1/autohide-behavior -s 0 2>/dev/null || true

echo "XFCE configurado para VNC" > /root/xfce-config.log