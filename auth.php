<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$current_user_id   = $_SESSION['user_id'] ?? null;
$current_fullname  = $_SESSION['fullname'] ?? '';
$current_username  = $_SESSION['username'] ?? '';
$current_role      = $_SESSION['role'] ?? '';

if ($current_user_id) {
    $stmt = $conn->prepare("SELECT session_token FROM tbl_users WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $current_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $userToken = $result->fetch_assoc()['session_token'] ?? null;
        $stmt->close();

        if (empty($userToken) || ($userToken !== ($_SESSION['session_token'] ?? null))) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();

            if (!headers_sent()) {
                setcookie('session_conflict', 'Akun Anda sedang dipakai di perangkat lain.', time() + 30, '/');
                header('Location: dashboard.php?error=session_conflict');
                exit;
            }
        }
    }
}

function checkRole($roles = [])
{
    global $current_role;

    if (!in_array($current_role, $roles)) {

        http_response_code(403);

        die("
            <div style='
                font-family:Arial;
                padding:40px;
                text-align:center;
            '>
                <h2>403 - Access Denied</h2>
                <p>Anda tidak memiliki akses ke halaman ini.</p>
            </div>
        ");

    }
}
function currentUser()
{
    return [
        'id'       => $_SESSION['user_id'] ?? null,
        'fullname' => $_SESSION['fullname'] ?? '',
        'username' => $_SESSION['username'] ?? '',
        'role'     => $_SESSION['role'] ?? ''
    ];
}
?>