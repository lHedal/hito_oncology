<?php
// Script directo para crear tablas faltantes
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "oncology_database";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔧 Reparando Sistema de Notificaciones</h2>";
    
    // Crear tabla notification_config
    $sql_config = "CREATE TABLE IF NOT EXISTS `notification_config` (
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
    echo "<p>✅ Tabla notification_config creada</p>";
    
    // Crear tabla notification_types
    $sql_types = "CREATE TABLE IF NOT EXISTS `notification_types` (
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
    echo "<p>✅ Tabla notification_types creada</p>";
    
    // Crear tabla notification_log
    $sql_log = "CREATE TABLE IF NOT EXISTS `notification_log` (
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
    echo "<p>✅ Tabla notification_log creada</p>";
    
    // Crear tabla notification_queue
    $sql_queue = "CREATE TABLE IF NOT EXISTS `notification_queue` (
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
    echo "<p>✅ Tabla notification_queue creada</p>";
    
    // Insertar configuración inicial
    $check_config = $pdo->query("SELECT COUNT(*) FROM notification_config")->fetchColumn();
    if($check_config == 0) {
        $insert_config = "INSERT INTO notification_config (smtp_host, smtp_port, smtp_security, from_email, from_name, notifications_enabled) VALUES 
        ('smtp.gmail.com', 587, 'tls', 'sistema@oncologia.cl', 'Sistema Oncológico', 1)";
        $pdo->exec($insert_config);
        echo "<p>✅ Configuración inicial insertada</p>";
    } else {
        echo "<p>ℹ️ Configuración ya existe</p>";
    }
    
    // Insertar tipos de notificación
    $check_types = $pdo->query("SELECT COUNT(*) FROM notification_types")->fetchColumn();
    if($check_types == 0) {
        $insert_types = "INSERT INTO notification_types (code, name, description, template_subject, template_body, send_to_patient, send_to_medic) VALUES
        ('appointment_scheduled', 'Cita Agendada', 'Notificación cuando se agenda una nueva cita', 'Cita Agendada - Sistema Oncológico', '<h2>✅ Cita Agendada Exitosamente</h2><p>Su cita ha sido agendada para el {{date}} a las {{time}}.</p><p><strong>Médico:</strong> {{medic_name}}</p>', 1, 1),
        ('appointment_reminder', 'Recordatorio de Cita', 'Recordatorio 24 horas antes de la cita', 'Recordatorio: Cita Mañana - Sistema Oncológico', '<h2>⏰ Recordatorio de Cita</h2><p>Le recordamos que tiene una cita mañana {{date}} a las {{time}}.</p><p><strong>Médico:</strong> {{medic_name}}</p>', 1, 0),
        ('waitlist_added', 'Agregado a Lista de Espera', 'Notificación cuando se agrega a lista de espera', 'Agregado a Lista de Espera - Sistema Oncológico', '<h2>📋 Agregado a Lista de Espera</h2><p>Ha sido agregado a la lista de espera para tratamiento oncológico.</p>', 1, 1),
        ('waitlist_assigned', 'Cita Asignada desde Lista de Espera', 'Notificación cuando se asigna cita desde lista de espera', '🎉 Cita Asignada - Sistema Oncológico', '<h2>🎉 ¡Su Cita Ha Sido Asignada!</h2><p>Nos complace informarle que se ha asignado una cita desde la lista de espera.</p>', 1, 1),
        ('patient_registered', 'Paciente Registrado', 'Notificación de bienvenida para nuevos pacientes', 'Bienvenido al Sistema Oncológico', '<h2>👋 ¡Bienvenido al Sistema Oncológico!</h2><p>Su registro ha sido completado exitosamente.</p>', 1, 0)";
        $pdo->exec($insert_types);
        echo "<p>✅ Tipos de notificación insertados</p>";
    } else {
        echo "<p>ℹ️ Tipos de notificación ya existen</p>";
    }
    
    echo "<br><div style='background: #d4edda; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb;'>";
    echo "<h3 style='color: #155724; margin: 0;'>🎉 ¡Sistema Reparado Exitosamente!</h3>";
    echo "<p style='color: #155724; margin: 10px 0 0 0;'>Todas las tablas han sido creadas y el sistema está listo para usar.</p>";
    echo "</div>";
    
    echo "<br><p><strong>Acciones disponibles:</strong></p>";
    echo "<ul>";
    echo "<li><a href='index.php?view=notificationconfig'>Configurar SMTP</a></li>";
    echo "<li><a href='index.php?view=notifications'>Ver Notificaciones</a></li>";
    echo "<li><a href='index.php?view=notificationqueue'>Ver Cola</a></li>";
    echo "<li><a href='index.php?view=oncologydashboard'>Ir al Dashboard</a></li>";
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3 style='color: #721c24; margin: 0;'>❌ Error de Base de Datos</h3>";
    echo "<p style='color: #721c24; margin: 10px 0 0 0;'>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
