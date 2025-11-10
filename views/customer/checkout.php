<?php
session_start();
if (!isset($_SESSION['customerID'])) {
    header("Location: /views/login.php");
    exit;
}

include("../../includes/db_connect.php");
include("../../includes/functions.php");

$customerID = $_SESSION['customerID'];

// Get customer info
$customerStmt = $pdo->prepare("SELECT * FROM customers WHERE customerID = ?");
$customerStmt->execute([$customerID]);
$customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

// Get cart items
$cartItems = getCartItems($pdo, $customerID);

if (empty($cartItems)) {
    header("Location: /views/customer/cart.php");
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['subtotal'];
}


$total = $subtotal;

// Handle order placement
$orderMessage = '';
$orderError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Check stock availability BEFORE creating order
        foreach ($cartItems as $item) {
            $stockCheck = $pdo->prepare("SELECT stockQuantity FROM products WHERE productID = ?");
            $stockCheck->execute([$item['productID']]);
            $product = $stockCheck->fetch(PDO::FETCH_ASSOC);

            if (!$product || $product['stockQuantity'] < $item['quantity']) {
                throw new Exception("Sorry, " . htmlspecialchars($item['productName']) . " is out of stock or has insufficient quantity.");
            }
        }

        // Create order WITHOUT cartID
        $orderStmt = $pdo->prepare("
            INSERT INTO orders (customerID, totalAmount, status, orderDate)
            VALUES (?, ?, 'pending', NOW())
        ");
        $orderStmt->execute([$customerID, $total]);
        $orderID = $pdo->lastInsertId();

        // Add order items AND reduce stock quantity
        foreach ($cartItems as $item) {
            // Insert order item
            $itemStmt = $pdo->prepare("
                INSERT INTO orderitems (orderID, productID, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            $itemStmt->execute([$orderID, $item['productID'], $item['quantity'], $item['price']]);

            // Reduce stock quantity
            $updateStock = $pdo->prepare("
                UPDATE products 
                SET stockQuantity = stockQuantity - ? 
                WHERE productID = ?
            ");
            $updateStock->execute([$item['quantity'], $item['productID']]);
        }

        // Clear cart items
        $clearCartStmt = $pdo->prepare("
            DELETE FROM cartitems 
            WHERE cartID IN (SELECT cartID FROM carts WHERE customerID = ?)
        ");
        $clearCartStmt->execute([$customerID]);

        $pdo->commit();

        // Redirect to success page
        header("Location: /views/customer/order-success.php?orderID=" . $orderID);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $orderError = "Error creating order: " . $e->getMessage();
        error_log($orderError);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - QuickPick</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/customer-css/checkout.css">

</head>

<body>
    <!-- Header -->
    <div class="top-header">
        <div class="container-fluid d-flex align-items-center gap-3 px-4">
            <div class="logo">
                <div class="logo-icon">Q</div>
                <span>QuickPick</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-checkout">
        <h1 class="page-title">
            <i class="fas fa-shopping-cart"></i> Checkout
        </h1>

        <?php if ($orderError): ?>
            <div class="alert alert-danger">
                <i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($orderError); ?>
            </div>
        <?php endif; ?>

        <div class="checkout-grid">
            <!-- Checkout Form -->
            <div class="checkout-form-card">
                <form method="POST">
                    <div class="form-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Pickup Location</h3>

                        <label class="radio-option">
                            <input type="radio" name="pickupLocation" value="main_branch" required>
                            <div class="option-content">
                                <div class="option-title">Main Branch - San Carlos</div>
                                <div class="option-desc">📍 Brgy. San Carlos, Lipa City</div>
                            </div>
                        </label>
                    </div>



                    <!-- Contact Info -->
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Contact Information</h3>
                        <div class="form-group">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($customer['name']); ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" value="<?php echo htmlspecialchars($customer['phoneNumber']); ?>" disabled>
                        </div>
                    </div>

                    <!-- Place Order Button -->
                    <button type="submit" class="btn-place-order">
                        <i class="fas fa-check-circle"></i> Place Order - ₱<?php echo number_format($total, 2); ?>
                    </button>
                </form>
            </div>

            <!-- Right: Order Summary -->
            <div class="order-summary-card">
                <h3 class="order-summary-title">Order Summary</h3>

                <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <div>
                            <div class="item-name"><?php echo htmlspecialchars($item['productName']); ?></div>
                            <div class="item-price">x<?php echo $item['quantity']; ?></div>
                        </div>
                        <div class="item-price">₱<?php echo number_format($item['subtotal'], 2); ?></div>
                    </div>
                <?php endforeach; ?>

                <div class="order-summary-section">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value">₱<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="total-row">
                        <span class="total-label">Total</span>
                        <span class="total-value">₱<?php echo number_format($total, 2); ?></span>
                    </div>
                </div>

                <p style="font-size: 12px; color: #718096; margin-top: 15px;">
                    <i class="fas fa-info-circle"></i> By placing this order, you agree to our Terms & Conditions
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>

</html>