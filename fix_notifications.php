<?php
// Script simple para verificar y crear las tablas de notificaciones
$host = "localhost";
$username = "root";
$password = "";
$database = "oncology_database";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Creando tablas de notificaciones...</h2>";
    
    // Crear tabla notification_config
    $sql1 = "CREATE TABLE IF NOT EXISTS notification_config (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        smtp_host VARCHAR(255) DEFAULT 'smtp.gmail.com',
        smtp_port INT DEFAULT 587,
        smtp_security ENUM('tls', 'ssl', 'none') DEFAULT 'tls',
        smtp_username VARCHAR(255),
        smtp_password VARCHAR(255),
        from_email VARCHAR(255),
        from_name VARCHAR(255) DEFAULT 'Sistema Oncológico',
        notifications_enabled BOOLEAN DEFAULT 1,
        auto_send_enabled BOOLEAN DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT NOW()
    )";
    
    $pdo->exec($sql1);
    echo "<p>✅ Tabla notification_config creada</p>";
    
    // Crear tabla notification_types
    $sql2 = "CREATE TABLE IF NOT EXISTS notification_types (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        template_subject VARCHAR(255),
        template_body TEXT,
        is_active BOOLEAN DEFAULT 1,
        send_to_patient BOOLEAN DEFAULT 1,
        send_to_medic BOOLEAN DEFAULT 1,
        created_at DATETIME NOT NULL DEFAULT NOW()
    )";
    
    $pdo->exec($sql2);
    echo "<p>✅ Tabla notification_types creada</p>";
    
    // Crear tabla notification_log
    $sql3 = "CREATE TABLE IF NOT EXISTS notification_log (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        notification_type_id INT NOT NULL,
        recipient_email VARCHAR(255) NOT NULL,
        recipient_name VARCHAR(255),
        recipient_type ENUM('patient', 'medic', 'admin') NOT NULL,
        subject VARCHAR(255),
        body TEXT,
        status ENUM('pending', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
        error_message TEXT,
        reference_id INT,
        reference_type ENUM('reservation', 'waitlist', 'patient', 'medic'),
        sent_at DATETIME,
        created_at DATETIME NOT NULL DEFAULT NOW()
    )";
    
    $pdo->exec($sql3);
    echo "<p>✅ Tabla notification_log creada</p>";
    
    // Crear tabla notification_queue
    $sql4 = "CREATE TABLE IF NOT EXISTS notification_queue (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        notification_type_id INT NOT NULL,
        recipient_email VARCHAR(255) NOT NULL,
        recipient_name VARCHAR(255),
        recipient_type ENUM('patient', 'medic', 'admin') NOT NULL,
        subject VARCHAR(255),
        body TEXT,
        scheduled_at DATETIME NOT NULL,
        reference_id INT,
        reference_type ENUM('reservation', 'waitlist', 'patient', 'medic'),
        attempts INT DEFAULT 0,
        max_attempts INT DEFAULT 3,
        status ENUM('pending', 'processing', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
        created_at DATETIME NOT NULL DEFAULT NOW()
    )";
    
    $pdo->exec($sql4);
    echo "<p>✅ Tabla notification_queue creada</p>";
    
    // Insertar configuración inicial
    $sql5 = "INSERT IGNORE INTO notification_config (
        smtp_host, smtp_port, smtp_security, 
        from_email, from_name, notifications_enabled
    ) VALUES (
        'smtp.gmail.com', 587, 'tls',
        'sistema@oncologia.cl', 'Sistema Oncológico', 1
    )";
    
    $pdo->exec($sql5);
    echo "<p>✅ Configuración inicial insertada</p>";
    
    // Insertar tipos de notificaciones
    $notification_types = [
        ['appointment_scheduled', 'Cita Agendada', 'Notificación cuando se agenda una nueva cita', 'Cita Agendada - Sistema Oncológico', '<h2>✅ Cita Agendada Exitosamente</h2><p>Su cita ha sido agendada para el {{date}} a las {{time}}.</p><p><strong>Médico:</strong> {{medic_name}}</p>', 1, 1],
        ['appointment_reminder', 'Recordatorio de Cita', 'Recordatorio 24 horas antes de la cita', 'Recordatorio: Cita Mañana - Sistema Oncológico', '<h2>⏰ Recordatorio de Cita</h2><p>Le recordamos que tiene una cita mañana {{date}} a las {{time}}.</p><p><strong>Médico:</strong> {{medic_name}}</p>', 1, 0],
        ['waitlist_added', 'Agregado a Lista de Espera', 'Notificación cuando se agrega a lista de espera', 'Agregado a Lista de Espera - Sistema Oncológico', '<h2>📋 Agregado a Lista de Espera</h2><p>Ha sido agregado a la lista de espera para tratamiento oncológico.</p>', 1, 1],
        ['patient_registered', 'Paciente Registrado', 'Notificación de bienvenida para nuevos pacientes', 'Bienvenido al Sistema Oncológico', '<h2>👋 ¡Bienvenido al Sistema Oncológico!</h2><p>Su registro ha sido completado exitosamente.</p>', 1, 0]
    ];
    
    foreach($notification_types as $type) {
        $sql_type = "INSERT IGNORE INTO notification_types (code, name, description, template_subject, template_body, send_to_patient, send_to_medic) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql_type);
        $stmt->execute($type);
    }
    
    echo "<p>✅ Tipos de notificaciones insertados</p>";
    
    echo "<h3>🎉 ¡Tablas creadas exitosamente!</h3>";
    echo "<p><a href='index.php?view=notifications'>Ir a Notificaciones</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
