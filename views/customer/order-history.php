<?php
session_start();
if (!isset($_SESSION['customerID'])) {
    header("Location: /views/login.php");
    exit;
}

include("../../includes/db_connect.php");

$customerID = $_SESSION['customerID'];

try {
    // Get all customer orders
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.orderItemID) as itemCount
        FROM orders o
        LEFT JOIN orderitems oi ON o.orderID = oi.orderID
        WHERE o.customerID = ?
        GROUP BY o.orderID
        ORDER BY o.orderDate DESC
    ");
    $stmt->execute([$customerID]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - QuickPick</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/customer-css/customer-dashboard.css">
    <link rel="stylesheet" href="/assets/css/customer-css/order-history.css">


</head>

<body>
    <!-- Header -->
    <header class="top-header">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <div class="logo">
                <div class="logo-icon">Q</div><span>QuickPick</span>
            </div>
            <a href="/views/customer/dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </header>

    <div class="orders-container">
        <h1 class="page-title">
            <i class="fas fa-history"></i> Order History
        </h1>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <h3>No Orders Yet</h3>
                <p style="color: #718096; margin-bottom: 20px;">You haven't placed any orders yet.</p>
                <a href="/views/customer/dashboard.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo str_pad($order['orderID'], 6, '0', STR_PAD_LEFT); ?></div>
                            <div class="order-date"><?php echo date('M d, Y H:i', strtotime($order['orderDate'])); ?></div>
                        </div>
                        <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>

                    <div class="order-details">
                        <div class="detail-item">
                            <span class="detail-label">Items:</span>
                            <span class="detail-value"><?php echo $order['itemCount']; ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Pickup Location:</span>
                            <span class="detail-value">Main Branch</span>
                        </div>
                    </div>

                    <div class="order-total">
                        <span class="total-label">Total Amount:</span>
                        <span class="total-amount">₱<?php echo number_format($order['totalAmount'], 2); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>