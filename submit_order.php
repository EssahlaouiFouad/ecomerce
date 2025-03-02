<?php
session_start();
require_once 'config/db.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = sanitize_input($_POST['name']);
    $phone = sanitize_input($_POST['phone']);
    $email = sanitize_input($_POST['email']);
    $address = sanitize_input($_POST['address']);
    $color = sanitize_input($_POST['color']);
    $quantity = filter_var($_POST['quantity'], FILTER_VALIDATE_INT);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Invalid email format');
    }

    // Validate quantity
    if ($quantity === false || $quantity < 1) {
        die('Invalid quantity');
    }

    // Validate color
    $allowed_colors = ['gold', 'silver', 'blue', 'red'];
    if (!in_array($color, $allowed_colors)) {
        die('Invalid color selection');
    }

    try {
        // Prepare SQL statement
        $stmt = $pdo->prepare("INSERT INTO orders (name, phone, email, address, color, quantity, order_date) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        
        // Execute statement with sanitized values
        $stmt->execute([
            $name,
            $phone,
            $email,
            $address,
            $color,
            $quantity
        ]);

        // Redirect to success page or show success message
        header('Location: order_success.php');
        exit();

    } catch(PDOException $e) {
        // Log error (in production, don't show detailed error messages to users)
        error_log('Order submission error: ' . $e->getMessage());
        die('An error occurred while processing your order. Please try again later.');
    }
} else {
    // If someone tries to access this file directly without POST
    header('Location: order.html');
    exit();
}