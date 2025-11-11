<?php
session_start();
if (!isset($_SESSION['customerID'])) {
    header("Location: /views/login.php");
    exit;
}

include_once('../../includes/db_connect.php');
include('../../includes/functions.php');

// Get filter parameters
$categoryFilter = $_GET['category'] ?? 'all';
$searchTerm = $_GET['search'] ?? '';
$sortBy = $_GET['sort'] ?? 'name';

// Build query
$query = "
    SELECT p.*, c.categoryName
    FROM products p
    LEFT JOIN categories c ON p.categoryID = c.categoryID
    WHERE p.isActive = 1 AND p.stockQuantity > 0
";

$params = [];

if ($categoryFilter !== 'all' && is_numeric($categoryFilter)) {
    $query .= " AND p.categoryID = ?";
    $params[] = $categoryFilter;
}

if (!empty($searchTerm)) {
    $query .= " AND (p.productName LIKE ? OR c.categoryName LIKE ?)";
    $params[] = "%{$searchTerm}%";
    $params[] = "%{$searchTerm}%";
}

// Sorting
switch ($sortBy) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name':
    default:
        $query .= " ORDER BY p.productName ASC";
        break;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categoriesStmt = $pdo->query("SELECT * FROM categories WHERE isActive = 1 ORDER BY categoryName");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickPick - Browse Products</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/customer-css/customer-dashboard.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .products-page {
            min-height: 100vh;
            padding-bottom: 40px;
        }

        .page-header {
            background: linear-gradient(135deg, #6dcff6 0%, #3a9bdc 100%);
            padding: 30px 0;
            margin-bottom: 40px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: white;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .breadcrumb-custom {
            background: none;
            padding: 0;
            margin-top: 10px;
        }

        .breadcrumb-custom a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            transition: all 0.3s;
        }

        .breadcrumb-custom a:hover {
            color: white;
        }

        .breadcrumb-custom .active {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Filter Section */
        .filters-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .filter-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .filter-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .results-count {
            color: #718096;
            font-size: 0.95rem;
        }

        .category-pills {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .category-pill {
            padding: 10px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            background: white;
            color: #2d3748;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .category-pill:hover {
            border-color: #6dcff6;
            background: #f0f9ff;
            color: #0284c7;
            transform: translateY(-2px);
        }

        .category-pill.active {
            background: linear-gradient(135deg, #6dcff6, #3a9bdc);
            border-color: #3a9bdc;
            color: white;
            box-shadow: 0 4px 12px rgba(109, 207, 246, 0.4);
        }

        .search-sort-bar {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 15px;
        }

        .search-box-wrapper {
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #6dcff6;
            box-shadow: 0 0 0 3px rgba(109, 207, 246, 0.1);
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
            font-size: 1.1rem;
        }

        .sort-dropdown {
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #2d3748;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }

        .sort-dropdown:focus {
            outline: none;
            border-color: #6dcff6;
        }

        /* Products Grid */
        .products-container {
            padding: 0 20px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .product-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 3px solid transparent;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #6dcff6, #3a9bdc);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .product-card:hover::before {
            transform: scaleX(1);
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
            border-color: rgba(109, 207, 246, 0.3);
        }

        .product-image-wrapper {
            width: 100%;
            height: 180px;
            border-radius: 15px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f8f9fa;
            margin-bottom: 15px;
            position: relative;
        }

        .product-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image-wrapper img {
            transform: scale(1.1);
        }

        .category-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(109, 207, 246, 0.95);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2d3748;
            margin: 10px 0 5px;
            line-height: 1.3;
            min-height: 45px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-unit {
            font-size: 0.85rem;
            color: #718096;
            margin-bottom: 10px;
        }

        .product-price {
            font-size: 1.8rem;
            font-weight: 900;
            color: #3a9bdc;
            margin: 15px 0;
        }

        .price-currency {
            font-size: 1.2rem;
            font-weight: 700;
        }

        .add-to-cart-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #6dcff6, #3a9bdc);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(58, 155, 220, 0.4);
        }

        .add-to-cart-btn:active {
            transform: translateY(0);
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .empty-icon {
            font-size: 5rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 10px;
        }

        .empty-text {
            font-size: 1.1rem;
            color: #718096;
            margin-bottom: 25px;
        }

        /* Toast Notification */
        .toast-notification {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
            display: flex;
            align-items: center;
            gap: 15px;
            transform: translateX(400px);
            transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            z-index: 1000;
            font-weight: 600;
        }

        .toast-notification.show {
            transform: translateX(0);
        }

        .toast-icon {
            font-size: 1.5rem;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 1.8rem;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
                gap: 15px;
            }

            .search-sort-bar {
                grid-template-columns: 1fr;
            }

            .category-pills {
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 10px;
            }

            .category-pill {
                flex-shrink: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="top-header">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between">
                <div class="logo">
                    <div class="logo-icon">Q</div><span>QuickPick</span>
                </div>

                <div class="header-icons">
                    <div class="cart-icon-wrapper">
                        <a href="/views/customer/cart.php" class="icon-circle">🛒</a>
                    </div>

                    <div class="user-dropdown">
                        <div class="icon-circle user-icon" id="userIcon">👤</div>
                        <div class="dropdown-menu" id="userMenu">
                            <a href="/views/customer/profile.php">Profile</a>
                            <a href="/views/customer/order-history.php">Orders</a>
                            <a href="/controllers/auth/logout.php" class="logout-link">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="products-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <h1 class="page-title">
                    <i class="fas fa-shopping-bag"></i> Browse Products
                </h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-custom">
                        <li class="breadcrumb-item"><a href="/views/customer/dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Products</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container products-container">
            <!-- Filters Section -->
            <div class="filters-section">
                <div class="filter-header">
                    <h3 class="filter-title">
                        <i class="fas fa-filter"></i> Filter Products
                    </h3>
                    <span class="results-count">
                        <strong><?php echo count($products); ?></strong> products found
                    </span>
                </div>

                <!-- Category Pills -->
                <div class="category-pills">
                    <a href="?category=all&search=<?php echo urlencode($searchTerm); ?>&sort=<?php echo $sortBy; ?>"
                        class="category-pill <?php echo $categoryFilter === 'all' ? 'active' : ''; ?>">
                        <i class="fas fa-th"></i> All Products
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="?category=<?php echo $cat['categoryID']; ?>&search=<?php echo urlencode($searchTerm); ?>&sort=<?php echo $sortBy; ?>"
                            class="category-pill <?php echo $categoryFilter == $cat['categoryID'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['categoryName']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Search and Sort Bar -->
                <form method="GET" class="search-sort-bar">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryFilter); ?>">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>">

                    <div class="search-box-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text"
                            name="search"
                            class="search-input"
                            placeholder="Search products by name..."
                            value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>

                    <select name="sort" class="sort-dropdown" onchange="this.form.submit()">
                        <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>
                            <i class="fas fa-sort-alpha-down"></i> Name (A-Z)
                        </option>
                        <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>
                            <i class="fas fa-sort-amount-down"></i> Price: Low to High
                        </option>
                        <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>
                            <i class="fas fa-sort-amount-up"></i> Price: High to Low
                        </option>
                    </select>
                </form>
            </div>

            <!-- Products Grid -->
            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image-wrapper">
                                <span class="category-badge">
                                    <?php echo htmlspecialchars($product['categoryName'] ?? 'Uncategorized'); ?>
                                </span>
                                <img src="/assets/images/products/<?php echo htmlspecialchars($product['imageURL']); ?>"
                                    alt="<?php echo htmlspecialchars($product['productName']); ?>">
                            </div>

                            <h3 class="product-name"><?php echo htmlspecialchars($product['productName']); ?></h3>
                            <p class="product-unit"><?php echo htmlspecialchars($product['unit']); ?></p>

                            <div class="product-price">
                                <span class="price-currency">₱</span><?php echo number_format($product['price'], 2); ?>
                            </div>

                            <button class="add-to-cart-btn" data-id="<?php echo $product['productID']; ?>">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">🔍</div>
                    <h2 class="empty-title">No Products Found</h2>
                    <p class="empty-text">Try adjusting your filters or search terms</p>
                    <a href="?category=all" class="btn btn-primary btn-lg">
                        <i class="fas fa-redo"></i> View All Products
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-notification" id="cartToast">
        <i class="fas fa-check-circle toast-icon"></i>
        <span>Product added to cart!</span>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // User dropdown
        const userIcon = document.getElementById('userIcon');
        const userMenu = document.getElementById('userMenu');

        if (userIcon && userMenu) {
            userIcon.addEventListener('click', (e) => {
                e.stopPropagation();
                userMenu.classList.toggle('show');
            });

            document.addEventListener('click', (e) => {
                if (!userMenu.contains(e.target) && !userIcon.contains(e.target)) {
                    userMenu.classList.remove('show');
                }
            });
        }

        // Add to cart functionality
        const toast = document.getElementById('cartToast');
        let toastTimeout;

        function showToast() {
            toast.classList.add('show');
            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.stopPropagation();
                const productID = button.getAttribute('data-id');

                // Disable button temporarily
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

                fetch('../../controllers/orders/add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        credentials: 'include',
                        body: `product_id=${productID}`
                    })
                    .then(res => res.json())
                    .then(data => {
                        showToast();

                        // Reset button
                        setTimeout(() => {
                            button.disabled = false;
                            button.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                        }, 500);
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Failed to add product to cart');
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                    });
            });
        });
    </script>
</body>

</html>