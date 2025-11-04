<?php
session_start();
include_once('../../includes/db_connect.php');

if (!isset($_SESSION['customerID'])) {
    header("Location: /views/login.php");
    exit;
}
// 1️⃣ Check for existing active cart
$stmt = $pdo->prepare("SELECT cartID FROM cart WHERE customerID = :customerID LIMIT 1");
$stmt->execute(['customerID' => $customerID]);
$cart = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cart) {
    $cartID = $cart['cartID'];
} else {
    // Create a new cart row
    $insertCart = $pdo->prepare("INSERT INTO cart (customerID, totalItems, totalAmount, dateCreated) VALUES (:customerID, 0, 0, NOW())");
    $insertCart->execute(['customerID' => $customerID]);
    $cartID = $pdo->lastInsertId();
}

// 2️⃣ Add or update product
$stmt = $pdo->prepare("SELECT * FROM cart WHERE cartID = :cartID AND productID = :productID");
$stmt->execute(['cartID' => $cartID, 'productID' => $productID]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if ($item) {
    // Update quantity & subtotal
    $update = $pdo->prepare("UPDATE cart SET quantity = quantity + 1, subtotal = (quantity + 1) * price WHERE cartID = :cartID AND productID = :productID");
    $update->execute(['cartID' => $cartID, 'productID' => $productID]);
} else {
    // Insert new product
    $insert = $pdo->prepare("INSERT INTO cart (cartID, customerID, productID, quantity, price, subtotal, dateCreated) VALUES (:cartID, :customerID, :productID, 1, :price, :price, NOW())");
    $insert->execute(['cartID' => $cartID, 'customerID' => $customerID, 'productID' => $productID, 'price' => $price]);
}

// 3️⃣ Update totals
$totalItems = $pdo->query("SELECT SUM(quantity) FROM cart WHERE cartID = $cartID")->fetchColumn();
$totalAmount = $pdo->query("SELECT SUM(subtotal) FROM cart WHERE cartID = $cartID")->fetchColumn();

$updateCart = $pdo->prepare("UPDATE cart SET totalItems = :totalItems, totalAmount = :totalAmount WHERE cartID = :cartID");
$updateCart->execute(['totalItems' => $totalItems, 'totalAmount' => $totalAmount, 'cartID' => $cartID]);
