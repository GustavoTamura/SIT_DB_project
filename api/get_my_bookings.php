<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to view bookings']);
    exit;
}

$customer_id = $_SESSION['user_id'];

$conn = getDBConnection();

try {
    // Get all bookings for this customer with details
    $sql = "SELECT 
                b.booking_id,
                b.booking_time,
                b.status,
                b.price as total_price,
                m.title as movie_title,
                m.duration,
                t.theater_name,
                r.room_name,
                s.show_date,
                s.show_time,
                GROUP_CONCAT(DISTINCT se.seat_number ORDER BY se.seat_number SEPARATOR ', ') as seats,
                COUNT(DISTINCT tk.ticket_id) as ticket_count
            FROM booking b
            JOIN showtime s ON b.showtime_id = s.showtime_id
            JOIN movie m ON s.movie_id = m.movie_id
            JOIN room r ON s.room_id = r.room_id
            JOIN theater t ON r.theater_id = t.theater_id
            LEFT JOIN ticket tk ON b.booking_id = tk.booking_id
            LEFT JOIN seats se ON tk.seat_id = se.seat_id
            WHERE b.customer_id = ?
            GROUP BY b.booking_id
            ORDER BY b.booking_time DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    echo json_encode(['success' => true, 'bookings' => $bookings]);
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error loading bookings: ' . $e->getMessage()]);
}

$conn->close();
?>
