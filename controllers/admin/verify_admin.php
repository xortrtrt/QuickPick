<?php
session_start();
include("../../includes/db_connect.php");

try {
    $checkTable = $pdo->query("SHOW TABLES LIKE 'admins'");

    if ($checkTable->rowCount() === 0) {
        echo "Creating admins table...";
        $pdo->exec("
            CREATE TABLE `admins` (
              `adminID` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              `email` varchar(150) UNIQUE NOT NULL,
              `password` varchar(255) NOT NULL,
              `phoneNumber` varchar(15),
              `isActive` tinyint(1) DEFAULT 1,
              `lastLogin` datetime,
              `dateCreated` datetime DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (`adminID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        echo "✓ Admins table created\n";
    }

    // Check if demo admin exists
    $adminCount = $pdo->query("SELECT COUNT(*) FROM admins WHERE email = 'admin@quickpick.com'")->fetchColumn();

    if ($adminCount === 0) {
        echo "Creating demo admin user...";
        $hashedPassword = '$2y$10$vkNCjWnnW9y6x7UG.F5OxOPWu0RN.TlLPKqVlRpqZEYCpKvjBjlWK'; // password123

        $stmt = $pdo->prepare("
            INSERT INTO admins (name, email, password, phoneNumber, isActive, dateCreated)
            VALUES ('Admin User', 'admin@quickpick.com', ?, '+639000000000', 1, NOW())
        ");
        $stmt->execute([$hashedPassword]);
        echo "✓ Demo admin created\n";
    } else {
        echo "✓ Demo admin already exists\n";
    }

    // Check orderitems table
    $checkOrderItems = $pdo->query("SHOW TABLES LIKE 'orderitems'");

    if ($checkOrderItems->rowCount() === 0) {
        echo "Creating orderitems table...";
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `orderitems` (
              `orderItemID` int(11) NOT NULL AUTO_INCREMENT,
              `orderID` int(11) NOT NULL,
              `productID` int(11) NOT NULL,
              `quantity` int(11) NOT NULL DEFAULT 1,
              `price` decimal(10,2) NOT NULL,
              PRIMARY KEY (`orderItemID`),
              FOREIGN KEY (`orderID`) REFERENCES `orders` (`orderID`) ON DELETE CASCADE,
              FOREIGN KEY (`productID`) REFERENCES `products` (`productID`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ");
        echo "✓ Orderitems table created\n";
    }

    echo "\n✅ All checks passed! Admin system is ready.\n";
    echo "📧 Demo Email: admin@quickpick.com\n";
    echo "🔐 Demo Password: password123\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
