<?php
session_start();
include("../../includes/db_connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ? AND isActive = 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['adminID'] = $admin['adminID'];
            $_SESSION['adminName'] = $admin['name'];
            $_SESSION['adminEmail'] = $admin['email'];

            // Update last login
            $update = $pdo->prepare("UPDATE admins SET lastLogin = NOW() WHERE adminID = ?");
            $update->execute([$admin['adminID']]);

            header("Location: /views/admin/dashboard.php");
            exit;
        } else {
            $_SESSION['error'] = 'Invalid email or password';
            header("Location: /views/admin/login.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error: ' . $e->getMessage();
        header("Location: /views/admin/login.php");
        exit;
    }
}
