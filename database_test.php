<?php
/**
 * Test de Conectividad de Base de Datos
 */

require_once('core/autoload.php');

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Test de Base de Datos</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: green; padding: 8px; margin: 5px 0; background: #d4edda; border-radius: 4px; }
        .error { color: red; padding: 8px; margin: 5px 0; background: #f8d7da; border-radius: 4px; }
        .info { color: blue; padding: 8px; margin: 5px 0; background: #d1ecf1; border-radius: 4px; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔌 Test de Conectividad de Base de Datos</h1>";

// Test 1: Conexión básica
echo "<h3>1. Test de Conexión</h3>";
try {
    $con = Database::getCon();
    if($con) {
        echo "<div class='success'>✅ Conexión a MySQL exitosa</div>";
        echo "<div class='info'>📊 Servidor: " . $con->host_info . "</div>";
        echo "<div class='info'>🗄️ Base de datos: oncology_database</div>";
    } else {
        echo "<div class='error'>❌ No se pudo conectar a la base de datos</div>";
        exit;
    }
} catch(Exception $e) {
    echo "<div class='error'>❌ Error de conexión: " . $e->getMessage() . "</div>";
    exit;
}

// Test 2: Verificar tablas existentes
echo "<h3>2. Tablas Existentes en la Base de Datos</h3>";
try {
    $result = $con->query("SHOW TABLES");
    if($result) {
        echo "<div class='info'>📋 Tablas encontradas:</div>";
        echo "<ul>";
        while($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }
} catch(Exception $e) {
    echo "<div class='error'>❌ Error listando tablas: " . $e->getMessage() . "</div>";
}

// Test 3: Verificar tablas de notificaciones específicamente
echo "<h3>3. Verificar Tablas de Notificaciones</h3>";
$notification_tables = ['notification_config', 'notification_types', 'notification_log', 'notification_queue'];

foreach($notification_tables as $table) {
    try {
        $result = $con->query("SHOW TABLES LIKE '$table'");
        if($result && $result->num_rows > 0) {
            echo "<div class='success'>✅ Tabla '$table' existe</div>";
            
            // Contar registros
            $count_result = $con->query("SELECT COUNT(*) as count FROM `$table`");
            if($count_result) {
                $count_row = $count_result->fetch_assoc();
                echo "<div class='info'>📊 Registros en '$table': " . $count_row['count'] . "</div>";
            }
        } else {
            echo "<div class='error'>❌ Tabla '$table' NO existe</div>";
        }
    } catch(Exception $e) {
        echo "<div class='error'>❌ Error verificando '$table': " . $e->getMessage() . "</div>";
    }
}

// Test 4: Test de Executor
echo "<h3>4. Test del Executor (sistema interno)</h3>";
try {
    $result = Executor::doit("SELECT 1 as test");
    if($result) {
        echo "<div class='success'>✅ Executor funcionando correctamente</div>";
    } else {
        echo "<div class='error'>❌ Executor no está funcionando</div>";
    }
} catch(Exception $e) {
    echo "<div class='error'>❌ Error en Executor: " . $e->getMessage() . "</div>";
}

// Test 5: Probar creación de tabla temporal
echo "<h3>5. Test de Permisos de Escritura</h3>";
try {
    $test_table = "test_notifications_" . time();
    $create_sql = "CREATE TEMPORARY TABLE `$test_table` (id INT AUTO_INCREMENT PRIMARY KEY, test_data VARCHAR(50))";
    $result = $con->query($create_sql);
    
    if($result) {
        echo "<div class='success'>✅ Permisos de creación de tablas: OK</div>";
        
        // Test insert
        $insert_sql = "INSERT INTO `$test_table` (test_data) VALUES ('test')";
        $insert_result = $con->query($insert_sql);
        
        if($insert_result) {
            echo "<div class='success'>✅ Permisos de inserción: OK</div>";
        }
    } else {
        echo "<div class='error'>❌ No hay permisos para crear tablas</div>";
        echo "<div class='error'>Error: " . $con->error . "</div>";
    }
} catch(Exception $e) {
    echo "<div class='error'>❌ Error testando permisos: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<h3>🔧 Acciones Disponibles</h3>";
echo "<p><a href='install_notifications_direct.php'>🔨 Ejecutar Instalador Directo</a></p>";
echo "<p><a href='verify_tables.php'>🔍 Verificar Tablas</a></p>";
echo "<p><a href='notification_test.php'>🧪 Pruebas Completas</a></p>";

echo "</div>
</body>
</html>";
?>
