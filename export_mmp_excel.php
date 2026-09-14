<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'purchasing'){
    exit("Akses ditolak.");
}
include 'config/database.php';

$mmp_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query_head = "SELECT keterangan_umum FROM tbl_mmp_head WHERE mmp_id = ?";
$stmt_head = $conn->prepare($query_head);
$stmt_head->bind_param("i", $mmp_id);
$stmt_head->execute();
$res_head = $stmt_head->get_result()->fetch_assoc();
$filename = "MMP_Report_" . ($res_head ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $res_head['keterangan_umum']) : $mmp_id);

$query_det = "SELECT * FROM tbl_mmp_det WHERE mmp_id = ?";
$stmt_det = $conn->prepare($query_det);
$stmt_det->bind_param("i", $mmp_id);
$stmt_det->execute();
$res_det = $stmt_det->get_result();

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$filename.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table border="1">
    <thead>
        <tr>
            <th colspan="17" style="font-size: 16px; font-weight: bold; text-align: center; height: 40px;">
                MONITORING MATERIAL PRICE REPORT: <?php echo htmlspecialchars($res_head['keterangan_umum'] ?? ''); ?>
            </th>
        </tr>
        <tr style="background-color: #1e293b; color: #ffffff; font-weight: bold;">
            <th>Customer</th>
            <th>Part Number</th>
            <th>Part Name</th>
            <th>Mat. Quot</th>
            <th>Mat. Akt</th>
            <th>Supplier</th>
            <th>Item Code</th>
            <th>Harga MKR</th>
            <th>Harga PCH</th>
            <th>Diff (%)</th>
            <th>Berat</th>
            <th>Qty DO</th>
            <th>AMT MKR</th>
            <th>AMT PCH</th>
            <th>Diff (Rp)</th>
            <th>Diff (%)</th>
            <th>Remark</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $res_det->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['customer']); ?></td>
            <td><?php echo htmlspecialchars($row['part_number']); ?></td>
            <td><?php echo htmlspecialchars($row['part_name']); ?></td>
            <td><?php echo htmlspecialchars($row['mat_quotation']); ?></td>
            <td><?php echo htmlspecialchars($row['mat_aktual']); ?></td>
            <td><?php echo htmlspecialchars($row['supplier']); ?></td>
            <td><?php echo htmlspecialchars($row['item_code']); ?></td>
            <td style="text-align: right;"><?php echo number_format($row['harga_mkr'], 2, '.', ''); ?></td>
            <td style="text-align: right;"><?php echo number_format($row['harga_pch'], 2, '.', ''); ?></td>
            <td style="text-align: right; font-weight: bold;"><?php echo htmlspecialchars($row['diff_kg']); ?></td>
            <td style="text-align: right;"><?php echo number_format($row['berat_part'], 3, '.', ''); ?></td>
            <td style="text-align: right;"><?php echo intval($row['qty_do']); ?></td>
            <td style="text-align: right;"><?php echo number_format($row['amt_mkr'], 2, '.', ''); ?></td>
            <td style="text-align: right;"><?php echo number_format($row['amt_pch'], 2, '.', ''); ?></td>
            <td style="text-align: right;"><?php echo number_format($row['diff_nominal'], 2, '.', ''); ?></td>
            <td style="text-align: right; font-weight: bold;"><?php echo htmlspecialchars($row['diff_persen']); ?></td>
            <td><?php echo htmlspecialchars($row['remark']); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>