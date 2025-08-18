<?php
echo "<h2>🐛 Debug: URL and Session Information</h2>";
echo "<p><strong>Current Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

echo "<h3>📍 URL Parameters</h3>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "<h3>🔐 Session Information</h3>";
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h3>🌐 Server Information</h3>";
echo "<p><strong>REQUEST_URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "</p>";
echo "<p><strong>QUERY_STRING:</strong> " . ($_SERVER['QUERY_STRING'] ?? 'Not set') . "</p>";
echo "<p><strong>HTTP_REFERER:</strong> " . ($_SERVER['HTTP_REFERER'] ?? 'Not set') . "</p>";

echo "<h3>📁 View File Check</h3>";
$views_to_check = ['home', 'index', 'oncologydashboard', 'login'];
foreach($views_to_check as $view) {
    $file_path = "core/app/view/" . $view . "-view.php";
    $exists = file_exists($file_path) ? "✅ EXISTS" : "❌ NOT FOUND";
    echo "<p><strong>$view-view.php:</strong> $exists</p>";
}

echo "<h3>🚀 Recommended Actions</h3>";
echo "<ul>";
echo "<li><a href='index.php'>Go to Index (no parameters)</a></li>";
echo "<li><a href='index.php?view=login'>Go to Login</a></li>";
echo "<li><a href='index.php?view=oncologydashboard'>Go to Dashboard</a></li>";
echo "<li><a href='logout.php'>Logout and Reset Session</a></li>";
echo "</ul>";
?>
