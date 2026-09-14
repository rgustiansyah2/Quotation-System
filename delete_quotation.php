<?php
session_start();
include 'config/database.php';

if (isset($_GET['quotation_no'])) {
    $quotation_no = $_GET['quotation_no'];
    $stmt = $conn->prepare("DELETE FROM tbl_quotation WHERE quotation_no = ?");
    $stmt->bind_param("s", $quotation_no);

    if ($stmt->execute()) {
        write_audit_log('tbl_quotation', 0, 'delete', 'quotation_no', $quotation_no, 'Menghapus quotation No: ' . $quotation_no);
        header("Location: report.php?status=deleted_success&quo=" . urlencode($quotation_no));
        exit();
    } else {
        header("Location: report.php?status=deleted_failed");
        exit();
    }
}