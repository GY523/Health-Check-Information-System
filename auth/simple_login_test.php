<?php
// Simple login test - no database, just HTML
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working!<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Login Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Test Login Form</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Login</button>
                        </form>
                        
                        <?php
                        if ($_POST) {
                            echo "<div class='alert alert-info mt-3'>";
                            echo "Form submitted!<br>";
                            echo "Username: " . ($_POST['username'] ?? 'not set') . "<br>";
                            echo "Password: " . (isset($_POST['password']) ? '[hidden]' : 'not set');
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>