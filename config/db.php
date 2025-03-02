<?php
try {
    $host = 'fdb29.awardspace.net';
    $dbname = '4269260_tube';
    $username = '4269260_tube';
    $password = 'tube1989';
    

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Create orders table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        email VARCHAR(255) NOT NULL,
        address TEXT NOT NULL,
        color VARCHAR(50) NOT NULL,
        quantity INT NOT NULL,
        order_date DATETIME NOT NULL,
        status VARCHAR(50) DEFAULT 'pending'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
} catch(PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}