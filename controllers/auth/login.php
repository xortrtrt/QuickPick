<?php
session_start();
include("../../includes/db_connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'signin') {
    $prefix = trim($_POST["countryCode"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $fullPhone = $prefix . $phone;

    $stmt = $pdo->prepare("SELECT * FROM customers WHERE phoneNumber = ?");
    $stmt->execute([$fullPhone]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: /views/customer/dashboard.php");
        exit;
    } else {
        echo "<script>alert('Invalid credentials'); window.history.back();</script>";
        exit;
    }
}
