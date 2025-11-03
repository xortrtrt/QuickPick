<?php
session_start();
include_once('../../includes/db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['customerID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first.']);
    exit;
}

$customerID = $_SESSION['customerID'];
$productID = intval($_POST['product_id']);

// 1️⃣ Fetch product details
$stmt = $pdo->prepare("SELECT price FROM products WHERE productID = :productID AND isActive = 1");
$stmt->execute(['productID' => $productID]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
    exit;
}

$price = $product['price'];

// 2️⃣ Check if product already exists in cart
$stmt = $pdo->prepare("SELECT * FROM cart WHERE customerID = :customerID AND productID = :productID");
$stmt->execute(['customerID' => $customerID, 'productID' => $productID]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    // Update quantity
    $update = $pdo->prepare("
        UPDATE cart 
        SET quantity = quantity + 1,
            subtotal = (quantity + 1) * price
        WHERE customerID = :customerID AND productID = :productID
    ");
    $update->execute(['customerID' => $customerID, 'productID' => $productID]);
} else {
    // Insert new product
    $insert = $pdo->prepare("
        INSERT INTO cart (customerID, productID, quantity, price, dateCreated) 
        VALUES (:customerID, :productID, 1, :price, NOW())
    ");
    $insert->execute(['customerID' => $customerID, 'productID' => $productID, 'price' => $price]);
}

// 3️⃣ Update totals for this customer’s cart
$totalItems = $pdo->query("SELECT SUM(quantity) FROM cart WHERE customerID = $customerID")->fetchColumn();
$totalAmount = $pdo->query("SELECT SUM(subtotal) FROM cart WHERE customerID = $customerID")->fetchColumn();

$updateCart = $pdo->prepare("
    UPDATE cart 
    SET totalItems = :totalItems, totalAmount = :totalAmount 
    WHERE customerID = :customerID
");
$updateCart->execute(['totalItems' => $totalItems, 'totalAmount' => $totalAmount, 'customerID' => $customerID]);

echo json_encode(['status' => 'success', 'message' => 'Product added to cart successfully.']);
