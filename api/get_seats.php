<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['showtime_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Showtime ID is required']);
    exit;
}

$showtime_id = intval($_GET['showtime_id']);
$conn = getDBConnection();

try {
    // Get room_id for this showtime
    $stmt = $conn->prepare("SELECT room_id FROM showtime WHERE showtime_id = ?");
    $stmt->bind_param("i", $showtime_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Showtime not found']);
        exit;
    }
    
    $showtime = $result->fetch_assoc();
    $room_id = $showtime['room_id'];
    $stmt->close();
    
    // Get all seats for this room with booking status
    // Check if seat is booked by looking at ticket table
    $sql = "SELECT s.seat_id, s.seat_number,
            CASE WHEN t.ticket_id IS NOT NULL THEN 1 ELSE 0 END as is_booked
            FROM seats s
            LEFT JOIN ticket t ON s.seat_id = t.seat_id
            LEFT JOIN booking b ON t.booking_id = b.booking_id 
                AND b.showtime_id = ? 
                AND b.status = 'confirmed'
            WHERE s.room_id = ?
            ORDER BY s.seat_number";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $showtime_id, $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $seats = [];
    while ($row = $result->fetch_assoc()) {
        $seats[] = $row;
    }
    
    echo json_encode(['success' => true, 'seats' => $seats]);
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error loading seats: ' . $e->getMessage()]);
}

$conn->close();
?>
