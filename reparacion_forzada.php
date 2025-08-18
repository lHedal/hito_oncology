<?php
// Script de reparación forzada - Elimina y recrea todas las tablas
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "oncology_database";

echo "<h1>🚨 Reparación Forzada del Sistema de Notificaciones</h1>";
echo "<p><strong>ADVERTENCIA:</strong> Este script eliminará y recreará todas las tablas de notificaciones.</p>";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<div style='background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ Conexión establecida con la base de datos";
    echo "</div>";
    
    // Paso 1: Eliminar tablas existentes
    echo "<h2>🗑️ Paso 1: Eliminando tablas existentes</h2>";
    $tables_to_drop = ['notification_queue', 'notification_log', 'notification_types', 'notification_config'];
    
    foreach($tables_to_drop as $table) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS $table");
            echo "<p style='color: orange;'>🗑️ Tabla $table eliminada</p>";
        } catch(Exception $e) {
            echo "<p style='color: red;'>⚠️ Error eliminando $table: " . $e->getMessage() . "</p>";
        }
    }
    
    // Paso 2: Crear tablas desde cero
    echo "<h2>🔨 Paso 2: Creando tablas desde cero</h2>";
    
    // Tabla notification_config
    $sql_config = "CREATE TABLE `notification_config` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `smtp_host` varchar(255) DEFAULT 'smtp.gmail.com',
        `smtp_port` int(11) DEFAULT 587,
        `smtp_security` enum('tls','ssl','none') DEFAULT 'tls',
        `smtp_username` varchar(255) DEFAULT NULL,
        `smtp_password` varchar(255) DEFAULT NULL,
        `from_email` varchar(255) DEFAULT NULL,
        `from_name` varchar(255) DEFAULT 'Sistema Oncológico',
        `notifications_enabled` tinyint(1) DEFAULT 1,
        `auto_send_enabled` tinyint(1) DEFAULT 1,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    $pdo->exec($sql_config);
    echo "<p style='color: green;'>✅ notification_config creada</p>";
    
    // Tabla notification_types
    $sql_types = "CREATE TABLE `notification_types` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `code` varchar(50) NOT NULL,
        `name` varchar(255) NOT NULL,
        `description` text,
        `template_subject` varchar(255) DEFAULT NULL,
        `template_body` text,
        `is_active` tinyint(1) DEFAULT 1,
        `send_to_patient` tinyint(1) DEFAULT 1,
        `send_to_medic` tinyint(1) DEFAULT 1,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `code` (`code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    $pdo->exec($sql_types);
    echo "<p style='color: green;'>✅ notification_types creada</p>";
    
    // Tabla notification_log
    $sql_log = "CREATE TABLE `notification_log` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `notification_type_id` int(11) NOT NULL,
        `recipient_email` varchar(255) NOT NULL,
        `recipient_name` varchar(255) DEFAULT NULL,
        `recipient_type` enum('patient','medic','admin') NOT NULL,
        `subject` varchar(255) DEFAULT NULL,
        `body` text,
        `status` enum('pending','sent','failed','cancelled') DEFAULT 'pending',
        `error_message` text,
        `reference_id` int(11) DEFAULT NULL,
        `reference_type` enum('reservation','waitlist','patient','medic') DEFAULT NULL,
        `sent_at` datetime DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_notification_log_status` (`status`),
        KEY `idx_notification_log_type` (`notification_type_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    $pdo->exec($sql_log);
    echo "<p style='color: green;'>✅ notification_log creada</p>";
    
    // Tabla notification_queue
    $sql_queue = "CREATE TABLE `notification_queue` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `notification_type_id` int(11) NOT NULL,
        `recipient_email` varchar(255) NOT NULL,
        `recipient_name` varchar(255) DEFAULT NULL,
        `recipient_type` enum('patient','medic','admin') NOT NULL,
        `subject` varchar(255) DEFAULT NULL,
        `body` text,
        `scheduled_at` datetime NOT NULL,
        `reference_id` int(11) DEFAULT NULL,
        `reference_type` enum('reservation','waitlist','patient','medic') DEFAULT NULL,
        `attempts` int(11) DEFAULT 0,
        `max_attempts` int(11) DEFAULT 3,
        `status` enum('pending','processing','sent','failed','cancelled') DEFAULT 'pending',
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_notification_queue_scheduled` (`scheduled_at`),
        KEY `idx_notification_queue_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    
    $pdo->exec($sql_queue);
    echo "<p style='color: green;'>✅ notification_queue creada</p>";
    
    // Paso 3: Insertar datos iniciales
    echo "<h2>📝 Paso 3: Insertando datos iniciales</h2>";
    
    // Configuración inicial
    $insert_config = "INSERT INTO notification_config (smtp_host, smtp_port, smtp_security, from_email, from_name, notifications_enabled) VALUES 
    ('smtp.gmail.com', 587, 'tls', 'sistema@oncologia.cl', 'Sistema Oncológico', 1)";
    $pdo->exec($insert_config);
    echo "<p style='color: green;'>✅ Configuración inicial insertada</p>";
    
    // Tipos de notificación
    $notification_types = [
        ['appointment_scheduled', 'Cita Agendada', 'Notificación cuando se agenda una nueva cita', 'Cita Agendada - Sistema Oncológico', '<h2>✅ Cita Agendada Exitosamente</h2><p>Su cita ha sido agendada para el {{date}} a las {{time}}.</p><p><strong>Médico:</strong> {{medic_name}}</p>', 1, 1],
        ['appointment_reminder', 'Recordatorio de Cita', 'Recordatorio 24 horas antes de la cita', 'Recordatorio: Cita Mañana - Sistema Oncológico', '<h2>⏰ Recordatorio de Cita</h2><p>Le recordamos que tiene una cita mañana {{date}} a las {{time}}.</p><p><strong>Médico:</strong> {{medic_name}}</p>', 1, 0],
        ['waitlist_added', 'Agregado a Lista de Espera', 'Notificación cuando se agrega a lista de espera', 'Agregado a Lista de Espera - Sistema Oncológico', '<h2>📋 Agregado a Lista de Espera</h2><p>Ha sido agregado a la lista de espera para tratamiento oncológico.</p>', 1, 1],
        ['waitlist_assigned', 'Cita Asignada desde Lista de Espera', 'Notificación cuando se asigna cita desde lista de espera', '🎉 Cita Asignada - Sistema Oncológico', '<h2>🎉 ¡Su Cita Ha Sido Asignada!</h2><p>Nos complace informarle que se ha asignado una cita desde la lista de espera.</p>', 1, 1],
        ['patient_registered', 'Paciente Registrado', 'Notificación de bienvenida para nuevos pacientes', 'Bienvenido al Sistema Oncológico', '<h2>👋 ¡Bienvenido al Sistema Oncológico!</h2><p>Su registro ha sido completado exitosamente.</p>', 1, 0]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO notification_types (code, name, description, template_subject, template_body, send_to_patient, send_to_medic) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach($notification_types as $type) {
        $stmt->execute($type);
        echo "<p style='color: green;'>✅ Tipo '{$type[0]}' insertado</p>";
    }
    
    // Paso 4: Verificación final
    echo "<h2>🔍 Paso 4: Verificación final</h2>";
    $tables_to_verify = ['notification_config', 'notification_types', 'notification_log', 'notification_queue'];
    
    foreach($tables_to_verify as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p style='color: green;'>✅ Tabla $table: $count registros</p>";
    }
    
    // Mensaje de éxito
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0; border: 2px solid #28a745;'>";
    echo "<h2 style='color: #155724; margin: 0;'>🎉 ¡REPARACIÓN COMPLETADA EXITOSAMENTE!</h2>";
    echo "<p style='color: #155724; margin: 10px 0 0 0;'>Todas las tablas han sido recreadas y el sistema está listo para usar.</p>";
    echo "</div>";
    
    echo "<h3>🚀 Próximos pasos:</h3>";
    echo "<ul>";
    echo "<li><a href='index.php?view=notificationconfig' style='color: blue; font-weight: bold;'>⚙️ Configurar SMTP</a></li>";
    echo "<li><a href='index.php?view=notifications' style='color: blue; font-weight: bold;'>📊 Ver panel de notificaciones</a></li>";
    echo "<li><a href='index.php?view=oncologydashboard' style='color: blue; font-weight: bold;'>🏠 Ir al dashboard</a></li>";
    echo "<li><a href='notification_test.php' style='color: blue; font-weight: bold;'>🧪 Ejecutar test del sistema</a></li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3 style='color: #721c24; margin: 0;'>❌ Error Crítico</h3>";
    echo "<p style='color: #721c24; margin: 10px 0 0 0;'>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
