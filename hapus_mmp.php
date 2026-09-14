<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array(strtolower(trim((string)($_SESSION['role'] ?? ''))), ['purchasing', 'admin'], true)) {
    header("Location: index.php");
    exit;
}

include 'config/database.php';

$mmp_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($mmp_id > 0) {
    $stmt_get = $conn->prepare("SELECT keterangan_umum FROM tbl_mmp_head WHERE mmp_id = ?");
    $stmt_get->bind_param("i", $mmp_id);
    $stmt_get->execute();
    $res = $stmt_get->get_result()->fetch_assoc();
    $judul_mmp = $res['keterangan_umum'] ?? 'Unknown MMP';

    $conn->begin_transaction();

    try {
        $query_det = "DELETE FROM tbl_mmp_det WHERE mmp_id = ?";
        $stmt_det = $conn->prepare($query_det);
        $stmt_det->bind_param("i", $mmp_id);
        $stmt_det->execute();

        $query_head = "DELETE FROM tbl_mmp_head WHERE mmp_id = ?";
        $stmt_head = $conn->prepare($query_head);
        $stmt_head->bind_param("i", $mmp_id);
        $stmt_head->execute();

        write_audit_log(
            'tbl_mmp_head',                                       
            $mmp_id,                                              
            'delete',                                             
            'keterangan_umum',                                    
            $judul_mmp,                                           
            "Menghapus dokumen Monitoring Material Price (MMP) ID #{$mmp_id}: {$judul_mmp}" 
        );

        $conn->commit();

        $_SESSION['mmp_events'] = [[
            'event' => 'delete_mmp',
            'data' => ['mmp_id' => $mmp_id, 'keterangan_umum' => $judul_mmp]
        ]];

        header("Location: kelola_mmp.php?status=deleted");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: kelola_mmp.php?status=error");
        exit;
    }
} else {
    header("Location: kelola_mmp.php");
    exit;
}