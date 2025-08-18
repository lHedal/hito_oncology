<?php
/**
 * Instalación Directa del Esquema de Notificaciones
 * Este script ejecuta directamente las consultas SQL para crear las tablas
 */

// Incluir dependencias
require_once('core/autoload.php');

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Instalación Directa - Notificaciones</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: green; margin: 10px 0; }
        .error { color: red; margin: 10px 0; }
        .info { color: blue; margin: 10px 0; }
        .step { background: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #007cba; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔧 Instalación Directa del Sistema de Notificaciones</h1>";

// Lista de consultas SQL a ejecutar
$queries = [
    // Crear tabla notification_config
    "CREATE TABLE IF NOT EXISTS notification_config (
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
    )",
    
    // Crear tabla notification_types
    "CREATE TABLE IF NOT EXISTS notification_types (
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
    )",
    
    // Crear tabla notification_log
    "CREATE TABLE IF NOT EXISTS notification_log (
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
    )",
    
    // Crear tabla notification_queue
    "CREATE TABLE IF NOT EXISTS notification_queue (
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
    )"
];

// Datos iniciales para insertar
$initial_data = [
    // Configuración inicial (solo si no existe)
    "INSERT IGNORE INTO notification_config (
        id, smtp_host, smtp_port, smtp_security, 
        from_email, from_name, notifications_enabled
    ) VALUES (
        1, 'smtp.gmail.com', 587, 'tls',
        'sistema@oncologia.cl', 'Sistema Oncológico', 1
    )",
    
    // Tipos de notificaciones
    "INSERT IGNORE INTO notification_types (code, name, description, template_subject, template_body, send_to_patient, send_to_medic) VALUES
    ('appointment_scheduled', 'Cita Agendada', 'Notificación cuando se agenda una nueva cita', 
     'Cita Agendada - Sistema Oncológico', 
     '<h2>✅ Cita Agendada Exitosamente</h2><p>Su cita ha sido agendada para el {{date}} a las {{time}}.</p><p><strong>Médico:</strong> {{medic_name}}</p><p><strong>Tratamiento:</strong> {{treatment_type}}</p><p>Por favor llegue 15 minutos antes de su cita.</p>',
     1, 1)",
     
    "INSERT IGNORE INTO notification_types (code, name, description, template_subject, template_body, send_to_patient, send_to_medic) VALUES
    ('appointment_confirmed', 'Cita Confirmada', 'Notificación de confirmación de cita',
     'Confirmación de Cita - Sistema Oncológico',
     '<h2>✅ Cita Confirmada</h2><p>Su cita del {{date}} a las {{time}} ha sido confirmada.</p><p><strong>Médico:</strong> {{medic_name}}</p><p><strong>Sillón:</strong> {{chair_name}}</p>',
     1, 1)",
     
    "INSERT IGNORE INTO notification_types (code, name, description, template_subject, template_body, send_to_patient, send_to_medic) VALUES
    ('appointment_reminder', 'Recordatorio de Cita', 'Recordatorio 24 horas antes de la cita',
     'Recordatorio: Cita Mañana - Sistema Oncológico',
     '<h2>⏰ Recordatorio de Cita</h2><p>Le recordamos que tiene una cita mañana {{date}} a las {{time}}.</p><p><strong>Médico:</strong> {{medic_name}}</p><p><strong>Sillón:</strong> {{chair_name}}</p><p>Por favor llegue 15 minutos antes.</p>',
     1, 0)",
     
    "INSERT IGNORE INTO notification_types (code, name, description, template_subject, template_body, send_to_patient, send_to_medic) VALUES
    ('waitlist_added', 'Agregado a Lista de Espera', 'Notificación cuando se agrega a lista de espera',
     'Agregado a Lista de Espera - Sistema Oncológico',
     '<h2>📋 Agregado a Lista de Espera</h2><p>Ha sido agregado a la lista de espera para tratamiento oncológico.</p><p><strong>Tipo de Tratamiento:</strong> {{treatment_type}}</p><p><strong>Prioridad:</strong> {{priority_level}}</p><p>Le notificaremos cuando se asigne una cita.</p>',
     1, 1)",
     
    "INSERT IGNORE INTO notification_types (code, name, description, template_subject, template_body, send_to_patient, send_to_medic) VALUES
    ('waitlist_assigned', 'Cita Asignada desde Lista de Espera', 'Notificación cuando se asigna cita desde lista de espera',
     '🎉 Cita Asignada - Sistema Oncológico',
     '<h2>🎉 ¡Su Cita Ha Sido Asignada!</h2><p>Nos complace informarle que se ha asignado una cita desde la lista de espera.</p><p><strong>Fecha:</strong> {{date}}</p><p><strong>Hora:</strong> {{time}}</p><p><strong>Médico:</strong> {{medic_name}}</p><p><strong>Sillón:</strong> {{chair_name}}</p>',
     1, 1)",
     
    "INSERT IGNORE INTO notification_types (code, name, description, template_subject, template_body, send_to_patient, send_to_medic) VALUES
    ('patient_registered', 'Paciente Registrado', 'Notificación de bienvenida para nuevos pacientes',
     'Bienvenido al Sistema Oncológico',
     '<h2>👋 ¡Bienvenido al Sistema Oncológico!</h2><p>Su registro ha sido completado exitosamente.</p><p><strong>Usuario:</strong> {{email}}</p><p>Ya puede acceder al sistema para ver sus citas y tratamientos.</p>',
     1, 0)"
];

// Índices de optimización
$indexes = [
    "CREATE INDEX IF NOT EXISTS idx_notification_log_status ON notification_log(status)",
    "CREATE INDEX IF NOT EXISTS idx_notification_log_type ON notification_log(notification_type_id)",
    "CREATE INDEX IF NOT EXISTS idx_notification_queue_scheduled ON notification_queue(scheduled_at)",
    "CREATE INDEX IF NOT EXISTS idx_notification_queue_status ON notification_queue(status)"
];

$step = 1;
$errors = 0;
$success_count = 0;

// Paso 1: Crear tablas
echo "<div class='step'>";
echo "<h3>Paso $step: Creando Tablas</h3>";
foreach($queries as $i => $query) {
    try {
        $result = Executor::doit($query);
        if($result !== false) {
            echo "<div class='success'>✅ Tabla " . ($i+1) . " creada exitosamente</div>";
            $success_count++;
        } else {
            echo "<div class='error'>❌ Error creando tabla " . ($i+1) . "</div>";
            $errors++;
        }
    } catch(Exception $e) {
        echo "<div class='error'>❌ Error creando tabla " . ($i+1) . ": " . $e->getMessage() . "</div>";
        $errors++;
    }
}
echo "</div>";
$step++;

// Paso 2: Insertar datos iniciales
echo "<div class='step'>";
echo "<h3>Paso $step: Insertando Datos Iniciales</h3>";
foreach($initial_data as $i => $query) {
    try {
        $result = Executor::doit($query);
        if($result !== false) {
            echo "<div class='success'>✅ Datos iniciales " . ($i+1) . " insertados</div>";
            $success_count++;
        } else {
            echo "<div class='info'>ℹ️ Datos iniciales " . ($i+1) . " ya existen (saltado)</div>";
        }
    } catch(Exception $e) {
        echo "<div class='error'>❌ Error insertando datos " . ($i+1) . ": " . $e->getMessage() . "</div>";
        $errors++;
    }
}
echo "</div>";
$step++;

// Paso 3: Crear índices
echo "<div class='step'>";
echo "<h3>Paso $step: Creando Índices de Optimización</h3>";
foreach($indexes as $i => $query) {
    try {
        $result = Executor::doit($query);
        if($result !== false) {
            echo "<div class='success'>✅ Índice " . ($i+1) . " creado exitosamente</div>";
            $success_count++;
        } else {
            echo "<div class='info'>ℹ️ Índice " . ($i+1) . " ya existe (saltado)</div>";
        }
    } catch(Exception $e) {
        echo "<div class='error'>❌ Error creando índice " . ($i+1) . ": " . $e->getMessage() . "</div>";
        $errors++;
    }
}
echo "</div>";
$step++;

// Paso 4: Verificación final
echo "<div class='step'>";
echo "<h3>Paso $step: Verificación Final</h3>";

$tables_to_check = ['notification_config', 'notification_types', 'notification_log', 'notification_queue'];
$tables_ok = 0;

foreach($tables_to_check as $table) {
    try {
        $result = Executor::doit("SELECT COUNT(*) as count FROM `$table`");
        if($result !== false) {
            echo "<div class='success'>✅ Tabla '$table' verificada y accesible</div>";
            $tables_ok++;
        } else {
            echo "<div class='error'>❌ Tabla '$table' no accesible</div>";
        }
    } catch(Exception $e) {
        echo "<div class='error'>❌ Error verificando tabla '$table': " . $e->getMessage() . "</div>";
    }
}
echo "</div>";

// Resumen final
echo "<div class='step'>";
echo "<h3>📊 Resumen de Instalación</h3>";
echo "<p><strong>Operaciones exitosas:</strong> $success_count</p>";
echo "<p><strong>Errores encontrados:</strong> $errors</p>";
echo "<p><strong>Tablas verificadas:</strong> $tables_ok / " . count($tables_to_check) . "</p>";

if($tables_ok == count($tables_to_check) && $errors == 0) {
    echo "<div class='success'><h2>🎉 ¡INSTALACIÓN COMPLETADA EXITOSAMENTE!</h2>";
    echo "<p>El sistema de notificaciones está listo para usar.</p>";
    echo "<p><a href='notification_test.php'>🧪 Ejecutar Pruebas del Sistema</a></p>";
    echo "<p><a href='?view=notificationconfig'>⚙️ Configurar SMTP</a></p>";
    echo "</div>";
} else {
    echo "<div class='error'><h2>⚠️ INSTALACIÓN INCOMPLETA</h2>";
    echo "<p>Algunos componentes no se instalaron correctamente. Revise los errores arriba.</p>";
    echo "</div>";
}
echo "</div>";

echo "</div>
</body>
</html>";
?>
