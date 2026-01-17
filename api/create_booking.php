<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['movie_id']) || !isset($data['showtime_id']) || 
    !isset($data['tickets']) || !isset($data['name']) || !isset($data['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$movie_id = intval($data['movie_id']);
$showtime_id = intval($data['showtime_id']);
$tickets = intval($data['tickets']);
$name = trim($data['name']);
$email = trim($data['email']);

if ($tickets < 1 || $tickets > 10) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid number of tickets']);
    exit;
}

if (empty($name) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name and email are required']);
    exit;
}

$conn = getDBConnection();

// Start transaction
$conn->begin_transaction();

try {
    // Check if customer exists, if not create one
    $stmt = $conn->prepare("SELECT customer_id FROM Customer WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        $customer_id = $customer['customer_id'];
    } else {
        // Create new customer
        $stmt = $conn->prepare("INSERT INTO Customer (name, email) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        $customer_id = $conn->insert_id;
    }
    $stmt->close();
    
    // Get showtime details
    $stmt = $conn->prepare("SELECT show_date, show_time FROM Showtime WHERE showtime_id = ?");
    $stmt->bind_param("i", $showtime_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Invalid showtime');
    }
    
    $showtime = $result->fetch_assoc();
    $stmt->close();
    
    // Create booking
    $booking_date = date('Y-m-d H:i:s');
    $status = 'confirmed';
    
    $stmt = $conn->prepare("INSERT INTO Booking (customer_id, showtime_id, booking_date, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $customer_id, $showtime_id, $booking_date, $status);
    $stmt->execute();
    $booking_id = $conn->insert_id;
    $stmt->close();
    
    // For simplicity, we'll create ticket entries without specific seat assignments
    // In a real system, you'd handle seat selection here
    for ($i = 0; $i < $tickets; $i++) {
        $ticket_number = "TKT" . str_pad($booking_id, 6, '0', STR_PAD_LEFT) . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
        $price = 10.00; // Default price
        
        $stmt = $conn->prepare("INSERT INTO Ticket (booking_id, ticket_number, price) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $booking_id, $ticket_number, $price);
        $stmt->execute();
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking_id' => $booking_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()]);
}

$conn->close();
?>
