<?php
require_once 'config.php';

// Session already started in config.php, no need to call session_start() again

// Destroy all session variables
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

session_destroy();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Logout successful']);
exit;
?>

