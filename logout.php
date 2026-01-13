<?php
require_once 'config.php';

session_start();

// Destroy all session variables
$_SESSION = array();

// Destroy session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

session_destroy();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Logout successful']);
exit;
?>

