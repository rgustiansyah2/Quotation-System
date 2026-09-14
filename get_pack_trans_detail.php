<?php
include 'config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$response = [];

if ($id > 0) {
    $stmt = $conn->prepare("SELECT id, part_name, total_packing_pcs, transport_pcs_total FROM tbl_pack_trans_project WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $project = $stmt->get_result()->fetch_assoc();

    if ($project) {
        $response['project'] = $project;
        $response['items'] = [];

        $stmt_items = $conn->prepare("
            SELECT i.*, p.description AS packing_desc, p.harga_jual AS harga_master 
            FROM tbl_pack_trans_items i 
            LEFT JOIN tbl_packing_standard p ON i.standard_id = p.id 
            WHERE i.project_id = ?
        ");
        $stmt_items->bind_param("i", $id);
        $stmt_items->execute();
        $res = $stmt_items->get_result();

        while($row = $res->fetch_assoc()) {
            $response['items'][] = $row;
        }
    } else {
        $response['error'] = "Data proyek tidak ditemukan!";
    }
} else {
    $response['error'] = "ID tidak valid!";
}

header('Content-Type: application/json');
echo json_encode($response);