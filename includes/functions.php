<?php
function getAvailableProducts($pdo, $limit = 8)
{
    try {
        $stmt = $pdo->prepare("
            SELECT 
                p.productID, 
                p.productName, 
                p.description, 
                p.price, 
                p.stockQuantity, 
                p.unit, 
                p.imageURL, 
                c.categoryName
            FROM products p
            LEFT JOIN categories c ON p.categoryID = c.categoryID
            WHERE p.stockQuantity > 0 AND p.isActive = 1
            ORDER BY p.productID DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getAvailableProducts(): " . $e->getMessage());
        return [];
    }
}

function getAvailableCategories($pdo)
{
    try {
        $stmt = $pdo->prepare("
            SELECT 
                categoryName,
                description
            FROM categories
            WHERE isActive = 1
            ORDER BY categoryID DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getAvailableCategories(): " . $e->getMessage());
        return [];
    }
}

// Fetch all cart items for a specific customer
function getCartItems(PDO $pdo, $customerID)
{
    $stmt = $pdo->prepare("
        SELECT 
            ci.cartItemID,
            ci.cartID,
            ci.productID,
            ci.quantity,
            ci.price,
            ci.subtotal,
            p.productName,
            p.unit,
            p.imageURL,
            cat.categoryName
        FROM cartitems ci
        JOIN carts c ON ci.cartID = c.cartID
        JOIN products p ON ci.productID = p.productID
        LEFT JOIN categories cat ON p.categoryID = cat.categoryID
        WHERE c.customerID = :customerID
        AND c.status = 'active'
        ORDER BY ci.dateAdded DESC
    ");
    $stmt->bindValue(':customerID', $customerID, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Get the active cart ID for a customer, or create one if it doesn't exist
function getActiveCart(PDO $pdo, $customerID)
{
    $stmt = $pdo->prepare("SELECT cartID FROM carts WHERE customerID = :customerID AND status = 'active'");
    $stmt->bindValue(':customerID', $customerID, PDO::PARAM_INT);
    $stmt->execute();
    $cart = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cart) {
        return $cart['cartID'];
    } else {
        // Create new cart
        $stmt = $pdo->prepare("INSERT INTO carts (customerID, status, totalItems, totalAmount, dateCreated) VALUES (:customerID, 'active', 0, 0, NOW())");
        $stmt->bindValue(':customerID', $customerID, PDO::PARAM_INT);
        $stmt->execute();
        return $pdo->lastInsertId();
    }
}

// Add a product to cart
function addToCart(PDO $pdo, $customerID, $productID, $quantity = 1)
{
    $cartID = getActiveCart($pdo, $customerID);

    // Check if item already exists in cartitems
    $stmt = $pdo->prepare("SELECT cartItemID, quantity FROM cartitems WHERE cartID = :cartID AND productID = :productID");
    $stmt->execute([':cartID' => $cartID, ':productID' => $productID]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get product price
    $stmtPrice = $pdo->prepare("SELECT price FROM products WHERE productID = :productID");
    $stmtPrice->execute([':productID' => $productID]);
    $product = $stmtPrice->fetch(PDO::FETCH_ASSOC);
    $price = $product['price'] ?? 0;

    if ($existing) {
        // Update quantity & subtotal
        $newQty = $existing['quantity'] + $quantity;
        $stmt = $pdo->prepare("UPDATE cartitems SET quantity = :quantity, subtotal = :subtotal WHERE cartItemID = :cartItemID");
        $stmt->execute([
            ':quantity' => $newQty,
            ':subtotal' => $newQty * $price,
            ':cartItemID' => $existing['cartItemID']
        ]);
    } else {
        // Insert new item
        $stmt = $pdo->prepare("INSERT INTO cartitems (cartID, productID, quantity, price, subtotal, dateAdded) VALUES (:cartID, :productID, :quantity, :price, :subtotal, NOW())");
        $stmt->execute([
            ':cartID' => $cartID,
            ':productID' => $productID,
            ':quantity' => $quantity,
            ':price' => $price,
            ':subtotal' => $quantity * $price
        ]);
    }

    // Update cart totals
    updateCartTotals($pdo, $cartID);
}

// Update cart item quantity
function updateCartQuantity(PDO $pdo, $cartItemID, $action)
{
    $stmt = $pdo->prepare("SELECT cartID, quantity, price FROM cartitems WHERE cartItemID = :cartItemID");
    $stmt->bindValue(':cartItemID', $cartItemID, PDO::PARAM_INT);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) return false;

    $quantity = $item['quantity'];
    if ($action === 'increase') $quantity++;
    if ($action === 'decrease' && $quantity > 1) $quantity--;

    $subtotal = $quantity * $item['price'];

    $stmt = $pdo->prepare("UPDATE cartitems SET quantity = :quantity, subtotal = :subtotal WHERE cartItemID = :cartItemID");
    $stmt->execute([
        ':quantity' => $quantity,
        ':subtotal' => $subtotal,
        ':cartItemID' => $cartItemID
    ]);

    // Update cart totals
    updateCartTotals($pdo, $item['cartID']);

    return true;
}

// Remove a cart item
function removeCartItem(PDO $pdo, $cartItemID)
{
    // Get cartID first
    $stmt = $pdo->prepare("SELECT cartID FROM cartitems WHERE cartItemID = :cartItemID");
    $stmt->bindValue(':cartItemID', $cartItemID, PDO::PARAM_INT);
    $stmt->execute();
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$item) return false;

    $cartID = $item['cartID'];

    // Delete item
    $stmt = $pdo->prepare("DELETE FROM cartitems WHERE cartItemID = :cartItemID");
    $stmt->bindValue(':cartItemID', $cartItemID, PDO::PARAM_INT);
    $stmt->execute();

    // Update cart totals
    updateCartTotals($pdo, $cartID);
    return true;
}

// Update cart total items & total amount
function updateCartTotals(PDO $pdo, $cartID)
{
    $stmt = $pdo->prepare("SELECT SUM(quantity) AS totalItems, SUM(subtotal) AS totalAmount FROM cartitems WHERE cartID = :cartID");
    $stmt->bindValue(':cartID', $cartID, PDO::PARAM_INT);
    $stmt->execute();
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmtUpdate = $pdo->prepare("UPDATE carts SET totalItems = :totalItems, totalAmount = :totalAmount WHERE cartID = :cartID");
    $stmtUpdate->execute([
        ':totalItems' => $totals['totalItems'] ?? 0,
        ':totalAmount' => $totals['totalAmount'] ?? 0,
        ':cartID' => $cartID
    ]);
}

// Get cart total price
function getCartTotal(PDO $pdo, $customerID)
{
    $stmt = $pdo->prepare("
        SELECT totalAmount 
        FROM carts 
        WHERE customerID = :customerID AND status = 'active'
        LIMIT 1
    ");
    $stmt->bindValue(':customerID', $customerID, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['totalAmount'] ?? 0;
}
