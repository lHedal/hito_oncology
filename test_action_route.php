<?php
/**
 * Simple Action Test
 */

echo "<h2>🧪 Action Test</h2>";

echo "<h3>Test Action Routing</h3>";
echo "<form method='post' action='index.php?action=testnotificationconfig'>";
echo "<p>Testing if the testnotificationconfig action can be reached...</p>";
echo "<input type='hidden' name='test_email' value='test@example.com'>";
echo "<input type='hidden' name='smtp_host' value='smtp.gmail.com'>";
echo "<input type='hidden' name='smtp_port' value='587'>";
echo "<input type='hidden' name='smtp_security' value='tls'>";
echo "<input type='hidden' name='smtp_username' value='test@gmail.com'>";
echo "<input type='hidden' name='smtp_password' value='testpass'>";
echo "<input type='hidden' name='from_email' value='system@test.com'>";
echo "<input type='hidden' name='from_name' value='Test System'>";
echo "<input type='submit' value='Test Action Route' style='background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 3px;'>";
echo "</form>";

echo "<br><h3>AJAX Test</h3>";
echo "<button onclick='testAjax()' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 3px;'>Test AJAX Call</button>";
echo "<div id='ajax-result' style='margin-top: 10px; padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6;'></div>";

echo "<script>
function testAjax() {
    var formData = new FormData();
    formData.append('test_email', 'test@example.com');
    formData.append('smtp_host', 'smtp.gmail.com');
    formData.append('smtp_port', '587');
    formData.append('smtp_security', 'tls');
    formData.append('smtp_username', 'test@gmail.com');
    formData.append('smtp_password', 'testpass');
    formData.append('from_email', 'system@test.com');
    formData.append('from_name', 'Test System');
    
    document.getElementById('ajax-result').innerHTML = 'Loading...';
    
    fetch('index.php?action=testnotificationconfig', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('ajax-result').innerHTML = '<h4>Response:</h4><pre>' + data + '</pre>';
    })
    .catch(error => {
        document.getElementById('ajax-result').innerHTML = '<h4>Error:</h4><pre>' + error + '</pre>';
    });
}
</script>";

echo "<br><a href='?view=notificationconfig'>🔙 Back to Configuration</a>";
?>
