<?php
/**
 * Database Configuration File
 * 
 * LEARNING OBJECTIVES:
 * - Understand database connection in PHP
 * - Learn about mysqli vs PDO
 * - Practice error handling
 * - Understand why we use constants
 * 
 * This file will be included in every page that needs database access.
 * We use require_once to ensure it's loaded only once.
 */

// ============================================
// DATABASE CREDENTIALS
// ============================================
// LEARNING NOTE: We use constants (define) instead of variables
// because these values should NEVER change during script execution.
// Constants are also slightly faster and more secure.

define('DB_HOST', 'localhost');        // Database server (XAMPP default)
define('DB_USER', 'root');             // Database username (XAMPP default)
define('DB_PASS', '');                 // Database password (XAMPP default is empty)
define('DB_NAME', 'server_loaning_system');  // Our database name

// ============================================
// CREATE DATABASE CONNECTION
// ============================================
// LEARNING NOTE: We use mysqli (MySQL Improved) extension
// It's more secure than the old mysql extension and supports prepared statements

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// ============================================
// CHECK CONNECTION
// ============================================
// LEARNING NOTE: Always check if connection succeeded
// If it fails, we stop the script and show an error

if ($conn->connect_error) {
    // In production, you'd log this error instead of displaying it
    die("Connection failed: " . $conn->connect_error);
}

// ============================================
// SET CHARACTER ENCODING
// ============================================
// LEARNING NOTE: UTF-8 ensures proper handling of special characters
// This prevents issues with names, descriptions, etc.

$conn->set_charset("utf8mb4");

// ============================================
// LEARNING EXERCISE #1
// ============================================
// TODO: Add a function to safely close the database connection
// This should be called at the end of scripts
// 
// HINT: Research mysqli close() method
// 
function closeConnection() {
   global $conn;
   // YOUR CODE HERE
   $conn->close();
}

// ============================================
// LEARNING EXERCISE #2 (Optional - Advanced)
// ============================================
// TODO: Add error reporting for development
// This helps you see errors while building
// REMOVE THIS IN PRODUCTION!
//
// HINT: Look up error_reporting() and ini_set()
//
// Uncomment these lines for development:
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// ============================================
// SUCCESS MESSAGE (for testing only)
// ============================================
// LEARNING NOTE: Comment this out after you verify connection works
// echo "Database connection established successfully.";

?>

<!-- 
============================================
TESTING INSTRUCTIONS
============================================

1. Save this file as: config/db_config.php

2. Create a test file: test_connection.php
   //<?php
   //require_once 'config/db_config.php';
   //echo "If you see this, database connection works!";
   //?>

3. Open in browser: http://localhost/server_loaning_system/test_connection.php

4. You should see: "If you see this, database connection works!"

5. If you see an error:
   - Check XAMPP MySQL is running
   - Verify database name is correct
   - Check username/password

============================================
COMMON ERRORS & SOLUTIONS
============================================

Error: "Unknown database 'server_loaning_system'"
Solution: Import the database_schema.sql file first

Error: "Access denied for user 'root'@'localhost'"
Solution: Check your MySQL username/password in XAMPP

Error: "Can't connect to MySQL server"
Solution: Start MySQL in XAMPP Control Panel

============================================
NEXT STEPS
============================================

After this works, you'll create:
1. auth/check_auth.php - Session validation
2. auth/login.php - Login page
3. auth/logout.php - Logout handler

Each file will build on what you learned here!
-->
