<?php
// File: includes/admin-sidebar.php
// Reusable admin sidebar component

// Make sure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

$admin_name = $_SESSION['adminName'] ?? 'Admin';
?>

<div class="sidebar">
    <div class="logo-admin">
        <div class="logo-icon">Q</div>
        <span>QuickPick Admin</span>
    </div>
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="/views/admin/dashboard.php" class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="/views/admin/products.php" class="nav-link <?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> Products
            </a>
        </li>
        <li class="nav-item">
            <a href="/views/admin/orders.php" class="nav-link <?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
        </li>
        <li class="nav-item">
            <a href="/views/admin/customers.php" class="nav-link <?php echo $current_page === 'customers.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Customers
            </a>
        </li>
        <li class="nav-item">
            <a href="/views/admin/inventory.php" class="nav-link <?php echo $current_page === 'inventory.php' ? 'active' : ''; ?>">
                <i class="fas fa-warehouse"></i> Inventory
            </a>
        </li>
        <li class="nav-item">
            <a href="/views/admin/categories.php" class="nav-link <?php echo $current_page === 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Categories
            </a>
        </li>
        <li class="nav-item">
            <a href="/views/admin/settings.php" class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
        </li>
        <li class="nav-item" style="margin-top: 40px;">
            <a href="/controllers/admin/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>