<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array(strtolower(trim((string)($_SESSION['role'] ?? ''))), ['purchasing', 'admin', 'administrator', 'superadmin'], true)) {
    header("Location: index.php");
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
include 'config/database.php';

$mmp_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($mmp_id <= 0) {
    die("ID MMP tidak valid.");
}

$stmt_head = $conn->prepare("SELECT h.keterangan_umum, h.tanggal_input, u.fullname 
                            FROM tbl_mmp_head h 
                            LEFT JOIN tbl_users u ON h.user_id = u.id 
                            WHERE h.mmp_id = ?");
$stmt_head->bind_param("i", $mmp_id);
$stmt_head->execute();
$head = $stmt_head->get_result()->fetch_assoc();

if (!$head) {
    die("Data MMP tidak ditemukan.");
}

$stmt_det = $conn->prepare("SELECT * FROM tbl_mmp_det WHERE mmp_id = ? ORDER BY detail_id ASC");
$stmt_det->bind_param("i", $mmp_id);
$stmt_det->execute();
$details = $stmt_det->get_result();

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 10,
    'margin_bottom' => 10
]);

if (file_exists('export_mmp_pdf.css')) {
    $stylesheet = file_get_contents('export_mmp_pdf.css');
    $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
}

write_audit_log(
    'tbl_mmp_head',                     
    $mmp_id,                            
    'insert',                           
    'export_pdf',                       
    null,                               
    "Mengunduh PDF MMP: " . $head['keterangan_umum'] 
);

$html = '
<div class="pdf-header-title">Monitoring Material Price (MMP)</div>
<div class="pdf-meta-info">
    <b>Judul Acuan:</b> ' . htmlspecialchars($head['keterangan_umum']) . ' &nbsp;|&nbsp; 
    <b>Dibuat Oleh:</b> ' . htmlspecialchars($head['fullname'] ?? 'Purchasing') . ' &nbsp;|&nbsp; 
    <b>Tanggal:</b> ' . date('d M Y H:i', strtotime($head['tanggal_input'])) . '
</div>

<table class="mmp-table">
    <thead>
        <tr>
            <th width="3%">No</th>
            <th width="8%">Customer</th>
            <th width="10%">Part Number</th>
            <th width="8%">Item Code</th>
            <th width="12%">Part Name</th>
            <th width="8%">Mat. Quot</th>
            <th width="8%">Mat. Akt</th>
            <th width="8%">Supplier</th>
            <th width="8%">Harga MKR</th>
            <th width="8%">Harga PCH</th>
            <th width="5%">Diff (%)</th>
            <th width="5%">Berat</th>
            <th width="9%">Remark</th>
        </tr>
    </thead>
    <tbody>';

$no = 1;
while ($row = $details->fetch_assoc()) {
    $bgClass = ($no % 2 === 0) ? 'class="stripe-row"' : '';
    $html .= '
        <tr ' . $bgClass . '>
            <td class="text-center">' . $no++ . '</td>
            <td>' . htmlspecialchars($row['customer']) . '</td>
            <td>' . htmlspecialchars($row['part_number']) . '</td>
            <td>' . htmlspecialchars($row['item_code']) . '</td>
            <td>' . htmlspecialchars($row['part_name']) . '</td>
            <td>' . htmlspecialchars($row['mat_quotation']) . '</td>
            <td>' . htmlspecialchars($row['mat_aktual']) . '</td>
            <td>' . htmlspecialchars($row['supplier']) . '</td>
            <td class="text-right">Rp ' . number_format($row['harga_mkr'], 0, ',', '.') . '</td>
            <td class="text-right">Rp ' . number_format($row['harga_pch'], 0, ',', '.') . '</td>
            <td class="text-right font-bold">' . number_format($row['diff_kg'], 2) . '%</td>
            <td class="text-right">' . number_format($row['berat_part'], 3) . '</td>
            <td>' . htmlspecialchars($row['remark']) . '</td>
        </tr>';
}

$html .= '
    </tbody>
</table>';

$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

$filename = "MMP_" . preg_replace('/[^A-Za-z0-9\-]/', '_', $head['keterangan_umum']) . ".pdf";

$mpdf->Output($filename, 'D');
exit;