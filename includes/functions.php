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
