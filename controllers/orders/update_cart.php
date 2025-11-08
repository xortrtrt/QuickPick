<?php
session_start();
include("../../includes/db_connect.php");
include("../../includes/functions.php");

header('Content-Type: application/json');

if (!isset($_SESSION['customerID'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartItemID = isset($_POST['cartID']) ? (int)$_POST['cartID'] : 0;
    $action = $_POST['action'] ?? null;

    if ($cartItemID && in_array($action, ['increase', 'decrease'])) {
        try {
            // Update quantity in backend
            $success = updateCartQuantity($pdo, $cartItemID, $action);

            if ($success) {
                // Fetch updated item info
                $stmt = $pdo->prepare("
                    SELECT ci.quantity, ci.price, ci.cartID
                    FROM cartitems ci
                    WHERE ci.cartItemID = :cartItemID
                ");
                $stmt->execute([':cartItemID' => $cartItemID]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                echo json_encode([
                    'success' => true,
                    'quantity' => $item['quantity'],
                    'subtotal' => number_format($item['quantity'] * $item['price'], 2),
                    'total' => number_format(getCartTotal($pdo, $_SESSION['customerID']), 2)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Item not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit;
