<?php
/**
 * Debug - Test Configuration Save
 */

require_once('core/autoload.php');
include "core/app/model/NotificationData.php";
include "core/app/model/NotificationService.php";

echo "<h2>🔧 Test de Guardado de Configuración</h2>";

// Test 1: Verificar que las clases existen
echo "<h3>1. Verificar Clases</h3>";
if(class_exists('NotificationConfigData')) {
    echo "<div style='color: green;'>✅ NotificationConfigData existe</div>";
} else {
    echo "<div style='color: red;'>❌ NotificationConfigData no existe</div>";
}

if(class_exists('NotificationService')) {
    echo "<div style='color: green;'>✅ NotificationService existe</div>";
} else {
    echo "<div style='color: red;'>❌ NotificationService no existe</div>";
}

// Test 2: Verificar configuración actual
echo "<h3>2. Configuración Actual</h3>";
try {
    $config = NotificationConfigData::getConfig();
    if($config) {
        echo "<div style='color: green;'>✅ Configuración existente encontrada</div>";
        echo "<div>ID: " . $config->id . "</div>";
        echo "<div>SMTP Host: " . $config->smtp_host . "</div>";
        echo "<div>From Email: " . $config->from_email . "</div>";
    } else {
        echo "<div style='color: orange;'>⚠️ No hay configuración existente</div>";
    }
} catch(Exception $e) {
    echo "<div style='color: red;'>❌ Error obteniendo configuración: " . $e->getMessage() . "</div>";
}

// Test 3: Probar creación/actualización
echo "<h3>3. Test de Actualización</h3>";
try {
    $test_config_data = [
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_security' => 'tls',
        'smtp_username' => 'test@gmail.com',
        'smtp_password' => 'test_password',
        'from_email' => 'sistema@oncologia.cl',
        'from_name' => 'Sistema Test',
        'notifications_enabled' => 1,
        'auto_send_enabled' => 1
    ];
    
    $result = NotificationService::updateConfig($test_config_data);
    
    if($result !== false) {
        echo "<div style='color: green;'>✅ Test de actualización exitoso</div>";
        echo "<div>Resultado: " . var_export($result, true) . "</div>";
    } else {
        echo "<div style='color: red;'>❌ Test de actualización falló</div>";
    }
} catch(Exception $e) {
    echo "<div style='color: red;'>❌ Error en test de actualización: " . $e->getMessage() . "</div>";
}

// Test 4: Verificar tabla
echo "<h3>4. Verificar Tabla</h3>";
try {
    $con = Database::getCon();
    $result = $con->query("SELECT COUNT(*) as count FROM notification_config");
    if($result) {
        $row = $result->fetch_assoc();
        echo "<div style='color: green;'>✅ Tabla notification_config tiene " . $row['count'] . " registros</div>";
        
        // Mostrar estructura de tabla
        $structure = $con->query("DESCRIBE notification_config");
        if($structure) {
            echo "<h4>Estructura de la tabla:</h4><ul>";
            while($field = $structure->fetch_assoc()) {
                echo "<li>" . $field['Field'] . " (" . $field['Type'] . ")</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<div style='color: red;'>❌ Error consultando tabla: " . $con->error . "</div>";
    }
} catch(Exception $e) {
    echo "<div style='color: red;'>❌ Error verificando tabla: " . $e->getMessage() . "</div>";
}

echo "<br><a href='?view=notificationconfig'>🔙 Volver a Configuración</a>";
?>
