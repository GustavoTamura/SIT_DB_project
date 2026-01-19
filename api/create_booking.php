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
if (!isset($data['showtime_id']) || !isset($data['seat_ids']) || 
    !is_array($data['seat_ids']) || empty($data['seat_ids'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields (showtime_id and seat_ids required)']);
    exit;
}

$showtime_id = intval($data['showtime_id']);
$seat_ids = array_map('intval', $data['seat_ids']);

if (count($seat_ids) < 1 || count($seat_ids) > 10) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid number of seats (1-10 allowed)']);
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to make a booking']);
    exit;
}

$customer_id = $_SESSION['user_id']; // Changed from customer_id to user_id

$conn = getDBConnection();

// Start transaction
$conn->begin_transaction();

try {
    // Verify all seats exist and are available for this showtime
    $placeholders = implode(',', array_fill(0, count($seat_ids), '?'));
    
    // Check if seats are already booked for this showtime
    $stmt = $conn->prepare("
        SELECT DISTINCT t.seat_id, s.seat_number
        FROM ticket t
        JOIN booking b ON t.booking_id = b.booking_id
        JOIN seats s ON t.seat_id = s.seat_id
        WHERE b.showtime_id = ? 
          AND b.status = 'confirmed'
          AND t.seat_id IN ($placeholders)
    ");
    
    $types = 'i' . str_repeat('i', count($seat_ids));
    $params = array_merge([$showtime_id], $seat_ids);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $booked_seats = [];
    while ($row = $result->fetch_assoc()) {
        $booked_seats[] = $row['seat_number'];
    }
    $stmt->close();
    
    if (!empty($booked_seats)) {
        throw new Exception('Some seats are already booked: ' . implode(', ', $booked_seats));
    }
    
    // Get showtime details and calculate total price
    $stmt = $conn->prepare("SELECT show_date, show_time, movie_id FROM showtime WHERE showtime_id = ?");
    $stmt->bind_param("i", $showtime_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Invalid showtime');
    }
    
    $showtime = $result->fetch_assoc();
    $stmt->close();
    
    // Calculate total price
    $price_per_ticket = 12.50;
    $total_price = $price_per_ticket * count($seat_ids);
    
    // Create booking
    $booking_time = date('Y-m-d H:i:s');
    $status = 'confirmed';
    
    $stmt = $conn->prepare("INSERT INTO booking (customer_id, showtime_id, booking_time, booking_date, status, price, payment_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssds", $customer_id, $showtime_id, $booking_time, $booking_time, $status, $total_price, $booking_time);
    $stmt->execute();
    $booking_id = $conn->insert_id;
    $stmt->close();
    
    // Create tickets for each selected seat
    $ticket_numbers = [];
    foreach ($seat_ids as $index => $seat_id) {
        $ticket_number = "TKT-" . date('Ymd') . "-" . str_pad($booking_id, 6, '0', STR_PAD_LEFT) . "-" . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
        
        $stmt = $conn->prepare("INSERT INTO ticket (booking_id, ticket_number, seat_id, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isid", $booking_id, $ticket_number, $seat_id, $price_per_ticket);
        $stmt->execute();
        $stmt->close();
        
        $ticket_numbers[] = $ticket_number;
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Booking created successfully',
        'booking_id' => $booking_id,
        'ticket_numbers' => $ticket_numbers,
        'total_price' => $total_price,
        'seats_count' => count($seat_ids)
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Booking failed: ' . $e->getMessage()]);
}

$conn->close();
?>
