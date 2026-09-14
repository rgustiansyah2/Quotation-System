<?php
include 'config/database.php';

$drafts = [];
$result = $conn->query("SELECT id, draft_name FROM tbl_rate_draft ORDER BY id DESC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $drafts[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($drafts);