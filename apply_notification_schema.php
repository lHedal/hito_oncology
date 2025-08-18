<?php
/**
 * Script para aplicar el esquema de notificaciones
 * Aplica solo las tablas de notificaciones sin afectar otros datos
 */

// Configuración de base de datos
$host = "localhost";
$username = "root";
$password = "";
$database = "oncology_database";

try {
    // Conectar a MySQL
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🔔 Aplicando Esquema de Notificaciones</h1>";
    
    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $database");
    echo "<p>✅ Base de datos '$database' verificada</p>";
    
    // Usar la base de datos
    $pdo->exec("USE $database");
    
    // Leer y ejecutar el esquema de notificaciones
    $schema_file = 'notification_schema.sql';
    if(file_exists($schema_file)) {
        $sql = file_get_contents($schema_file);
        
        // Separar las consultas por punto y coma
        $queries = explode(';', $sql);
        
        foreach($queries as $query) {
            $query = trim($query);
            if(!empty($query) && !preg_match('/^--/', $query)) {
                try {
                    $pdo->exec($query);
                    
                    // Mostrar progreso para consultas importantes
                    if(preg_match('/CREATE TABLE (\w+)/', $query, $matches)) {
                        echo "<p>✅ Tabla '{$matches[1]}' creada exitosamente</p>";
                    } elseif(preg_match('/INSERT INTO (\w+)/', $query, $matches)) {
                        echo "<p>✅ Datos iniciales insertados en '{$matches[1]}'</p>";
                    }
                } catch(PDOException $e) {
                    // Ignorar errores de "tabla ya existe"
                    if(strpos($e->getMessage(), 'already exists') === false) {
                        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
                    } else {
                        if(preg_match('/CREATE TABLE (\w+)/', $query, $matches)) {
                            echo "<p>ℹ️ Tabla '{$matches[1]}' ya existe</p>";
                        }
                    }
                }
            }
        }
        
        echo "<p>✅ Esquema de notificaciones aplicado exitosamente</p>";
        
        // Verificar que las tablas fueron creadas
        echo "<h2>🔍 Verificando Tablas Creadas:</h2>";
        $tables = ['notification_config', 'notification_types', 'notification_log', 'notification_queue'];
        
        foreach($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if($stmt->rowCount() > 0) {
                echo "<p>✅ Tabla '$table' existe</p>";
                
                // Mostrar número de registros
                $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo "<p>&nbsp;&nbsp;&nbsp;📊 $count registros</p>";
            } else {
                echo "<p>❌ Tabla '$table' no encontrada</p>";
            }
        }
        
        echo "<h2>🎉 Sistema de Notificaciones Listo</h2>";
        echo "<p>El sistema de notificaciones ha sido configurado exitosamente.</p>";
        echo "<p><strong>Próximos pasos:</strong></p>";
        echo "<ul>";
        echo "<li>Configurar SMTP en: <a href='index.php?view=notificationconfig'>Configuración de Notificaciones</a></li>";
        echo "<li>Ver historial en: <a href='index.php?view=notifications'>Historial de Notificaciones</a></li>";
        echo "<li>Gestionar cola en: <a href='index.php?view=notificationqueue'>Cola de Notificaciones</a></li>";
        echo "</ul>";
        
    } else {
        echo "<p>❌ Archivo '$schema_file' no encontrado</p>";
    }
    
} catch(PDOException $e) {
    echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
}
?>
