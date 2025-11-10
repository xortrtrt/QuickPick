<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['adminID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include("../../includes/db_connect.php");

$customerID = $_POST['customerID'] ?? 0;

try {
    // Delete related data first
    $pdo->prepare("DELETE FROM cartitems WHERE cartID IN (SELECT cartID FROM carts WHERE customerID = ?)")->execute([$customerID]);
    $pdo->prepare("DELETE FROM carts WHERE customerID = ?")->execute([$customerID]);
    $pdo->prepare("DELETE FROM orderitems WHERE orderID IN (SELECT orderID FROM orders WHERE customerID = ?)")->execute([$customerID]);
    $pdo->prepare("DELETE FROM orders WHERE customerID = ?")->execute([$customerID]);
    $pdo->prepare("DELETE FROM customers WHERE customerID = ?")->execute([$customerID]);

    echo json_encode(['success' => true, 'message' => 'Customer deleted']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
