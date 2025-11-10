<?php
// File: controllers/admin/delete_category_simple.php
// Simple form-based delete (No AJAX needed)

session_start();

if (!isset($_SESSION['adminID'])) {
    $_SESSION['error'] = 'Unauthorized';
    header("Location: /views/admin/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method';
    header("Location: /views/admin/categories.php");
    exit;
}

include("../../includes/db_connect.php");

$categoryID = intval($_POST['categoryID'] ?? 0);

if ($categoryID <= 0) {
    $_SESSION['error'] = 'Invalid category ID';
    header("Location: /views/admin/categories.php");
    exit;
}

try {
    // Check if category exists
    $checkStmt = $pdo->prepare("SELECT categoryName FROM categories WHERE categoryID = ?");
    $checkStmt->execute([$categoryID]);
    $category = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        $_SESSION['error'] = 'Category not found';
        header("Location: /views/admin/categories.php");
        exit;
    }

    // Update products to remove category reference
    $updateStmt = $pdo->prepare("UPDATE products SET categoryID = NULL WHERE categoryID = ?");
    $updateStmt->execute([$categoryID]);

    // Delete the category
    $deleteStmt = $pdo->prepare("DELETE FROM categories WHERE categoryID = ?");
    $deleteStmt->execute([$categoryID]);

    $_SESSION['success'] = 'Category "' . $category['categoryName'] . '" deleted successfully!';
    header("Location: /views/admin/categories.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error: ' . $e->getMessage();
    header("Location: /views/admin/categories.php");
    exit;
}
