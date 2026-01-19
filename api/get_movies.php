<?php
require_once '../config.php';

header('Content-Type: application/json');

$conn = getDBConnection();

// Create movie_ratings table if it doesn't exist
$createTableSQL = "CREATE TABLE IF NOT EXISTS movie_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    movie_id INT NOT NULL,
    customer_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating IN (2, 4, 6, 8, 10)),
    rated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_movie (movie_id, customer_id),
    FOREIGN KEY (movie_id) REFERENCES Movie(movie_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id) ON DELETE CASCADE
)";
$conn->query($createTableSQL);

// Get all movies with their average ratings
$sql = "SELECT 
    m.movie_id, 
    m.title, 
    m.duration, 
    m.description, 
    m.poster_image,
    COALESCE(AVG(r.rating), 0) as average_rating,
    COUNT(r.rating_id) as total_ratings
FROM movie m
LEFT JOIN movie_ratings r ON m.movie_id = r.movie_id
GROUP BY m.movie_id, m.title, m.duration, m.description, m.poster_image
ORDER BY m.title";

$result = $conn->query($sql);

$movies = [];
while ($row = $result->fetch_assoc()) {
    // Round average rating to 1 decimal place
    $row['average_rating'] = $row['average_rating'] > 0 ? round($row['average_rating'], 1) : null;
    $row['total_ratings'] = (int)$row['total_ratings'];
    $movies[] = $row;
}

$conn->close();

echo json_encode(['success' => true, 'movies' => $movies]);
?>

