<?php
/**
 * Layout Template System
 * 
 * LEARNING OBJECTIVES:
 * - Understand MVC separation of concerns
 * - Learn template inheritance
 * - Practice DRY principle (Don't Repeat Yourself)
 * - Implement industry-standard layout patterns
 */

function renderLayout($title, $content, $activeMenu = '', $additionalCSS = '', $additionalJS = '') {
    require_once '../auth/check_auth.php';
    
    // Get current user info
    $userName = getCurrentUserName();
    $userRole = ucfirst($_SESSION['role']);
    
    // Generate navigation menu
    $menuItems = [
        'dashboard' => ['icon' => 'speedometer2', 'text' => 'Dashboard', 'url' => 'dashboard.php'],
        'loan_record' => ['icon' => 'plus-circle', 'text' => 'Record New Loan', 'url' => 'loan_record.php'],
        'loans_active' => ['icon' => 'arrow-left-right', 'text' => 'Active Loans', 'url' => 'loans_active.php'],
        'loans_history' => ['icon' => 'clock-history', 'text' => 'Loan History', 'url' => 'loans_history.php'],
        'separator' => null,
        'assets_list' => ['icon' => 'server', 'text' => 'View Assets', 'url' => 'assets_list.php'],
        'asset_add' => ['icon' => 'plus-circle', 'text' => 'Add Asset', 'url' => 'asset_add.php']
    ];
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> - Server Loaning System</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
        <?php echo $additionalCSS; ?>
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="dashboard.php">
                    <i class="bi bi-server"></i> Server Loaning System
                </a>
                <div class="navbar-nav ms-auto">
                    <span class="navbar-text me-3">
                        Welcome, <?php echo $userName; ?> (<?php echo $userRole; ?>)
                    </span>
                    <a class="nav-link" href="../auth/logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="container-fluid mt-4">
            <div class="row">
                
                <!-- Sidebar -->
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-list"></i> Menu</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($menuItems as $key => $item): ?>
                                <?php if ($key === 'separator'): ?>
                                    <hr>
                                <?php else: ?>
                                    <a href="<?php echo $item['url']; ?>" 
                                       class="list-group-item list-group-item-action <?php echo $activeMenu === $key ? 'active' : ''; ?>">
                                        <i class="bi bi-<?php echo $item['icon']; ?>"></i> <?php echo $item['text']; ?>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-md-9">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <?php echo $additionalJS; ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

/**
 * Render Alert Messages
 */
function renderAlert($message, $type = 'success', $dismissible = true) {
    if (empty($message)) return '';
    
    $icon = $type === 'success' ? 'check-circle' : 'exclamation-triangle';
    $dismissibleClass = $dismissible ? 'alert-dismissible fade show' : '';
    $dismissButton = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' : '';
    
    return "
    <div class='alert alert-{$type} {$dismissibleClass}' role='alert'>
        <i class='bi bi-{$icon}'></i> {$message}
        {$dismissButton}
    </div>";
}

/**
 * Render Page Header
 */
function renderPageHeader($title, $icon = '', $actionButton = '') {
    $iconHtml = $icon ? "<i class='bi bi-{$icon}'></i> " : '';
    
    return "
    <div class='d-flex justify-content-between align-items-center mb-4'>
        <h2>{$iconHtml}{$title}</h2>
        {$actionButton}
    </div>";
}

/**
 * Render Card Container
 */
function renderCard($title, $content, $icon = '', $headerActions = '') {
    $iconHtml = $icon ? "<i class='bi bi-{$icon}'></i> " : '';
    
    return "
    <div class='card'>
        <div class='card-header d-flex justify-content-between align-items-center'>
            <h5>{$iconHtml}{$title}</h5>
            {$headerActions}
        </div>
        <div class='card-body'>
            {$content}
        </div>
    </div>";
}
?>