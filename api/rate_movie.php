<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

session_start();
require_once('../db_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to rate movies']);
    exit;
}

$user_id = $_SESSION['user_id'];
$movie_id = isset($_POST['movie_id']) ? intval($_POST['movie_id']) : 0;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

// Validate input
if ($movie_id <= 0 || $rating < 2 || $rating > 10 || $rating % 2 !== 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating value']);
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

    // Check if user has already rated this movie
    $checkSQL = "SELECT rating_id, rating FROM movie_ratings WHERE movie_id = ? AND customer_id = ?";
    $checkStmt = $conn->prepare($checkSQL);
    $checkStmt->bind_param("ii", $movie_id, $user_id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing rating
        $updateSQL = "UPDATE movie_ratings SET rating = ?, rated_at = NOW() WHERE movie_id = ? AND customer_id = ?";
        $updateStmt = $conn->prepare($updateSQL);
        $updateStmt->bind_param("iii", $rating, $movie_id, $user_id);
        
        if ($updateStmt->execute()) {
            // Get average rating
            $avgSQL = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM movie_ratings WHERE movie_id = ?";
            $avgStmt = $conn->prepare($avgSQL);
            $avgStmt->bind_param("i", $movie_id);
            $avgStmt->execute();
            $avgResult = $avgStmt->get_result();
            $avgData = $avgResult->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'message' => 'Rating updated successfully',
                'user_rating' => $rating,
                'average_rating' => round($avgData['avg_rating'], 1),
                'total_ratings' => $avgData['total_ratings']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update rating']);
        }
        
        $updateStmt->close();
    } else {
        // Insert new rating
        $insertSQL = "INSERT INTO movie_ratings (movie_id, customer_id, rating) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSQL);
        $insertStmt->bind_param("iii", $movie_id, $user_id, $rating);
        
        if ($insertStmt->execute()) {
            // Get average rating
            $avgSQL = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings FROM movie_ratings WHERE movie_id = ?";
            $avgStmt = $conn->prepare($avgSQL);
            $avgStmt->bind_param("i", $movie_id);
            $avgStmt->execute();
            $avgResult = $avgStmt->get_result();
            $avgData = $avgResult->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'message' => 'Rating submitted successfully',
                'user_rating' => $rating,
                'average_rating' => round($avgData['avg_rating'], 1),
                'total_ratings' => $avgData['total_ratings']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit rating']);
        }
        
        $insertStmt->close();
    }
    
    $checkStmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
