<?php
session_start();
include("../../includes/db_connect.php");
include("../auth/send_sms.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'verify_otp') {
    $enteredOtp = trim($_POST['otp']);

    if (!isset($_SESSION['pending_user'])) {
        echo "<script>alert('No pending verification found. Please register again.'); window.location.href='/views/login.php';</script>";
        exit;
    }

    $user = $_SESSION['pending_user'];
    $response = verifyOtp($user['phone'], $enteredOtp);

    if (!empty($response['success']) && $response['success'] === true && !empty($response['data']['valid'])) {
        $stmt = $pdo->prepare("INSERT INTO customers (name, phoneNumber, password, dateCreated, status)
                               VALUES (?, ?, ?, NOW(), 'Offline')");
        if ($stmt->execute([$user['name'], $user['phone'], $user['password']])) {
            unset($_SESSION['pending_user']);
            echo "<script>alert('Registration successful! Please sign in.'); window.location.href='/views/login.php';</script>";
            exit;
        }
    } else {
        $errorMsg = $response['message'] ?? ($response['error'] ?? 'OTP is invalid or expired');
        echo "<script>alert('Verification failed: {$errorMsg}'); window.history.back();</script>";
        exit;
    }
}
