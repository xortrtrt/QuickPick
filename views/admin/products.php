<?php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

// Get all products
$stmt = $pdo->prepare("
    SELECT p.*, c.categoryName
    FROM products p
    LEFT JOIN categories c ON p.categoryID = c.categoryID
    ORDER BY p.productID DESC
");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$adminName = $_SESSION['adminName'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickPick - Products Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin-css/admin-dashboard.css">
    <style>
        .modal-header {
            background: linear-gradient(135deg, #6dcff6, #3a9bdc);
            color: white;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .action-dropdown {
            position: relative;
        }

        .dropdown-menu {
            min-width: 150px;
        }
    </style>
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
                    <a href="/views/admin/products.php" class="nav-link active">
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
                <li class="nav-item" style="margin-top: 40px;">
                    <a href="/controllers/admin/logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content" style="flex: 1;">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1 class="page-title">Products Management</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus"></i> Add Product
                </button>
            </div>

            <!-- Products Table -->
            <div class="table-card">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="/assets/images/products/<?php echo htmlspecialchars($product['imageURL']); ?>"
                                        class="product-img" alt="<?php echo htmlspecialchars($product['productName']); ?>">
                                </td>
                                <td><strong><?php echo htmlspecialchars($product['productName']); ?></strong></td>
                                <td><?php echo htmlspecialchars($product['categoryName'] ?? 'N/A'); ?></td>
                                <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                <td><?php echo $product['stockQuantity']; ?></td>
                                <td><?php echo htmlspecialchars($product['unit']); ?></td>
                                <td>
                                    <?php if ($product['isActive']): ?>
                                        <span class="stock-badge stock-in">Active</span>
                                    <?php else: ?>
                                        <span class="stock-badge stock-low">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="action-btn btn-edit" onclick="editProduct(<?php echo $product['productID']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn btn-delete" onclick="deleteProduct(<?php echo $product['productID']; ?>)">
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

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="productForm" method="POST" action="/controllers/admin/add_product.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Product Name *</label>
                            <input type="text" class="form-control" name="productName" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select class="form-select" name="categoryID" required>
                                <option value="">Select Category</option>
                                <?php
                                $categories = $pdo->query("SELECT * FROM categories WHERE isActive = 1")->fetchAll();
                                foreach ($categories as $cat):
                                ?>
                                    <option value="<?php echo $cat['categoryID']; ?>">
                                        <?php echo htmlspecialchars($cat['categoryName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Price (₱) *</label>
                                    <input type="number" class="form-control" name="price" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Stock Quantity *</label>
                                    <input type="number" class="form-control" name="stockQuantity" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Unit *</label>
                            <input type="text" class="form-control" name="unit" placeholder="e.g., kg, piece, box" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="imageURL" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProduct(productID) {
            // Redirect to edit page or open modal with product data
            window.location.href = `/views/admin/edit_product.php?id=${productID}`;
        }

        function deleteProduct(productID) {
            if (confirm('Are you sure you want to delete this product?')) {
                fetch(`/controllers/admin/delete_product.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `productID=${productID}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('Product deleted successfully');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            }
        }
    </script>
</body>

</html>