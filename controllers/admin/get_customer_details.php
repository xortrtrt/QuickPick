<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['adminID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include("../../includes/db_connect.php");

$customerID = $_GET['id'] ?? 0;

try {
    // Get customer info
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE customerID = ?");
    $stmt->execute([$customerID]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$customer) {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
        exit;
    }

    // Get customer statistics
    $statsStmt = $pdo->prepare("
        SELECT COUNT(*) as totalOrders, COALESCE(SUM(totalAmount), 0) as totalSpent
        FROM orders
        WHERE customerID = ?
    ");
    $statsStmt->execute([$customerID]);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Get recent orders
    $ordersStmt = $pdo->prepare("
        SELECT o.orderID, o.totalAmount, o.status, o.orderDate
        FROM orders o
        WHERE o.customerID = ?
        ORDER BY o.orderDate DESC
        LIMIT 10
    ");
    $ordersStmt->execute([$customerID]);
    $orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'customer' => $customer,
        'stats' => $stats,
        'orders' => $orders
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
