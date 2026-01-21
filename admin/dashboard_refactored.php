<?php
/**
 * Dashboard - Refactored with Template System
 * 
 * BEFORE: 200+ lines of HTML/PHP mixed code
 * AFTER: Clean separation of logic and presentation
 */

require_once '../auth/check_auth.php';
requireAdminOrEngineer();

require_once '../config/db_config.php';
require_once '../includes/layout.php';

// ============================================
// BUSINESS LOGIC ONLY
// ============================================

// Get dashboard statistics
$total_assets = $conn->query("SELECT COUNT(*) as total FROM assets")->fetch_assoc()['total'];
$available_assets = $conn->query("SELECT COUNT(*) as available FROM assets WHERE status = 'Available'")->fetch_assoc()['available'];
$active_loans = $conn->query("SELECT COUNT(*) as active FROM loans WHERE status = 'Active'")->fetch_assoc()['active'];

// ============================================
// PRESENTATION LOGIC
// ============================================

// Build page content
ob_start();
?>

<?php echo renderPageHeader('Dashboard'); ?>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
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
    
    <div class="col-md-4">
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
    
    <div class="col-md-4">
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
</div>

<!-- Quick Actions -->
<?php
$quickActionsContent = '
<div class="d-grid gap-2">
    <a href="loan_record.php" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Record New Loan
    </a>
    <a href="loans_active.php" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left-right"></i> View Active Loans
    </a>
    <a href="asset_add.php" class="btn btn-outline-secondary">
        <i class="bi bi-server"></i> Add New Asset
    </a>
</div>';

echo renderCard('Quick Actions', $quickActionsContent, 'lightning');
?>

<?php
$content = ob_get_clean();

// ============================================
// RENDER FINAL PAGE
// ============================================
echo renderLayout('Dashboard', $content, 'dashboard');
?>

<!-- 
============================================
COMPARISON: BEFORE vs AFTER
============================================

BEFORE (Traditional approach):
- 200+ lines of mixed HTML/PHP
- Repeated navigation code in every file
- Hard to maintain (change menu = edit 10+ files)
- Inconsistent styling
- Business logic mixed with presentation

AFTER (Template system):
- 50 lines of clean, focused code
- Navigation defined once, used everywhere
- Change menu = edit 1 file (layout.php)
- Consistent styling guaranteed
- Clear separation of concerns

BENEFITS:
✅ DRY principle (Don't Repeat Yourself)
✅ Single source of truth for UI
✅ Easier maintenance
✅ Consistent user experience
✅ Faster development
✅ Better testability
-->