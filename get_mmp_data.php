<?php
include 'config/database.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get_mmp_all') {
    $query = "SELECT h.mmp_id, h.keterangan_umum AS judul_mmp,
                     d.detail_id, d.part_number, d.part_name, d.mat_quotation,
                     d.mat_aktual, d.harga_mkr, d.harga_pch,
                     COALESCE(d.berat_part, 0) AS weight_per_pcs,
                     COALESCE(d.harga_pch, 0) AS idr_price_kg
              FROM tbl_mmp_head h
              LEFT JOIN tbl_mmp_det d ON h.mmp_id = d.mmp_id
              ORDER BY h.mmp_id DESC";
    $result = $conn->query($query);
    $data = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    echo json_encode(['success' => true, 'data' => $data]);
    exit();
}

if ($action === 'get_mmp_list') {
    $query = "SELECT mmp_id, keterangan_umum FROM tbl_mmp_head ORDER BY mmp_id DESC";
    $result = $conn->query($query);
    
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    echo json_encode(['success' => true, 'data' => $data]);
    exit();
}

if ($action === 'get_specs') {
    $mmp_id = intval($_GET['mmp_id'] ?? 0);
    $data = [];
    if ($mmp_id > 0) {
        $stmt = $conn->prepare("SELECT detail_id AS id, mat_aktual, harga_pch, berat_part FROM tbl_mmp_det WHERE mmp_id = ? AND mat_aktual != '' ORDER BY mat_aktual ASC");
        $stmt->bind_param("i", $mmp_id);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
    }
    echo json_encode(['success' => true, 'data' => $data]);
    exit();
}

if ($action === 'get_spec_detail') {
    $detail_id = intval($_GET['detail_id'] ?? 0);
    if ($detail_id > 0) {
        $stmt = $conn->prepare("SELECT mat_aktual, harga_pch, berat_part FROM tbl_mmp_det WHERE detail_id = ?");
        $stmt->bind_param("i", $detail_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            echo json_encode(['success' => true, 'data' => $row]);
            exit();
        }
        $stmt->close();
    }
    echo json_encode(['success' => false]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>