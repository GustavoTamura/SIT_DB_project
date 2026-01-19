<?php
require_once 'config.php';

/**
 * This script updates the customer passwords with proper hashed passwords
 * Run this script once to fix the password hashes in the database
 */

$conn = getDBConnection();

// Define default passwords for each customer
$customers = [
    ['email' => 'ali@mail.com', 'password' => 'ali123'],
    ['email' => 'sara@mail.com', 'password' => 'sara123'],
    ['email' => 'omar@mail.com', 'password' => 'omar123'],
    ['email' => 'lina@mail.com', 'password' => 'lina123'],
    ['email' => 'nora@mail.com', 'password' => 'nora123'],
    ['email' => 'adam@mail.com', 'password' => 'adam123'],
    ['email' => 'yas@mail.com', 'password' => 'yas123'],
    ['email' => 'maya@mail.com', 'password' => 'maya123'],
    ['email' => 'sam@mail.com', 'password' => 'sam123'],
    ['email' => 'emma@mail.com', 'password' => 'emma123']
];

$updated = 0;
$failed = 0;

foreach ($customers as $customer) {
    $email = $customer['email'];
    $password = $customer['password'];
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE Customer SET password_hash = ? WHERE email = ?");
    $stmt->bind_param("ss", $password_hash, $email);
    
    if ($stmt->execute()) {
        echo "✓ Updated password for: $email (password: $password)<br>";
        $updated++;
    } else {
        echo "✗ Failed to update: $email<br>";
        $failed++;
    }
    
    $stmt->close();
}

$conn->close();

echo "<br><strong>Summary:</strong><br>";
echo "Updated: $updated<br>";
echo "Failed: $failed<br>";
echo "<br><strong>Login credentials:</strong><br>";
echo "<ul>";
foreach ($customers as $customer) {
    echo "<li>Email: {$customer['email']}, Password: {$customer['password']}</li>";
}
echo "</ul>";
?>
