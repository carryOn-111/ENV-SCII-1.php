<?php
/**
 * EcoLearn Platform Setup Script
 * Run this file once to set up the database and initial configuration
 */

// Database configuration - UPDATE THESE VALUES
$host = 'localhost';
$dbname = 'ecolearn_db';
$username = 'your_username';  // Change this
$password = 'your_password';  // Change this

echo "<h1>EcoLearn Platform Setup</h1>";

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✓ Connected to MySQL server</p>";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
    echo "<p>✓ Database '$dbname' created or already exists</p>";
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read and execute SQL setup file
    $sql = file_get_contents('database_setup.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            $pdo->exec($statement);
        }
    }
    
    echo "<p>✓ Database tables created successfully</p>";
    echo "<p>✓ Sample data inserted</p>";
    
    // Update database configuration file
    $config_content = "<?php
// Database configuration
\$host = '$host';
\$dbname = '$dbname';
\$username = '$username';
\$password = '$password';

try {
    \$pdo = new PDO(\"mysql:host=\$host;dbname=\$dbname;charset=utf8mb4\", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException \$e) {
    die(\"Database connection failed: \" . \$e->getMessage());
}
?>";
    
    file_put_contents('config/database.php', $config_content);
    echo "<p>✓ Database configuration updated</p>";
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Setup Complete!</h3>";
    echo "<p><strong>Your EcoLearn platform is ready to use.</strong></p>";
    echo "<p><strong>Default Login Credentials:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Teacher:</strong> username: teacher1, password: password123</li>";
    echo "<li><strong>Student:</strong> username: student1, password: password123</li>";
    echo "</ul>";
    echo "<p><a href='login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>Important Security Notes:</h4>";
    echo "<ul>";
    echo "<li>Change the default passwords immediately</li>";
    echo "<li>Remove or secure this setup.php file</li>";
    echo "<li>Update database credentials in config/database.php if needed</li>";
    echo "<li>Ensure proper file permissions are set</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px;'>";
    echo "<h3>Setup Failed</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database credentials and try again.</p>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>EcoLearn Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f8f9fa;
        }
        h1 {
            color: #27ae60;
            text-align: center;
        }
        p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
</body>
</html>