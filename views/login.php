<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("../includes/db_connect.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $phone = $_POST["phone"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM customers WHERE phoneNumber = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['customerID'] = $user['customerID'];
            $_SESSION['name'] = $user['name'];

            $conn->query("UPDATE customers SET status='Online' WHERE customerID=" . $user['customerID']);
            echo "<script>alert('Login successful!'); window.location.href='../views/dashboard.php';</script>";
        } else {
            echo "<script>alert('Invalid password!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Account not found!'); window.history.back();</script>";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"
        integrity="sha512-+4zCK9k+qNFUR5X+cKL9EIR+zOhtIl0N19GIK5Z7VMyNsYpYcUrUeQc9vNfzsWFV28IAII3i96P9sdNyeRssA=="
        crossorigin="anonymous" />
    <link rel="stylesheet" href="/assets/css/login.css" />
    <title>QuickPick</title>
</head>

<body>
    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form>
                <h1>Sign Up</h1>
                <input type="text" placeholder="Name" />
                <input type="text" placeholder="+63" />
                <input type="password" placeholder="Password" />
                <input type="password" placeholder="Confirm Password" />
                <button onclick="return false;">Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in-container">
            <form>
                <h1>Sign In</h1>
                <input type="text" placeholder="+63" />
                <input type="password" placeholder="Password" />
                <a href="#">Forgot your password?</a>
                <button onclick="return false;">Sign In</button>
            </form>
        </div>
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Welcome Back!</h1>
                    <p>Please login with your personal info</p>
                    <button class="ghost" id="signIn">Sign In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1>Hello, Friend!</h1>
                    <p>Enter your personal details and start journey with us</p>
                    <button class="ghost" id="signUp">Sign Up</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        const signUpButton = document.getElementById("signUp");
        const signInButton = document.getElementById("signIn");
        const container = document.getElementById("container");

        signUpButton.addEventListener("click", () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener("click", () => {
            container.classList.remove("right-panel-active");
        });
    </script>
</body>

</html>