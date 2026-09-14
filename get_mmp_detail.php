<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(!isset($_SESSION['user_id'])){
    echo json_encode([]);
    exit;
}
include 'config/database.php';

$mmp_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT * FROM tbl_mmp_det WHERE mmp_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $mmp_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);