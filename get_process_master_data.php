<?php
include 'config/database.php';

header('Content-Type: application/json');

$response = [
    'success' => true,
    'purging' => [],
    'dandori' => [],
    'mold' => []
];

$purging = $conn->query("SELECT mc_ton_min, mc_ton_max, purging_ori_kg, purging_cellpurg_kg FROM tbl_purging_master WHERE status='active'");
if ($purging) {
    while ($row = $purging->fetch_assoc()) {
        $response['purging'][] = $row;
    }
}

$dandori = $conn->query("SELECT mc_ton_min, mc_ton_max, machine_type, dandori_minutes FROM tbl_dandori_master WHERE status='active'");
if ($dandori) {
    while ($row = $dandori->fetch_assoc()) {
        $response['dandori'][] = $row;
    }
}

$mold = $conn->query("SELECT id, category_type AS tipe_kategori, key_name, cost_per_month FROM tbl_mold_maintenance_master WHERE status='active'");
if ($mold) {
    while ($row = $mold->fetch_assoc()) {
        $response['mold'][] = $row;
    }
}

echo json_encode($response);
