<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['adminID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include("../../includes/db_connect.php");

$productID = $_POST['productID'] ?? 0;

try {
    $stmt = $pdo->prepare("UPDATE products SET isActive = 0 WHERE productID = ?");
    $stmt->execute([$productID]);
    echo json_encode(['success' => true, 'message' => 'Product deleted']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
