<?php
/**
 * Database Connection Test
 * 
 * This file tests if your database connection works
 * and shows some sample data from the database
 */

require_once 'config/db_config.php';

echo "<h2>Database Connection Test</h2>";

// Test 1: Connection Status
if ($conn) {
    echo "<p style='color: green;'>✅ Database connected successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    exit();
}

// Test 2: Show sample users
echo "<h3>Sample Users in Database:</h3>";
$result = $conn->query("SELECT username, full_name, role FROM users");

if ($result->num_rows > 0) {
    echo "<ul>";
    while($row = $result->fetch_assoc()) {
        echo "<li>" . $row['full_name'] . " (" . $row['username'] . ") - " . $row['role'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No users found. Make sure you imported database_schema.sql</p>";
}

// Test 3: Show sample assets
echo "<h3>Sample Assets in Database:</h3>";
$result = $conn->query("SELECT model, asset_type, status FROM assets LIMIT 5");

if ($result->num_rows > 0) {
    echo "<ul>";
    while($row = $result->fetch_assoc()) {
        echo "<li>" . $row['model'] . " (" . $row['asset_type'] . ") - " . $row['status'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No assets found. Make sure you imported database_schema.sql</p>";
}

echo "<hr>";
echo "<p><strong>If you see data above, your database is working correctly!</strong></p>";
echo "<p><strong>Next step:</strong> Create the authentication system (login.php)</p>";

closeConnection();
?>