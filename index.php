<?php
/**
 * Main Index Page
 * 
 * LEARNING OBJECTIVES:
 * - Understand application entry points
 * - Practice redirects
 * - Learn about user flow design
 */

// Check if user is already logged in
session_start();

if (isset($_SESSION['user_id'])) {
    // User is logged in - redirect to appropriate dashboard
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
} else {
    // User not logged in - redirect to login page
    header('Location: auth/login.php');
}

exit();
?>

<!-- 
============================================
LEARNING NOTE
============================================

This is the main entry point of your application.
When someone visits: http://localhost/server_loaning_system/

They will be automatically redirected to:
- Login page (if not logged in)
- Admin dashboard (if logged in as admin)
- User dashboard (if logged in as regular user)

This creates a smooth user experience!

============================================
ALTERNATIVE APPROACH (Optional)
============================================

Instead of immediate redirect, you could show a welcome page:

<!DOCTYPE html>
<html>
<head>
    <title>Server Loaning System</title>
</head>
<body>
    <h1>Welcome to Server Loaning System</h1>
    <p>Please <a href="auth/login.php">login</a> to continue.</p>
</body>
</html>

But immediate redirect is more professional for internal systems.
-->