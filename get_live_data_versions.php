<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

include 'config/database.php';

$versions = [
    'quotation' => 0,
    'pack_trans' => 0,
    'mmp' => 0
];

$queries = [
    'quotation' => "SELECT COALESCE(MAX(id), 0) AS version FROM tbl_quotation",
    'pack_trans' => "SELECT COALESCE(MAX(updated_at), '1970-01-01 00:00:00') AS version FROM tbl_pack_trans_project",
    'mmp' => "SELECT COALESCE(MAX(mmp_id), 0) AS version FROM tbl_mmp_head"
];

foreach ($queries as $name => $query) {
    $result = $conn->query($query);
    if ($result) {
        $versions[$name] = (string)($result->fetch_assoc()['version'] ?? 0);
        $result->free();
    }
}

echo json_encode(['success' => true, 'versions' => $versions]);