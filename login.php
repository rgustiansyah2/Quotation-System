<?php
session_start();
header('Content-Type: application/json');
include 'config/database.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Bad Request'
    ]);
    exit;
}
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
if (empty($username) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Username dan password wajib diisi'
    ]);
    exit;
}
$stmt = $conn->prepare("
    SELECT 
        id,
        fullname,
        username,
        password,
        role,
        status,
        session_token,
        session_device_id
    FROM tbl_users
    WHERE username = ?
    LIMIT 1
");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    if ($row['status'] !== 'active') {
        echo json_encode([
            'success' => false,
            'message' => 'User tidak aktif'
        ]);
        exit;
    }
    if (password_verify($password, $row['password'])) {
        $deviceId = $_COOKIE['app_device_id'] ?? '';
        if (!preg_match('/^[a-f0-9]{64}$/', $deviceId)) {
            $deviceId = bin2hex(random_bytes(32));
            setcookie('app_device_id', $deviceId, [
                'expires' => time() + (86400 * 365 * 2),
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        $sameDevice = !empty($row['session_device_id']) && hash_equals((string)$row['session_device_id'], $deviceId);
        $sessionToken = $sameDevice && !empty($row['session_token'])
            ? $row['session_token']
            : bin2hex(random_bytes(32));

        $_SESSION['user_id'] = $row['id'];
        $_SESSION['fullname'] = $row['fullname'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['session_token'] = $sessionToken;

        $updateToken = $conn->prepare("UPDATE tbl_users SET session_token = ?, session_device_id = ? WHERE id = ? LIMIT 1");
        if ($updateToken) {
            $updateToken->bind_param('ssi', $sessionToken, $deviceId, $row['id']);
            $updateToken->execute();
            $updateToken->close();
        }

        write_audit_log('tbl_users', $row['id'], 'insert', 'login', null, 'Login berhasil user: ' . $row['fullname'] . ' (' . $row['username'] . ')');

        $ip_address = $_SERVER['REMOTE_ADDR'];
        $log = $conn->prepare("
            INSERT INTO tbl_login_log (
                user_id,
                ip_address
            ) VALUES (?, ?)
        ");
        $log->bind_param(
            "is",
            $row['id'],
            $ip_address
        );
        $log->execute();
        echo json_encode([
            'success' => true,
            'username' => $row['username'],
            'fullname' => $row['fullname'],
            'role' => $row['role']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Password salah'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Username tidak ditemukan'
    ]);
}
$stmt->close();
$conn->close();
?>