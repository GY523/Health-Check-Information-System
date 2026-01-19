<?php
/**
 * Admin Dashboard
 * 
 * LEARNING OBJECTIVES:
 * - Use session protection (require_once check_auth.php)
 * - Display user information from session
 * - Create navigation menu
 * - Practice HTML structure with PHP
 * - Understand admin-only access
 */

// ============================================
// PROTECT THIS PAGE
// ============================================
// LEARNING NOTE: This line makes the page require login
// If user not logged in, they'll be redirected to login.php
require_once '../auth/check_auth.php';

// LEARNING NOTE: Only admins should access this page
requireAdmin();

// Get database connection for statistics
require_once '../config/db_config.php';

// ============================================
// GET DASHBOARD STATISTICS
// ============================================
// LEARNING NOTE: We'll show some basic stats on the dashboard

// Count total assets
$total_assets_query = "SELECT COUNT(*) as total FROM assets";
$total_assets_result = $conn->query($total_assets_query);
$total_assets = $total_assets_result->fetch_assoc()['total'];

// Count available assets
$available_assets_query = "SELECT COUNT(*) as available FROM assets WHERE status = 'Available'";
$available_assets_result = $conn->query($available_assets_query);
$available_assets = $available_assets_result->fetch_assoc()['available'];

// Count active loans
$active_loans_query = "SELECT COUNT(*) as active FROM loans WHERE status = 'Active'";
$active_loans_result = $conn->query($active_loans_query);
$active_loans = $active_loans_result->fetch_assoc()['active'];

// Count pending approvals
$pending_approvals_query = "SELECT COUNT(*) as pending FROM loans WHERE status = 'Pending'";
$pending_approvals_result = $conn->query($pending_approvals_query);
$pending_approvals = $pending_approvals_result->fetch_assoc()['pending'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Server Loaning System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="bi bi-server"></i> Server Loaning System
            </a>
            
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    Welcome, <?php echo getCurrentUserName(); ?> (Admin)
                </span>
                <a class="nav-link" href="../auth/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            
            <!-- Sidebar Navigation -->
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-list"></i> Admin Menu</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="dashboard.php" class="list-group-item list-group-item-action active">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                        <a href="assets_list.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-server"></i> View Assets
                        </a>
                        <a href="asset_add.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-plus-circle"></i> Add Asset
                        </a>
                        <a href="pending_approvals.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-clock-history"></i> Pending Approvals
                            <?php if ($pending_approvals > 0): ?>
                                <span class="badge bg-warning"><?php echo $pending_approvals; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="active_loans.php" class="list-group-item list-group-item-action">
                            <i class="bi bi-arrow-left-right"></i> Active Loans
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Admin Dashboard</h2>
                    <div class="text-muted">
                        <?php echo date('l, F j, Y'); ?>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $total_assets; ?></h4>
                                        <p class="mb-0">Total Assets</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-server fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $available_assets; ?></h4>
                                        <p class="mb-0">Available</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-check-circle fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $active_loans; ?></h4>
                                        <p class="mb-0">Active Loans</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-arrow-left-right fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4><?php echo $pending_approvals; ?></h4>
                                        <p class="mb-0">Pending Approvals</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-clock-history fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-lightning"></i> Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="asset_add.php" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Add New Asset
                                    </a>
                                    <a href="assets_list.php" class="btn btn-outline-primary">
                                        <i class="bi bi-list"></i> View All Assets
                                    </a>
                                    <?php if ($pending_approvals > 0): ?>
                                    <a href="pending_approvals.php" class="btn btn-warning">
                                        <i class="bi bi-clock-history"></i> Review Pending Approvals (<?php echo $pending_approvals; ?>)
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="bi bi-info-circle"></i> System Information</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Logged in as:</strong> <?php echo getCurrentUserName(); ?></p>
                                <p><strong>Role:</strong> Administrator</p>
                                <p><strong>Last Login:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                                <p><strong>Session ID:</strong> <?php echo substr(session_id(), 0, 8); ?>...</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!-- 
============================================
LEARNING EXERCISE #1
============================================
TODO: Add recent activity feed
Show the last 5 loans/returns/approvals

HINT: 
1. Query loans table with ORDER BY created_at DESC LIMIT 5
2. JOIN with users and assets tables to get names
3. Display in a timeline format

============================================
LEARNING EXERCISE #2
============================================
TODO: Add overdue loans alert
Show warning if any loans are past due date

HINT:
1. Query: WHERE status='Active' AND expected_return_date < CURDATE()
2. If count > 0, show red alert box
3. Link to overdue loans page

============================================
WHAT YOU'VE LEARNED
============================================

✅ Session protection (require_once check_auth.php)
✅ Role-based access (requireAdmin())
✅ Database queries for statistics
✅ Bootstrap navigation and layout
✅ PHP functions from check_auth.php
✅ Professional dashboard design

============================================
TESTING INSTRUCTIONS
============================================

1. Login with admin/admin123
2. Should see this dashboard with statistics
3. Try clicking logout - should return to login
4. Try accessing without login - should redirect to login
5. Navigation links will show 404 (we'll create them next)

============================================
NEXT STEPS
============================================

Now that login flow works completely:
1. Test this dashboard thoroughly
2. Next: Create assets_list.php (view all assets)
3. Then: Create asset_add.php (add new assets)

Each page will follow the same pattern:
- require_once '../auth/check_auth.php'
- Database operations
- HTML with Bootstrap styling
-->