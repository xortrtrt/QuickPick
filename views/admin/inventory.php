<?php
// File: views/admin/inventory.php
// Updated with tabs to view Main Inventory and Cancelled Inventory separately

session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

// Get active tab (default to main inventory)
$activeTab = $_GET['tab'] ?? 'main';

// Get main inventory products
$mainStmt = $pdo->prepare("
    SELECT p.productID, p.productName, p.stockQuantity, p.unit, c.categoryName, p.price
    FROM products p
    LEFT JOIN categories c ON p.categoryID = c.categoryID
    WHERE p.isActive = 1
    ORDER BY p.stockQuantity ASC
");
$mainStmt->execute();
$mainProducts = $mainStmt->fetchAll(PDO::FETCH_ASSOC);

// Get cancelled inventory products
$cancelledStmt = $pdo->prepare("
    SELECT 
        ci.cancelledStockID,
        ci.productID,
        ci.productName,
        ci.cancelledQuantity,
        ci.unit,
        ci.reason,
        ci.lastUpdated,
        p.price,
        c.categoryName
    FROM cancelled_inventory ci
    LEFT JOIN products p ON ci.productID = p.productID
    LEFT JOIN categories c ON p.categoryID = c.categoryID
    WHERE ci.cancelledQuantity > 0
    ORDER BY ci.cancelledQuantity DESC
");
$cancelledStmt->execute();
$cancelledProducts = $cancelledStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$totalMainStock = array_sum(array_column($mainProducts, 'stockQuantity'));
$lowStockCount = count(array_filter($mainProducts, fn($p) => $p['stockQuantity'] < 10));
$totalCancelledStock = array_sum(array_column($cancelledProducts, 'cancelledQuantity'));
$totalCancelledValue = 0;
foreach ($cancelledProducts as $item) {
    $totalCancelledValue += ($item['cancelledQuantity'] * ($item['price'] ?? 0));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickPick - Inventory Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin-css/admin-dashboard.css">
    <style>
        .inventory-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .inventory-main {
            flex: 1;
            overflow-y: auto;
            background: #f5f7fa;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #6dcff6;
        }

        .stat-box.cancelled {
            border-left-color: #f56565;
        }

        .stat-box.warning {
            border-left-color: #f59e0b;
        }

        .stat-box-value {
            font-size: 32px;
            font-weight: 800;
            color: #2d3748;
            margin: 10px 0;
        }

        .stat-box-label {
            font-size: 13px;
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Tab System */
        .inventory-tabs {
            background: white;
            border-radius: 12px;
            padding: 5px;
            display: inline-flex;
            gap: 5px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .tab-button {
            padding: 12px 30px;
            border: none;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            color: #718096;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-button:hover {
            background: #f7fafc;
            color: #2d3748;
        }

        .tab-button.active {
            background: linear-gradient(135deg, #6dcff6, #3a9bdc);
            color: white;
            box-shadow: 0 4px 12px rgba(109, 207, 246, 0.3);
        }

        .tab-button.cancelled.active {
            background: linear-gradient(135deg, #f56565, #e53e3e);
        }

        /* Table Styles */
        .table-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inventory-table thead {
            background: #f7fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        .inventory-table th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 700;
            color: #2d3748;
            font-size: 13px;
            text-transform: uppercase;
        }

        .inventory-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
        }

        .inventory-table tbody tr:hover {
            background: #f7fafc;
        }

        .stock-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .stock-in {
            background: #c6f6d5;
            color: #22543d;
        }

        .stock-low {
            background: #fed7d7;
            color: #742a2a;
        }

        .stock-critical {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-right: 5px;
            transition: all 0.3s;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-edit {
            background: #bee3f8;
            color: #2c5282;
        }

        .btn-edit:hover {
            background: #90cdf4;
            transform: translateY(-2px);
        }

        .btn-restore {
            background: #c6f6d5;
            color: #22543d;
        }

        .btn-restore:hover {
            background: #9ae6b4;
            transform: translateY(-2px);
        }

        .btn-discard {
            background: #fed7d7;
            color: #742a2a;
        }

        .btn-discard:hover {
            background: #fc8181;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #718096;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .cancelled-info {
            background: #fef2f2;
            border-left: 4px solid #f56565;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #991b1b;
        }

        .cancelled-info i {
            margin-right: 8px;
        }

        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: 1fr;
            }

            .tab-button {
                padding: 10px 15px;
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="inventory-wrapper">
        <!-- Sidebar -->
        <?php include("../../includes/admin-sidebar.php"); ?>

        <!-- Main Content -->
        <div class="main-content inventory-main">
            <div class="top-bar">
                <h1 class="page-title">
                    <i class="fas fa-warehouse"></i> Inventory Management
                </h1>
            </div>

            <!-- Statistics -->
            <div style="padding: 0 20px;">
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-box-label">
                            <i class="fas fa-boxes"></i> Total Main Stock
                        </div>
                        <div class="stat-box-value"><?php echo number_format($totalMainStock); ?></div>
                    </div>

                    <div class="stat-box warning">
                        <div class="stat-box-label">
                            <i class="fas fa-exclamation-triangle"></i> Low Stock Items
                        </div>
                        <div class="stat-box-value"><?php echo $lowStockCount; ?></div>
                    </div>

                    <div class="stat-box cancelled">
                        <div class="stat-box-label">
                            <i class="fas fa-undo-alt"></i> Cancelled Stock
                        </div>
                        <div class="stat-box-value"><?php echo number_format($totalCancelledStock); ?></div>
                    </div>

                    <div class="stat-box cancelled">
                        <div class="stat-box-label">
                            <i class="fas fa-dollar-sign"></i> Cancelled Value
                        </div>
                        <div class="stat-box-value">₱<?php echo number_format($totalCancelledValue, 2); ?></div>
                    </div>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div style="padding: 0 20px;">
                <div class="inventory-tabs">
                    <a href="?tab=main" class="tab-button <?php echo $activeTab === 'main' ? 'active' : ''; ?>">
                        <i class="fas fa-box"></i>
                        Main Inventory
                    </a>
                    <a href="?tab=cancelled" class="tab-button cancelled <?php echo $activeTab === 'cancelled' ? 'active' : ''; ?>">
                        <i class="fas fa-times-circle"></i>
                        Cancelled Inventory
                    </a>
                </div>
            </div>

            <!-- Main Inventory Table -->
            <?php if ($activeTab === 'main'): ?>
                <div style="padding: 0 20px 20px;">
                    <div class="table-card">
                        <h3 style="font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 20px;">
                            <i class="fas fa-list"></i> Active Products
                        </h3>

                        <?php if (!empty($mainProducts)): ?>
                            <table class="inventory-table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Current Stock</th>
                                        <th>Unit</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mainProducts as $product): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($product['productName']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($product['categoryName'] ?? 'N/A'); ?></td>
                                            <td>
                                                <input type="number" class="form-control"
                                                    value="<?php echo $product['stockQuantity']; ?>"
                                                    id="stock-<?php echo $product['productID']; ?>"
                                                    style="max-width: 100px;">
                                            </td>
                                            <td><?php echo htmlspecialchars($product['unit']); ?></td>
                                            <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                            <td>
                                                <?php
                                                if ($product['stockQuantity'] <= 0) {
                                                    echo '<span class="stock-badge stock-critical">Out of Stock</span>';
                                                } elseif ($product['stockQuantity'] < 10) {
                                                    echo '<span class="stock-badge stock-low">Low Stock</span>';
                                                } else {
                                                    echo '<span class="stock-badge stock-in">In Stock</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <button class="action-btn btn-edit"
                                                    onclick="updateStock(<?php echo $product['productID']; ?>)">
                                                    <i class="fas fa-save"></i> Save
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">📦</div>
                                <h3>No Products in Inventory</h3>
                                <p>Add products to start managing inventory</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Cancelled Inventory Table -->
            <?php if ($activeTab === 'cancelled'): ?>
                <div style="padding: 0 20px 20px;">
                    <div class="cancelled-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>About Cancelled Inventory:</strong> These items were cancelled from customer orders.
                        You can restore them to main inventory or permanently discard them.
                    </div>

                    <div class="table-card">
                        <h3 style="font-size: 18px; font-weight: 700; color: #2d3748; margin-bottom: 20px;">
                            <i class="fas fa-times-circle"></i> Cancelled Items
                        </h3>

                        <?php if (!empty($cancelledProducts)): ?>
                            <table class="inventory-table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Category</th>
                                        <th>Cancelled Quantity</th>
                                        <th>Unit</th>
                                        <th>Reason</th>
                                        <th>Last Updated</th>
                                        <th>Value</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cancelledProducts as $item): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($item['productName']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($item['categoryName'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span style="font-size: 18px; font-weight: 700; color: #f56565;">
                                                    <?php echo $item['cancelledQuantity']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['unit']); ?></td>
                                            <td><?php echo htmlspecialchars($item['reason'] ?? 'N/A'); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($item['lastUpdated'])); ?></td>
                                            <td>₱<?php echo number_format($item['cancelledQuantity'] * ($item['price'] ?? 0), 2); ?></td>
                                            <td>
                                                <button class="action-btn btn-restore"
                                                    onclick="restoreToMain(<?php echo $item['productID']; ?>, <?php echo $item['cancelledQuantity']; ?>)">
                                                    <i class="fas fa-redo"></i> Restore
                                                </button>
                                                <button class="action-btn btn-discard"
                                                    onclick="discardItem(<?php echo $item['cancelledStockID']; ?>)">
                                                    <i class="fas fa-trash"></i> Discard
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">✨</div>
                                <h3>No Cancelled Items</h3>
                                <p>All inventory items are in main stock</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update main inventory stock
        function updateStock(productID) {
            const quantity = document.getElementById(`stock-${productID}`).value;

            fetch('/controllers/admin/update_stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `productID=${productID}&quantity=${quantity}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Stock updated successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Failed to update stock');
                });
        }

        // Restore cancelled item to main inventory
        function restoreToMain(productID, quantity) {
            if (!confirm(`Restore ${quantity} items back to main inventory?`)) return;

            fetch('/controllers/admin/restore_cancelled_stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `productID=${productID}&quantity=${quantity}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Items restored to main inventory successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Failed to restore items');
                });
        }

        // Permanently discard cancelled item
        function discardItem(cancelledStockID) {
            if (!confirm('Permanently discard this cancelled item? This cannot be undone.')) return;

            fetch('/controllers/admin/discard_cancelled_stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `cancelledStockID=${cancelledStockID}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Item discarded successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Failed to discard item');
                });
        }
    </script>
</body>

</html>