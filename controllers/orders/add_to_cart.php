<?php
session_start();
include_once('../../includes/db_connect.php');

if (!isset($_SESSION['customerID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first.']);
    exit;
}

$customerID = $_SESSION['customerID'];
$productID = intval($_POST['product_id']);

// 1️⃣ Get product price
$stmt = $pdo->prepare("SELECT price FROM products WHERE productID = :productID AND isActive = 1");
$stmt->execute(['productID' => $productID]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
    exit;
}
$price = $product['price'];

// 2️⃣ Check for active cart
$stmt = $pdo->prepare("SELECT cartID FROM carts WHERE customerID = :customerID AND status = 'active' LIMIT 1");
$stmt->execute(['customerID' => $customerID]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cart) {
    $cartID = $cart['cartID'];
} else {
    // Create new cart
    $stmt = $pdo->prepare("INSERT INTO carts (customerID, status, totalItems, totalAmount, dateCreated) VALUES (:customerID,'active',0,0,NOW())");
    $stmt->execute(['customerID' => $customerID]);
    $cartID = $pdo->lastInsertId();
}

// 3️⃣ Add or update cart item
$stmt = $pdo->prepare("SELECT * FROM cartItems WHERE cartID = :cartID AND productID = :productID");
$stmt->execute(['cartID' => $cartID, 'productID' => $productID]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($item) {
    // Update quantity
    $stmt = $pdo->prepare("UPDATE cartItems SET quantity = quantity + 1, subtotal = (quantity + 1) * price WHERE cartID = :cartID AND productID = :productID");
    $stmt->execute(['cartID' => $cartID, 'productID' => $productID]);
} else {
    // Insert new item
    $stmt = $pdo->prepare("INSERT INTO cartItems (cartID, productID, quantity, price, subtotal, dateAdded) VALUES (:cartID,:productID,1,:price,:price,NOW())");
    $stmt->execute(['cartID' => $cartID, 'productID' => $productID, 'price' => $price]);
}

// 4️⃣ Update cart totals
$totalItems = $pdo->query("SELECT SUM(quantity) FROM cartItems WHERE cartID = $cartID")->fetchColumn();
$totalAmount = $pdo->query("SELECT SUM(subtotal) FROM cartItems WHERE cartID = $cartID")->fetchColumn();

$stmt = $pdo->prepare("UPDATE carts SET totalItems = :totalItems, totalAmount = :totalAmount WHERE cartID = :cartID");
$stmt->execute(['totalItems' => $totalItems, 'totalAmount' => $totalAmount, 'cartID' => $cartID]);

echo json_encode(['status' => 'success', 'message' => 'Product added to cart successfully.']);
