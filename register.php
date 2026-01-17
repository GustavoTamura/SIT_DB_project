<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$name = trim($data['name']);
$email = trim($data['email']);
$password = $data['password'];

// Validation
if (empty($name) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

$conn = getDBConnection();

// Check if email already exists
$stmt = $conn->prepare("SELECT customer_id FROM Customer WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    $conn->close();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'This email is already in use']);
    exit;
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert new user (default non-admin)
$stmt = $conn->prepare("INSERT INTO Customer (name, email, password_hash, admin) VALUES (?, ?, ?, 0)");
$stmt->bind_param("sss", $name, $email, $password_hash);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;
    
    // Create session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $email;
    $_SESSION['is_admin'] = false;
    
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Account created successfully',
        'user' => [
            'id' => $user_id,
            'name' => $name,
            'email' => $email,
            'is_admin' => false
        ]
    ]);
} else {
    $stmt->close();
    $conn->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error creating account']);
}
?>

