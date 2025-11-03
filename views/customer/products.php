<?php
session_start();
if (!isset($_SESSION['customerID'])) {
    header("Location: /views/login.php");
    exit;
}

include_once('../../includes/db_connect.php');
include_once('../../includes/header.php');

try {
    // Fetch all available products using PDO
    $stmt = $pdo->prepare("
        SELECT 
            p.productID, 
            p.productName, 
            p.description, 
            p.price, 
            p.stockQuantity, 
            p.unit, 
            p.imageURL, 
            c.categoryName
        FROM products p
        LEFT JOIN categories c ON p.categoryID = c.categoryID
        WHERE p.stockQuantity > 0 AND p.isActive = 1
    ");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<p>Error fetching products: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>


<head>
    <link rel="stylesheet" href="/assets/css/products.css">
</head>
<main class="products-section">
    <div class="section-header">
        <h2 class="section-title">Available Products</h2>
        <div class="header-buttons">
            <a href="/views/customer/dashboard.php" class="back-btn">← Back to Dashboard</a>
            <a href="categories.php" class="see-all-link">View Categories</a>
        </div>
    </div>

    <div class="products-grid">
        <?php if (!empty($products)): ?>
            <?php foreach ($products as $row): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="/assets/images/products/<?php echo htmlspecialchars($row['imageURL']); ?>"
                            alt="<?php echo htmlspecialchars($row['productName']); ?>">
                    </div>
                    <div class="product-name"><?php echo htmlspecialchars($row['productName']); ?></div>
                    <div class="product-subtitle"><?php echo htmlspecialchars($row['categoryName'] ?? 'Uncategorized'); ?></div>
                    <div class="product-weight"><?php echo htmlspecialchars($row['unit']); ?></div>
                    <div class="product-price">
                        <span class="price-currency">₱</span><?php echo number_format($row['price'], 2); ?>
                    </div>
                    <button class="add-btn" data-id="<?php echo $row['productID']; ?>">+</button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products available at the moment.</p>
        <?php endif; ?>
    </div>
</main>
<script>
    document.querySelectorAll('.add-btn').forEach(button => {
        button.addEventListener('click', () => {
            const productID = button.getAttribute('data-id');

            fetch('../../controllers/orders/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    credentials: 'include', // 👈 important: keeps PHP session cookies!
                    body: `product_id=${productID}`
                })

                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                })
                .catch(err => console.error(err));
        });
    });
</script>


<?php include_once('../../includes/footer.php'); ?>