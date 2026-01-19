<?php
require_once 'config.php';

header('Content-Type: application/json');
session_start();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Read JSON data sent via fetch
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password required']);
    exit;
}

$email    = trim($data['email']);
$password = $data['password'];

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password required']);
    exit;
}

$conn = getDBConnection();

// Fetch user by email
$stmt = $conn->prepare("SELECT customer_id, name, email, password_hash, admin FROM Customer WHERE email = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

// If no user found
if ($stmt->num_rows === 0) {
    $stmt->close();
    $conn->close();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

// Bind database columns to PHP variables
$stmt->bind_result($customer_id, $name, $emailDb, $password_hash, $admin);
$stmt->fetch();

// Verify password hash
if (!password_verify($password, $password_hash)) {
    $stmt->close();
    $conn->close();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

// Create session for logged-in user
$_SESSION['user_id']    = $customer_id;
$_SESSION['user_name']  = $name;
$_SESSION['user_email'] = $emailDb;
$_SESSION['is_admin']   = (bool)$admin;

$stmt->close();
$conn->close();

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user' => [
        'id'       => $customer_id,
        'name'     => $name,
        'email'    => $emailDb,
        'is_admin' => (bool)$admin
    ]
]);
exit;
?>
