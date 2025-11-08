<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['customerID'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in.']);
    exit;
}

if (!isset($_POST['cartItemID']) || empty($_POST['cartItemID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Cart item ID is required.']);
    exit;
}

$cartItemID = intval($_POST['cartItemID']);
$customerID = $_SESSION['customerID'];

include("../../includes/db_connect.php");

try {
    // Verify the cart item belongs to this customer
    $stmt = $pdo->prepare("SELECT * FROM cartitems ci 
                           JOIN carts c ON ci.cartID = c.cartID 
                           WHERE ci.cartItemID = :cartItemID AND c.customerID = :customerID");
    $stmt->execute(['cartItemID' => $cartItemID, 'customerID' => $customerID]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(['status' => 'error', 'message' => 'Cart item not found.']);
        exit;
    }

    // Delete the cart item
    $delStmt = $pdo->prepare("DELETE FROM cartitems WHERE cartItemID = :cartItemID");
    $delStmt->execute(['cartItemID' => $cartItemID]);

    // Calculate new cart total
    $totalStmt = $pdo->prepare("SELECT SUM(subtotal) AS total FROM cartitems ci 
                                JOIN carts c ON ci.cartID = c.cartID
                                WHERE c.customerID = :customerID");
    $totalStmt->execute(['customerID' => $customerID]);
    $totalResult = $totalStmt->fetch(PDO::FETCH_ASSOC);
    $total = $totalResult['total'] ?? 0;

    echo json_encode([
        'status' => 'success',
        'message' => 'Item removed from cart',
        'total' => number_format($total, 2)
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
