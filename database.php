<?php

require_once __DIR__ . '/config.php';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);


$database_name = $DB_NAME;
$users_table = "users";
$products_table = "products";
$ai_table = "ai_suggestions";


if ($conn->connect_error) {
    echo "<p>Connection failed</p>";
}


if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$database_name`")) {
    die("Database creation failed: " . $conn->error);
}

$conn->select_db($database_name);


$conn->query("CREATE TABLE IF NOT EXISTS $users_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    industry_name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    otp_code VARCHAR(6) DEFAULT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
// Ensure OTP columns exist on older installs
$cols_to_ensure = [
    'otp_code' => "VARCHAR(6) DEFAULT NULL",
    'is_verified' => "TINYINT(1) DEFAULT 0",
];
foreach ($cols_to_ensure as $col => $definition) {
    $check = $conn->query("SHOW COLUMNS FROM $users_table LIKE '$col'");
    if (!$check || $check->num_rows == 0) {
        $conn->query("ALTER TABLE $users_table ADD COLUMN $col $definition");
    }
}

// Remove legacy security question columns if present
$legacy = ['security_question', 'security_answer'];
foreach ($legacy as $col) {
    $check = $conn->query("SHOW COLUMNS FROM $users_table LIKE '$col'");
    if ($check && $check->num_rows > 0) {
        $conn->query("ALTER TABLE $users_table DROP COLUMN $col");
    }
}

$conn->query("CREATE TABLE IF NOT EXISTS $products_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_name VARCHAR(100),
    category VARCHAR(50),
    quantity INT,
    unit VARCHAR(20),
    condition_status VARCHAR(50),
    location VARCHAR(100),
    price DECIMAL(10,2),
    description TEXT,
    status VARCHAR(20) DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

// Simple messages table for product inquiries
$conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    sender_id INT,
    receiver_id INT,
    message_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
)");
?>