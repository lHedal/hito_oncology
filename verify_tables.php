<?php
/**
 * Verificador Simple de Tablas de Notificaciones
 */

require_once('core/autoload.php');

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Verificador de Tablas</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: green; padding: 8px; margin: 5px 0; }
        .error { color: red; padding: 8px; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔍 Verificador de Tablas de Notificaciones</h1>";

$tables = [
    'notification_config' => 'Configuración SMTP',
    'notification_types' => 'Tipos de Notificaciones', 
    'notification_log' => 'Historial de Envíos',
    'notification_queue' => 'Cola de Notificaciones'
];

echo "<table>";
echo "<tr><th>Tabla</th><th>Descripción</th><th>Estado</th><th>Registros</th></tr>";

$all_ok = true;

foreach($tables as $table => $description) {
    echo "<tr>";
    echo "<td><strong>$table</strong></td>";
    echo "<td>$description</td>";
    
    try {
        // Verificar existencia y contar registros
        $result = Executor::doit("SELECT COUNT(*) as count FROM `$table`");
        
        if($result && isset($result[0])) {
            // Obtener el resultado
            if(is_array($result[0]) && isset($result[0]['count'])) {
                $count = $result[0]['count'];
            } else if(is_object($result[0])) {
                $row = mysqli_fetch_assoc($result[0]);
                $count = $row ? $row['count'] : 0;
            } else {
                $count = 0;
            }
            
            echo "<td class='success'>✅ Existe</td>";
            echo "<td>$count</td>";
        } else {
            echo "<td class='error'>❌ No existe</td>";
            echo "<td>-</td>";
            $all_ok = false;
        }
    } catch(Exception $e) {
        echo "<td class='error'>❌ Error</td>";
        echo "<td>Error: " . $e->getMessage() . "</td>";
        $all_ok = false;
    }
    
    echo "</tr>";
}

echo "</table>";

if($all_ok) {
    echo "<div class='success'>";
    echo "<h2>🎉 ¡Todas las tablas están funcionando correctamente!</h2>";
    echo "<p>El sistema de notificaciones está listo para usar.</p>";
    echo "<h3>Próximos pasos:</h3>";
    echo "<ul>";
    echo "<li><a href='?view=notificationconfig'>⚙️ Configurar SMTP</a></li>";
    echo "<li><a href='notification_test.php'>🧪 Ejecutar Pruebas Completas</a></li>";
    echo "<li><a href='?view=notifications'>📧 Ver Historial de Notificaciones</a></li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<h2>⚠️ Hay problemas con las tablas</h2>";
    echo "<p>Ejecute el instalador directo para corregir:</p>";
    echo "<p><a href='install_notifications_direct.php'>🔧 Instalador Directo</a></p>";
    echo "</div>";
}

echo "</div>
</body>
</html>";
?>
