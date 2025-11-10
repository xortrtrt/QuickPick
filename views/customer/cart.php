<?php
session_start();
if (!isset($_SESSION['customerID'])) {
    header("Location: /views/login.php");
    exit;
}

include("../../includes/db_connect.php");
include("../../includes/functions.php");

// Fetch cart items for logged-in user
$customerID = $_SESSION['customerID'];
$cartItems = getCartItems($pdo, $customerID);
$totalPrice = 0;
foreach ($cartItems as $item) {
    $totalPrice += $item['subtotal']; // use subtotal from DB
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>QuickPick - Cart</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/customer-css/customer-dashboard.css">
    <link rel="stylesheet" href="/assets/css/customer-css/cart.css">
</head>

<body>
    <!-- HEADER -->
    <header class="top-header">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <div class="logo">
                <div class="logo-icon">Q</div><span>QuickPick</span>
            </div>
            <div class="search-container">
                <input type="text" class="form-control" placeholder="Search products...">
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="/views/customer/products.php" class="btn btn-outline-secondary">Back to Shop</a>
                <div class="user-dropdown">
                    <div class="icon-circle user-icon">👤</div>
                    <div class="dropdown-menu">
                        <a href="/views/customer/profile.php">Profile</a>
                        <a href="/views/customer/settings.php">Settings</a>
                        <a href="/controllers/auth/logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- CART SECTION -->
    <section class="cart-section py-5">
        <div class="container">
            <h2 class="mb-4">Your Cart</h2>

            <?php if (!empty($cartItems)): ?>
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="cart-items" id="cart-items-container">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="cart-card d-flex align-items-center mb-3 p-3 shadow-sm rounded-3" id="cart-item-<?= $item['cartItemID'] ?>">
                                    <div class="cart-image me-3">
                                        <img src="/assets/images/products/<?= htmlspecialchars($item['imageURL']); ?>"
                                            alt="<?= htmlspecialchars($item['productName']); ?>"
                                            class="rounded-3" width="80">
                                    </div>
                                    <div class="cart-details flex-grow-1">
                                        <h5 class="product-name mb-1"><?= htmlspecialchars($item['productName']); ?></h5>
                                        <div class="product-category text-muted mb-2"><?= htmlspecialchars($item['categoryName'] ?? 'Uncategorized'); ?></div>
                                        <div class="quantity-control d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn"
                                                data-action="decrease" data-itemid="<?= $item['cartItemID']; ?>">-</button>
                                            <span class="quantity px-2" id="qty-<?= $item['cartItemID']; ?>">
                                                <?= $item['quantity']; ?>
                                            </span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn"
                                                data-action="increase" data-itemid="<?= $item['cartItemID']; ?>">+</button>
                                            <span class="product-price ms-auto" id="subtotal-<?= $item['cartItemID']; ?>">
                                                ₱<?= number_format($item['price'], 2); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="cart-remove ms-3">
                                        <button class="remove-btn btn btn-danger btn-sm" data-itemid="<?= $item['cartItemID']; ?>">✕</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- CHECKOUT SUMMARY -->
                    <div class="col-lg-4">
                        <div class="checkout-summary p-4 shadow-sm rounded-3">
                            <h4 class="mb-3">Order Summary</h4>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="cart-total">₱<?= number_format($totalPrice, 2); ?></span>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                            <span>Total:</span>
                            <span id="cart-total">₱<?= number_format($totalPrice, 2); ?></span>
                        </div>
                        <a href="/views/customer/checkout.php" class="btn btn-primary w-100">Proceed to Checkout</a>
                    </div>
                </div>
            <?php else: ?>
                <p>Your cart is empty. <a href="/views/customer/dashboard.php">Start shopping now!</a></p>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/customer-js/cart.js"></script>
</body>

</html>