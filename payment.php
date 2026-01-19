<?php
require_once 'config.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// User must be logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to pay']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$bookingId  = $data['bookingId']  ?? null; // optional for now
$cardNumber = $data['cardNumber'] ?? null;
$expiryDate = $data['expiryDate'] ?? null; // expected YYYY-MM-DD
$cvv        = $data['cvv']        ?? null;
$userId     = $_SESSION['user_id'];

if (!$cardNumber || !$expiryDate || !$cvv) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Card number, expiry date and CVV are required']);
    exit;
}

// Basic validation (you can improve these rules)
if (strlen(preg_replace('/\D/', '', $cardNumber)) < 12) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid card number']);
    exit;
}

if (strlen($cvv) < 3 || strlen($cvv) > 4) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid CVV']);
    exit;
}

try {
    $conn = getDBConnection();

    // Update customer payment info
    $stmt = $conn->prepare("
        UPDATE customer
        SET card_number = ?, expiry_date = ?, cvv = ?
        WHERE customer_id = ?
    ");

    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("sssi", $cardNumber, $expiryDate, $cvv, $userId);

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $stmt->close();

    // Optional: if you have a Booking table, mark this booking as paid.
    // Example (adjust table/column names to your schema):
    /*
    if ($bookingId) {
        $stmt2 = $conn->prepare("
            UPDATE Booking
            SET paid = 1
            WHERE booking_id = ? AND customer_id = ?
        ");
        $stmt2->bind_param("ii", $bookingId, $userId);
        $stmt2->execute();
        $stmt2->close();
    }
    */

    $conn->close();

    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully'
    ]);
    exit;

} catch (Exception $e) {
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    if (isset($conn) && $conn) {
        $conn->close();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}
?>
