<?php
session_start();
include("../../includes/db_connect.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'signin') {
    $prefix = trim($_POST["countryCode"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $fullPhone = $prefix . $phone;

    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE phoneNumber = ?");
    $stmt->execute([$fullPhone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // ✅ Use correct column names
        $_SESSION['customerID'] = $user['customerID'];
        $_SESSION['customerName'] = $user['name'];


        // Optional: update last login and status
        $update = $pdo->prepare("UPDATE customers SET lastLogin = NOW(), status = 'Online' WHERE customerID = ?");
        $update->execute([$user['customerID']]);

        header("Location: /views/customer/dashboard.php");
        exit;
    } else {
        echo "<script>alert('Invalid credentials'); window.history.back();</script>";
        exit;
    }
}
