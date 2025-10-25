<<<<<<< Updated upstream
=======
<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "quickpick";

try {
    // Data Source Name (DSN)
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

    // Create PDO instance
    $pdo = new PDO($dsn, $user, $pass);

    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional: confirm successful connection (remove in production)
    // echo "Connected successfully!";

} catch (PDOException $e) {
    // Handle connection error
    die("Database connection failed: " . $e->getMessage());
};
>>>>>>> Stashed changes
