<?php
session_start();
include("../../includes/db_connect.php");
include("../auth/send_sms.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'signup') {
    $name = trim($_POST["name"]);
    $prefix = trim($_POST["countryCode"]);
    $phone = trim($_POST["phone"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];
    $fullPhone = $prefix . $phone;

    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    $check = $pdo->prepare("SELECT * FROM customers WHERE phoneNumber = ?");
    $check->execute([$fullPhone]);
    if ($check->rowCount() > 0) {
        echo "<script>alert('Phone number already registered!'); window.history.back();</script>";
        exit;
    }

    // Store pending user in session (without OTP)
    $_SESSION['pending_user'] = [
        'name' => $name,
        'phone' => $fullPhone,
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ];

    // Send OTP using SkyIO (automatic OTP generation)
    $message = "Your QuickPick OTP is {{otp}}. Valid for 5 minutes.";
    $response = sendOtp($fullPhone, $message, 300);

    if (!empty($response['success']) && $response['success'] === true) {
        header("Location: /views/login.php?verify=1");
        exit;
    } else {
        $errorMsg = $response['message'] ?? ($response['error'] ?? 'Unknown error');
        echo "<script>alert('Failed to send OTP: {$errorMsg}'); window.history.back();</script>";
        exit;
    }
}
