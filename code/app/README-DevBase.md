# DevBase React App

## 🚀 Stack Tecnológico

- **React 18** - Framework frontend
- **Vite** - Build tool y dev server
- **SWC** - Compilador rápido para JavaScript/TypeScript
- **Hot Module Replacement (HMR)** - Recarga en vivo durante desarrollo

## 📦 Estructura del Proyecto

```
code/app/
├── public/          # Archivos estáticos
├── src/             # Código fuente React
│   ├── components/  # Componentes React
│   ├── App.jsx      # Componente principal
│   └── main.jsx     # Punto de entrada
├── package.json     # Dependencias
└── vite.config.js   # Configuración Vite + SWC
```

## 🛠️ Comandos Disponibles

```bash
# Iniciar servidor de desarrollo
npm run dev

# Construir para producción
npm run build

# Previsualizar build de producción
npm run preview

# Linting
npm run lint
```

## 🔧 Configuración

### Vite + SWC
- **Hot Module Replacement**: Activado
- **Host**: `0.0.0.0` (accesible desde contenedor)
- **Puerto**: `3000`
- **Compilador**: SWC (más rápido que Babel)

### Desarrollo en Contenedor
- Servidor accesible en: `http://localhost:3000`
- Archivos sincronizados con host
- Hot reload configurado para desarrollo en Docker

## 🚀 URLs de Acceso

| Servicio | URL | Descripción |
|----------|-----|-------------|
| **React App** | `http://localhost:3000` | Aplicación React con HMR |
| **Laravel API** | `http://localhost` | Backend API |
| **VS Code Web** | `http://localhost:8080` | Editor web |
| **pgAdmin** | `http://localhost:5050` | Administrador PostgreSQL |

## 🔄 Flujo de Desarrollo

1. Editar archivos en `src/`
2. Los cambios se reflejan automáticamente (HMR)
3. El frontend puede comunicarse con la API Laravel
4. Base de datos PostgreSQL disponible para el backend

## 📋 Funcionalidades

- ✅ Desarrollo con recarga en vivo
- ✅ Compilación rápida con SWC
- ✅ Integración con VS Code
- ✅ Linting automático
- ✅ Build optimizado para producción
- ✅ Conectividad con backend Laravel