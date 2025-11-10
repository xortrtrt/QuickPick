<?php
// File: views/admin/dashboard.php
// Refactored with sidebar include and DYNAMIC sales chart

session_start();

// Check if admin is logged in
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

// Get dashboard statistics
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE isActive = 1")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(totalAmount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();
$lowStockProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE stockQuantity < 10 AND isActive = 1")->fetchColumn();

// ============================================
// DYNAMIC SALES DATA FOR LAST 7 DAYS
// ============================================
$salesData = [];
$maxSales = 0;

// Get sales data for last 7 days
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = date('D', strtotime("-$i days"));

    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(totalAmount), 0) as daily_total, COUNT(*) as order_count
        FROM orders
        WHERE DATE(orderDate) = ? AND status = 'completed'
    ");
    $stmt->execute([$date]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $dailyTotal = floatval($result['daily_total'] ?? 0);
    $orderCount = intval($result['order_count'] ?? 0);

    $salesData[] = [
        'date' => $date,
        'day' => $dayName,
        'total' => $dailyTotal,
        'orders' => $orderCount,
        'formatted' => '₱' . number_format($dailyTotal, 0)
    ];

    // Track max for scaling
    if ($dailyTotal > $maxSales) {
        $maxSales = $dailyTotal;
    }
}

// If no sales, set max to 5000 for visual consistency
if ($maxSales == 0) {
    $maxSales = 5000;
}

// Calculate percentage heights for bars (0-100%)
$chartData = [];
foreach ($salesData as $day) {
    $percentage = ($day['total'] / $maxSales) * 100;
    if ($percentage < 5 && $day['total'] > 0) {
        $percentage = 5; // Minimum height for visibility
    }
    $chartData[] = [
        'day' => $day['day'],
        'total' => $day['total'],
        'formatted' => $day['formatted'],
        'percentage' => $percentage,
        'orders' => $day['orders']
    ];
}

// Get recent orders
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

// Calculate 7-day total
$totalWeekRevenue = array_sum(array_column($chartData, 'total'));

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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chart-info {
            font-size: 13px;
            color: #718096;
            font-weight: 500;
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

        /* ============================================
           DYNAMIC CHART STYLES
           ============================================ */
        .chart-container {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            height: 320px;
            background: linear-gradient(to top, #f7fafc 0%, transparent 100%);
            border-radius: 10px;
            padding: 20px;
            gap: 15px;
            position: relative;
        }

        /* Horizontal grid lines */
        .chart-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                repeating-linear-gradient(0deg,
                    #e2e8f0 0px,
                    #e2e8f0 1px,
                    transparent 1px,
                    transparent 60px);
            pointer-events: none;
            border-radius: 10px;
        }

        .chart-bar-wrapper {
            flex: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            justify-content: flex-end;
        }

        .chart-bar {
            width: 100%;
            background: linear-gradient(135deg, #6dcff6, #3a9bdc);
            border-radius: 8px 8px 0 0;
            transition: all 0.3s ease;
            position: relative;
            box-shadow: 0 4px 12px rgba(109, 207, 246, 0.3);
            cursor: pointer;
        }

        .chart-bar:hover {
            background: linear-gradient(135deg, #3a9bdc, #2876b8);
            box-shadow: 0 8px 20px rgba(58, 155, 220, 0.4);
            transform: translateY(-4px);
        }

        .chart-bar-value {
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            font-weight: 700;
            color: #2d3748;
            font-size: 13px;
            white-space: nowrap;
            margin-top: 8px;
        }

        .chart-day-label {
            position: absolute;
            bottom: -55px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            color: #718096;
            font-weight: 600;
        }

        .chart-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%) scale(0);
            background: #2d3748;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            opacity: 0;
            transition: all 0.2s ease;
            margin-bottom: 8px;
            pointer-events: none;
            z-index: 10;
        }

        .chart-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: #2d3748;
        }

        .chart-bar:hover .chart-tooltip {
            opacity: 1;
            transform: translateX(-50%) scale(1);
        }

        .chart-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            font-size: 14px;
        }

        .summary-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .summary-label {
            color: #718096;
            font-weight: 500;
        }

        .summary-value {
            color: #2d3748;
            font-weight: 700;
            font-size: 16px;
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

            .chart-container {
                height: 250px;
                gap: 8px;
                padding: 15px;
            }

            .chart-bar-value {
                font-size: 10px;
                bottom: -28px;
            }

            .chart-day-label {
                font-size: 10px;
                bottom: -50px;
            }

            .chart-summary {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-wrapper">
        <!-- Include Sidebar Here -->
        <?php include("../../includes/admin-sidebar.php"); ?>

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

            <!-- DYNAMIC CHART SECTION -->
            <div class="card-section">
                <h3>
                    <span>📊 Sales Overview (Last 7 Days)</span>
                    <span class="chart-info">Total: <strong>₱<?php echo number_format($totalWeekRevenue, 2); ?></strong></span>
                </h3>

                <div class="chart-container">
                    <?php foreach ($chartData as $day): ?>
                        <div class="chart-bar-wrapper" title="<?php echo $day['day']; ?>">
                            <div class="chart-bar" style="height: <?php echo $day['percentage']; ?>%;">
                                <div class="chart-tooltip">
                                    <div><?php echo $day['formatted']; ?></div>
                                    <div style="font-size: 11px; margin-top: 2px;"><?php echo $day['orders']; ?> orders</div>
                                </div>
                            </div>
                            <div class="chart-bar-value"><?php echo $day['formatted']; ?></div>
                            <div class="chart-day-label"><?php echo $day['day']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="chart-summary">
                    <div class="summary-item">
                        <i class="fas fa-calendar-alt" style="color: #6dcff6;"></i>
                        <span class="summary-label">Period:</span>
                        <span class="summary-value"><?php echo date('M d', strtotime('-6 days')); ?> - <?php echo date('M d, Y'); ?></span>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-shopping-bag" style="color: #48bb78;"></i>
                        <span class="summary-label">Total Orders:</span>
                        <span class="summary-value"><?php echo array_sum(array_column($chartData, 'orders')); ?></span>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-money-bill" style="color: #ed8936;"></i>
                        <span class="summary-label">Weekly Revenue:</span>
                        <span class="summary-value">₱<?php echo number_format($totalWeekRevenue, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>