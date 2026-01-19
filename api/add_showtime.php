<?php
header('Content-Type: application/json');

require_once '../config.php';

// Check that user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Administrator rights required.']);
    exit;
}

$conn = getDBConnection();

// Basic validation
if (
    empty($_POST['movie_id']) ||
    empty($_POST['room_id']) ||
    empty($_POST['show_date']) ||
    empty($_POST['show_time'])
) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing required fields'
    ]);
    exit;
}

$movie_id  = (int) $_POST['movie_id'];
$room_id   = (int) $_POST['room_id'];
$show_date = $_POST['show_date'];
$show_time = $_POST['show_time'];


// --------------------------------------------------
// 1. Get movie duration
// --------------------------------------------------
$movieResult = $conn->query("
    SELECT duration 
    FROM movie 
    WHERE movie_id = $movie_id
");

if (!$movieResult || $movieResult->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Movie not found'
    ]);
    exit;
}

$movie = $movieResult->fetch_assoc();
$duration = (int) $movie['duration']; // in minutes


// --------------------------------------------------
// 2. Check for conflicting showtimes in same room
// --------------------------------------------------
$conflictQuery = "
    SELECT s.showtime_id
    FROM showtime s
    JOIN movie m ON s.movie_id = m.movie_id
    WHERE s.room_id = $room_id
      AND s.show_date = '$show_date'
      AND (
            TIME('$show_time') < ADDTIME(s.show_time, SEC_TO_TIME(m.duration * 60))
        AND ADDTIME(TIME('$show_time'), SEC_TO_TIME($duration * 60)) > s.show_time
      )
";

$conflictResult = $conn->query($conflictQuery);

if ($conflictResult && $conflictResult->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Conflicting showtime in this room'
    ]);
    exit;
}


// --------------------------------------------------
// 3. Insert showtime
// --------------------------------------------------
$insertQuery = "
    INSERT INTO showtime (show_date, show_time, room_id, movie_id)
    VALUES ('$show_date', '$show_time', $room_id, $movie_id)
";

if ($conn->query($insertQuery)) {
    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to insert showtime'
    ]);
}
