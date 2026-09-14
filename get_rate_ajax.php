<?php
include 'config/database.php';

$draftId = isset($_GET['draft_id']) ? intval($_GET['draft_id']) : 0;
$tonnage = isset($_GET['tonnage']) ? intval($_GET['tonnage']) : 0;

$ratePerSecond = 0.0000;

if ($draftId > 0 && $tonnage > 0) {
    $stmt = $conn->prepare("SELECT rate_per_second FROM tbl_rate_master WHERE draft_id = ? AND tonnage = ?");
    $stmt->bind_param('ii', $draftId, $tonnage);
    $stmt->execute();
    $stmt->bind_result($ratePerSecond);
    $stmt->fetch();
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode(['rate_per_second' => (float)$ratePerSecond]);