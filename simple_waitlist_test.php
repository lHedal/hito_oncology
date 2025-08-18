<?php
/**
 * Test simple para agregar lista de espera
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Simple: Agregar a Lista de Espera</h2>";

try {
    require_once('core/autoload.php');
    require_once('core/app/model/OncologyWaitlistData.php');
    require_once('core/app/model/PacientData.php');
    
    echo "<p>✅ Archivos cargados correctamente</p>";
    
    // Verificar conexión a DB
    $con = Database::getCon();
    if($con) {
        echo "<p>✅ Conexión a base de datos exitosa</p>";
    } else {
        throw new Exception("No se pudo conectar a la base de datos");
    }
    
    // Verificar que existe al menos un paciente
    $patient_check = $con->query("SELECT id FROM pacient WHERE is_active = 1 LIMIT 1");
    if($patient_check && $patient_check->num_rows > 0) {
        $patient = $patient_check->fetch_assoc();
        $patient_id = $patient['id'];
        echo "<p>✅ Paciente encontrado: ID $patient_id</p>";
        
        // Crear objeto de lista de espera
        $waitlist = new OncologyWaitlistData();
        $waitlist->pacient_id = $patient_id;
        $waitlist->treatment_type = "Quimioterapia Test";
        $waitlist->priority_level = 2;
        $waitlist->requested_date = date('Y-m-d', strtotime('+3 days'));
        $waitlist->requested_time = "14:00:00";
        $waitlist->duration_minutes = 90;
        $waitlist->notes = "Test desde script de diagnóstico";
        
        echo "<p>✅ Objeto creado con datos:</p>";
        echo "<ul>";
        echo "<li>Paciente ID: $waitlist->pacient_id</li>";
        echo "<li>Tratamiento: $waitlist->treatment_type</li>";
        echo "<li>Prioridad: $waitlist->priority_level</li>";
        echo "<li>Fecha: $waitlist->requested_date</li>";
        echo "<li>Hora: $waitlist->requested_time</li>";
        echo "</ul>";
        
        // Intentar insertar
        echo "<p>🔄 Intentando insertar...</p>";
        $result = $waitlist->add();
        
        if($result && is_array($result) && $result[1] > 0) {
            echo "<p style='color: green;'>✅ ¡Éxito! Lista de espera creada con ID: " . $result[1] . "</p>";
            
            // Verificar en base de datos
            $verify = $con->query("SELECT * FROM oncology_waitlist WHERE id = " . $result[1]);
            if($verify && $verify->num_rows > 0) {
                $data = $verify->fetch_assoc();
                echo "<p style='color: green;'>✅ Verificado en BD - Estado: " . $data['status'] . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Error al insertar</p>";
            echo "<p>Resultado: " . var_export($result, true) . "</p>";
            echo "<p>Error MySQL: " . $con->error . "</p>";
        }
        
    } else {
        echo "<p style='color: orange;'>⚠️ No hay pacientes activos en el sistema</p>";
        echo "<p>Crear un paciente primero en el sistema</p>";
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}

echo "<br><a href='index.php?view=newoncologywaitlist'>🔙 Ir a formulario de lista de espera</a>";
?>
