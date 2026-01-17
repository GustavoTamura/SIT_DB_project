<?php
require_once 'config.php';

header('Content-Type: application/json');

if (isLoggedIn()) {
    $user = getCurrentUser();
    echo json_encode([
        'logged_in' => true,
        'user' => $user
    ]);
} else {
    echo json_encode([
        'logged_in' => false,
        'user' => null
    ]);
}
?>

