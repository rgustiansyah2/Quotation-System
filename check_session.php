<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/database.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    echo json_encode(['valid' => false, 'message' => 'No active session']);
    exit;
}

$stmt = $conn->prepare("SELECT session_token FROM tbl_users WHERE id = ? LIMIT 1");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['valid' => false, 'message' => 'Database error']);
    exit;
}

$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$currentToken = $_SESSION['session_token'] ?? null;
$storedToken = $user['session_token'] ?? null;

if (empty($storedToken) || $storedToken !== $currentToken) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();

    echo json_encode(['valid' => false, 'message' => 'Session conflict detected']);
    exit;
}

echo json_encode(['valid' => true, 'message' => 'Session active']);
