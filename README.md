# 🚀 ComandaFlow CE

ComandaFlow Community Edition - Sistema de gestión de comandas y restaurantes de código abierto.

## 📁 Estructura del Proyecto

```
comandaflow-ce/
├── code/
│   ├── api/          # Laravel API Backend
│   └── app/          # Frontend Application
├── docker/           # Docker configurations
├── docs/            # Documentación del proyecto
└── docker-compose.yml
```

## 🐳 Configuración con Docker

Este proyecto utiliza Docker para crear un entorno de desarrollo completo y consistente.

### 🔧 Configuración Inicial

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/pakodiazdev/comandaflow-ce.git
   cd comandaflow-ce
   ```

2. **Configurar variables de entorno:**
   ```bash
   cp .env.example .env
   ```

3. **Levantar los contenedores:**
   ```bash
   docker-compose up -d
   ```

### ⚙️ Configuración de Puertos

Los puertos son completamente personalizables a través de variables de entorno. Ver la [documentación de puertos](docs/PORTS.md) para más detalles.

### 📦 Servicios Incluidos

- **🖥️ Dev Container**: Entorno de desarrollo completo con PHP, Node.js, VS Code Server
- **🌐 Nginx**: Servidor web y proxy reverso
- **🗄️ PostgreSQL**: Base de datos principal
- **🔧 pgAdmin**: Interfaz web para PostgreSQL

## 🌐 URLs de Acceso (por defecto)

- **VS Code Server**: http://localhost:8080
- **noVNC (Escritorio Web)**: http://localhost:6080  
- **pgAdmin**: http://localhost:5050
- **API Laravel**: http://localhost/api
- **Frontend**: http://localhost:3000

## ⏱️ Time Tracking

El proyecto incluye comandos Artisan para seguimiento de tiempo en issues de GitHub:

```bash
# Iniciar seguimiento de tiempo
php artisan task:start {issue}

# Finalizar seguimiento de tiempo  
php artisan task:end {issue}
```

Ver la configuración de variables de entorno en `.env` para GitHub integration.

## 📚 Documentación

- [Configuración de Puertos](docs/PORTS.md)
- [Convenciones de Branches](docs/conventions/branches.md)
- [Convenciones de Commits](docs/conventions/commits.md)
- [Pull Request Guidelines](docs/conventions/pull-request.md)

## 🛠️ Desarrollo

Para desarrollar en este proyecto, utiliza el contenedor de desarrollo que incluye todas las herramientas necesarias:

```bash
# Acceder al contenedor de desarrollo
docker exec -it dev_container bash

# O usar VS Code Server en http://localhost:8080
```

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor, revisa las [convenciones del proyecto](docs/conventions/) antes de contribuir.