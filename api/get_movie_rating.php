<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();
require_once('../db_connect.php');

$movie_id = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : 0;

if ($movie_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid movie ID']);
    exit;
}

try {
    // Create table if it doesn't exist
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

    // Get average rating and total count
    $avgSQL = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM movie_ratings WHERE movie_id = ?";
    $avgStmt = $conn->prepare($avgSQL);
    $avgStmt->bind_param("i", $movie_id);
    $avgStmt->execute();
    $avgResult = $avgStmt->get_result();
    $avgData = $avgResult->fetch_assoc();
    
    $response = [
        'success' => true,
        'average_rating' => $avgData['avg_rating'] ? round($avgData['avg_rating'], 1) : 0,
        'total_ratings' => $avgData['total_ratings'],
        'user_rating' => null
    ];
    
    // Get user's rating if logged in
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $userSQL = "SELECT rating FROM movie_ratings WHERE movie_id = ? AND customer_id = ?";
        $userStmt = $conn->prepare($userSQL);
        $userStmt->bind_param("ii", $movie_id, $user_id);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        
        if ($userResult->num_rows > 0) {
            $userData = $userResult->fetch_assoc();
            $response['user_rating'] = $userData['rating'];
        }
        
        $userStmt->close();
    }
    
    echo json_encode($response);
    $avgStmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
