<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

// Get dashboard statistics from your database
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE isActive = 1")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(totalAmount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();

// Get low stock products
$lowStockProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE stockQuantity < 10 AND isActive = 1")->fetchColumn();

// Get recent orders with customer info
$recentOrders = $pdo->prepare("
    SELECT o.orderID, c.name, o.totalAmount, o.status, o.orderDate
    FROM orders o
    JOIN customers c ON o.customerID = c.customerID
    ORDER BY o.orderDate DESC
    LIMIT 5
");
$recentOrders->execute();
$recentOrdersList = $recentOrders->fetchAll(PDO::FETCH_ASSOC);

// Get top products
$topProducts = $pdo->prepare("
    SELECT p.productID, p.productName, p.price, COALESCE(COUNT(oi.orderItemID), 0) as sales
    FROM products p
    LEFT JOIN orderitems oi ON p.productID = oi.productID
    WHERE p.isActive = 1
    GROUP BY p.productID
    ORDER BY sales DESC
    LIMIT 5
");
$topProducts->execute();
$topProductsList = $topProducts->fetchAll(PDO::FETCH_ASSOC);

$adminName = $_SESSION['adminName'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickPick Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin-css/admin-dashboard.css">
    <style>
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .main-dashboard {
            flex: 1;
            overflow-y: auto;
        }

        .recent-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card-section h3 {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 20px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-info {
            flex: 1;
        }

        .order-customer {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .order-date {
            font-size: 12px;
            color: #718096;
        }

        .order-amount {
            font-weight: 700;
            color: #2d3748;
            font-size: 18px;
            margin-right: 15px;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-completed {
            background: #c6f6d5;
            color: #22543d;
        }

        .badge-pending {
            background: #fed7d7;
            color: #742a2a;
        }

        .badge-cancelled {
            background: #e2e8f0;
            color: #2d3748;
        }

        .chart-container {
            height: 300px;
            background: #f7fafc;
            border-radius: 10px;
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            padding: 20px;
            gap: 10px;
        }

        .chart-bar {
            background: linear-gradient(135deg, #6dcff6, #3a9bdc);
            border-radius: 5px 5px 0 0;
            flex: 1;
            min-height: 10px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .recent-section {
                grid-template-columns: 1fr;
            }

            .order-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-amount {
                margin-right: 0;
                margin-top: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-admin">
                <div class="logo-icon">Q</div>
                <span>QuickPick Admin</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="/views/admin/dashboard.php" class="nav-link active">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/views/admin/products.php" class="nav-link">
                        <i class="fas fa-box"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/views/admin/orders.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/views/admin/customers.php" class="nav-link">
                        <i class="fas fa-users"></i> Customers
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/views/admin/inventory.php" class="nav-link">
                        <i class="fas fa-warehouse"></i> Inventory
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/views/admin/categories.php" class="nav-link">
                        <i class="fas fa-list"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/views/admin/settings.php" class="nav-link">
                        <i class="fas fa-list"></i> Settings
                    </a>
                </li>
                <li class="nav-item" style="margin-top: 40px;">
                    <a href="/controllers/admin/logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content main-dashboard">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1 class="page-title">Dashboard</h1>
                <div class="admin-profile">
                    <div class="admin-avatar"><?php echo strtoupper(substr($adminName, 0, 1)); ?></div>
                    <div>
                        <div style="font-weight: 600; color: #2d3748;"><?php echo htmlspecialchars($adminName); ?></div>
                        <div style="font-size: 12px; color: #718096;">Administrator</div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e6fffa; color: #319795;">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalProducts; ?></div>
                    <div class="stat-label">Total Products</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef5e7; color: #d69e2e;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-value"><?php echo $lowStockProducts; ?></div>
                    <div class="stat-label">Low Stock Items</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalOrders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #fce7f3; color: #be185d;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value"><?php echo $totalCustomers; ?></div>
                    <div class="stat-label">Total Customers</div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #e8f5e9; color: #2e7d32;">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-value">₱<?php echo number_format($totalRevenue, 2); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>

            <!-- Recent Data Section -->
            <div class="recent-section">
                <!-- Recent Orders -->
                <div class="card-section">
                    <h3>Recent Orders</h3>
                    <?php if (!empty($recentOrdersList)): ?>
                        <?php foreach ($recentOrdersList as $order): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <div class="order-customer"><?php echo htmlspecialchars($order['name']); ?></div>
                                    <div class="order-date">Order #<?php echo $order['orderID']; ?> • <?php echo date('M d, Y', strtotime($order['orderDate'])); ?></div>
                                </div>
                                <div class="order-amount">₱<?php echo number_format($order['totalAmount'], 2); ?></div>
                                <span class="badge-status badge-<?php echo strtolower($order['status'] ?? 'pending'); ?>">
                                    <?php echo ucfirst($order['status'] ?? 'pending'); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #718096; text-align: center;">No orders yet</p>
                    <?php endif; ?>
                </div>

                <!-- Top Products -->
                <div class="card-section">
                    <h3>Top Selling Products</h3>
                    <?php if (!empty($topProductsList)): ?>
                        <?php foreach ($topProductsList as $product): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <div class="order-customer"><?php echo htmlspecialchars($product['productName']); ?></div>
                                    <div class="order-date">₱<?php echo number_format($product['price'], 2); ?> each</div>
                                </div>
                                <div class="order-amount"><?php echo $product['sales'] ?? 0; ?> sold</div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #718096; text-align: center;">No sales data yet</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="card-section">
                <h3>Sales Overview (Last 7 Days)</h3>
                <div class="chart-container">
                    <div class="chart-bar" style="height: 40%;">
                        <span>₱2.5K</span>
                    </div>
                    <div class="chart-bar" style="height: 65%;">
                        <span>₱4.2K</span>
                    </div>
                    <div class="chart-bar" style="height: 55%;">
                        <span>₱3.8K</span>
                    </div>
                    <div class="chart-bar" style="height: 80%;">
                        <span>₱5.1K</span>
                    </div>
                    <div class="chart-bar" style="height: 45%;">
                        <span>₱2.9K</span>
                    </div>
                    <div class="chart-bar" style="height: 70%;">
                        <span>₱4.5K</span>
                    </div>
                    <div class="chart-bar" style="height: 60%;">
                        <span>₱3.9K</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set active nav link
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.href === window.location.href) {
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        });
    </script>
</body>

</html>