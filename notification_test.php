<?php
/**
 * Test del Sistema de Notificaciones
 * Verifica que todas las funcionalidades estén operativas
 */

// Incluir dependencias
require_once('core/autoload.php');
require_once('core/app/model/NotificationData.php');
require_once('core/app/model/NotificationService.php');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test - Sistema de Notificaciones</title>
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
    <style>
        body { background: #f4f4f4; padding: 20px; }
        .container { max-width: 900px; }
        .test-section { margin: 20px 0; padding: 20px; background: white; border-radius: 5px; }
        .success { color: #5cb85c; }
        .error { color: #d9534f; }
        .warning { color: #f0ad4e; }
        .test-result { padding: 10px; margin: 5px 0; border-radius: 3px; }
        .test-pass { background: #dff0d8; border: 1px solid #d6e9c6; }
        .test-fail { background: #f2dede; border: 1px solid #ebccd1; }
        .test-warn { background: #fcf8e3; border: 1px solid #faebcc; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">
            <i class="fa fa-flask"></i> 
            Test del Sistema de Notificaciones
        </h1>
        <p class="text-center text-muted">Verificación completa de funcionalidades</p>
        <hr>

        <?php
        $tests_passed = 0;
        $tests_failed = 0;
        $tests_warning = 0;
        
        function runTest($test_name, $condition, $success_msg, $error_msg, $warning = false) {
            global $tests_passed, $tests_failed, $tests_warning;
            
            echo '<div class="test-result ';
            if($condition) {
                echo 'test-pass"><i class="fa fa-check success"></i> ';
                echo '<strong>' . $test_name . ':</strong> ' . $success_msg;
                $tests_passed++;
            } else {
                if($warning) {
                    echo 'test-warn"><i class="fa fa-exclamation-triangle warning"></i> ';
                    echo '<strong>' . $test_name . ':</strong> ' . $error_msg;
                    $tests_warning++;
                } else {
                    echo 'test-fail"><i class="fa fa-times error"></i> ';
                    echo '<strong>' . $test_name . ':</strong> ' . $error_msg;
                    $tests_failed++;
                }
            }
            echo '</div>';
            
            return $condition;
        }

        // Test 1: Modelos de datos
        echo '<div class="test-section">';
        echo '<h3><i class="fa fa-database"></i> Test 1: Modelos de Datos</h3>';
        
        runTest(
            'NotificationData',
            class_exists('NotificationData'),
            'Clase NotificationData cargada correctamente',
            'Clase NotificationData no encontrada'
        );
        
        runTest(
            'NotificationService',
            class_exists('NotificationService'),
            'Clase NotificationService cargada correctamente',
            'Clase NotificationService no encontrada'
        );
        
        runTest(
            'NotificationTypeData',
            class_exists('NotificationTypeData'),
            'Clase NotificationTypeData cargada correctamente',
            'Clase NotificationTypeData no encontrada'
        );
        
        runTest(
            'NotificationConfigData',
            class_exists('NotificationConfigData'),
            'Clase NotificationConfigData cargada correctamente',
            'Clase NotificationConfigData no encontrada'
        );
        
        echo '</div>';

        // Test 2: Base de datos
        echo '<div class="test-section">';
        echo '<h3><i class="fa fa-table"></i> Test 2: Tablas de Base de Datos</h3>';
          try {
            // Verificar tablas
            $tables = ['notification_config', 'notification_types', 'notification_log', 'notification_queue'];            foreach($tables as $table) {
                try {
                    $sql = "SELECT 1 FROM `$table` LIMIT 1";
                    $result = Executor::doit($sql);
                    $table_exists = ($result !== false);
                } catch(Exception $e) {
                    $table_exists = false;
                }
                
                runTest(
                    "Tabla $table",
                    $table_exists,
                    "Tabla '$table' existe y es accesible",
                    "Tabla '$table' no encontrada o no accesible"
                );
            }
            
            // Verificar datos iniciales
            try {
                $types_result = NotificationTypeData::getAll();
                $types_count = is_array($types_result) ? count($types_result) : 0;
                runTest(
                    'Tipos de notificación',
                    $types_count > 0,
                    "$types_count tipos de notificación configurados",
                    'No hay tipos de notificación configurados',
                    true
                );
            } catch(Exception $e) {
                runTest(
                    'Tipos de notificación',
                    false,
                    'Tipos obtenidos correctamente',
                    'Error obteniendo tipos: ' . $e->getMessage()
                );
            }
            
        } catch(Exception $e) {
            runTest(
                'Conexión BD',
                false,
                'Conexión exitosa',
                'Error de conexión: ' . $e->getMessage()
            );
        }
        
        echo '</div>';

        // Test 3: Configuración
        echo '<div class="test-section">';
        echo '<h3><i class="fa fa-cog"></i> Test 3: Configuración</h3>';
        
        $config = NotificationConfigData::getConfig();
        runTest(
            'Configuración SMTP',
            $config !== null,
            'Configuración SMTP encontrada',
            'No hay configuración SMTP',
            true
        );
        
        if($config) {
            runTest(
                'Notificaciones habilitadas',
                $config->notifications_enabled == 1,
                'Sistema de notificaciones habilitado',
                'Sistema de notificaciones deshabilitado',
                true
            );
            
            runTest(
                'Envío automático',
                $config->auto_send_enabled == 1,
                'Envío automático habilitado',
                'Envío automático deshabilitado',
                true
            );
            
            runTest(
                'Configuración SMTP completa',
                !empty($config->smtp_username) && !empty($config->smtp_password),
                'Credenciales SMTP configuradas',
                'Credenciales SMTP faltantes',
                true
            );
        }
        
        echo '</div>';

        // Test 4: Archivos del sistema
        echo '<div class="test-section">';
        echo '<h3><i class="fa fa-files-o"></i> Test 4: Archivos del Sistema</h3>';
        
        $files = [
            'core/app/view/notifications-view.php' => 'Vista de historial',
            'core/app/view/notificationconfig-view.php' => 'Vista de configuración',
            'core/app/view/notificationqueue-view.php' => 'Vista de cola',
            'core/app/action/updatenotificationconfig-action.php' => 'Acción configuración',
            'core/app/action/processnotificationqueue-action.php' => 'Procesador de cola',
            'notification_processor.php' => 'Procesador automático'
        ];
        
        foreach($files as $file => $description) {
            runTest(
                $description,
                file_exists($file),
                "Archivo '$file' existe",
                "Archivo '$file' no encontrado"
            );
        }
        
        echo '</div>';

        // Test 5: Funcionalidades
        echo '<div class="test-section">';
        echo '<h3><i class="fa fa-code"></i> Test 5: Funcionalidades</h3>';
        
        // Test métodos del servicio
        runTest(
            'Método sendNotification',
            method_exists('NotificationService', 'sendNotification'),
            'Método sendNotification disponible',
            'Método sendNotification no encontrado'
        );
        
        runTest(
            'Método scheduleNotification',
            method_exists('NotificationService', 'scheduleNotification'),
            'Método scheduleNotification disponible',
            'Método scheduleNotification no encontrado'
        );
        
        runTest(
            'Método processQueue',
            method_exists('NotificationService', 'processQueue'),
            'Método processQueue disponible',
            'Método processQueue no encontrado'
        );
        
        // Test métodos específicos
        $specific_methods = [
            'notifyAppointmentScheduled',
            'notifyPatientRegistered',
            'notifyWaitlistAdded',
            'notifyWaitlistAssignment'
        ];
        
        foreach($specific_methods as $method) {
            runTest(
                "Método $method",
                method_exists('NotificationService', $method),
                "Método $method disponible",
                "Método $method no encontrado"
            );
        }
        
        echo '</div>';

        // Test 6: Estadísticas
        echo '<div class="test-section">';
        echo '<h3><i class="fa fa-bar-chart"></i> Test 6: Estadísticas del Sistema</h3>';
        
        try {
            $recent_notifications = NotificationData::getRecentNotifications(10);
            echo '<div class="test-result test-pass">';
            echo '<i class="fa fa-info-circle"></i> ';
            echo '<strong>Notificaciones recientes:</strong> ' . count($recent_notifications) . ' registros';
            echo '</div>';
            
            $pending_queue = NotificationQueueData::getByStatus('pending');
            echo '<div class="test-result test-pass">';
            echo '<i class="fa fa-info-circle"></i> ';
            echo '<strong>Cola pendiente:</strong> ' . count($pending_queue) . ' notificaciones';
            echo '</div>';
            
            $notification_types = NotificationTypeData::getActive();
            echo '<div class="test-result test-pass">';
            echo '<i class="fa fa-info-circle"></i> ';
            echo '<strong>Tipos activos:</strong> ' . count($notification_types) . ' tipos de notificación';
            echo '</div>';
            
        } catch(Exception $e) {
            runTest(
                'Estadísticas',
                false,
                'Estadísticas obtenidas',
                'Error al obtener estadísticas: ' . $e->getMessage()
            );
        }
        
        echo '</div>';

        // Resumen final
        echo '<div class="test-section">';
        echo '<h3><i class="fa fa-flag-checkered"></i> Resumen del Test</h3>';
        
        $total_tests = $tests_passed + $tests_failed + $tests_warning;
        
        echo '<div class="row">';
        echo '<div class="col-md-4">';
        echo '<div class="alert alert-success">';
        echo '<h4><i class="fa fa-check-circle"></i> Exitosos</h4>';
        echo '<h2>' . $tests_passed . '/' . $total_tests . '</h2>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="col-md-4">';
        echo '<div class="alert alert-warning">';
        echo '<h4><i class="fa fa-exclamation-triangle"></i> Advertencias</h4>';
        echo '<h2>' . $tests_warning . '</h2>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="col-md-4">';
        echo '<div class="alert alert-danger">';
        echo '<h4><i class="fa fa-times-circle"></i> Fallidos</h4>';
        echo '<h2>' . $tests_failed . '</h2>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        $success_rate = round(($tests_passed / $total_tests) * 100, 1);
        
        if($success_rate >= 90) {
            $status_class = 'success';
            $status_icon = 'check-circle';
            $status_text = '¡Excelente!';
        } elseif($success_rate >= 70) {
            $status_class = 'warning';
            $status_icon = 'exclamation-triangle';
            $status_text = 'Aceptable';
        } else {
            $status_class = 'danger';
            $status_icon = 'times-circle';
            $status_text = 'Requiere atención';
        }
        
        echo '<div class="alert alert-' . $status_class . ' text-center">';
        echo '<h3><i class="fa fa-' . $status_icon . '"></i> ' . $status_text . '</h3>';
        echo '<p>Tasa de éxito: <strong>' . $success_rate . '%</strong></p>';
        
        if($tests_failed == 0 && $tests_warning <= 2) {
            echo '<p>✅ El sistema de notificaciones está listo para usar</p>';
            echo '<a href="index.php?view=notificationconfig" class="btn btn-primary">Configurar SMTP</a> ';
            echo '<a href="index.php?view=notifications" class="btn btn-info">Ver Notificaciones</a>';
        } else {
            echo '<p>⚠️ Revise los errores antes de usar el sistema</p>';
            echo '<a href="notification_installer.php" class="btn btn-warning">Ejecutar Instalador</a>';
        }
        echo '</div>';
        
        echo '</div>';
        ?>
        
        <div class="text-center" style="margin-top: 30px;">
            <p class="text-muted">
                <i class="fa fa-flask"></i> 
                Test del Sistema de Notificaciones - <?php echo date('d/m/Y H:i:s'); ?>
            </p>
        </div>
    </div>
</body>
</html>
