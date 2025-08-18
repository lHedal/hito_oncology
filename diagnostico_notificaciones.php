<?php
// Script de diagnóstico para verificar el estado de las tablas
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "oncology_database";

echo "<h1>🔍 Diagnóstico del Sistema de Notificaciones</h1>";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "✅ Conexión a base de datos exitosa";
    echo "</div>";
    
    // Verificar qué tablas existen
    echo "<h2>📋 Verificando Tablas Existentes</h2>";
    $tables_to_check = ['notification_config', 'notification_types', 'notification_log', 'notification_queue'];
    
    foreach($tables_to_check as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if($stmt->rowCount() > 0) {
                echo "<div style='background: #d4edda; padding: 5px; margin: 5px 0; border-radius: 3px;'>";
                echo "✅ Tabla <strong>$table</strong> existe";
                
                // Contar registros
                $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo " ($count registros)";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 5px; margin: 5px 0; border-radius: 3px;'>";
                echo "❌ Tabla <strong>$table</strong> NO existe";
                echo "</div>";
            }
        } catch(Exception $e) {
            echo "<div style='background: #fff3cd; padding: 5px; margin: 5px 0; border-radius: 3px;'>";
            echo "⚠️ Error verificando tabla <strong>$table</strong>: " . $e->getMessage();
            echo "</div>";
        }
    }
    
    // Verificar estructura de las tablas
    echo "<h2>🔧 Estructura de Tablas</h2>";
    foreach($tables_to_check as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            if($stmt->rowCount() > 0) {
                echo "<h3>Tabla: $table</h3>";
                echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
                echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Clave</th><th>Default</th></tr>";
                while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . $row['Field'] . "</td>";
                    echo "<td>" . $row['Type'] . "</td>";
                    echo "<td>" . $row['Null'] . "</td>";
                    echo "<td>" . $row['Key'] . "</td>";
                    echo "<td>" . $row['Default'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch(Exception $e) {
            echo "<p style='color: red;'>Error describiendo tabla $table: " . $e->getMessage() . "</p>";
        }
    }
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24;'>❌ Error de Conexión</h3>";
    echo "<p style='color: #721c24;'>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}

// Verificar archivos del sistema
echo "<h2>📁 Verificando Archivos del Sistema</h2>";
$files_to_check = [
    'core/app/model/NotificationData.php',
    'core/app/model/NotificationService.php',
    'core/app/view/notifications-view.php',
    'core/app/view/notificationconfig-view.php'
];

foreach($files_to_check as $file) {
    if(file_exists($file)) {
        echo "<div style='background: #d4edda; padding: 5px; margin: 5px 0; border-radius: 3px;'>";
        echo "✅ Archivo <strong>$file</strong> existe";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 5px; margin: 5px 0; border-radius: 3px;'>";
        echo "❌ Archivo <strong>$file</strong> NO existe";
        echo "</div>";
    }
}

// Test de clases PHP
echo "<h2>🧪 Test de Clases PHP</h2>";
try {
    include_once('core/app/model/NotificationData.php');
    
    if(class_exists('NotificationData')) {
        echo "<div style='background: #d4edda; padding: 5px; margin: 5px 0; border-radius: 3px;'>";
        echo "✅ Clase NotificationData cargada correctamente";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 5px; margin: 5px 0; border-radius: 3px;'>";
        echo "❌ Clase NotificationData no encontrada";
        echo "</div>";
    }
    
    if(class_exists('NotificationConfigData')) {
        echo "<div style='background: #d4edda; padding: 5px; margin: 5px 0; border-radius: 3px;'>";
        echo "✅ Clase NotificationConfigData cargada correctamente";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 5px; margin: 5px 0; border-radius: 3px;'>";
        echo "❌ Clase NotificationConfigData no encontrada";
        echo "</div>";
    }
    
} catch(Exception $e) {
    echo "<div style='background: #f8d7da; padding: 5px; margin: 5px 0; border-radius: 3px;'>";
    echo "❌ Error cargando clases: " . $e->getMessage();
    echo "</div>";
}

echo "<h2>🛠️ Acciones Recomendadas</h2>";
echo "<ul>";
echo "<li><a href='repair_notifications.php' style='color: blue;'>🔧 Ejecutar reparación de tablas</a></li>";
echo "<li><a href='index.php?view=notificationconfig' style='color: blue;'>⚙️ Probar acceso a configuración</a></li>";
echo "<li><a href='notification_test.php' style='color: blue;'>🧪 Ejecutar test completo</a></li>";
echo "</ul>";

echo "<p><em>Diagnóstico completado el " . date('d/m/Y H:i:s') . "</em></p>";
?>
