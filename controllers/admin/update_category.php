<?php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryID = $_POST['categoryID'] ?? 0;
    $categoryName = trim($_POST['categoryName'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $isActive = isset($_POST['isActive']) ? 1 : 0;

    if (empty($categoryName)) {
        $_SESSION['error'] = 'Category name is required';
        header("Location: /views/admin/categories.php");
        exit;
    }

    try {
        // Check if another category with same name exists
        $checkStmt = $pdo->prepare("SELECT categoryID FROM categories WHERE categoryName = ? AND categoryID != ?");
        $checkStmt->execute([$categoryName, $categoryID]);

        if ($checkStmt->rowCount() > 0) {
            $_SESSION['error'] = 'Another category with this name already exists';
            header("Location: /views/admin/categories.php");
            exit;
        }

        // Update category
        $stmt = $pdo->prepare("
            UPDATE categories
            SET categoryName = ?, description = ?, isActive = ?
            WHERE categoryID = ?
        ");
        $stmt->execute([$categoryName, $description, $isActive, $categoryID]);

        $_SESSION['success'] = 'Category updated successfully!';
        header("Location: /views/admin/categories.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header("Location: /views/admin/categories.php");
        exit;
    }
}
