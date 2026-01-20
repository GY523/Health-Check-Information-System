<?php
/**
 * Authentication Check File
 * 
 * LEARNING OBJECTIVES:
 * - Understand PHP sessions
 * - Learn about security (session hijacking prevention)
 * - Practice redirects and headers
 * - Understand include vs require_once
 * 
 * This file should be included at the top of EVERY protected page.
 * It checks if user is logged in, if not, redirects to login.
 */

// ============================================
// START SESSION
// ============================================
// LEARNING NOTE: session_start() must be called before any output (HTML)
// Sessions store data on the server and give user a session ID cookie
// This allows us to "remember" who is logged in across pages

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// CHECK IF USER IS LOGGED IN
// ============================================
// LEARNING NOTE: We check if session variables exist
// If user logged in successfully, we stored their info in $_SESSION

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    // User is NOT logged in - redirect to login page
    
    // LEARNING NOTE: header('Location: ...') redirects the browser
    // We use relative paths that work from any folder depth
    header('Location: ../auth/login.php');
    exit(); // IMPORTANT: Always exit after redirect to stop script execution
}

// ============================================
// SECURITY: SESSION REGENERATION
// ============================================
// LEARNING NOTE: This prevents session hijacking attacks
// We regenerate session ID periodically for security

if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// ============================================
// SECURITY: SESSION TIMEOUT
// ============================================
// LEARNING NOTE: Auto-logout after inactivity
// This prevents someone using an abandoned computer

$timeout_duration = 3600; // 1 hour in seconds

if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > $timeout_duration) {
        // Session expired - destroy and redirect
        session_unset();
        session_destroy();
        header('Location: ../auth/login.php?timeout=1');
        exit();
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Check if current user is admin
 * LEARNING NOTE: We stored user role in session during login
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if current user is engineer
 * LEARNING NOTE: Engineers have similar permissions to admins
 */
function isEngineer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'engineer';
}

/**
 * Check if current user is admin or engineer
 * LEARNING NOTE: Most features are available to both roles
 */
function isAdminOrEngineer() {
    return isAdmin() || isEngineer();
}

/**
 * Get current user's full name
 * LEARNING NOTE: Safely get session data with fallback
 */
function getCurrentUserName() {
    return isset($_SESSION['full_name']) ? $_SESSION['full_name'] : $_SESSION['username'];
}

/**
 * Get current user ID
 * LEARNING NOTE: This is used in database queries (foreign keys)
 */
function getCurrentUserId() {
    return $_SESSION['user_id'];
}

/**
 * Redirect admin-only pages
 * LEARNING NOTE: Some pages should only be accessible by admins
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ../auth/login.php?error=admin_required');
        exit();
    }
}

/**
 * Require admin or engineer access
 * LEARNING NOTE: Most pages require either admin or engineer role
 */
function requireAdminOrEngineer() {
    if (!isAdminOrEngineer()) {
        header('Location: ../auth/login.php?error=access_denied');
        exit();
    }
}

// ============================================
// LEARNING EXERCISE #1
// ============================================
// TODO: Add a function to check if user can access a specific asset
// Some assets might be restricted to certain departments
// 
// HINT: You'll need to check $_SESSION['department'] against asset restrictions
// 
// function canAccessAsset($asset_id) {
//     // YOUR CODE HERE
//     // Return true if user can access this asset, false otherwise
// }

// ============================================
// LEARNING EXERCISE #2
// ============================================
// TODO: Add a function to log user activities
// This creates an audit trail of who did what when
// 
// HINT: You'll need to INSERT into a logs table (create it first)
// 
// function logActivity($action, $details = '') {
//     // YOUR CODE HERE
//     // Insert: user_id, action, details, timestamp into logs table
// }

?>