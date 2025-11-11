<?php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

// Get active category filter
$activeCategory = $_GET['category'] ?? 'all';
$searchTerm = $_GET['search'] ?? '';

// Build main inventory query
$mainQuery = "
    SELECT p.productID, p.productName, p.stockQuantity, p.unit, c.categoryName, p.price, p.categoryID
    FROM products p
    LEFT JOIN categories c ON p.categoryID = c.categoryID
    WHERE p.isActive = 1
";

$params = [];

if ($activeCategory !== 'all' && is_numeric($activeCategory)) {
    $mainQuery .= " AND p.categoryID = ?";
    $params[] = $activeCategory;
}

if (!empty($searchTerm)) {
    $mainQuery .= " AND p.productName LIKE ?";
    $params[] = "%{$searchTerm}%";
}

$mainQuery .= " ORDER BY p.stockQuantity ASC";

$mainStmt = $pdo->prepare($mainQuery);
$mainStmt->execute($params);
$mainProducts = $mainStmt->fetchAll(PDO::FETCH_ASSOC);

// Get cancelled inventory with category filter
$cancelledQuery = "
    SELECT 
        ci.cancelledStockID,
        ci.productID,
        ci.productName,
        ci.cancelledQuantity,
        ci.unit,
        ci.reason,
        ci.lastUpdated,
        p.price,
        p.categoryID,
        c.categoryName
    FROM cancelled_inventory ci
    LEFT JOIN products p ON ci.productID = p.productID
    LEFT JOIN categories c ON p.categoryID = c.categoryID
    WHERE ci.cancelledQuantity > 0
";

$cancelledParams = [];

if ($activeCategory !== 'all' && is_numeric($activeCategory)) {
    $cancelledQuery .= " AND p.categoryID = ?";
    $cancelledParams[] = $activeCategory;
}

if (!empty($searchTerm)) {
    $cancelledQuery .= " AND ci.productName LIKE ?";
    $cancelledParams[] = "%{$searchTerm}%";
}

$cancelledQuery .= " ORDER BY ci.cancelledQuantity DESC";

$cancelledStmt = $pdo->prepare($cancelledQuery);
$cancelledStmt->execute($cancelledParams);
$cancelledProducts = $cancelledStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for filter
$categoriesStmt = $pdo->query("SELECT categoryID, categoryName FROM categories WHERE isActive = 1 ORDER BY categoryName");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics (filtered)
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

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .category-filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .category-chip {
            padding: 10px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            background: white;
            color: #2d3748;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .category-chip:hover {
            border-color: #6dcff6;
            background: #f0f9ff;
            color: #0284c7;
        }

        .category-chip.active {
            background: linear-gradient(135deg, #6dcff6, #3a9bdc);
            border-color: #3a9bdc;
            color: white;
        }

        .search-box {
            display: flex;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-input:focus {
            outline: none;
            border-color: #6dcff6;
        }

        .btn-search {
            padding: 12px 30px;
            background: linear-gradient(135deg, #6dcff6, #3a9bdc);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(58, 155, 220, 0.3);
        }

        /* Table Styles */
        .table-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
            margin-bottom: 25px;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .category-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            background: #e0f2fe;
            color: #0284c7;
        }

        .no-category {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: 1fr;
            }

            .category-filters {
                justify-content: center;
            }

            .search-box {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="inventory-wrapper">
        <?php include("../../includes/admin-sidebar.php"); ?>

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

            <!-- Filter Section -->
            <div style="padding: 0 20px;">
                <div class="filter-section">
                    <h4 style="margin-bottom: 15px; font-weight: 700; color: #2d3748;">
                        <i class="fas fa-filter"></i> Filter by Category
                    </h4>

                    <div class="category-filters">
                        <a href="?category=all" class="category-chip <?php echo $activeCategory === 'all' ? 'active' : ''; ?>">
                            <i class="fas fa-th"></i> All Categories
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="?category=<?php echo $cat['categoryID']; ?>"
                                class="category-chip <?php echo $activeCategory == $cat['categoryID'] ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($cat['categoryName']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <form method="GET" class="search-box" style="margin-top: 15px;">
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($activeCategory); ?>">
                        <input type="text" name="search" class="search-input"
                            placeholder="Search products..."
                            value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
            </div>

            <!-- Main Inventory Table -->
            <div style="padding: 0 20px 20px;">
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">
                            <i class="fas fa-box"></i> Main Inventory
                            <?php if ($activeCategory !== 'all'): ?>
                                <?php
                                $selectedCat = array_filter($categories, fn($c) => $c['categoryID'] == $activeCategory);
                                $selectedCat = reset($selectedCat);
                                ?>
                                <span style="color: #0284c7;">- <?php echo htmlspecialchars($selectedCat['categoryName'] ?? ''); ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>

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
                                        <td>
                                            <?php if ($product['categoryName']): ?>
                                                <span class="category-badge">
                                                    <?php echo htmlspecialchars($product['categoryName']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="category-badge no-category">No Category</span>
                                            <?php endif; ?>
                                        </td>
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
                            <h3>No Products Found</h3>
                            <p>No products match your current filter</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Cancelled Inventory Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title" style="color: #f56565;">
                            <i class="fas fa-times-circle"></i> Cancelled Inventory
                            <?php if ($activeCategory !== 'all'): ?>
                                <span style="color: #0284c7;">- <?php echo htmlspecialchars($selectedCat['categoryName'] ?? ''); ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>

                    <?php if (!empty($cancelledProducts)): ?>
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Cancelled Qty</th>
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
                                        <td>
                                            <?php if ($item['categoryName']): ?>
                                                <span class="category-badge">
                                                    <?php echo htmlspecialchars($item['categoryName']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="category-badge no-category">No Category</span>
                                            <?php endif; ?>
                                        </td>
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
                            <p>No cancelled items in this category</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
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