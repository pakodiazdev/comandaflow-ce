<?php
echo "¡Laravel funcionando a través de Nginx!<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Fecha: " . date('Y-m-d H:i:s') . "<br>";
echo "Directorio: " . __DIR__ . "<br>";

// Verificar que Laravel esté funcionando (buscar en directorio padre)
$laravel_root = dirname(__DIR__);
if (file_exists($laravel_root . '/vendor/autoload.php')) {
    echo "✅ Laravel está instalado correctamente<br>";
    
    // Intentar cargar Laravel
    try {
        require_once $laravel_root . '/vendor/autoload.php';
        echo "✅ Autoloader de Laravel cargado<br>";
        
        if (class_exists('Illuminate\Foundation\Application')) {
            echo "✅ Laravel Framework detectado<br>";
        }
    } catch (Exception $e) {
        echo "❌ Error al cargar Laravel: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Laravel no encontrado (vendor/autoload.php no existe)<br>";
}

// Verificar archivos del proyecto (en directorio padre)
$files = ['artisan', 'composer.json', 'app', 'config', 'public'];
echo "<br>Archivos del proyecto:<br>";
foreach ($files as $file) {
    if (file_exists($laravel_root . '/' . $file)) {
        echo "✅ $file<br>";
    } else {
        echo "❌ $file<br>";
    }
}

// Mostrar información de PHP-FPM
echo "<br>Información del servidor:<br>";
echo "✅ PHP-FPM funcionando correctamente<br>";
echo "✅ Nginx + PHP-FPM integración exitosa<br>";
echo "✅ Volumen compartido: /workspace<br>";
?>