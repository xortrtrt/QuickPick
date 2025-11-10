<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['adminID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include("../../includes/db_connect.php");

$orderID = $_POST['orderID'] ?? 0;
$status = $_POST['status'] ?? '';

// Validate status
$validStatuses = ['pending', 'completed', 'cancelled'];
if (!in_array(strtolower($status), $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE orderID = ?");
    $stmt->execute([$status, $orderID]);
    echo json_encode(['success' => true, 'message' => 'Order updated']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
