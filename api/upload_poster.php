<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check that user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Administrator rights required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['movie_id']) || !isset($_FILES['poster'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Movie ID and poster file are required']);
    exit;
}

$movie_id = intval($_POST['movie_id']);
$file = $_FILES['poster'];

// Validate file
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
    exit;
}

if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 5MB.']);
    exit;
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'File upload error: ' . $file['error']]);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Get movie title for filename
    $stmt = $conn->prepare("SELECT title FROM movie WHERE movie_id = ?");
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Movie not found']);
        exit;
    }
    
    $movie = $result->fetch_assoc();
    $stmt->close();
    
    // Create upload directory if it doesn't exist
    $upload_dir = '../uploads/movies/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $movie_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        exit;
    }
    
    // Update database with relative path
    $relative_path = 'uploads/movies/' . $filename;
    $stmt = $conn->prepare("UPDATE movie SET poster_image = ? WHERE movie_id = ?");
    $stmt->bind_param("si", $relative_path, $movie_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Poster uploaded successfully',
            'poster_url' => $relative_path
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
