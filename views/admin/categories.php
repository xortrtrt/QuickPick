<?php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

// Display success/error messages
$successMsg = $_SESSION['success'] ?? null;
$errorMsg = $_SESSION['error'] ?? null;
unset($_SESSION['success']);
unset($_SESSION['error']);

// Handle search and filters
$searchTerm = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';

// Build query
$query = "
    SELECT c.*, 
           COUNT(p.productID) as productCount
    FROM categories c
    LEFT JOIN products p ON c.categoryID = p.categoryID AND p.isActive = 1
    WHERE 1=1
";

$params = [];

// Add search filter
if (!empty($searchTerm)) {
    $query .= " AND (c.categoryName LIKE ? OR c.description LIKE ?)";
    $searchWildcard = "%{$searchTerm}%";
    $params = [$searchWildcard, $searchWildcard];
}

// Add status filter
if (!empty($statusFilter)) {
    $query .= " AND c.isActive = ?";
    $params[] = ($statusFilter === 'active') ? 1 : 0;
}

$query .= " GROUP BY c.categoryID ORDER BY c.categoryID DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$activeCategories = $pdo->query("SELECT COUNT(*) FROM categories WHERE isActive = 1")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE isActive = 1")->fetchColumn();
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
    <link rel="stylesheet" href="/assets/css/admin-css/categories.css">

</head>

<body>
    <div class="categories-wrapper">
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
                    <a href="/views/admin/inventory.php" class="nav-link">
                        <i class="fas fa-warehouse"></i> Inventory
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
        <div class="main-content categories-main">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1 class="page-title">Categories Management</h1>
                <button class="btn-add-category" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus-circle"></i> Add Category
                </button>
            </div>

            <!-- Messages -->
            <div style="padding: 0 20px;">
                <?php if ($successMsg): ?>
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($successMsg); ?>
                    </div>
                <?php endif; ?>
                <?php if ($errorMsg): ?>
                    <div class="alert-danger">
                        <i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($errorMsg); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Statistics -->
            <div style="padding: 0 20px;">
                <div class="stats-row">
                    <div class="stat-box">
                        <div class="stat-box-label"><i class="fas fa-list"></i> Total Categories</div>
                        <div class="stat-box-value"><?php echo $totalCategories; ?></div>
                    </div>
                    <div class="stat-box" style="border-left-color: #48bb78;">
                        <div class="stat-box-label"><i class="fas fa-check-circle"></i> Active</div>
                        <div class="stat-box-value"><?php echo $activeCategories; ?></div>
                    </div>
                    <div class="stat-box" style="border-left-color: #ed8936;">
                        <div class="stat-box-label"><i class="fas fa-box"></i> Total Products</div>
                        <div class="stat-box-value"><?php echo $totalProducts; ?></div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div style="padding: 0 20px;">
                <form method="GET" class="filter-section">
                    <div class="filter-row">
                        <input type="text" name="search" placeholder="Search categories..."
                            value="<?php echo htmlspecialchars($searchTerm); ?>" class="form-control">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active Only</option>
                            <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive Only</option>
                        </select>
                    </div>
                    <div class="filter-row">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="/views/admin/categories.php" class="btn-reset">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Categories Grid -->
            <div style="padding: 0 20px 20px;">
                <?php if (empty($categories)): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📁</div>
                        <h3>No Categories Found</h3>
                        <p>No categories match your search criteria</p>
                        <button class="btn-add-category" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fas fa-plus-circle"></i> Create First Category
                        </button>
                    </div>
                <?php else: ?>
                    <div class="categories-grid">
                        <?php foreach ($categories as $category): ?>
                            <div class="category-card">
                                <div class="category-header">
                                    <div class="category-name"><?php echo htmlspecialchars($category['categoryName']); ?></div>
                                    <span class="category-status <?php echo $category['isActive'] ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $category['isActive'] ? '✓ Active' : '✗ Inactive'; ?>
                                    </span>
                                </div>

                                <div class="category-description">
                                    <?php echo htmlspecialchars($category['description'] ?? 'No description provided'); ?>
                                </div>

                                <div class="category-stats">
                                    <div class="stat-item">
                                        <div class="stat-value"><?php echo $category['productCount']; ?></div>
                                        <div class="stat-label">Products</div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-value"><?php echo $category['categoryID']; ?></div>
                                        <div class="stat-label">ID</div>
                                    </div>
                                </div>

                                <div class="category-actions">
                                    <button class="btn-edit-cat" onclick="editCategory(<?php echo $category['categoryID']; ?>, '<?php echo htmlspecialchars($category['categoryName']); ?>', '<?php echo htmlspecialchars($category['description'] ?? ''); ?>', <?php echo $category['isActive']; ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" action="/controllers/admin/delete_category.php" style="flex: 1; margin: 0;">
                                        <input type="hidden" name="categoryID" value="<?php echo $category['categoryID']; ?>">
                                        <button type="submit" class="btn-delete-cat" style="width: 100%; margin: 0;" onclick="return confirm('Delete <?php echo htmlspecialchars($category['categoryName']); ?>?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="/controllers/admin/add_category_form.php">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Category Name *</label>
                            <input type="text" class="form-control" name="categoryName" placeholder="e.g., Fresh Vegetables" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Describe this category..."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" name="isActive" value="1" checked>
                                Active Category
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="/controllers/admin/update_category.php">
                    <input type="hidden" id="editCategoryID" name="categoryID">
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Category Name *</label>
                            <input type="text" class="form-control" id="editCategoryName" name="categoryName" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="editCategoryDesc" name="description" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" id="editCategoryActive" name="isActive" value="1">
                                Active Category
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCategory(categoryID, name, description, isActive) {
            document.getElementById('editCategoryID').value = categoryID;
            document.getElementById('editCategoryName').value = name;
            document.getElementById('editCategoryDesc').value = description;
            document.getElementById('editCategoryActive').checked = isActive == 1;

            const editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
            editModal.show();
        }
    </script>
</body>

</html>