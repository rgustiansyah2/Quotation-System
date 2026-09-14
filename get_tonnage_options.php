<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once 'config/database.php';

header('Content-Type: application/json');

$draft_id = isset($_GET['draft_id']) ? intval($_GET['draft_id']) : 0;
$options = [];

if ($draft_id > 0) {
    $query = "SELECT tonnage AS mc_tonnage, machine_name AS mc_label 
              FROM tbl_rate_master 
              WHERE draft_id = ? 
              ORDER BY tonnage ASC";
              
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $draft_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }
        $stmt->close();
    }
}

echo json_encode($options);
exit;