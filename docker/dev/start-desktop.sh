#!/bin/bash

# Esperar a que Xvnc esté disponible
sleep 5

# Configurar DISPLAY
export DISPLAY=:1

# Crear directorio para D-Bus
mkdir -p /var/run/dbus

# Iniciar D-Bus
dbus-daemon --system --fork

# Configurar variables de entorno para D-Bus
eval `dbus-launch --sh-syntax`
export DBUS_SESSION_BUS_ADDRESS

# Iniciar el administrador de ventanas
startxfce4 > /root/desktop.log 2>&1