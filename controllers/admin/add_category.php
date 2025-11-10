<?php
session_start();
if (!isset($_SESSION['adminID'])) {
    header("Location: /views/admin/login.php");
    exit;
}

include("../../includes/db_connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryName = trim($_POST['categoryName'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $isActive = isset($_POST['isActive']) ? 1 : 0;

    if (empty($categoryName)) {
        $_SESSION['error'] = 'Category name is required';
        header("Location: /views/admin/categories.php");
        exit;
    }

    try {
        // Check if category already exists
        $checkStmt = $pdo->prepare("SELECT categoryID FROM categories WHERE categoryName = ?");
        $checkStmt->execute([$categoryName]);

        if ($checkStmt->rowCount() > 0) {
            $_SESSION['error'] = 'Category already exists';
            header("Location: /views/admin/categories.php");
            exit;
        }

        // Insert new category
        $stmt = $pdo->prepare("
            INSERT INTO categories (categoryName, description, isActive, dateAdded)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$categoryName, $description, $isActive]);

        $_SESSION['success'] = 'Category created successfully!';
        header("Location: /views/admin/categories.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header("Location: /views/admin/categories.php");
        exit;
    }
}
