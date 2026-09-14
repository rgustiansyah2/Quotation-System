<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

global $conn;

if (!isset($conn) || !$conn) {
    if (file_exists(__DIR__ . '/config/database.php')) {
        require_once __DIR__ . '/config/database.php';
    } elseif (file_exists(__DIR__ . '/koneksi.php')) {
        require_once __DIR__ . '/koneksi.php';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'mark_pack_trans_read' && !empty($_POST['id'])) {
        $projectId = intval($_POST['id']);
        if ($projectId > 0 && isset($conn) && $conn instanceof mysqli) {
            $stmt = $conn->prepare("UPDATE tbl_pack_trans_project SET is_read = 1 WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $projectId);
                $stmt->execute();
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }
}

$userRole = strtolower($_SESSION['role'] ?? 'user');

$unreadQuotation = 0;
$unreadPackTrans = 0;
$unreadActivity = 0;

if ($userRole === 'general manager' || $userRole === 'admin' || $userRole === 'manager') {
    if (isset($conn) && $conn instanceof mysqli) {
        $qQuo = mysqli_query($conn, "SELECT COUNT(DISTINCT quotation_no) AS total FROM tbl_quotation WHERE is_read = 0");
        if ($qQuo) {
            $unreadQuotation = (int)(mysqli_fetch_assoc($qQuo)['total'] ?? 0);
        }

        $qPT = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tbl_pack_trans_project WHERE is_read = 0");
        if ($qPT) {
            $unreadPackTrans = (int)(mysqli_fetch_assoc($qPT)['total'] ?? 0);
        }
    }
}

if (isset($conn) && $conn instanceof mysqli) {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId > 0) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM tbl_activity_log WHERE created_at > COALESCE((SELECT last_activity_seen_at FROM tbl_users WHERE id = ?), '1970-01-01 00:00:00')");
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            $unreadActivity = (int)($res->fetch_assoc()['total'] ?? 0);
            $stmt->close();
        }
    }
}

header('Content-Type: application/json');
echo json_encode([
    'quotation' => $unreadQuotation,
    'pack_trans' => $unreadPackTrans,
    'activity' => $unreadActivity,
]);