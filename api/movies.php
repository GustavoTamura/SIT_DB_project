<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check that user is admin
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Administrator rights required.']);
    exit;
}

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("SELECT movie_id, title, duration, description, poster_image FROM movie WHERE movie_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Movie not found']);
            } else {
                echo json_encode(['success' => true, 'movie' => $result->fetch_assoc()]);
            }
            $stmt->close();
            break;
        }

        if (isset($_GET['search'])) {
            $search = '%' . $conn->real_escape_string($_GET['search']) . '%';
            $stmt = $conn->prepare("SELECT movie_id, title, duration, description, poster_image FROM movie WHERE title LIKE ? OR description LIKE ? ORDER BY title");
            $stmt->bind_param("ss", $search, $search);
            $stmt->execute();
            $result = $stmt->get_result();

            $movies = [];
            while ($row = $result->fetch_assoc()) {
                $movies[] = $row;
            }
            $stmt->close();

            echo json_encode(['success' => true, 'movies' => $movies]);
            break;
        }

        $result = $conn->query("SELECT movie_id, title, duration, description, poster_image FROM movie ORDER BY title");
        $movies = [];
        while ($row = $result->fetch_assoc()) {
            $movies[] = $row;
        }
        echo json_encode(['success' => true, 'movies' => $movies]);
        break;

    case 'POST':
        // DELETE via POST?action=delete
        if (isset($_GET['action']) && $_GET['action'] === 'delete') {
            $data = json_decode(file_get_contents('php://input'), true);

            if (!isset($data['movie_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Movie ID required']);
                exit;
            }

            $movie_id = intval($data['movie_id']);

            // Check if movie exists
            $stmt = $conn->prepare("SELECT movie_id FROM movie WHERE movie_id = ?");
            $stmt->bind_param("i", $movie_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                $stmt->close();
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Movie not found']);
                exit;
            }
            $stmt->close();

            // Attempt delete
            $stmt = $conn->prepare("DELETE FROM movie WHERE movie_id = ?");
            $stmt->bind_param("i", $movie_id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Movie deleted successfully']);
            } else {
                // Elegant error message for FK constraints
                $errorMsg = $stmt->error ?: $conn->error;
                http_response_code(409); // conflict
                echo json_encode([
                    'success' => false,
                    'message' => 'Cannot delete this movie because it is linked to existing showtimes or bookings.'
                ]);
            }

            $stmt->close();
            break;
        }

        // CREATE movie
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['title']) || empty(trim($data['title']))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Title is required']);
            exit;
        }

        $title = trim($data['title']);
        $duration = isset($data['duration']) ? intval($data['duration']) : null;
        $description = isset($data['description']) ? trim($data['description']) : null;

        $stmt = $conn->prepare("INSERT INTO movie (title, duration, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $title, $duration, $description);

        if ($stmt->execute()) {
            $movie_id = $conn->insert_id;
            echo json_encode([
                'success' => true,
                'message' => 'Movie created successfully',
                'movie' => [
                    'movie_id' => $movie_id,
                    'title' => $title,
                    'duration' => $duration,
                    'description' => $description
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error creating movie']);
        }

        $stmt->close();
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['movie_id']) || !isset($data['title']) || empty(trim($data['title']))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID and title required']);
            exit;
        }

        $movie_id = intval($data['movie_id']);
        $title = trim($data['title']);
        $duration = isset($data['duration']) ? intval($data['duration']) : null;
        $description = isset($data['description']) ? trim($data['description']) : null;

        $stmt = $conn->prepare("UPDATE movie SET title = ?, duration = ?, description = ? WHERE movie_id = ?");
        $stmt->bind_param("sisi", $title, $duration, $description, $movie_id);

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Movie updated successfully',
                'movie' => [
                    'movie_id' => $movie_id,
                    'title' => $title,
                    'duration' => $duration,
                    'description' => $description
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error updating movie']);
        }
        $stmt->close();
        break;

    case 'DELETE':
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'DELETE method not supported. Use POST?action=delete instead.']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

$conn->close();
?>
