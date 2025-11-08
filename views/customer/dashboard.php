<?php
session_start();
if (!isset($_SESSION['customerID'])) {
    header("Location: /views/login.php");
    exit;
}
include("../../includes/db_connect.php");
include('../../includes/functions.php');
$products = getAvailableProducts($pdo, 8);
$categories = getAvailableCategories($pdo, 2);

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>QuickPick - Customer Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/customer-dashboard.css">

</head>


<body>
    <header class="top-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between">
                <div class="logo">
                    <div class="logo-icon">Q</div><span>QuickPick</span>
                </div>

                <div class="search-container">
                    <div class="search-box">
                        <input type="text" placeholder="Search for Grocery, Stores, Vegetable or Meat">
                    </div>
                </div>

                <div class="d-flex align-items-center gap-4">
                    <div class="order-timer">
                        ⚡ Order now and get it within <span class="timer-highlight">15 min!</span>
                    </div>

                    <div class="header-icons">
                        <div class="cart-icon-wrapper">
                            <a href="pickup_location.php" class="icon-circle" id="location-icon">📍</a>
                        </div>

                        <div class="cart-icon-wrapper" id="headerCart">
                            <a href="cart.php" class="icon-circle">🛒</a>
                        </div>
                    </div>

                    <!-- USER DROPDOWN -->
                    <div class="user-dropdown">
                        <div class="icon-circle user-icon" id="userIcon">👤</div>
                        <div class="dropdown-menu" id="userMenu">
                            <a href="/views/customer/profile.php">Profile</a>
                            <a href="/views/customer/settings.php">Settings</a>
                            <a href="/controllers/auth/logout.php" class="logout-link">Logout</a>
                        </div>
                    </div>
                    <!-- END USER DROPDOWN -->
                </div>
            </div>
        </div>
        </div>
    </header>

    <!-- HERO SECTION W/ ANIMATION -->
    <section class="hero-section" aria-label="Hero">
        <div class="floating-icon one" aria-hidden="true">🥦</div>
        <div class="floating-icon two" aria-hidden="true">🚚</div>
        <div class="floating-icon three" aria-hidden="true">💳</div>
        <div class="floating-icon four" aria-hidden="true">🛍️</div>

        <div class="hero-content">
            <h1 class="hero-title">We bring the store to your door</h1>
            <p class="hero-description">(DESCRIPTION)</p>
            <button class="shop-now-btn" aria-label="Shop now">Shop now →</button>
        </div>

        <div class="hero-image" aria-hidden="true">
            <img src="/assets/images/heropic.png" alt="Shopping cart illustration">
        </div>

        <div class="hero-divider" aria-hidden="true">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none" style="width:100%; height:100px; display:block;">
                <path d="M0,40 C360,120 1080,0 1440,60 L1440,120 L0,120 Z" fill="#f5f7fa"></path>
            </svg>
        </div>
    </section>

    <!-- CATEGORIES -->
    <section class="categories-section">
        <div class="container-fluid">
            <div class="section-header">
                <h2 class="section-title">Shop by Category</h2>
                <a href="/views/customer/categories.php" class="see-all-link">See all →</a>
            </div>
            <div class="categories-scroll">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $row): ?>
                        <div class="category-card">
                            <div class="category-image">
                                <!-- no image yet -->
                                <!-- <img src="/assets/images/categories/<?php echo htmlspecialchars($row['imageURL']); ?>" alt="<?php echo htmlspecialchars($row['categoryName']); ?>"> -->
                            </div>
                            <div class="category-name"><?php echo htmlspecialchars($row['categoryName']); ?></div>
                            <div class="category-subtitle"><?php echo htmlspecialchars($row['description'] ?? 'Uncategorized'); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No products available at the moment.</p>
                <?php endif; ?>
            </div>

        </div>
    </section>
    <!-- SAMPLE PRODUCTS -->
    <section class="products-section">
        <div class="container-fluid">
            <div class="section-header">
                <h2 class="section-title">You might need</h2>
                <a href="products.php" class="see-all-link">See more →</a>
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
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No products available at the moment.</p>
                <?php endif; ?>
            </div>

        </div>
        </div>
    </section>
    <!-- FEATURED DISCOUNTS -->
    <section id="discount-carousel" style="padding:32px 20px;">
        <h2 class="featured-discounts">FEATURED DISCOUNTS</h2>
        <div class="dc-card">
            <button class="dc-btn dc-left" aria-label="Previous item">‹</button>
            <div class="dc-track-wrap" aria-live="polite">
                <ul class="dc-track"></ul>
            </div>
            <button class="dc-btn dc-right" aria-label="Next item">›</button>
            <div class="dc-thumbs" aria-hidden="false"></div>
        </div>
    </section>
    <script src="/assets/js/customer-dashboard.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>