<?php
// File: views/admin/order_details.php
// UPDATED: Cancelled items go to SEPARATE inventory, NOT back to main stock

session_start();

if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

$orderID = intval($_GET['id'] ?? 0);

if ($orderID <= 0) {
    header("Location: /views/admin/orders.php");
    exit;
}

// Fetch order details with customer info
$orderStmt = $pdo->prepare("
    SELECT o.*, c.name as customer_name, c.phoneNumber, c.address
    FROM orders o
    JOIN customers c ON o.customerID = c.customerID
    WHERE o.orderID = ?
");
$orderStmt->execute([$orderID]);
$order = $orderStmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION['error'] = 'Order not found';
    header("Location: /views/admin/orders.php");
    exit;
}

// Fetch order items
$itemsStmt = $pdo->prepare("
    SELECT oi.*, p.productName, p.stockQuantity
    FROM orderitems oi
    JOIN products p ON oi.productID = p.productID
    WHERE oi.orderID = ?
");
$itemsStmt->execute([$orderID]);
$orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch cancelled items for this order
$cancelledStmt = $pdo->prepare("
    SELECT * FROM cancelled_order_items
    WHERE orderID = ?
    ORDER BY cancelledAt DESC
");
$cancelledStmt->execute([$orderID]);
$cancelledItems = $cancelledStmt->fetchAll(PDO::FETCH_ASSOC);

$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $orderItemID = intval($_POST['orderItemID'] ?? 0);

    if ($action === 'reduce_quantity') {
        $newQuantity = intval($_POST['newQuantity'] ?? 0);
        $cancelledQuantity = intval($_POST['cancelledQuantity'] ?? 0);

        if ($newQuantity < 0) {
            $errorMsg = 'Invalid quantity';
        } elseif ($cancelledQuantity <= 0) {
            $errorMsg = 'Cancelled quantity must be greater than 0';
        } else {
            try {
                $pdo->beginTransaction();

                // Get current order item
                $currentItem = $pdo->prepare("SELECT * FROM orderitems WHERE orderItemID = ?");
                $currentItem->execute([$orderItemID]);
                $item = $currentItem->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    throw new Exception('Order item not found');
                }

                // Get product info for cancelled inventory
                $productStmt = $pdo->prepare("SELECT productName, unit FROM products WHERE productID = ?");
                $productStmt->execute([$item['productID']]);
                $product = $productStmt->fetch(PDO::FETCH_ASSOC);

                // IMPORTANT: Do NOT restore to main stock - items will go to cancelled_inventory via trigger

                // Update order item quantity
                if ($newQuantity > 0) {
                    $updateItem = $pdo->prepare("
                        UPDATE orderitems SET quantity = ? WHERE orderItemID = ?
                    ");
                    $updateItem->execute([$newQuantity, $orderItemID]);
                } else {
                    // Remove item entirely if quantity becomes 0
                    $deleteItem = $pdo->prepare("DELETE FROM orderitems WHERE orderItemID = ?");
                    $deleteItem->execute([$orderItemID]);
                }

                // Record cancelled item - trigger will automatically add to cancelled_inventory
                $recordCancel = $pdo->prepare("
                    INSERT INTO cancelled_order_items 
                    (orderID, productID, productName, originalQuantity, cancelledQuantity, price, reason, cancelledAt)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $recordCancel->execute([
                    $orderID,
                    $item['productID'],
                    $product['productName'],
                    $item['quantity'],
                    $cancelledQuantity,
                    $item['price'],
                    $_POST['reason'] ?? 'Customer changed mind'
                ]);

                // Recalculate order total
                $newTotal = $pdo->prepare("
                    SELECT COALESCE(SUM(quantity * price), 0) as total FROM orderitems WHERE orderID = ?
                ");
                $newTotal->execute([$orderID]);
                $totalResult = $newTotal->fetch(PDO::FETCH_ASSOC);

                // Update order total amount
                $updateOrder = $pdo->prepare("UPDATE orders SET totalAmount = ? WHERE orderID = ?");
                $updateOrder->execute([$totalResult['total'], $orderID]);

                $pdo->commit();

                $successMsg = 'Order item updated successfully. Cancelled items moved to separate inventory.';

                // Refresh order data
                $orderStmt = $pdo->prepare("
                    SELECT o.*, c.name as customer_name, c.phoneNumber, c.address
                    FROM orders o
                    JOIN customers c ON o.customerID = c.customerID
                    WHERE o.orderID = ?
                ");
                $orderStmt->execute([$orderID]);
                $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

                // Refresh items
                $itemsStmt = $pdo->prepare("
                    SELECT oi.*, p.productName, p.stockQuantity
                    FROM orderitems oi
                    JOIN products p ON oi.productID = p.productID
                    WHERE oi.orderID = ?
                ");
                $itemsStmt->execute([$orderID]);
                $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

                // Refresh cancelled items
                $cancelledStmt = $pdo->prepare("
                    SELECT * FROM cancelled_order_items
                    WHERE orderID = ?
                    ORDER BY cancelledAt DESC
                ");
                $cancelledStmt->execute([$orderID]);
                $cancelledItems = $cancelledStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $pdo->rollBack();
                $errorMsg = 'Error: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'complete_order') {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE orderID = ?");
            $stmt->execute([$orderID]);

            $successMsg = 'Order marked as completed';

            $orderStmt = $pdo->prepare("
                SELECT o.*, c.name as customer_name, c.phoneNumber, c.address
                FROM orders o
                JOIN customers c ON o.customerID = c.customerID
                WHERE o.orderID = ?
            ");
            $orderStmt->execute([$orderID]);
            $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $errorMsg = 'Error: ' . $e->getMessage();
        }
    }
}

// Calculate totals
$subtotal = 0;
foreach ($orderItems as $item) {
    $subtotal += $item['quantity'] * $item['price'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - QuickPick Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin-css/admin-dashboard.css">
    <style>
        .order-details-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .main-order {
            flex: 1;
            overflow-y: auto;
            background: #f5f7fa;
        }

        .order-header {
            background: white;
            padding: 30px 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-bottom: 3px solid #6dcff6;
        }

        .order-header-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .order-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: #2d3748;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .order-header-meta {
            display: flex;
            gap: 20px;
            font-size: 14px;
            color: #718096;
        }

        .order-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }

        .order-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .order-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .order-status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fed7d7;
            color: #742a2a;
        }

        .status-completed {
            background: #c6f6d5;
            color: #22543d;
        }

        .status-cancelled {
            background: #e2e8f0;
            color: #2d3748;
        }

        .customer-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }

        .info-label {
            color: #718096;
            font-weight: 600;
        }

        .info-value {
            color: #2d3748;
        }

        .order-items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-items-table thead {
            background: #f7fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .order-items-table th {
            padding: 12px;
            text-align: left;
            font-weight: 700;
            color: #2d3748;
            font-size: 13px;
            text-transform: uppercase;
        }

        .order-items-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }

        .product-cell {
            font-weight: 600;
            color: #2d3748;
        }

        .btn-modify {
            background: #bee3f8;
            color: #2c5282;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }

        .btn-modify:hover {
            background: #90cdf4;
        }

        .order-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
        }

        .summary-label {
            color: #718096;
        }

        .summary-value {
            color: #2d3748;
            font-weight: 600;
        }

        .summary-total {
            border-top: 2px solid #e2e8f0;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 0;
            margin: 12px 0;
            font-size: 16px;
        }

        .summary-total .summary-label {
            font-weight: 700;
            color: #2d3748;
        }

        .summary-total .summary-value {
            font-size: 20px;
            font-weight: 800;
            color: #3a9bdc;
        }

        .modify-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modify-modal.active {
            display: flex;
        }

        .modify-modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modify-modal-header {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 20px;
        }

        .modify-form-group {
            margin-bottom: 15px;
        }

        .modify-form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 6px;
            display: block;
        }

        .modify-form-control {
            width: 100%;
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
        }

        .modify-form-control:focus {
            outline: none;
            border-color: #6dcff6;
        }

        .modify-modal-actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .btn-submit {
            flex: 1;
            background: linear-gradient(135deg, #6dcff6, #3a9bdc);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(58, 155, 220, 0.3);
        }

        .btn-close-modal {
            flex: 1;
            background: #e2e8f0;
            color: #2d3748;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-close-modal:hover {
            background: #cbd5e0;
        }

        .alert-message {
            padding: 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #48bb78;
        }

        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #f56565;
        }

        .cancelled-items-section {
            background: #fff5f5;
            border: 2px solid #fed7d7;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }

        .cancelled-items-title {
            font-weight: 700;
            color: #742a2a;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cancelled-item-row {
            background: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }

        .cancelled-item-name {
            font-weight: 600;
            color: #2d3748;
        }

        .cancelled-item-qty {
            color: #718096;
        }

        /* NEW: Info note about separate inventory */
        .inventory-note {
            background: #e0f2fe;
            border-left: 4px solid #0284c7;
            padding: 12px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 13px;
            color: #0c4a6e;
            display: flex;
            align-items: start;
            gap: 8px;
        }

        .inventory-note i {
            margin-top: 2px;
        }

        .btn-complete {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-complete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(72, 187, 120, 0.3);
        }

        @media (max-width: 768px) {
            .order-grid {
                grid-template-columns: 1fr;
            }

            .order-header h1 {
                font-size: 24px;
            }

            .order-header-meta {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="order-details-wrapper">
        <?php include("../../includes/admin-sidebar.php"); ?>

        <div class="main-order">
            <div class="order-header">
                <div class="order-header-content">
                    <h1>
                        <i class="fas fa-shopping-bag"></i> Order #<?php echo str_pad($orderID, 6, '0', STR_PAD_LEFT); ?>
                    </h1>
                    <div class="order-header-meta">
                        <span><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></span>
                        <span><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['orderDate'])); ?></span>
                        <span>
                            <strong>Status:</strong>
                            <span class="order-status-badge status-<?php echo strtolower($order['status']); ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="order-content">
                <?php if ($successMsg): ?>
                    <div class="alert-message alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($successMsg); ?>
                    </div>
                <?php endif; ?>

                <?php if ($errorMsg): ?>
                    <div class="alert-message alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($errorMsg); ?>
                    </div>
                <?php endif; ?>

                <div class="order-grid">
                    <div>
                        <div class="order-card">
                            <div class="card-title">
                                <i class="fas fa-list"></i> Order Items
                            </div>

                            <?php if (!empty($orderItems)): ?>
                                <table class="order-items-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderItems as $item): ?>
                                            <tr>
                                                <td class="product-cell"><?php echo htmlspecialchars($item['productName']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                                <td>₱<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                                                <td>
                                                    <button class="btn-modify" onclick="openModifyModal(<?php echo $item['orderItemID']; ?>, '<?php echo htmlspecialchars($item['productName']); ?>', <?php echo $item['quantity']; ?>, <?php echo $item['price']; ?>)">
                                                        <i class="fas fa-edit"></i> Modify
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p style="color: #718096; text-align: center; padding: 20px;">No items in this order</p>
                            <?php endif; ?>

                            <?php if (!empty($cancelledItems)): ?>
                                <div class="cancelled-items-section">
                                    <div class="cancelled-items-title">
                                        <i class="fas fa-times-circle"></i> Cancelled/Modified Items
                                    </div>
                                    <?php foreach ($cancelledItems as $cancelled): ?>
                                        <div class="cancelled-item-row">
                                            <div>
                                                <div class="cancelled-item-name"><?php echo htmlspecialchars($cancelled['productName']); ?></div>
                                                <div class="cancelled-item-qty">Reason: <?php echo htmlspecialchars($cancelled['reason']); ?></div>
                                            </div>
                                            <div style="text-align: right;">
                                                <div style="font-weight: 700; color: #742a2a;">-<?php echo $cancelled['cancelledQuantity']; ?></div>
                                                <div style="font-size: 11px; color: #718096;">₱<?php echo number_format($cancelled['cancelledQuantity'] * $cancelled['price'], 2); ?></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <!-- NEW: Info note about separate inventory -->
                                    <div class="inventory-note">
                                        <i class="fas fa-info-circle"></i>
                                        <div>
                                            <strong>Note:</strong> Cancelled items are stored in separate inventory, not returned to main stock.
                                            View them in the dashboard statistics.
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <div class="order-card" style="margin-bottom: 25px;">
                            <div class="card-title">
                                <i class="fas fa-user"></i> Customer Information
                            </div>

                            <div class="customer-info">
                                <div class="info-row">
                                    <span class="info-label">Name:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Phone:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['phoneNumber']); ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Address:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['address'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="order-card">
                            <div class="card-title">
                                <i class="fas fa-receipt"></i> Order Summary
                            </div>

                            <div class="order-summary">
                                <div class="summary-row">
                                    <span class="summary-label">Subtotal:</span>
                                    <span class="summary-value">₱<?php echo number_format($subtotal, 2); ?></span>
                                </div>
                                <div class="summary-row summary-total">
                                    <span class="summary-label">Total:</span>
                                    <span class="summary-value">₱<?php echo number_format($order['totalAmount'], 2); ?></span>
                                </div>
                            </div>

                            <?php if ($order['status'] === 'pending'): ?>
                                <form method="POST" style="margin-top: 20px;">
                                    <input type="hidden" name="action" value="complete_order">
                                    <button type="submit" class="btn-complete" onclick="return confirm('Mark this order as completed?')">
                                        <i class="fas fa-check"></i> Complete Order
                                    </button>
                                </form>
                            <?php else: ?>
                                <div style="background: #c6f6d5; padding: 12px; border-radius: 8px; text-align: center; color: #22543d; font-weight: 600; margin-top: 20px;">
                                    <i class="fas fa-check-circle"></i> Order Completed
                                </div>
                            <?php endif; ?>

                            <a href="/views/admin/orders.php" style="display: block; text-align: center; margin-top: 12px; color: #6dcff6; text-decoration: none; font-weight: 600;">
                                <i class="fas fa-arrow-left"></i> Back to Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modify-modal" id="modifyModal">
        <div class="modify-modal-content">
            <div class="modify-modal-header">
                <i class="fas fa-edit"></i> Modify Order Item
            </div>

            <form method="POST" id="modifyForm">
                <input type="hidden" name="action" value="reduce_quantity">
                <input type="hidden" name="orderItemID" id="modalOrderItemID">

                <div class="modify-form-group">
                    <label class="modify-form-label">Product Name:</label>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; color: #2d3748; font-weight: 600;" id="modalProductName"></div>
                </div>

                <div class="modify-form-group">
                    <label class="modify-form-label">Original Quantity:</label>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 8px; color: #2d3748; font-weight: 600;" id="modalOriginalQty"></div>
                </div>

                <div class="modify-form-group">
                    <label class="modify-form-label">New Quantity (0 to remove):</label>
                    <input type="number" class="modify-form-control" name="newQuantity" id="modalNewQuantity" min="0" required>
                </div>

                <div class="modify-form-group">
                    <label class="modify-form-label">Cancelled Quantity:</label>
                    <input type="number" class="modify-form-control" id="modalCancelledQuantity" name="cancelledQuantity" readonly style="background: #f8f9fa;">
                </div>

                <div class="modify-form-group">
                    <label class="modify-form-label">Reason for Cancellation:</label>
                    <select class="modify-form-control" name="reason">
                        <option value="Customer changed mind">Customer changed mind</option>
                        <option value="Out of stock">Out of stock</option>
                        <option value="Damaged item">Damaged item</option>
                        <option value="Customer request">Customer request</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="modify-modal-actions">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <button type="button" class="btn-close-modal" onclick="closeModifyModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modifyModal = document.getElementById('modifyModal');
        const modalOrderItemID = document.getElementById('modalOrderItemID');
        const modalProductName = document.getElementById('modalProductName');
        const modalOriginalQty = document.getElementById('modalOriginalQty');
        const modalNewQuantity = document.getElementById('modalNewQuantity');
        const modalCancelledQuantity = document.getElementById('modalCancelledQuantity');

        let currentOriginalQty = 0;

        // Open modal and set values
        function openModifyModal(orderItemID, productName, originalQty, price) {
            modifyModal.classList.add('active');
            modalOrderItemID.value = orderItemID;
            modalProductName.textContent = productName;
            modalOriginalQty.textContent = originalQty;
            modalNewQuantity.value = originalQty;
            modalCancelledQuantity.value = 0;
            currentOriginalQty = originalQty;
        }

        // Close modal
        function closeModifyModal() {
            modifyModal.classList.remove('active');
        }

        // Update cancelled quantity live
        modalNewQuantity.addEventListener('input', function() {
            let newQty = parseInt(modalNewQuantity.value);
            if (isNaN(newQty) || newQty < 0) newQty = 0;
            const cancelledQty = currentOriginalQty - newQty;
            modalCancelledQuantity.value = cancelledQty > 0 ? cancelledQty : 0;
        });

        // Close modal when clicking outside
        modifyModal.addEventListener('click', function(e) {
            if (e.target === modifyModal) {
                closeModifyModal();
            }
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>