<?php
header('Content-Type: application/json');
require_once 'config/database.php';

$search   = trim($_GET['term'] ?? $_GET['q'] ?? '');
$mmpParam = trim($_GET['mmp_id'] ?? $_GET['mmp'] ?? ''); 

$whereClause = "WHERE 1=1";
$params = [];
$types = "";

if (!empty($mmpParam) && $mmpParam !== '0' && strpos($mmpParam, '--') === false) {
    if (is_numeric($mmpParam)) {
        $whereClause .= " AND h.mmp_id = ?";
        $params[] = intval($mmpParam);
        $types .= "i";
    } else {
        $whereClause .= " AND h.keterangan_umum LIKE ?";
        $params[] = "%" . $mmpParam . "%";
        $types .= "s";
    }
}

if (strlen($search) > 0) {
    $whereClause .= " AND (d.mat_quotation LIKE ? OR d.mat_aktual LIKE ? OR d.part_name LIKE ?)";
    $searchTerm = "%" . $search . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

$query = "SELECT 
            h.mmp_id, 
            h.keterangan_umum AS judul_mmp, 
            d.detail_id,   
            d.part_name,
            d.mat_quotation, 
            d.mat_aktual,
            d.harga_pch,
            COALESCE(d.berat_part, 0) AS weight_per_pcs,
            COALESCE(d.harga_pch, 0) AS idr_price_kg
          FROM tbl_mmp_head h
          JOIN tbl_mmp_det d ON h.mmp_id = d.mmp_id    
          {$whereClause}
          ORDER BY d.detail_id ASC 
          LIMIT 30";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$res = $stmt->get_result();

$results = [];
while ($row = $res->fetch_assoc()) {
    $matSpec = !empty($row['mat_quotation']) ? $row['mat_quotation'] : $row['mat_aktual'];

    $displayText = !empty($row['judul_mmp']) 
        ? $matSpec . " (" . $row['judul_mmp'] . ")"
        : $matSpec;

    $results[] = [
        'label'             => $displayText,            
        'value'             => $matSpec,                
        'id'                => $matSpec, 
        'text'              => $displayText,
        'mat_spec'          => $matSpec,
        'mmp_id'            => $row['mmp_id'],
        'judul_mmp'         => $row['judul_mmp'],
        'idr_price_kg'      => floatval($row['idr_price_kg']),
        'weight_per_pcs'    => floatval($row['weight_per_pcs']),
    ];
}

echo json_encode($results);
exit;