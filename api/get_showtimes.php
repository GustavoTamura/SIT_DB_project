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
$theater_id = isset($_GET['theater_id']) ? intval($_GET['theater_id']) : null;
$date = isset($_GET['date']) ? $_GET['date'] : null;

$conn = getDBConnection();

// Build query with optional filters
$sql = "SELECT s.showtime_id, s.show_date, s.show_time, s.movie_id,
               r.room_name, r.room_id, r.theater_id,
               t.theater_name
        FROM showtime s
        JOIN room r ON s.room_id = r.room_id
        JOIN theater t ON r.theater_id = t.theater_id
        WHERE s.movie_id = ?";

$params = [$movie_id];
$types = "i";

if ($theater_id) {
    $sql .= " AND t.theater_id = ?";
    $params[] = $theater_id;
    $types .= "i";
}

if ($date) {
    $sql .= " AND s.show_date = ?";
    $params[] = $date;
    $types .= "s";
}

$sql .= " ORDER BY s.show_date, s.show_time";

$stmt = $conn->prepare($sql);

// Bind parameters dynamically
if (count($params) > 1) {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param($types, $params[0]);
}

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
