<?php
header('Content-Type: application/json');

require_once '../config.php';

$conn = getDBConnection();

$sql = "
    SELECT 
        r.room_id,
        r.room_name,
        t.theater_name
    FROM room r
    LEFT JOIN theater t ON r.theater_id = t.theater_id
    ORDER BY t.theater_name, r.room_name
";

$result = $conn->query($sql);

$rooms = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rooms[] = [
            'room_id' => $row['room_id'],
            'label' => $row['theater_name'] . ' - ' . $row['room_name']
        ];
    }
}

echo json_encode($rooms);
