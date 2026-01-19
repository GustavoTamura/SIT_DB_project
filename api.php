<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'db_connect.php';

// Set JSON header
header('Content-Type: application/json');

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        
        // Get all movies
        case 'get_movies':
            $sql = "SELECT movie_id, title, duration, description FROM Movie ORDER BY title";
            $result = $conn->query($sql);
            
            $movies = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $movies[] = $row;
                }
            }
            echo json_encode(['success' => true, 'data' => $movies]);
            break;
            
        // Get showtimes for a movie
        case 'get_showtimes':
            $movie_id = intval($_GET['movie_id'] ?? 0);
            
            if ($movie_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid movie ID']);
                break;
            }
            
            $sql = "SELECT s.showtime_id, s.show_date, s.show_time, r.room_name, t.theater_name 
                    FROM Showtime s
                    JOIN Room r ON s.room_id = r.room_id
                    JOIN Theater t ON r.theater_id = t.theater_id
                    WHERE s.movie_id = ? AND s.show_date >= CURDATE()
                    ORDER BY s.show_date, s.show_time";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $movie_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $showtimes = [];
            while($row = $result->fetch_assoc()) {
                $showtimes[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $showtimes]);
            break;
            
        // Get seats for a showtime
        case 'get_seats':
            $showtime_id = intval($_GET['showtime_id'] ?? 0);
            
            if ($showtime_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid showtime ID']);
                break;
            }
            
            // Get room_id for this showtime
            $sql = "SELECT room_id FROM Showtime WHERE showtime_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $showtime_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $showtime = $result->fetch_assoc();
            
            if (!$showtime) {
                echo json_encode(['success' => false, 'message' => 'Showtime not found']);
                break;
            }
            
            $room_id = $showtime['room_id'];
            
            // Get all seats for this room with booking status
            $sql = "SELECT s.seat_id, s.seat_number,
                    CASE WHEN b.booking_id IS NOT NULL THEN 1 ELSE 0 END as is_booked
                    FROM Seats s
                    LEFT JOIN Booking b ON s.seat_id = b.seat_id AND b.showtime_id = ? AND b.status = 'confirmed'
                    WHERE s.room_id = ?
                    ORDER BY s.seat_number";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $showtime_id, $room_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $seats = [];
            while($row = $result->fetch_assoc()) {
                $seats[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $seats]);
            break;
            
        // Process booking
        case 'book':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Invalid request method']);
                break;
            }
            
            // Get POST data
            $data = json_decode(file_get_contents('php://input'), true);
            
            $customer_name = $conn->real_escape_string($data['name'] ?? '');
            $customer_email = $conn->real_escape_string($data['email'] ?? '');
            $customer_phone = $conn->real_escape_string($data['phone'] ?? '');
            $showtime_id = intval($data['showtime_id'] ?? 0);
            $seat_id = intval($data['seat_id'] ?? 0);
            $price = floatval($data['price'] ?? 12.50);
            
            // Validate input
            if (empty($customer_name) || empty($customer_email) || empty($customer_phone)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                break;
            }
            
            if ($showtime_id <= 0 || $seat_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid showtime or seat']);
                break;
            }
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Check if customer exists
                $sql = "SELECT customer_id FROM Customer WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $customer_email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $customer = $result->fetch_assoc();
                    $customer_id = $customer['customer_id'];
                } else {
                    // Create new customer
                    $sql = "INSERT INTO Customer (name, email, phone) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $customer_name, $customer_email, $customer_phone);
                    $stmt->execute();
                    $customer_id = $conn->insert_id;
                }
                
                // Check if seat is still available
                $sql = "SELECT booking_id FROM Booking WHERE showtime_id = ? AND seat_id = ? AND status = 'confirmed'";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $showtime_id, $seat_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    throw new Exception("Seat is already booked");
                }
                
                // Create booking
                $sql = "INSERT INTO Booking (booking_time, status, price, payment_time, customer_id, showtime_id, seat_id) 
                        VALUES (NOW(), 'confirmed', ?, NOW(), ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("diii", $price, $customer_id, $showtime_id, $seat_id);
                $stmt->execute();
                $booking_id = $conn->insert_id;
                
                // Commit transaction
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'booking_id' => $booking_id,
                    'message' => 'Booking confirmed successfully!'
                ]);
                
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                error_log("Booking error: " . $e->getMessage());
                
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>
