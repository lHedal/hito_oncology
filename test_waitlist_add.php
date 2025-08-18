<?php
/**
 * Test específico para agregar a lista de espera
 */

require_once "core/controller/Core.php";
require_once "core/controller/Database.php";
require_once "core/controller/Executor.php";
require_once "core/controller/Model.php";
require_once "core/app/model/OncologyWaitlistData.php";
require_once "core/app/model/PacientData.php";

echo "<h2>🧪 Test: Agregar Atención a Lista de Espera</h2>";

try {
    // Test 1: Verificar que las clases existen
    echo "<h3>1. Verificación de Clases</h3>";
    if(class_exists('OncologyWaitlistData')) {
        echo "<div style='color: green;'>✅ Clase OncologyWaitlistData cargada</div>";
    } else {
        echo "<div style='color: red;'>❌ Clase OncologyWaitlistData no encontrada</div>";
        exit;
    }
    
    // Test 2: Verificar tabla existe
    echo "<h3>2. Verificación de Tabla</h3>";
    $con = Database::getCon();
    $result = $con->query("SHOW TABLES LIKE 'oncology_waitlist'");
    if($result && $result->num_rows > 0) {
        echo "<div style='color: green;'>✅ Tabla oncology_waitlist existe</div>";
    } else {
        echo "<div style='color: red;'>❌ Tabla oncology_waitlist no existe</div>";
        
        // Mostrar tablas disponibles
        $tables_result = $con->query("SHOW TABLES");
        echo "<div><strong>Tablas disponibles:</strong><ul>";
        while($row = $tables_result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul></div>";
        exit;
    }
    
    // Test 3: Verificar estructura de tabla
    echo "<h3>3. Verificación de Estructura de Tabla</h3>";
    $structure = $con->query("DESCRIBE oncology_waitlist");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while($field = $structure->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $field['Field'] . "</td>";
        echo "<td>" . $field['Type'] . "</td>";
        echo "<td>" . $field['Null'] . "</td>";
        echo "<td>" . $field['Key'] . "</td>";
        echo "<td>" . $field['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test 4: Verificar pacientes disponibles
    echo "<h3>4. Verificación de Pacientes</h3>";
    $patients_result = $con->query("SELECT COUNT(*) as count FROM pacient WHERE is_active = 1");
    if($patients_result) {
        $patient_count = $patients_result->fetch_assoc()['count'];
        echo "<div style='color: green;'>✅ Pacientes activos disponibles: $patient_count</div>";
        
        if($patient_count > 0) {
            // Obtener un paciente de ejemplo
            $sample_patient = $con->query("SELECT id, name, lastname FROM pacient WHERE is_active = 1 LIMIT 1");
            $patient = $sample_patient->fetch_assoc();
            echo "<div>Paciente de ejemplo: ID {$patient['id']} - {$patient['name']} {$patient['lastname']}</div>";
            
            // Test 5: Intentar crear entrada en lista de espera
            echo "<h3>5. Test de Creación de Lista de Espera</h3>";
            
            $waitlist = new OncologyWaitlistData();
            $waitlist->pacient_id = $patient['id'];
            $waitlist->treatment_type = "Test Quimioterapia";
            $waitlist->priority_level = 3;
            $waitlist->requested_date = date('Y-m-d', strtotime('+1 week'));
            $waitlist->requested_time = "10:00:00";
            $waitlist->duration_minutes = 120;
            $waitlist->notes = "Test de inserción desde script de depuración";
            
            echo "<div><strong>Datos a insertar:</strong></div>";
            echo "<ul>";
            echo "<li>Paciente ID: " . $waitlist->pacient_id . "</li>";
            echo "<li>Tipo de tratamiento: " . $waitlist->treatment_type . "</li>";
            echo "<li>Prioridad: " . $waitlist->priority_level . "</li>";
            echo "<li>Fecha solicitada: " . $waitlist->requested_date . "</li>";
            echo "<li>Hora solicitada: " . $waitlist->requested_time . "</li>";
            echo "<li>Duración: " . $waitlist->duration_minutes . " minutos</li>";
            echo "</ul>";
            
            // Intentar la inserción
            $result = $waitlist->add();
            
            if($result && $result[1] > 0) {
                echo "<div style='color: green;'>✅ Lista de espera creada exitosamente. ID: " . $result[1] . "</div>";
                
                // Verificar que se insertó correctamente
                $verify = $con->query("SELECT * FROM oncology_waitlist WHERE id = " . $result[1]);
                if($verify && $verify->num_rows > 0) {
                    $inserted = $verify->fetch_assoc();
                    echo "<div style='color: green;'>✅ Verificación: Registro encontrado en base de datos</div>";
                    echo "<div>Estado: " . $inserted['status'] . "</div>";
                } else {
                    echo "<div style='color: red;'>❌ Error: No se encontró el registro insertado</div>";
                }
            } else {
                echo "<div style='color: red;'>❌ Error al crear lista de espera</div>";
                echo "<div>Resultado: " . var_export($result, true) . "</div>";
                echo "<div>Error MySQL: " . $con->error . "</div>";
            }
        }
    } else {
        echo "<div style='color: red;'>❌ Error al verificar pacientes: " . $con->error . "</div>";
    }
    
} catch(Exception $e) {
    echo "<div style='color: red;'>❌ Error en test: " . $e->getMessage() . "</div>";
    echo "<div>Archivo: " . $e->getFile() . " Línea: " . $e->getLine() . "</div>";
    echo "<div>Stack trace:</div><pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><br><a href='index.php?view=oncologywaitlist'>🔙 Volver a Lista de Espera</a>";
?>
