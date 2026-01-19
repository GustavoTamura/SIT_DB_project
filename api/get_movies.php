<?php
require_once '../config.php';

header('Content-Type: application/json');

$conn = getDBConnection();

// Récupérer tous les films (accessible à tous, pas besoin d'être admin)
$result = $conn->query("SELECT movie_id, title, duration, description FROM Movie ORDER BY title");

$movies = [];
while ($row = $result->fetch_assoc()) {
    $movies[] = $row;
}

$conn->close();

echo json_encode(['success' => true, 'movies' => $movies]);
?>

