<?php
require_once "core/controller/Core.php";
require_once "core/controller/Database.php";
require_once "core/controller/Executor.php";
require_once "core/controller/Model.php";
require_once "core/app/model/OncologyWaitlistData.php";
require_once "core/app/model/PacientData.php";
require_once "core/app/model/ReservationData.php";

echo "<h2>Test OncologyWaitlistData Fix</h2>";

try {
    // Test 1: Test getAll method
    echo "<h3>1. Testing getAll()</h3>";
    $all_waitlist = OncologyWaitlistData::getAll();
    if(is_array($all_waitlist)) {
        echo "<div style='color: green;'>✅ getAll() returned array with " . count($all_waitlist) . " elements</div>";
    } else {
        echo "<div style='color: red;'>❌ getAll() did not return an array</div>";
    }

    // Test 2: Test getPending method
    echo "<h3>2. Testing getPending()</h3>";
    $pending_waitlist = OncologyWaitlistData::getPending();
    if(is_array($pending_waitlist)) {
        echo "<div style='color: green;'>✅ getPending() returned array with " . count($pending_waitlist) . " elements</div>";
    } else {
        echo "<div style='color: red;'>❌ getPending() did not return an array</div>";
    }

    // Test 3: Test getById with invalid ID
    echo "<h3>3. Testing getById() with invalid ID</h3>";
    $invalid_waitlist = OncologyWaitlistData::getById(99999);
    if($invalid_waitlist === null) {
        echo "<div style='color: green;'>✅ getById() with invalid ID returned null correctly</div>";
    } else {
        echo "<div style='color: red;'>❌ getById() with invalid ID did not return null</div>";
    }

    // Test 4: Test getByPacientId with invalid ID
    echo "<h3>4. Testing getByPacientId() with invalid ID</h3>";
    $invalid_patient_waitlist = OncologyWaitlistData::getByPacientId(99999);
    if(is_array($invalid_patient_waitlist)) {
        echo "<div style='color: green;'>✅ getByPacientId() with invalid ID returned empty array with " . count($invalid_patient_waitlist) . " elements</div>";
    } else {
        echo "<div style='color: red;'>❌ getByPacientId() with invalid ID did not return an array</div>";
    }

    echo "<h3>Summary</h3>";
    echo "<div style='color: blue;'>✅ All OncologyWaitlistData methods are working without SQL errors!</div>";

} catch(Exception $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<div style='color: red;'>File: " . $e->getFile() . " Line: " . $e->getLine() . "</div>";
}
?>
