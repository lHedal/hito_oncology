<?php
// Simple user check
include "core/controller/Database.php";
include "core/controller/Executor.php";

echo "<h2>🔍 User Database Check</h2>";

try {
    $con = Database::getCon();
    echo "<p>✅ Database connection successful</p>";
    
    // Check if user table exists
    $result = $con->query("SHOW TABLES LIKE 'user'");
    if($result && $result->num_rows > 0) {
        echo "<p>✅ User table exists</p>";
        
        // Get users
        $users = $con->query("SELECT id, username, name FROM user LIMIT 5");
        if($users && $users->num_rows > 0) {
            echo "<h3>📋 Available Users:</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Username</th><th>Name</th></tr>";
            while($user = $users->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $user['id'] . "</td>";
                echo "<td>" . $user['username'] . "</td>";
                echo "<td>" . $user['name'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>❌ No users found in database</p>";
            echo "<p><strong>Creating default admin user...</strong></p>";
            
            // Create default admin user
            $password_hash = sha1(md5("admin"));
            $insert_sql = "INSERT INTO user (username, name, lastname, email, password, is_active, created_at) VALUES ('admin', 'Administrator', 'System', 'admin@hospital.com', '$password_hash', 1, NOW())";
            
            if($con->query($insert_sql)) {
                echo "<p>✅ Default admin user created</p>";
                echo "<p><strong>Login credentials:</strong></p>";
                echo "<p>Username: <code>admin</code></p>";
                echo "<p>Password: <code>admin</code></p>";
            } else {
                echo "<p>❌ Error creating user: " . $con->error . "</p>";
            }
        }
    } else {
        echo "<p>❌ User table does not exist</p>";
    }
    
} catch(Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🚀 Test Login</h3>";
echo "<p><a href='index.php?view=login'>Go to Login Page</a></p>";
echo "<p><a href='index.php'>Go to Main Page</a></p>";
?>
