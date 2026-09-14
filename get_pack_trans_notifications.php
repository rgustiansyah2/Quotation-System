<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$role = strtolower(trim((string)($_SESSION['role'] ?? '')));
if (!in_array($role, ['marketing', 'manager'], true)) {
    echo json_encode(['success' => true, 'latest_id' => 0, 'events' => []]);
    exit;
}

include 'config/database.php';

$sinceId = max(0, intval($_GET['since'] ?? 0));
$latestId = 0;
$latestResult = $conn->query("SELECT COALESCE(MAX(id), 0) AS latest_id FROM tbl_activity_log");
if ($latestResult) {
    $latestId = (int)($latestResult->fetch_assoc()['latest_id'] ?? 0);
    $latestResult->free();
}

$events = [];
$stmt = $conn->prepare("SELECT l.id, l.row_id, l.description, l.created_at
                        FROM tbl_activity_log l
                        WHERE l.id > ?
                          AND l.table_name = 'tbl_pack_trans_project'
                          AND l.action = 'update'
                        ORDER BY l.id ASC
                        LIMIT 20");

if ($stmt) {
    $stmt->bind_param('i', $sinceId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id' => (int)$row['id'],
            'project_id' => (int)$row['row_id'],
            'description' => $row['description'],
            'created_at' => $row['created_at']
        ];
    }
    $stmt->close();
}

echo json_encode([
    'success' => true,
    'latest_id' => $latestId,
    'events' => $events
]);
