<?php
include_once('../../includes/db_connect.php');
include_once('../../includes/header.php');
session_start();

// Fetch all categories with product count
$query = "
    SELECT c.categoryID, c.categoryName, c.description, COUNT(p.productID) AS total_products
    FROM categories c
    LEFT JOIN products p ON c.categoryID = p.categoryID AND p.isActive = 1
    GROUP BY c.categoryID
    ORDER BY c.categoryName ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<head>
    <link rel="stylesheet" href="/assets/css/customer-css/categories.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<main class="categories-section">
    <div class="section-header">
        <h2 class="section-title">Categories</h2>
        <div class="header-buttons">
            <a href="/views/customer/dashboard.php" class="back-btn">← Back to Dashboard</a>
            <a href="products.php" class="see-all-link">View Products</a>
        </div>
    </div>


    <div class="categories-grid">
        <?php if ($categories && count($categories) > 0): ?>
            <?php foreach ($categories as $cat): ?>
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                    <div class="category-info">
                        <h3 class="category-name"><?php echo htmlspecialchars($cat['categoryName']); ?></h3>
                        <p class="category-desc"><?php echo htmlspecialchars($cat['description']); ?></p>
                        <span class="product-count">
                            <?php echo $cat['total_products']; ?> product<?php echo $cat['total_products'] != 1 ? 's' : ''; ?>
                        </span>
                    </div>
                    <a href="products.php?category=<?php echo $cat['categoryID']; ?>" class="view-btn">View</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No categories available at the moment.</p>
        <?php endif; ?>
    </div>
</main>

<?php include_once('../../includes/footer.php'); ?>