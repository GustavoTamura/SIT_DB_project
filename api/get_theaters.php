<?php
require_once '../config.php';

header('Content-Type: application/json');

$conn = getDBConnection();

try {
    $sql = "SELECT theater_id, theater_name FROM theater ORDER BY theater_name";
    $result = $conn->query($sql);
    
    $theaters = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $theaters[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'theaters' => $theaters]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error loading theaters: ' . $e->getMessage()]);
}

$conn->close();
?>
