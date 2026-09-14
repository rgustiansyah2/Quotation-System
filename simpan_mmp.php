<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
$userRole = strtolower(trim((string)($_SESSION['role'] ?? '')));

$allowedRoles = ['purchasing', 'admin', 'superadmin', 'administrator'];

if (!isset($_SESSION['user_id']) || !in_array($userRole, $allowedRoles, true)) {
    header("Location: index.php");
    exit;
}

include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $judul_mmp = isset($_POST['judul_mmp']) ? trim($_POST['judul_mmp']) : 'MMP Tanpa Judul';
    
    $query_head = "INSERT INTO tbl_mmp_head (user_id, keterangan_umum) VALUES (?, ?)";
    $stmt_head = $conn->prepare($query_head);
    $stmt_head->bind_param("is", $user_id, $judul_mmp); 
    
    if ($stmt_head->execute()) {
        $mmp_id = $conn->insert_id; 
        
        $customers     = $_POST['customer'] ?? [];
        $part_numbers  = $_POST['part_number'] ?? [];
        $part_names    = $_POST['part_name'] ?? [];
        $mat_quotations= $_POST['mat_quotation'] ?? [];
        $mat_aktuals   = $_POST['mat_aktual'] ?? [];
        $suppliers     = $_POST['supplier'] ?? [];
        $item_codes    = $_POST['item_code'] ?? [];
        $harga_mkrs    = $_POST['harga_mkr'] ?? [];
        $harga_pchs    = $_POST['harga_pch'] ?? [];
        $diff_kgs      = $_POST['diff_kg'] ?? [];
        $berat_parts   = $_POST['berat_part'] ?? [];
        $remarks       = $_POST['remark'] ?? [];

        $query_det = "INSERT INTO tbl_mmp_det 
            (mmp_id, customer, part_number, part_name, mat_quotation, mat_aktual, supplier, item_code, harga_mkr, harga_pch, diff_kg, berat_part, remark) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_det = $conn->prepare($query_det);
    
        foreach ($customers as $index => $customer) {
            if(empty($customer)) continue;

            $part_num   = $part_numbers[$index] ?? '';
            $part_name  = $part_names[$index] ?? '';
            $mat_quot   = $mat_quotations[$index] ?? '';
            $mat_akt    = $mat_aktuals[$index] ?? '';
            $supplier   = $suppliers[$index] ?? '';
            $item_code  = $item_codes[$index] ?? '';
            
            $mkr        = floatval($harga_mkrs[$index] ?? 0);
            $pch        = floatval($harga_pchs[$index] ?? 0);
            $d_kg       = $diff_kgs[$index] ?? '0.00%'; 
            $berat      = floatval($berat_parts[$index] ?? 0);
            $rem        = $remarks[$index] ?? '';

            $stmt_det->bind_param("isssssssdddds", 
                $mmp_id, $customer, $part_num, $part_name, $mat_quot, $mat_akt, $supplier, $item_code, 
                $mkr, $pch, $d_kg, $berat, $rem
            );
            
            $stmt_det->execute();
        }

        write_audit_log(
            'tbl_mmp_head',
            $mmp_id,
            'insert',
            'keterangan_umum',
            NULL,
            "Membuat dokumen Monitoring Material Price (MMP): {$judul_mmp}"
        );

        $_SESSION['flash_message'] = [
            'type' => 'success',
            'text' => 'Data Monitoring Material Price berhasil disimpan!'
        ];
        $_SESSION['mmp_events'] = [[
            'event' => 'new_mmp',
            'data' => ['mmp_id' => $mmp_id, 'keterangan_umum' => $judul_mmp]
        ]];

        header("Location: mmp.php");
        exit;
    } else {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'text' => 'Gagal menyimpan data utama MMP!'
        ];
        header("Location: mmp.php");
        exit;
    }
}
?>