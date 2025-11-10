<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['adminID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include("../../includes/db_connect.php");

$productID = $_POST['productID'] ?? 0;
$quantity = $_POST['quantity'] ?? 0;

try {
    $stmt = $pdo->prepare("UPDATE products SET stockQuantity = ? WHERE productID = ?");
    $stmt->execute([$quantity, $productID]);
    echo json_encode(['success' => true, 'message' => 'Stock updated']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
