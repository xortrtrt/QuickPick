<?php
session_start();
if (!isset($_SESSION['customerID'])) {
    header("Location: /views/login.php");
    exit;
}

include("../../includes/db_connect.php");

$customerID = $_SESSION['customerID'];
$orderID = intval($_GET['orderID'] ?? 0);

if ($orderID <= 0) {
    header("Location: /views/customer/dashboard.php");
    exit;
}

try {
    // Get order details
    $orderStmt = $pdo->prepare("
        SELECT o.*, c.name, c.phoneNumber
        FROM orders o
        JOIN customers c ON o.customerID = c.customerID
        WHERE o.orderID = ? AND o.customerID = ?
    ");
    $orderStmt->execute([$orderID, $customerID]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        header("Location: /views/customer/dashboard.php");
        exit;
    }

    // Get order items
    $itemsStmt = $pdo->prepare("
        SELECT oi.*, p.productName
        FROM orderitems oi
        JOIN products p ON oi.productID = p.productID
        WHERE oi.orderID = ?
    ");
    $itemsStmt->execute([$orderID]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Order Confirmed - QuickPick</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/customer-css/order-success.css">


</head>

<body>
    <!-- Header -->
    <div class="top-header">
        <div class="container-fluid d-flex align-items-center gap-3 px-4">
            <div class="logo">
                <div class="logo-icon">Q</div>
                <span>QuickPick</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-success">
        <div class="success-card">
            <!-- Success Icon -->
            <div class="success-icon">
                ✓
            </div>

            <!-- Success Message -->
            <h1 class="success-title">Order Confirmed!</h1>
            <p class="success-subtitle">Your order has been successfully placed. You'll receive a confirmation SMS shortly.</p>

            <!-- Delivery Info -->
            <div class="delivery-info">
                <i class="fas fa-truck delivery-info-icon"></i>
                <div class="delivery-info-text">
                    <strong>Preparation Time:</strong> Within 10 minutes to your selected pickup location<br>
                    <strong>Order Status:</strong> You can track your order in your account
                </div>
            </div>

            <!-- Order Information -->
            <div class="order-info">
                <div class="info-row">
                    <span class="info-label">Order Number</span>
                    <span class="info-value">#<?php echo str_pad($orderID, 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Customer Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['name']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone Number</span>
                    <span class="info-value"><?php echo htmlspecialchars($order['phoneNumber']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Order Date</span>
                    <span class="info-value"><?php echo date('M d, Y H:i', strtotime($order['orderDate'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Total Amount</span>
                    <span class="info-value" style="font-weight: 700; color: #3A9BDC;">₱<?php echo number_format($order['totalAmount'], 2); ?></span>
                </div>
            </div>

            <!-- Order Items -->
            <div class="order-items">
                <div class="items-title">
                    <i class="fas fa-shopping-bag"></i> Order Items
                </div>
                <?php foreach ($items as $item): ?>
                    <div class="item-row">
                        <div>
                            <div class="item-name"><?php echo htmlspecialchars($item['productName']); ?></div>
                            <div class="item-price">Quantity: <?php echo $item['quantity']; ?></div>
                        </div>
                        <div class="item-price">₱<?php echo number_format($item['quantity'] * $item['price'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="/views/customer/dashboard.php" class="btn-primary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
                <a href="/views/customer/order-history.php" class="btn-secondary">
                    <i class="fas fa-history"></i> View Orders
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>