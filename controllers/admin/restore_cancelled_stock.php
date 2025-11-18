<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['adminID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include("../../includes/db_connect.php");

$productID = intval($_POST['productID'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 0);

if ($productID <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID or quantity']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Get cancelled inventory info
    $checkStmt = $pdo->prepare("SELECT cancelledQuantity FROM cancelled_inventory WHERE productID = ?");
    $checkStmt->execute([$productID]);
    $cancelled = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$cancelled) {
        throw new Exception('Cancelled item not found');
    }

    if ($quantity > $cancelled['cancelledQuantity']) {
        throw new Exception('Cannot restore more than available cancelled quantity');
    }

    // Add quantity back to main inventory
    $restoreStmt = $pdo->prepare("
        UPDATE products 
        SET stockQuantity = stockQuantity + ? 
        WHERE productID = ?
    ");
    $restoreStmt->execute([$quantity, $productID]);

    // Reduce or remove from cancelled inventory
    if ($quantity >= $cancelled['cancelledQuantity']) {
        // Remove completely if restoring all
        $deleteStmt = $pdo->prepare("DELETE FROM cancelled_inventory WHERE productID = ?");
        $deleteStmt->execute([$productID]);
    } else {
        // Just reduce the quantity
        $updateStmt = $pdo->prepare("
            UPDATE cancelled_inventory 
            SET cancelledQuantity = cancelledQuantity - ?,
                lastUpdated = NOW()
            WHERE productID = ?
        ");
        $updateStmt->execute([$quantity, $productID]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Items restored to main inventory successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
