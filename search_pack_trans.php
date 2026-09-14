<?php
include 'config/database.php';

header('Content-Type: application/json');

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, part_name, total_packing_pcs, transport_pcs_total 
    FROM tbl_pack_trans_project 
    WHERE part_name LIKE ? 
    ORDER BY id DESC 
    LIMIT 10
");

$searchTerm = "%{$query}%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id'                 => $row['id'],
        'part_name'          => $row['part_name'],
        'total_packing_pcs'  => floatval($row['total_packing_pcs']),
        'transport_pcs_total'=> floatval($row['transport_pcs_total'])
    ];
}

echo json_encode($data);
$stmt->close();