<?php
/**
 * Script de migración de base de datos
 * Migra de BookClinica a oncology_database
 */

// Configuración de base de datos original
$original_host = "localhost";
$original_user = "root";
$original_pass = "";
$original_db = "BookClinica";

// Configuración de nueva base de datos
$new_host = "localhost";
$new_user = "root";
$new_pass = "";
$new_db = "oncology_database";

echo "<h1>🏥 Migración de Base de Datos - Sistema de Oncología</h1>";

try {
    // Conectar a MySQL
    $mysqli = new mysqli($original_host, $original_user, $original_pass);
    
    if ($mysqli->connect_error) {
        die("Error de conexión: " . $mysqli->connect_error);
    }
    
    echo "<p>✅ Conexión establecida con MySQL</p>";
    
    // Crear nueva base de datos
    $sql_create_db = "CREATE DATABASE IF NOT EXISTS `$new_db` CHARACTER SET utf8 COLLATE utf8_general_ci";
    if ($mysqli->query($sql_create_db)) {
        echo "<p>✅ Base de datos '$new_db' creada exitosamente</p>";
    } else {
        echo "<p>❌ Error creando base de datos: " . $mysqli->error . "</p>";
    }
    
    // Conectar a la base de datos original
    $mysqli->select_db($original_db);
    
    if ($mysqli->error) {
        echo "<p>⚠️ Base de datos original '$original_db' no encontrada. Creando estructura básica...</p>";
        
        // Conectar a la nueva base de datos
        $mysqli->select_db($new_db);
        
        // Crear estructura básica
        $tables_sql = [
            // Tabla de usuarios
            "CREATE TABLE IF NOT EXISTS `user` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `lastname` varchar(100) NOT NULL,
                `username` varchar(50) NOT NULL,
                `email` varchar(255) DEFAULT NULL,
                `password` varchar(60) NOT NULL,
                `is_active` tinyint(1) NOT NULL DEFAULT '1',
                `kind` int(2) DEFAULT '2',
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            
            // Tabla de categorías
            "CREATE TABLE IF NOT EXISTS `category` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(200) NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            
            // Tabla de pacientes
            "CREATE TABLE IF NOT EXISTS `pacient` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `no` varchar(50) DEFAULT NULL,
                `name` varchar(50) NOT NULL,
                `lastname` varchar(50) NOT NULL,
                `gender` varchar(1) DEFAULT NULL,
                `born` date DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `address` text,
                `phone` varchar(255) DEFAULT NULL,
                `image` varchar(255) DEFAULT NULL,
                `sick` text,
                `medicaments` text,
                `alergy` text,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `is_active` tinyint(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            
            // Tabla de médicos
            "CREATE TABLE IF NOT EXISTS `medic` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `no` varchar(50) DEFAULT NULL,
                `name` varchar(50) NOT NULL,
                `lastname` varchar(50) NOT NULL,
                `username` varchar(50) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `password` varchar(60) DEFAULT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `is_active` tinyint(1) NOT NULL DEFAULT '1',
                `category_id` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `category_id` (`category_id`),
                CONSTRAINT `medic_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            
            // Tabla de reservaciones
            "CREATE TABLE IF NOT EXISTS `reservation` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(100) DEFAULT NULL,
                `note` text,
                `message` text,
                `date_at` date NOT NULL,
                `time_at` time NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `pacient_id` int(11) NOT NULL,
                `medic_id` int(11) NOT NULL,
                `status_id` int(11) NOT NULL DEFAULT '1',
                `payment_id` int(11) NOT NULL DEFAULT '1',
                `is_web` tinyint(1) NOT NULL DEFAULT '0',
                `chair_id` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `pacient_id` (`pacient_id`),
                KEY `medic_id` (`medic_id`),
                CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`pacient_id`) REFERENCES `pacient` (`id`),
                CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`medic_id`) REFERENCES `medic` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            
            // Tabla de sillones de oncología
            "CREATE TABLE IF NOT EXISTS `oncology_chair` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `description` text,
                `is_active` tinyint(1) NOT NULL DEFAULT '1',
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            
            // Tabla de lista de espera de oncología
            "CREATE TABLE IF NOT EXISTS `oncology_waitlist` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `pacient_id` int(11) NOT NULL,
                `treatment_type` varchar(100) NOT NULL,
                `priority_level` int(11) NOT NULL DEFAULT '3',
                `requested_date` date NOT NULL,
                `requested_time` time NOT NULL,
                `duration_minutes` int(11) NOT NULL DEFAULT '60',
                `notes` text,
                `status` enum('pending','assigned','completed','cancelled') NOT NULL DEFAULT 'pending',
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `assigned_at` datetime DEFAULT NULL,
                `reservation_id` int(11) DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `pacient_id` (`pacient_id`),
                KEY `reservation_id` (`reservation_id`),
                CONSTRAINT `oncology_waitlist_ibfk_1` FOREIGN KEY (`pacient_id`) REFERENCES `pacient` (`id`),
                CONSTRAINT `oncology_waitlist_ibfk_2` FOREIGN KEY (`reservation_id`) REFERENCES `reservation` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            
            // Tabla de disponibilidad de sillones
            "CREATE TABLE IF NOT EXISTS `chair_availability` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `chair_id` int(11) NOT NULL,
                `date_at` date NOT NULL,
                `start_time` time NOT NULL,
                `end_time` time NOT NULL,
                `is_blocked` tinyint(1) NOT NULL DEFAULT '0',
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `chair_id` (`chair_id`),
                CONSTRAINT `chair_availability_ibfk_1` FOREIGN KEY (`chair_id`) REFERENCES `oncology_chair` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
            
            // Tabla de configuración de oncología
            "CREATE TABLE IF NOT EXISTS `oncology_config` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `default_appointment_duration` int(11) NOT NULL DEFAULT '60',
                `max_daily_appointments` int(11) NOT NULL DEFAULT '8',
                `auto_assign_enabled` tinyint(1) NOT NULL DEFAULT '1',
                `waitlist_enabled` tinyint(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        ];
        
        foreach ($tables_sql as $table_sql) {
            if ($mysqli->query($table_sql)) {
                echo "<p>✅ Tabla creada exitosamente</p>";
            } else {
                echo "<p>❌ Error creando tabla: " . $mysqli->error . "</p>";
            }
        }
        
        // Insertar datos básicos
        $basic_data = [
            "INSERT IGNORE INTO `category` (`id`, `name`) VALUES (1, 'Oncología')",
            "INSERT IGNORE INTO `user` (`id`, `name`, `lastname`, `username`, `email`, `password`, `kind`) VALUES 
                (1, 'Administrador', 'Sistema', 'admin', 'admin@oncology.com', '" . sha1(md5('admin')) . "', 1)",
            "INSERT IGNORE INTO `oncology_chair` (`name`, `description`) VALUES 
                ('Sillón Oncología A', 'Sillón principal para quimioterapia'),
                ('Sillón Oncología B', 'Sillón secundario para tratamientos'),
                ('Sillón Oncología C', 'Sillón de reserva')",
            "INSERT IGNORE INTO `oncology_config` (`default_appointment_duration`, `max_daily_appointments`, `auto_assign_enabled`, `waitlist_enabled`) VALUES 
                (60, 8, 1, 1)"
        ];
        
        foreach ($basic_data as $data_sql) {
            if ($mysqli->query($data_sql)) {
                echo "<p>✅ Datos básicos insertados</p>";
            } else {
                echo "<p>❌ Error insertando datos: " . $mysqli->error . "</p>";
            }
        }
        
    } else {
        echo "<p>✅ Base de datos original encontrada. Copiando datos relevantes...</p>";
        
        // Conectar a la nueva base de datos
        $mysqli->select_db($new_db);
        
        // Copiar estructura y datos de tablas esenciales
        $tables_to_copy = ['user', 'category', 'pacient', 'medic', 'reservation'];
        
        foreach ($tables_to_copy as $table) {
            // Crear tabla en nueva base de datos
            $create_table_sql = "CREATE TABLE IF NOT EXISTS `$new_db`.`$table` LIKE `$original_db`.`$table`";
            if ($mysqli->query($create_table_sql)) {
                echo "<p>✅ Estructura de tabla '$table' copiada</p>";
                
                // Copiar datos
                $copy_data_sql = "INSERT IGNORE INTO `$new_db`.`$table` SELECT * FROM `$original_db`.`$table`";
                if ($mysqli->query($copy_data_sql)) {
                    echo "<p>✅ Datos de tabla '$table' copiados</p>";
                } else {
                    echo "<p>⚠️ No se pudieron copiar datos de '$table': " . $mysqli->error . "</p>";
                }
            } else {
                echo "<p>❌ Error copiando estructura de '$table': " . $mysqli->error . "</p>";
            }
        }
        
        // Copiar tablas específicas de oncología si existen
        $oncology_tables = ['oncology_chair', 'oncology_waitlist', 'chair_availability', 'oncology_config'];
        
        foreach ($oncology_tables as $table) {
            $check_table = "SELECT 1 FROM `$original_db`.`$table` LIMIT 1";
            if ($mysqli->query($check_table)) {
                $create_table_sql = "CREATE TABLE IF NOT EXISTS `$new_db`.`$table` LIKE `$original_db`.`$table`";
                if ($mysqli->query($create_table_sql)) {
                    $copy_data_sql = "INSERT IGNORE INTO `$new_db`.`$table` SELECT * FROM `$original_db`.`$table`";
                    $mysqli->query($copy_data_sql);
                    echo "<p>✅ Tabla de oncología '$table' migrada</p>";
                }
            }
        }
    }
    
    echo "<h2>🎉 Migración Completada</h2>";
    echo "<p><strong>Nueva base de datos:</strong> $new_db</p>";
    echo "<p><strong>Sistema listo para usar!</strong></p>";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<p>❌ Error durante la migración: " . $e->getMessage() . "</p>";
}
?>
