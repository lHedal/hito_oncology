<?php
/**
 * Direct SMTP Test Tool
 * Simple test without routing complexity
 */

require_once('core/autoload.php');
include "core/app/model/NotificationData.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Direct SMTP Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .form-group { margin: 15px 0; }
        .form-group label { display: inline-block; width: 150px; font-weight: bold; }
        .form-group input, .form-group select { width: 300px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #005a8b; }
        .result { margin: 20px 0; padding: 15px; border-radius: 4px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🧪 Direct SMTP Test Tool</h1>
    <p>This tool tests SMTP configuration directly without complex routing.</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_smtp'])) {
    echo "<div class='result info'><h3>🔄 Testing SMTP Configuration...</h3></div>";
    
    try {
        // Validate input
        $required_fields = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_email', 'test_email'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Field '$field' is required");
            }
        }
        
        // Load PHPMailer
        require_once("core/controller/class.phpmailer.php");
        
        if (!class_exists('PHPMailer')) {
            throw new Exception("PHPMailer class not found");
        }
        
        $phpmailer = new PHPMailer(true); // Enable exceptions
        
        // SMTP Configuration
        $phpmailer->isSMTP();
        $phpmailer->Host = $_POST['smtp_host'];
        $phpmailer->Port = intval($_POST['smtp_port']);
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $_POST['smtp_username'];
        $phpmailer->Password = $_POST['smtp_password'];
        
        // Security
        if ($_POST['smtp_security'] == 'ssl') {
            $phpmailer->SMTPSecure = 'ssl';
        } else if ($_POST['smtp_security'] == 'tls') {
            $phpmailer->SMTPSecure = 'tls';
        }
        
        // Debug mode for detailed output
        $phpmailer->SMTPDebug = 2;
        $phpmailer->Debugoutput = function($str, $level) {
            echo "<div style='background: #f8f9fa; padding: 5px; margin: 2px 0; border-left: 3px solid #007cba; font-family: monospace; font-size: 12px;'>";
            echo htmlspecialchars($str);
            echo "</div>";
        };
        
        // Sender and recipient
        $phpmailer->setFrom($_POST['from_email'], $_POST['from_name']);
        $phpmailer->addAddress($_POST['test_email'], 'Test Recipient');
        
        // Email content
        $phpmailer->isHTML(true);
        $phpmailer->Subject = 'SMTP Test - Oncology System';
        $phpmailer->Body = '
        <h2 style="color: #28a745;">✅ SMTP Test Successful!</h2>
        <p>This email confirms that your SMTP configuration is working correctly.</p>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <strong>Configuration Details:</strong><br>
            <strong>Server:</strong> ' . htmlspecialchars($_POST['smtp_host']) . '<br>
            <strong>Port:</strong> ' . htmlspecialchars($_POST['smtp_port']) . '<br>
            <strong>Security:</strong> ' . htmlspecialchars($_POST['smtp_security']) . '<br>
            <strong>Username:</strong> ' . htmlspecialchars($_POST['smtp_username']) . '<br>
            <strong>Test Date:</strong> ' . date('Y-m-d H:i:s') . '
        </div>
        <p><strong>Your notification system is ready to use!</strong></p>';
        
        echo "<div class='result info'><h4>📤 Sending email...</h4></div>";
        
        $result = $phpmailer->send();
        
        if ($result) {
            echo "<div class='result success'>
                <h3>🎉 Email sent successfully!</h3>
                <p>Check the inbox of <strong>" . htmlspecialchars($_POST['test_email']) . "</strong></p>
                <p>Your SMTP configuration is working correctly!</p>
            </div>";
        } else {
            echo "<div class='result error'>
                <h3>❌ Failed to send email</h3>
                <p>Error: " . htmlspecialchars($phpmailer->ErrorInfo) . "</p>
            </div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='result error'>
            <h3>❌ Error occurred</h3>
            <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>
            <p><strong>Line:</strong> " . $e->getLine() . "</p>
        </div>";
    }
}

// Form
echo "<form method='post' style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>📧 SMTP Configuration</h3>";

echo "<div class='form-group'>
    <label>SMTP Host:</label>
    <input type='text' name='smtp_host' value='" . (isset($_POST['smtp_host']) ? htmlspecialchars($_POST['smtp_host']) : 'smtp.gmail.com') . "' required>
</div>";

echo "<div class='form-group'>
    <label>SMTP Port:</label>
    <input type='number' name='smtp_port' value='" . (isset($_POST['smtp_port']) ? htmlspecialchars($_POST['smtp_port']) : '587') . "' required>
</div>";

echo "<div class='form-group'>
    <label>Security:</label>
    <select name='smtp_security' required>
        <option value='tls'" . ((isset($_POST['smtp_security']) && $_POST['smtp_security'] == 'tls') ? ' selected' : ' selected') . ">TLS</option>
        <option value='ssl'" . ((isset($_POST['smtp_security']) && $_POST['smtp_security'] == 'ssl') ? ' selected' : '') . ">SSL</option>
        <option value='none'" . ((isset($_POST['smtp_security']) && $_POST['smtp_security'] == 'none') ? ' selected' : '') . ">None</option>
    </select>
</div>";

echo "<div class='form-group'>
    <label>Username:</label>
    <input type='email' name='smtp_username' value='" . (isset($_POST['smtp_username']) ? htmlspecialchars($_POST['smtp_username']) : '') . "' placeholder='your-email@gmail.com' required>
</div>";

echo "<div class='form-group'>
    <label>Password:</label>
    <input type='password' name='smtp_password' value='" . (isset($_POST['smtp_password']) ? htmlspecialchars($_POST['smtp_password']) : '') . "' placeholder='App Password (16 digits)' required>
</div>";

echo "<div class='form-group'>
    <label>From Email:</label>
    <input type='email' name='from_email' value='" . (isset($_POST['from_email']) ? htmlspecialchars($_POST['from_email']) : '') . "' placeholder='system@yoursite.com' required>
</div>";

echo "<div class='form-group'>
    <label>From Name:</label>
    <input type='text' name='from_name' value='" . (isset($_POST['from_name']) ? htmlspecialchars($_POST['from_name']) : 'Sistema Oncológico') . "' required>
</div>";

echo "<div class='form-group'>
    <label>Test Email:</label>
    <input type='email' name='test_email' value='" . (isset($_POST['test_email']) ? htmlspecialchars($_POST['test_email']) : '') . "' placeholder='test@example.com' required>
</div>";

echo "<div class='form-group'>
    <input type='submit' name='test_smtp' value='🧪 Test SMTP Configuration' class='btn'>
</div>";

echo "</form>";

echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #007cba; margin: 20px 0;'>
    <h4>📋 Gmail Setup Instructions:</h4>
    <ol>
        <li><strong>Enable 2-Factor Authentication</strong> in your Google Account</li>
        <li><strong>Generate App Password:</strong>
            <ul>
                <li>Go to Google Account → Security → App passwords</li>
                <li>Select 'Mail' and your device</li>
                <li>Copy the 16-digit password (no spaces)</li>
            </ul>
        </li>
        <li><strong>Use these settings:</strong>
            <ul>
                <li>Host: smtp.gmail.com</li>
                <li>Port: 587</li>
                <li>Security: TLS</li>
                <li>Username: your-gmail@gmail.com</li>
                <li>Password: 16-digit app password</li>
            </ul>
        </li>
    </ol>
</div>";

echo "<p><a href='?view=notificationconfig'>🔙 Back to Configuration</a> | 
      <a href='test_email_debug.php'>🔧 Advanced Debug Tool</a></p>";

echo "</div>
</body>
</html>";
?>
