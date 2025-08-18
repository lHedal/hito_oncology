<?php
/**
 * Instalador del Sistema de Notificaciones
 * Este script configura automáticamente el sistema de notificaciones
 */

// Configuración de base de datos
$host = "localhost";
$username = "root";
$password = "";
$database = "oncology_database";

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Instalador - Sistema de Notificaciones</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
    <style>
        body { background: #f4f4f4; padding: 20px; }
        .container { max-width: 800px; }
        .step { margin: 20px 0; padding: 20px; background: white; border-radius: 5px; }
        .step-title { color: #337ab7; margin-bottom: 15px; }
        .success { color: #5cb85c; }
        .error { color: #d9534f; }
        .warning { color: #f0ad4e; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">
            <i class="fa fa-bell"></i> 
            Instalador del Sistema de Notificaciones
        </h1>
        <p class="text-center text-muted">Configuración automática para notificaciones por email</p>
        <hr>

        <?php
        $errors = [];
        $warnings = [];
        $success_messages = [];

        // Paso 1: Verificar requisitos
        echo '<div class="step">';
        echo '<h3 class="step-title"><i class="fa fa-check-circle"></i> Paso 1: Verificando Requisitos</h3>';
        
        // Verificar PHP
        if(version_compare(PHP_VERSION, '5.6.0', '>=')) {
            echo '<p class="success"><i class="fa fa-check"></i> PHP ' . PHP_VERSION . ' (Compatible)</p>';
        } else {
            $errors[] = 'Se requiere PHP 5.6.0 o superior';
            echo '<p class="error"><i class="fa fa-times"></i> PHP ' . PHP_VERSION . ' (Incompatible)</p>';
        }

        // Verificar extensiones
        $required_extensions = ['pdo', 'pdo_mysql', 'openssl', 'mbstring'];
        foreach($required_extensions as $ext) {
            if(extension_loaded($ext)) {
                echo '<p class="success"><i class="fa fa-check"></i> Extensión ' . $ext . ' disponible</p>';
            } else {
                $errors[] = "Extensión $ext no disponible";
                echo '<p class="error"><i class="fa fa-times"></i> Extensión ' . $ext . ' no disponible</p>';
            }
        }

        // Verificar PHPMailer
        $phpmailer_path = 'core/controller/class.phpmailer.php';
        if(file_exists($phpmailer_path)) {
            echo '<p class="success"><i class="fa fa-check"></i> PHPMailer encontrado</p>';
        } else {
            $errors[] = 'PHPMailer no encontrado';
            echo '<p class="error"><i class="fa fa-times"></i> PHPMailer no encontrado</p>';
        }

        echo '</div>';

        // Paso 2: Verificar base de datos
        echo '<div class="step">';
        echo '<h3 class="step-title"><i class="fa fa-database"></i> Paso 2: Verificando Base de Datos</h3>';
        
        try {
            $pdo = new PDO("mysql:host=$host", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo '<p class="success"><i class="fa fa-check"></i> Conexión a MySQL exitosa</p>';
            
            // Verificar base de datos
            $pdo->exec("USE $database");
            echo '<p class="success"><i class="fa fa-check"></i> Base de datos \'oncology_database\' accesible</p>';
            
            // Verificar tablas de notificaciones
            $notification_tables = ['notification_config', 'notification_types', 'notification_log', 'notification_queue'];
            $tables_exist = 0;
            
            foreach($notification_tables as $table) {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                if($stmt->rowCount() > 0) {
                    echo '<p class="success"><i class="fa fa-check"></i> Tabla \'' . $table . '\' existe</p>';
                    $tables_exist++;
                } else {
                    echo '<p class="warning"><i class="fa fa-exclamation-triangle"></i> Tabla \'' . $table . '\' no existe</p>';
                }
            }
            
            if($tables_exist < count($notification_tables)) {
                $warnings[] = 'Algunas tablas de notificaciones no existen';
                echo '<p class="warning"><i class="fa fa-wrench"></i> <a href="apply_notification_schema.php" target="_blank">Aplicar esquema de notificaciones</a></p>';
            }
            
        } catch(PDOException $e) {
            $errors[] = 'Error de base de datos: ' . $e->getMessage();
            echo '<p class="error"><i class="fa fa-times"></i> Error: ' . $e->getMessage() . '</p>';
        }
        
        echo '</div>';

        // Paso 3: Verificar archivos del sistema
        echo '<div class="step">';
        echo '<h3 class="step-title"><i class="fa fa-files-o"></i> Paso 3: Verificando Archivos del Sistema</h3>';
        
        $notification_files = [
            'core/app/model/NotificationData.php' => 'Modelos de datos',
            'core/app/model/NotificationService.php' => 'Servicio de notificaciones',
            'core/app/view/notifications-view.php' => 'Vista de historial',
            'core/app/view/notificationconfig-view.php' => 'Vista de configuración',
            'core/app/view/notificationqueue-view.php' => 'Vista de cola',
            'core/app/action/updatenotificationconfig-action.php' => 'Acción de configuración',
            'core/app/action/processnotificationqueue-action.php' => 'Procesador de cola',
            'notification_processor.php' => 'Procesador automático (cron)'
        ];
        
        $files_exist = 0;
        foreach($notification_files as $file => $description) {
            if(file_exists($file)) {
                echo '<p class="success"><i class="fa fa-check"></i> ' . $description . ' (' . $file . ')</p>';
                $files_exist++;
            } else {
                echo '<p class="error"><i class="fa fa-times"></i> ' . $description . ' (' . $file . ') no encontrado</p>';
                $errors[] = "Archivo faltante: $file";
            }
        }
        
        echo '</div>';

        // Paso 4: Configuración de ejemplo
        echo '<div class="step">';
        echo '<h3 class="step-title"><i class="fa fa-cog"></i> Paso 4: Configuración Sugerida</h3>';
        
        echo '<div class="alert alert-info">';
        echo '<h4><i class="fa fa-lightbulb-o"></i> Configuración recomendada para Gmail:</h4>';
        echo '<ul>';
        echo '<li><strong>Servidor SMTP:</strong> smtp.gmail.com</li>';
        echo '<li><strong>Puerto:</strong> 587</li>';
        echo '<li><strong>Seguridad:</strong> TLS</li>';
        echo '<li><strong>Usuario:</strong> su-email@gmail.com</li>';
        echo '<li><strong>Contraseña:</strong> Contraseña de aplicación (no su contraseña normal)</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="alert alert-warning">';
        echo '<h4><i class="fa fa-exclamation-triangle"></i> Importante:</h4>';
        echo '<p>Para Gmail, debe:</p>';
        echo '<ol>';
        echo '<li>Activar la verificación en 2 pasos</li>';
        echo '<li>Generar una contraseña de aplicación específica</li>';
        echo '<li>Usar esa contraseña en la configuración SMTP</li>';
        echo '</ol>';
        echo '</div>';
        
        echo '</div>';

        // Paso 5: Próximos pasos
        echo '<div class="step">';
        echo '<h3 class="step-title"><i class="fa fa-play"></i> Paso 5: Próximos Pasos</h3>';
        
        if(empty($errors)) {
            echo '<div class="alert alert-success">';
            echo '<h4><i class="fa fa-check-circle"></i> ¡Sistema Listo!</h4>';
            echo '<p>El sistema de notificaciones está instalado y listo para usar.</p>';
            echo '</div>';
            
            echo '<div class="btn-group" role="group">';
            echo '<a href="index.php?view=notificationconfig" class="btn btn-primary">';
            echo '<i class="fa fa-cog"></i> Configurar SMTP';
            echo '</a>';
            echo '<a href="index.php?view=notifications" class="btn btn-info">';
            echo '<i class="fa fa-list"></i> Ver Notificaciones';
            echo '</a>';
            echo '<a href="index.php?view=oncologydashboard" class="btn btn-success">';
            echo '<i class="fa fa-dashboard"></i> Ir al Dashboard';
            echo '</a>';
            echo '</div>';
            
        } else {
            echo '<div class="alert alert-danger">';
            echo '<h4><i class="fa fa-times-circle"></i> Errores Encontrados</h4>';
            echo '<ul>';
            foreach($errors as $error) {
                echo '<li>' . $error . '</li>';
            }
            echo '</ul>';
            echo '<p>Por favor corrija estos errores antes de continuar.</p>';
            echo '</div>';
        }
        
        if(!empty($warnings)) {
            echo '<div class="alert alert-warning">';
            echo '<h4><i class="fa fa-exclamation-triangle"></i> Advertencias</h4>';
            echo '<ul>';
            foreach($warnings as $warning) {
                echo '<li>' . $warning . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        
        echo '</div>';

        // Información adicional
        echo '<div class="step">';
        echo '<h3 class="step-title"><i class="fa fa-info-circle"></i> Información Adicional</h3>';
        
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h4>Funcionalidades del Sistema:</h4>';
        echo '<ul>';
        echo '<li>Notificaciones automáticas de citas agendadas</li>';
        echo '<li>Recordatorios 24 horas antes de citas</li>';
        echo '<li>Notificaciones de lista de espera</li>';
        echo '<li>Notificaciones de asignación de citas</li>';
        echo '<li>Bienvenida para nuevos pacientes</li>';
        echo '<li>Plantillas HTML responsivas</li>';
        echo '<li>Cola de notificaciones programadas</li>';
        echo '<li>Historial completo de envíos</li>';
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="col-md-6">';
        echo '<h4>Configuración de Cron Job:</h4>';
        echo '<p>Para procesamiento automático, configure un cron job:</p>';
        echo '<pre style="background: #f5f5f5; padding: 10px; border-radius: 3px;">';
        echo '# Cada 10 minutos' . "\n";
        echo '*/10 * * * * /usr/bin/php ' . realpath('notification_processor.php');
        echo '</pre>';
        echo '<p><small>Ajuste la ruta según su configuración del servidor.</small></p>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
        ?>
        
        <div class="text-center" style="margin-top: 30px;">
            <p class="text-muted">
                <i class="fa fa-heart text-danger"></i> 
                Sistema de Notificaciones para Oncología - Versión 1.0
            </p>
        </div>
    </div>
</body>
</html>
