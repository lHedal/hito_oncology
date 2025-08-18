<?php
/**
 * Test Directo - Identificar Error de Lista de Espera
 */

// Habilitar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Diagnóstico: Agregar a Lista de Espera</h1>";

// Test 1: Inclusión básica
echo "<h2>1. Test de Inclusiones</h2>";
try {
    include "core/controller/Core.php";
    echo "✅ Core cargado<br>";
    
    include "core/controller/Database.php";
    echo "✅ Database cargado<br>";
    
    include "core/controller/Executor.php";
    echo "✅ Executor cargado<br>";
    
    include "core/controller/Model.php";
    echo "✅ Model cargado<br>";
    
} catch(Exception $e) {
    echo "❌ Error en inclusiones: " . $e->getMessage() . "<br>";
}

// Test 2: Conexión DB
echo "<h2>2. Test de Conexión</h2>";
try {
    $con = Database::getCon();
    if($con) {
        echo "✅ Conexión exitosa<br>";
        echo "Base de datos: " . $con->get_server_info() . "<br>";
    }
} catch(Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
}

// Test 3: Verificar tabla
echo "<h2>3. Test de Tabla</h2>";
try {
    $result = $con->query("SHOW TABLES LIKE 'oncology_waitlist'");
    if($result && $result->num_rows > 0) {
        echo "✅ Tabla oncology_waitlist existe<br>";
        
        // Mostrar estructura
        $structure = $con->query("DESCRIBE oncology_waitlist");
        echo "<table border='1'>";
        echo "<tr><th>Campo</th><th>Tipo</th></tr>";
        while($field = $structure->fetch_assoc()) {
            echo "<tr><td>" . $field['Field'] . "</td><td>" . $field['Type'] . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "❌ Tabla oncology_waitlist no existe<br>";
    }
} catch(Exception $e) {
    echo "❌ Error verificando tabla: " . $e->getMessage() . "<br>";
}

// Test 4: Test de SQL directo
echo "<h2>4. Test de SQL Directo</h2>";
try {
    // Verificar si hay pacientes
    $patients = $con->query("SELECT COUNT(*) as count FROM pacient WHERE is_active = 1");
    $patient_count = $patients->fetch_assoc()['count'];
    echo "Pacientes activos: $patient_count<br>";
    
    if($patient_count > 0) {
        // Obtener un paciente
        $patient_query = $con->query("SELECT id FROM pacient WHERE is_active = 1 LIMIT 1");
        $patient_id = $patient_query->fetch_assoc()['id'];
        
        // Test de INSERT directo
        $test_sql = "INSERT INTO oncology_waitlist (pacient_id, treatment_type, priority_level, requested_date, requested_time, duration_minutes, notes, status, created_at) 
                     VALUES ($patient_id, 'Test Directo', 2, '" . date('Y-m-d', strtotime('+1 week')) . "', '10:00:00', 60, 'Test SQL directo', 'pending', NOW())";
        
        echo "SQL a ejecutar:<br>";
        echo "<code>$test_sql</code><br>";
        
        $insert_result = $con->query($test_sql);
        if($insert_result) {
            echo "✅ INSERT directo exitoso. ID insertado: " . $con->insert_id . "<br>";
            
            // Verificar inserción
            $verify = $con->query("SELECT * FROM oncology_waitlist WHERE id = " . $con->insert_id);
            if($verify && $verify->num_rows > 0) {
                echo "✅ Verificación exitosa<br>";
                $data = $verify->fetch_assoc();
                echo "Estado: " . $data['status'] . "<br>";
                echo "Creado: " . $data['created_at'] . "<br>";
            }
        } else {
            echo "❌ Error en INSERT: " . $con->error . "<br>";
        }
    } else {
        echo "❌ No hay pacientes en el sistema<br>";
    }
    
} catch(Exception $e) {
    echo "❌ Error en test SQL: " . $e->getMessage() . "<br>";
}

echo "<h2>Resumen</h2>";
echo "Si el INSERT directo funciona, el problema está en la clase OncologyWaitlistData.<br>";
echo "Si no funciona, el problema está en la estructura de la base de datos.<br>";

echo "<br><a href='index.php?view=newoncologywaitlist'>🔙 Ir al formulario</a>";
?>
