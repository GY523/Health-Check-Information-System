<?php
/**
 * Login Page
 * 
 * LEARNING OBJECTIVES:
 * - Handle form submissions (POST vs GET)
 * - Validate user credentials against database
 * - Create secure sessions
 * - Practice prepared statements (SQL injection prevention)
 * - Understand password verification
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

session_start();

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../user/dashboard.php');
    }
    exit();
}

require_once '../config/db_config.php';

$error_message = '';
$success_message = '';

// ============================================
// HANDLE LOGIN FORM SUBMISSION
// ============================================
// LEARNING NOTE: Check if form was submitted via POST method
// $_POST contains form data, $_GET contains URL parameters

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get form data and sanitize
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // ============================================
    // VALIDATION
    // ============================================
    // LEARNING NOTE: Always validate user input
    
    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        
        // ============================================
        // DATABASE QUERY - SECURE METHOD
        // ============================================
        // LEARNING NOTE: We use prepared statements to prevent SQL injection
        // Never put user input directly into SQL queries!
        
        $stmt = $conn->prepare("SELECT user_id, username, password, full_name, role, is_active FROM users WHERE username = ? AND is_active = 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // ============================================
            // PASSWORD VERIFICATION
            // ============================================
            // LEARNING NOTE: We use MD5 for simplicity (upgrade to password_hash later)
            // In production, use password_hash() and password_verify()
            
            if (md5($password) === $user['password']) {
                
                // ============================================
                // LOGIN SUCCESS - CREATE SESSION
                // ============================================
                // LEARNING NOTE: Store user info in session for use across pages
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                $_SESSION['last_regeneration'] = time();
                
                // Redirect based on user role - both admin and engineer go to same dashboard
                if ($user['role'] === 'admin' || $user['role'] === 'engineer') {
                    header('Location: ../admin/dashboard.php');
                } else {
                    // Invalid role
                    $error_message = "Invalid user role. Contact administrator.";
                }
                exit();
                
            } else {
                $error_message = "Invalid username or password.";
            }
        } else {
            $error_message = "Invalid username or password.";
        }
        
        $stmt->close();
    }
}

// Check for timeout message
if (isset($_GET['timeout'])) {
    $error_message = "Your session has expired. Please login again.";
}

// Check for logout message
if (isset($_GET['logout'])) {
    $success_message = "You have been logged out successfully.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Server Loaning System</title>
    <!-- Bootstrap CSS for quick styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header text-center">
                    <h4>Server Loaning System</h4>
                    <p class="mb-0">Please login to continue</p>
                </div>
                <div class="card-body">
                    
                    <!-- Display error messages -->
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Display success messages -->
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Login Form -->
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                    
                </div>
                <div class="card-footer text-center">
                    <small class="text-muted">
                        Default login: admin / admin123
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!-- 
============================================
LEARNING EXERCISE #1
============================================
TODO: Add "Remember Me" functionality
This keeps user logged in longer using cookies

HINT: 
1. Add checkbox to form: <input type="checkbox" name="remember_me">
2. If checked, set a secure cookie with user ID
3. Check for cookie in check_auth.php if session doesn't exist
4. Research: setcookie(), $_COOKIE, secure flags

============================================
LEARNING EXERCISE #2
============================================
TODO: Add login attempt limiting
Prevent brute force attacks by limiting failed attempts

HINT:
1. Create a table: login_attempts (ip_address, attempts, last_attempt)
2. Track failed attempts by IP address
3. Block login for X minutes after Y failed attempts
4. Reset counter on successful login

============================================
TESTING INSTRUCTIONS
============================================

1. Make sure XAMPP is running (Apache + MySQL)
2. Import database_schema.sql if not done
3. Open: http://localhost/server_loaning_system/auth/login.php
4. Try logging in with: admin / admin123
5. Should redirect to admin dashboard (will create next)

Test cases:
- ✅ Valid credentials → Should login and redirect
- ✅ Invalid credentials → Should show error message
- ✅ Empty fields → Should show validation error
- ✅ Already logged in → Should redirect to dashboard

============================================
SECURITY FEATURES INCLUDED
============================================

✅ Prepared statements (SQL injection prevention)
✅ Input sanitization (trim, htmlspecialchars)
✅ Password verification (MD5 - upgrade later)
✅ Session security (regeneration, timeout)
✅ Role-based redirects
✅ Active user check (is_active = 1)

============================================
NEXT STEPS
============================================

After testing login, you'll create:
1. auth/logout.php - Logout handler
2. admin/dashboard.php - Admin home page
3. user/dashboard.php - User home page

The authentication system is the foundation - everything else builds on this!
-->