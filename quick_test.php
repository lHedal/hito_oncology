<?php
/**
 * Test Rápido de Tablas de Notificaciones
 */

require_once('core/autoload.php');
require_once('core/app/model/NotificationData.php');

echo "<h2>🔍 Test Rápido de Tablas</h2>";

// Test directo usando mysqli
try {
    $con = Database::getCon();
    
    $tables = ['notification_config', 'notification_types', 'notification_log', 'notification_queue'];
    
    foreach($tables as $table) {
        $result = $con->query("SELECT COUNT(*) as count FROM `$table`");
        if($result) {
            $row = $result->fetch_assoc();
            echo "<div style='color: green;'>✅ Tabla '$table': " . $row['count'] . " registros</div>";
        } else {
            echo "<div style='color: red;'>❌ Error en tabla '$table': " . $con->error . "</div>";
        }
    }
    
    echo "<br><h3>📊 Test de NotificationConfigData</h3>";
    
    // Test de la clase
    try {
        $config = NotificationConfigData::getConfig();
        if($config) {
            echo "<div style='color: green;'>✅ NotificationConfigData::getConfig() funciona</div>";
            echo "<div>Configuración encontrada: ID " . $config->id . "</div>";
        } else {
            echo "<div style='color: orange;'>⚠️ No hay configuración guardada (normal en primera instalación)</div>";
        }
    } catch(Exception $e) {
        echo "<div style='color: red;'>❌ Error en NotificationConfigData: " . $e->getMessage() . "</div>";
    }
    
} catch(Exception $e) {
    echo "<div style='color: red;'>❌ Error de conexión: " . $e->getMessage() . "</div>";
}

echo "<br><a href='install_notifications_direct.php'>🔧 Instalador Directo</a> | ";
echo "<a href='?view=notificationconfig'>⚙️ Configuración</a>";
?>
