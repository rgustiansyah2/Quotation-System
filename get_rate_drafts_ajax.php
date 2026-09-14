<?php
include 'config/database.php';

header('Content-Type: application/json');

$drafts = [];
$result = $conn->query("SELECT d.id, d.draft_title, d.base_reference, d.manpower_rate_sec,
                              m.tonnage, m.rate_per_second
                       FROM tbl_rate_drafts d
                       LEFT JOIN tbl_rate_master m ON d.id = m.draft_id
                       ORDER BY d.id DESC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $draftId = (int)$row['id'];
        if (!isset($drafts[$draftId])) {
            $drafts[$draftId] = [
                'id' => $draftId,
                'draft_title' => $row['draft_title'],
                'base_reference' => $row['base_reference'],
                'manpower_rate_sec' => (float)$row['manpower_rate_sec'],
                'matrix' => []
            ];
        }
        if ($row['tonnage'] !== null) {
            $drafts[$draftId]['matrix'][$row['tonnage']] = (float)$row['rate_per_second'];
        }
    }
}

echo json_encode(['success' => true, 'data' => array_values($drafts)]);
