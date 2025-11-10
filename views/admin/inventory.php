<?php
// File: views/admin/inventory.php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

// Get low stock products
$stmt = $pdo->prepare("
    SELECT p.productID, p.productName, p.stockQuantity, p.unit, c.categoryName, p.price
    FROM products p
    LEFT JOIN categories c ON p.categoryID = c.categoryID
    WHERE p.isActive = 1
    ORDER BY p.stockQuantity ASC
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
</head>

<body>
    <div style="display: flex;">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-admin">
                <div class="logo-icon">Q</div>
                <span>QuickPick Admin</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="/views/admin/dashboard.php" class="nav-link">
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
                    <a href="/views/admin/inventory.php" class="nav-link active">
                        <i class="fas fa-warehouse"></i> Inventory
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
        <div class="main-content" style="flex: 1;">
            <div class="top-bar">
                <h1 class="page-title">Inventory Management</h1>
            </div>

            <!-- Inventory Table -->
            <div class="table-card">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Current Stock</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($product['productName']); ?></strong></td>
                                <td><?php echo htmlspecialchars($product['categoryName'] ?? 'N/A'); ?></td>
                                <td>
                                    <input type="number" class="form-control" value="<?php echo $product['stockQuantity']; ?>"
                                        id="stock-<?php echo $product['productID']; ?>" style="max-width: 100px;">
                                </td>
                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                <td>
                                    <?php
                                    $stockClass = $product['stockQuantity'] < 10 ? 'stock-low' : 'stock-in';
                                    $stockText = $product['stockQuantity'] < 10 ? 'Low Stock' : 'In Stock';
                                    ?>
                                    <span class="stock-badge <?php echo $stockClass; ?>"><?php echo $stockText; ?></span>
                                </td>
                                <td>
                                    <button class="action-btn btn-edit"
                                        onclick="updateStock(<?php echo $product['productID']; ?>)">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                    }
                });
        }
    </script>
</body>

</html>

<?php
// File: views/admin/categories.php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

$categories = $pdo->query("SELECT * FROM categories ORDER BY categoryID DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickPick - Categories Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin-css/admin-dashboard.css">
</head>

<body>
    <div style="display: flex;">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo-admin">
                <div class="logo-icon">Q</div>
                <span>QuickPick Admin</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="/views/admin/dashboard.php" class="nav-link">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/views/admin/products.php" class="nav-link">
                        <i class="fas fa-box"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/views/admin/categories.php" class="nav-link active">
                        <i class="fas fa-list"></i> Categories
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
        <div class="main-content" style="flex: 1;">
            <div class="top-bar">
                <h1 class="page-title">Categories Management</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </div>

            <!-- Categories Table -->
            <div class="table-card">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cat['categoryName']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cat['description'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="stock-badge <?php echo $cat['isActive'] ? 'stock-in' : 'stock-low'; ?>">
                                        <?php echo $cat['isActive'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn btn-edit" onclick="editCategory(<?php echo $cat['categoryID']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn btn-delete" onclick="deleteCategory(<?php echo $cat['categoryID']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #6dcff6, #3a9bdc); color: white;">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1);"></button>
                </div>
                <form method="POST" action="/controllers/admin/add_category.php">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Category Name *</label>
                            <input type="text" class="form-control" name="categoryName" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteCategory(categoryID) {
            if (confirm('Are you sure?')) {
                fetch('/controllers/admin/delete_category.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `categoryID=${categoryID}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('Category deleted');
                            location.reload();
                        }
                    });
            }
        }
    </script>
</body>

</html>