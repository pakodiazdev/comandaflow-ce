# Dockerfile para un entorno de desarrollo completo
FROM php:fpm

# Instalar dependencias del sistema y Google Chrome
RUN apt-get update && apt-get install -y \
    supervisor \
    xfce4 \
    xfce4-goodies \
    tigervnc-standalone-server \
    novnc \
    websockify \
    wget \
    curl \
    gnupg \
    git \
    sudo \
    dbus-x11 \
    fonts-dejavu-core \
    fontconfig \
    xfonts-base \
    xfonts-75dpi \
    xfonts-100dpi \
    x11-xserver-utils \
    apt-transport-https \
    ca-certificates \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libpq-dev \
    unzip \
    locales

# Configurar extensiones GD con FreeType y JPEG
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Configurar locales para evitar errores de locale
RUN echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen && \
    echo "C.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen && \
    update-locale LANG=C.UTF-8

# Configurar variables de entorno de locale
ENV LANG=C.UTF-8
ENV LC_ALL=C.UTF-8

# Instalar extensiones PHP necesarias para Laravel (incluyendo PostgreSQL)
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip

# Instalar Google Chrome
RUN wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb \
    && apt-get install -y ./google-chrome-stable_current_amd64.deb \
    && rm google-chrome-stable_current_amd64.deb

# Instalar Firefox
RUN apt-get install -y firefox-esr

# Instalar VS Code
RUN wget -qO- https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > packages.microsoft.gpg \
    && install -o root -g root -m 644 packages.microsoft.gpg /etc/apt/trusted.gpg.d/ \
    && sh -c 'echo "deb [arch=amd64,arm64,armhf signed-by=/etc/apt/trusted.gpg.d/packages.microsoft.gpg] https://packages.microsoft.com/repos/code stable main" > /etc/apt/sources.list.d/vscode.list' \
    && apt-get update \
    && apt-get install -y code

# Instalar code-server (VS Code Web)
RUN curl -fsSL https://code-server.dev/install.sh | sh

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalar Laravel Installer globalmente
RUN composer global require laravel/installer

# Configurar PATH para Composer global
ENV PATH="/root/.config/composer/vendor/bin:${PATH}"

# Copiar scripts optimizados para contenedor
COPY docker/dev/chrome-launcher.sh /usr/local/bin/chrome-launcher.sh
COPY docker/dev/firefox-launcher.sh /usr/local/bin/firefox-launcher.sh
COPY docker/dev/vscode-launcher.sh /usr/local/bin/vscode-launcher.sh
RUN chmod +x /usr/local/bin/chrome-launcher.sh /usr/local/bin/firefox-launcher.sh /usr/local/bin/vscode-launcher.sh

# Crear wrapper para el comando code que use nuestro launcher
RUN mv /usr/bin/code /usr/bin/code-original && \
    ln -s /usr/local/bin/vscode-launcher.sh /usr/bin/code

# Configurar entorno de root
WORKDIR /root

# Instalar NVM, Node.js, npm y yarn
ENV NVM_DIR /root/.nvm
ENV NODE_VERSION 22.12.0
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.1/install.sh | bash
ENV PATH $NVM_DIR/versions/node/v$NODE_VERSION/bin:$PATH
RUN . $NVM_DIR/nvm.sh && nvm install $NODE_VERSION && nvm use $NODE_VERSION && npm install -g yarn && nvm alias default $NODE_VERSION

# Instalar dependencias adicionales para Cypress
RUN apt-get update && apt-get install -y \
    libgtk-3-0 \
    libgbm1 \
    libnss3 \
    libxss1 \
    libasound2 \
    libxtst6 \
    xauth \
    xvfb \
    libxrandr2 \
    libxcomposite1 \
    libxcursor1 \
    libxdamage1 \
    libxi6 \
    libxfixes3 \
    && rm -rf /var/lib/apt/lists/*

# Instalar Cypress globalmente
RUN . $NVM_DIR/nvm.sh && npm install -g cypress

# Verificar instalación de Cypress
RUN . $NVM_DIR/nvm.sh && cypress verify

# Configuración de VNC y Supervisor
RUN mkdir -p /root/.vnc && \
    echo "devbase" | vncpasswd -f > /root/.vnc/passwd && \
    chmod 600 /root/.vnc/passwd

# Crear configuración de VNC para redimensionamiento dinámico
RUN echo "desktop=DevBase Desktop" > /root/.vnc/config && \
    echo "geometry=1024x768" >> /root/.vnc/config && \
    echo "localhost=no" >> /root/.vnc/config && \
    echo "alwaysshared=yes" >> /root/.vnc/config && \
    echo "SecurityTypes=None" >> /root/.vnc/config
COPY docker/dev/xstartup /root/.vnc/xstartup
RUN chmod +x /root/.vnc/xstartup

RUN mkdir -p /var/log/supervisor
COPY docker/dev/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copiar archivo de redirección para noVNC
COPY docker/dev/index.html /usr/share/novnc/index.html

# Script de inicio
COPY docker/dev/start.sh /start.sh
COPY docker/dev/start-desktop.sh /start-desktop.sh
COPY docker/dev/configure-xfce.sh /configure-xfce.sh
COPY docker/dev/configure-vscode.sh /configure-vscode.sh
COPY docker/dev/configure-code-server.sh /configure-code-server.sh
COPY docker/dev/vnc-refresh.sh /vnc-refresh.sh
COPY docker/dev/resolution-monitor.sh /resolution-monitor.sh
COPY docker/dev/create-shortcuts.sh /create-shortcuts.sh
RUN chmod +x /start.sh /start-desktop.sh /configure-xfce.sh /configure-vscode.sh /configure-code-server.sh /vnc-refresh.sh /resolution-monitor.sh /create-shortcuts.sh

EXPOSE 80 443 5901 6080 8080

CMD ["/start.sh"]