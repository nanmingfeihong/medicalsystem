<?php
echo "PHP is working!<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Vendor autoload exists: " . (file_exists(__DIR__ . '/../vendor/autoload.php') ? 'Yes' : 'No') . "<br>";

try {
    require __DIR__ . '/../vendor/autoload.php';
    echo "Autoload successful<br>";
    
    // Test database connection
    $db = new PDO('sqlite:' . __DIR__ . '/../runtime/medical_system.db');
    echo "Database connection successful<br>";
    
    // Test table exists
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>