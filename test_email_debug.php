<?php
/**
 * Debug Test Email Configuration
 */

require_once('core/autoload.php');
include "core/app/model/NotificationData.php";

echo "<h2>🧪 Debug Test Email Configuration</h2>";

// Test basic email sending capability
echo "<h3>1. Test PHP Mail Extensions</h3>";
if(function_exists('mail')) {
    echo "<div style='color: green;'>✅ PHP mail() function available</div>";
} else {
    echo "<div style='color: red;'>❌ PHP mail() function not available</div>";
}

if(class_exists('PHPMailer')) {
    echo "<div style='color: green;'>✅ PHPMailer class available</div>";
} else {
    echo "<div style='color: red;'>❌ PHPMailer class not available</div>";
    echo "<div>Trying to include PHPMailer...</div>";
    require_once("core/controller/class.phpmailer.php");
    if(class_exists('PHPMailer')) {
        echo "<div style='color: green;'>✅ PHPMailer loaded successfully</div>";
    } else {
        echo "<div style='color: red;'>❌ Could not load PHPMailer</div>";
    }
}

// Test 2: Manual email test
echo "<h3>2. Manual Email Test</h3>";

if(isset($_POST['test_manual'])) {
    try {
        require_once("core/controller/class.phpmailer.php");
        
        $phpmailer = new PHPMailer();
        
        // Configuración SMTP
        $phpmailer->isSMTP();
        $phpmailer->Host = $_POST['smtp_host'];
        $phpmailer->Port = intval($_POST['smtp_port']);
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $_POST['smtp_username'];
        $phpmailer->Password = $_POST['smtp_password'];
        
        // Debug mode
        $phpmailer->SMTPDebug = 2;
        $phpmailer->Debugoutput = 'html';
        
        // Configurar seguridad
        if ($_POST['smtp_security'] == 'ssl') {
            $phpmailer->SMTPSecure = 'ssl';
        } else if ($_POST['smtp_security'] == 'tls') {
            $phpmailer->SMTPSecure = 'tls';
        }
        
        // Configurar remitente
        $phpmailer->setFrom($_POST['from_email'], $_POST['from_name']);
        
        // Configurar destinatario
        $phpmailer->addAddress($_POST['test_email'], 'Test User');
        
        // Configurar mensaje
        $phpmailer->isHTML(true);
        $phpmailer->Subject = 'Test Email from Oncology System';
        $phpmailer->Body = '<h2>Test Email</h2><p>This is a test email from the oncology notification system.</p><p>Time: ' . date('Y-m-d H:i:s') . '</p>';
        
        echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; margin: 10px 0;'>";
        echo "<h4>SMTP Debug Output:</h4>";
        $result = $phpmailer->send();
        echo "</div>";
        
        if($result) {
            echo "<div style='color: green; font-weight: bold;'>✅ Email sent successfully!</div>";
        } else {
            echo "<div style='color: red; font-weight: bold;'>❌ Failed to send email</div>";
            echo "<div style='color: red;'>Error: " . $phpmailer->ErrorInfo . "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='color: red; font-weight: bold;'>❌ Exception: " . $e->getMessage() . "</div>";
    }
}

// Form for manual testing
echo "<h3>3. Manual Test Form</h3>";
echo "<form method='post' style='background: #f8f9fa; padding: 20px; border-radius: 5px;'>";
echo "<table>";
echo "<tr><td>SMTP Host:</td><td><input type='text' name='smtp_host' value='smtp.gmail.com' style='width: 200px;'></td></tr>";
echo "<tr><td>SMTP Port:</td><td><input type='number' name='smtp_port' value='587' style='width: 200px;'></td></tr>";
echo "<tr><td>Security:</td><td><select name='smtp_security' style='width: 200px;'><option value='tls'>TLS</option><option value='ssl'>SSL</option><option value='none'>None</option></select></td></tr>";
echo "<tr><td>Username:</td><td><input type='text' name='smtp_username' placeholder='your-email@gmail.com' style='width: 200px;'></td></tr>";
echo "<tr><td>Password:</td><td><input type='password' name='smtp_password' placeholder='your-app-password' style='width: 200px;'></td></tr>";
echo "<tr><td>From Email:</td><td><input type='email' name='from_email' placeholder='system@yoursite.com' style='width: 200px;'></td></tr>";
echo "<tr><td>From Name:</td><td><input type='text' name='from_name' value='Oncology System' style='width: 200px;'></td></tr>";
echo "<tr><td>Test Email:</td><td><input type='email' name='test_email' placeholder='test@example.com' style='width: 200px;'></td></tr>";
echo "<tr><td colspan='2'><input type='submit' name='test_manual' value='Send Test Email' style='background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 3px; margin-top: 10px;'></td></tr>";
echo "</table>";
echo "</form>";

echo "<br><h3>4. Tips for Gmail Configuration</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #007cba;'>";
echo "<h4>For Gmail Setup:</h4>";
echo "<ol>";
echo "<li><strong>Enable 2-Factor Authentication</strong> on your Gmail account</li>";
echo "<li><strong>Generate App Password:</strong> Go to Google Account → Security → App passwords</li>";
echo "<li><strong>Use these settings:</strong>";
echo "<ul>";
echo "<li>SMTP Host: smtp.gmail.com</li>";
echo "<li>Port: 587</li>";
echo "<li>Security: TLS</li>";
echo "<li>Username: your-gmail@gmail.com</li>";
echo "<li>Password: the-16-digit-app-password (without spaces)</li>";
echo "</ul>";
echo "</li>";
echo "</ol>";
echo "</div>";

echo "<br><a href='?view=notificationconfig'>🔙 Back to Configuration</a>";
?>
