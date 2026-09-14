<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if (!isset($_SESSION['user_id']) || !in_array(strtolower(trim((string)($_SESSION['role'] ?? ''))), ['purchasing', 'admin'], true)) {
    header("Location: index.php");
    exit;
}
include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mmp_id = intval($_POST['mmp_id']);
    $judul_mmp = isset($_POST['judul_mmp']) ? trim($_POST['judul_mmp']) : 'MMP Tanpa Judul';

    $old_judul = '';
    $stmt_old = $conn->prepare("SELECT keterangan_umum FROM tbl_mmp_head WHERE mmp_id = ?");
    $stmt_old->bind_param("i", $mmp_id);
    $stmt_old->execute();
    $res_old = $stmt_old->get_result()->fetch_assoc();
    if ($res_old) {
        $old_judul = $res_old['keterangan_umum'];
    }

    $query_head = "UPDATE tbl_mmp_head SET keterangan_umum = ? WHERE mmp_id = ?";
    $stmt_head = $conn->prepare($query_head);
    $stmt_head->bind_param("si", $judul_mmp, $mmp_id);
    
    if ($stmt_head->execute()) {
        
        $query_del = "DELETE FROM tbl_mmp_det WHERE mmp_id = ?";
        $stmt_del = $conn->prepare($query_del);
        $stmt_del->bind_param("i", $mmp_id);
        $stmt_del->execute();

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

            $part_num   = $part_numbers[$index];
            $part_name  = $part_names[$index];
            $mat_quot   = $mat_quotations[$index];
            $mat_akt    = $mat_aktuals[$index];
            $supplier   = $suppliers[$index];
            $item_code  = $item_codes[$index];
            
            $mkr        = floatval($harga_mkrs[$index]);
            $pch        = floatval($harga_pchs[$index]);
            $d_kg       = floatval($diff_kgs[$index]);
            $berat      = floatval($berat_parts[$index]);
            $rem        = $remarks[$index];

            $stmt_det->bind_param("isssssssdddds", 
                $mmp_id, $customer, $part_num, $part_name, $mat_quot, $mat_akt, $supplier, $item_code, 
                $mkr, $pch, $d_kg, $berat, $rem
            );
            $stmt_det->execute();
        }

        write_audit_log(
            'tbl_mmp_head',
            $mmp_id,
            'update',
            'keterangan_umum',
            $old_judul, 
            " memperbarui data MMP ID #{$mmp_id}: {$judul_mmp}"
        );

        $_SESSION['mmp_events'] = [[
            'event' => 'update_mmp',
            'data' => ['mmp_id' => $mmp_id, 'keterangan_umum' => $judul_mmp]
        ]];

        echo "<script>
            alert('Data Berhasil Diperbarui!');
            window.location.href = 'kelola_mmp.php';
        </script>";
    } else {
        echo "Gagal memperbarui data utama.";
    }
}
?>