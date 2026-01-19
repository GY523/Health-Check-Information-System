<?php
/**
 * Logout Handler
 * 
 * LEARNING OBJECTIVES:
 * - Understand session destruction
 * - Learn about secure logout practices
 * - Practice redirects with messages
 * - Understand session security
 */

session_start();

// ============================================
// DESTROY SESSION DATA
// ============================================
// LEARNING NOTE: Proper logout requires multiple steps for security

// Step 1: Remove all session variables
session_unset();

// Step 2: Destroy the session
session_destroy();

// Step 3: Remove session cookie (extra security)
// LEARNING NOTE: This prevents session fixation attacks
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ============================================
// REDIRECT TO LOGIN WITH SUCCESS MESSAGE
// ============================================
// LEARNING NOTE: We use URL parameters to pass messages between pages
// This is better than using sessions for one-time messages

header('Location: login.php?logout=1');
exit();

?>

<!-- 
============================================
USAGE INSTRUCTIONS
============================================

This file should be called when user clicks "Logout" button.

Example logout link in your pages:
<a href="../auth/logout.php" class="btn btn-outline-secondary">Logout</a>

Or with confirmation:
<a href="../auth/logout.php" onclick="return confirm('Are you sure you want to logout?')">Logout</a>

============================================
SECURITY FEATURES
============================================

✅ Complete session destruction
✅ Session cookie removal
✅ Prevents session fixation attacks
✅ Clean redirect to login page
✅ Success message confirmation

============================================
LEARNING EXERCISE
============================================

TODO: Add logout logging
Track when users logout for security auditing

HINT: Before destroying session, log the logout event:
1. Get user_id from $_SESSION
2. Insert into logs table: user_id, action='logout', timestamp
3. Then destroy session

function logLogout() {
    if (isset($_SESSION['user_id'])) {
        // YOUR CODE HERE
        // Insert logout event into database
    }
}

============================================
TESTING INSTRUCTIONS
============================================

1. Login first using login.php
2. Navigate to: http://localhost/server_loaning_system/auth/logout.php
3. Should redirect to login.php with success message
4. Try accessing a protected page - should redirect back to login
5. Verify session is completely destroyed

============================================
NEXT STEPS
============================================

Now you have complete authentication system:
✅ check_auth.php - Session protection
✅ login.php - Login form and validation  
✅ logout.php - Secure logout

Next: Create dashboard pages that use this authentication!
-->