<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check if movie_id is provided
if (!isset($_GET['movie_id']) || empty($_GET['movie_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Movie ID is required']);
    exit;
}

$movie_id = intval($_GET['movie_id']);

$conn = getDBConnection();

// Get showtimes for the movie
// Join with Room and Theater to get more information
$sql = "SELECT s.showtime_id, s.show_date, s.show_time, s.movie_id,
               r.room_name, r.room_id,
               t.theater_name
        FROM Showtime s
        JOIN Room r ON s.room_id = r.room_id
        JOIN Theater t ON r.theater_id = t.theater_id
        WHERE s.movie_id = ?
        ORDER BY s.show_date, s.show_time";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $movie_id);
$stmt->execute();
$result = $stmt->get_result();

$showtimes = [];
while ($row = $result->fetch_assoc()) {
    $showtimes[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'showtimes' => $showtimes]);
?>
