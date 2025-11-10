<?php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['productName'] ?? '';
    $categoryID = $_POST['categoryID'] ?? null;
    $price = $_POST['price'] ?? 0;
    $stockQuantity = $_POST['stockQuantity'] ?? 0;
    $unit = $_POST['unit'] ?? '';
    $description = $_POST['description'] ?? '';
    $imageURL = 'default.jpg';

    // Handle image upload
    if (isset($_FILES['imageURL']) && $_FILES['imageURL']['error'] === 0) {
        $uploadDir = __DIR__ . '/../../assets/images/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fileName = time() . '_' . basename($_FILES['imageURL']['name']);
        if (move_uploaded_file($_FILES['imageURL']['tmp_name'], $uploadDir . $fileName)) {
            $imageURL = $fileName;
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO products (productName, categoryID, price, stockQuantity, unit, description, imageURL, isActive, dateAdded)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([$productName, $categoryID ?: null, $price, $stockQuantity, $unit, $description, $imageURL]);

        $_SESSION['success'] = 'Product added successfully!';
        header("Location: /views/admin/products.php");
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error adding product: ' . $e->getMessage();
        header("Location: /views/admin/products.php");
    }
}
