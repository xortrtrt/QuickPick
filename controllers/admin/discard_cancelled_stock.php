<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['adminID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include("../../includes/db_connect.php");

$cancelledStockID = intval($_POST['cancelledStockID'] ?? 0);

if ($cancelledStockID <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cancelled stock ID']);
    exit;
}

try {
    // Check if the cancelled stock exists
    $checkStmt = $pdo->prepare("SELECT cancelledStockID FROM cancelled_inventory WHERE cancelledStockID = ?");
    $checkStmt->execute([$cancelledStockID]);

    if ($checkStmt->rowCount() === 0) {
        throw new Exception('Cancelled stock record not found');
    }

    // Delete the cancelled stock record (permanently discard)
    $deleteStmt = $pdo->prepare("DELETE FROM cancelled_inventory WHERE cancelledStockID = ?");
    $deleteStmt->execute([$cancelledStockID]);

    echo json_encode(['success' => true, 'message' => 'Cancelled item discarded successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
