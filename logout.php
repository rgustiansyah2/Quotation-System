<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/database.php';

$userId = $_SESSION['user_id'] ?? null;
if ($userId) {
    write_audit_log('tbl_users', $userId, 'update', 'session', null, 'Logout user ID: ' . $userId);

    $stmt = $conn->prepare("UPDATE tbl_users SET session_token = NULL WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->close();
    }
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

http_response_code(200);
exit;