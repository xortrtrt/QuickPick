<?php
include("../includes/db_connect.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $password = $_POST["password"];
    $confirm = $_POST["confirm_password"];

    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    $check = $conn->prepare("SELECT * FROM customers WHERE phoneNumber = ?");
    $check->bind_param("s", $phone);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Phone number already registered!'); window.history.back();</script>";
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO customers (name, phoneNumber, password, email, address) VALUES (?, ?, ?, '', '')");
    $stmt->bind_param("sss", $name, $phone, $hashed);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful! You can now log in.'); window.location.href='../views/login.php';</script>";
    } else {
        echo "<script>alert('Error during registration!'); window.history.back();</script>";
    }

    $stmt->close();
}
$conn->close();
?>

<form action="../backend/register_backend.php" method="POST">
