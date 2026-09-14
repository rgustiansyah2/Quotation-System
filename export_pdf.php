<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';
include 'config/database.php';

$quotation_no = $_GET['quotation_no'] ?? '';

if (empty($quotation_no)) {
    die("Nomor Quotation tidak ditemukan.");
}

$query = "SELECT q.*, c.customer_name, c.pic, c.address, c.phone, c.email
          FROM tbl_quotation q
          LEFT JOIN tbl_customer c ON q.customer_id = c.id
          WHERE q.quotation_no = ? 
          ORDER BY q.customer_id DESC LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $quotation_no);
$stmt->execute();
$main_data = $stmt->get_result()->fetch_assoc();

if (!$main_data) {
    die("Data Quotation tidak ditemukan.");
}

$query_items = "SELECT * FROM tbl_quotation WHERE quotation_no = ? ORDER BY id ASC";
$stmt_items = $conn->prepare($query_items);
$stmt_items->bind_param("s", $quotation_no);
$stmt_items->execute();
$res_items = $stmt_items->get_result();

$items = [];
while ($row = $res_items->fetch_assoc()) {
    $row['cr_pct_list'] = is_string($row['cr_lta_pct_json'] ?? null) ? json_decode($row['cr_lta_pct_json'], true) : [];
    if (!is_array($row['cr_pct_list'])) { $row['cr_pct_list'] = []; }

    $row['cr_res_list'] = is_string($row['cr_lta_res_json'] ?? null) ? json_decode($row['cr_lta_res_json'], true) : [];
    if (!is_array($row['cr_res_list'])) { $row['cr_res_list'] = []; }

    if (empty($row['cr_pct_list']) && !empty($row['cr_pct_lta1'])) {
        $row['cr_pct_list'] = [floatval($row['cr_pct_lta1']), floatval($row['cr_pct_lta2'] ?? 0), floatval($row['cr_pct_lta3'] ?? 0)];
    }

    if (empty($row['cr_res_list']) && !empty($row['cr_base_val'])) {
        $row['cr_res_list'] = [
            floatval($row['cr_base_val']) * (1 + (floatval($row['cr_pct_lta1'] ?? 0) / 100)),
            (floatval($row['cr_base_val']) * (1 + (floatval($row['cr_pct_lta1'] ?? 0) / 100))) * (1 + (floatval($row['cr_pct_lta2'] ?? 0) / 100)),
            (floatval($row['cr_base_val']) * (1 + (floatval($row['cr_pct_lta1'] ?? 0) / 100)) * (1 + (floatval($row['cr_pct_lta2'] ?? 0) / 100))) * (1 + (floatval($row['cr_pct_lta3'] ?? 0) / 100))
        ];
    }

    $items[] = $row;
}

$conn->close();

$firstItemCrMode = count($items) > 0 ? ($items[0]['cr_mode'] ?? 'none') : 'none';
$showCr = ($firstItemCrMode === 'with_bl' || $firstItemCrMode === 'without_bl');
$showBl = ($firstItemCrMode === 'with_bl');

$maxLtaYears = 0;
foreach ($items as $item) {
    $count = count($item['cr_res_list'] ?? []) ?: count($item['cr_pct_list'] ?? []);
    if ($count > $maxLtaYears) {
        $maxLtaYears = $count;
    }
}

if ($maxLtaYears === 0 && $showCr) {
    $maxLtaYears = 3;
}

$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cost Breakdown Quotation - ' . htmlspecialchars($quotation_no) . '</title>
</head>
<body>

    <div class="title">COST BREAKDOWN QUOTATION</div>

    <table class="header-table">
        <tr>
            <td width="50%" style="vertical-align: top;">
                <strong style="color: #0284c7;">FROM:</strong><br>
                <strong>PT Citra Plastik Makmur</strong><br>
                Jl. Jababeka XIV A Blok J4F, Cikarang Utara, Bekasi<br>
                No. Quo: <strong>' . htmlspecialchars($main_data['quotation_no']) . '</strong> | Tanggal: ' . date('d-m-Y', strtotime($main_data['quotation_date'])) . '
            </td>
            <td width="50%" style="vertical-align: top;">
                <strong style="color: #0284c7;">TO:</strong><br>
                Company: <strong>' . htmlspecialchars($main_data['customer_name'] ?? '-') . '</strong><br>
                Attn / PIC: ' . htmlspecialchars($main_data['pic'] ?: '-') . '<br>
                Address: ' . htmlspecialchars($main_data['address'] ?: '-') . '<br>
                Telp/Email: ' . htmlspecialchars($main_data['phone'] ?: '-') . ' / ' . htmlspecialchars($main_data['email'] ?: '-') . '
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="2%">No</th>
                <th width="3%">Type</th>
                <th width="5%">Part No</th>
                <th width="6%">Part Name</th>
                <th width="5%">Mat. Spec</th>
                <th width="4%">Basic Price</th>
                <th width="4%">Pigmen Cost</th>
                <th width="3%">Weight/Pcs</th>
                <th width="4%">IDR/Kg</th>
                <th width="4%">Mat. Cost</th>
                <th width="3%">Purging</th>
                <th width="3%">Dandori</th>
                <th width="2%">C/T</th>
                <th width="2%">Cav</th>
                <th width="3%">M/C Ton</th>
                <th width="4%">Rate/s</th>
                <th width="4%">Proc. Cost</th>
                <th width="3%">Reject</th>
                <th width="3%">Other Proc</th>
                <th width="3%">Packing</th>
                <th width="3%">Transport</th>
                <th width="4%" style="background-color: #cbd5e1;">COGS</th>
                <th width="4%">OH/Profit</th>
                <th width="3%">Mold Mtn</th>
                <th width="4%">Dep. Mold</th>
                <th width="5%" style="background-color: #bbf7d0; color: #166534;">Selling Price</th>
                <th width="4%" style="background-color: #fef3c7; color: #92400e;">Lump Sum</th>';

                if ($showCr) {
                    $html .= '<th width="4%" style="background-color: #0284c7; color: #ffffff;">Base CR</th>';
                    for ($i = 1; $i <= $maxLtaYears; $i++) {
                        $html .= '<th width="4%" style="background-color: #0284c7; color: #ffffff;">LTA ' . $i . '</th>';
                    }
                }
                if ($showBl) {
                    $html .= '<th width="3%" style="background-color: #15803d; color: #ffffff;">BL</th>';
                }
                if ($showCr) {
                    $html .= '<th width="4%" style="background-color: #0284c7; color: #ffffff;">Final Cost</th>';
                }

$html .= '
            </tr>
        </thead>
        <tbody>';

        foreach ($items as $index => $item) {
            $sym = ($item['currency'] === 'USD' || $item['currency'] === '$') ? '$ ' : 'Rp ';
            
            $weightPcs      = $item['weight_per_pcs'] ?? $item['weight_pcs'] ?? 0;
            $pigmenCost     = $item['pigmen_cost'] ?? $item['pigmen'] ?? 0;
            $otherProcCost  = $item['other_process_cost'] ?? $item['inspection_cost'] ?? 0;
            $purging        = $item['purging'] ?? 0;
            $dandori        = $item['dandori'] ?? 0;
            $moldDepPcs     = $item['mold_depreciation_pcs'] ?? 0;
            $lumpsumPcs     = $item['lumpsum_pcs'] ?? $item['lumpsum_price'] ?? 0;

            $baseCr  = floatval($item['cr_base_val'] ?? 0);
            $bl      = floatval($item['cr_pct_bl'] ?? 0);
            $crFinal = floatval($item['cr_final_cost'] ?? $item['selling_price'] ?? 0);

            $crResList = is_array($item['cr_res_list'] ?? null) ? $item['cr_res_list'] : [];
            $crPctList = is_array($item['cr_pct_list'] ?? null) ? $item['cr_pct_list'] : [];

            $html .= '
            <tr>
                <td class="text-center">' . ($index + 1) . '</td>
                <td class="text-center bold" style="color:' . ($item['type'] === 'external' ? '#2563eb' : '#16a34a') . '">' . strtoupper(htmlspecialchars($item['type'])) . '</td>
                <td class="bold">' . htmlspecialchars($item['part_number']) . '</td>
                <td>' . htmlspecialchars($item['part_name']) . '</td>
                <td>' . htmlspecialchars($item['material_spec']) . '</td>
                <td class="text-right">' . $sym . number_format((float)$item['basic_price'], 2, ',', '.') . '</td>
                <td class="text-right bg-mat">' . $sym . number_format((float)$pigmenCost, 2, ',', '.') . '</td>
                <td class="text-right bg-mat">' . number_format((float)$weightPcs, 4, ',', '.') . '</td>
                <td class="text-right bg-mat">' . $sym . number_format((float)$item['idr_price_kg'], 2, ',', '.') . '</td>
                <td class="text-right bg-mat bold">' . $sym . number_format((float)$item['material_price'], 2, ',', '.') . '</td>
                <td class="text-right bg-mat">' . $sym . number_format((float)$purging, 2, ',', '.') . '</td>
                <td class="text-right bg-proc">' . $sym . number_format((float)$dandori, 2, ',', '.') . '</td>
                <td class="text-center bg-proc">' . htmlspecialchars($item['cycle_time']) . '</td>
                <td class="text-center bg-proc">' . htmlspecialchars($item['cavity']) . '</td>
                <td class="text-center bg-proc">' . htmlspecialchars($item['mc_ton']) . 'T</td>
                <td class="text-right bg-proc">' . $sym . number_format((float)$item['rate_hour'], 0, ',', '.') . '</td>
                <td class="text-right bg-proc bold">' . $sym . number_format((float)$item['process_cost'], 2, ',', '.') . '</td>
                <td class="text-right">' . $sym . number_format((float)$item['rejection_cost'], 2, ',', '.') . '</td>
                <td class="text-right">' . $sym . number_format((float)$otherProcCost, 2, ',', '.') . '</td>
                <td class="text-right">' . $sym . number_format((float)$item['packing_cost'], 2, ',', '.') . '</td>
                <td class="text-right">' . $sym . number_format((float)$item['transport_cost'], 2, ',', '.') . '</td>
                <td class="text-right bg-cogs bold">' . $sym . number_format((float)$item['cogs'], 2, ',', '.') . '</td>
                <td class="text-right">' . $sym . number_format((float)$item['oh_profit'], 2, ',', '.') . '</td>
                <td class="text-right">' . $sym . number_format((float)$item['mold_maintenance'], 2, ',', '.') . '</td>
                <td class="text-right">' . $sym . number_format((float)$moldDepPcs, 2, ',', '.') . '</td>
                <td class="text-right bg-total bold">' . $sym . number_format((float)$item['selling_price'], 2, ',', '.') . '</td>
                <td class="text-right bold" style="background-color: #fef3c7; color: #92400e;">' . $sym . number_format((float)$lumpsumPcs, 2, ',', '.') . '</td>';

                if ($showCr) {
                    $html .= '<td class="text-right" style="background-color: #fef3c7;">' . $sym . number_format((float)$baseCr, 2, ',', '.') . '</td>';
                    for ($y = 0; $y < $maxLtaYears; $y++) {
                        $ltaValue = floatval($crResList[$y] ?? 0);
                        $pctValue = floatval($crPctList[$y] ?? 0);
                        $html .= '<td class="text-right" style="background-color: #e0f2fe;">' . $sym . number_format($ltaValue, 2, ',', '.') . '<br><small>(' . number_format($pctValue, 2, ',', '.') . '%)</small></td>';
                    }
                }
                if ($showBl) {
                    $html .= '<td class="text-right" style="background-color: #e0f2fe;">' . $sym . number_format((float)$bl, 2, ',', '.') . '</td>';
                }
                if ($showCr) {
                    $html .= '<td class="text-right bold" style="background-color: #dcfce7; color: #15803d;">' . $sym . number_format((float)$crFinal, 2, ',', '.') . '</td>';
                }

            $html .= '</tr>';
        }

$html .= '
        </tbody>
    </table>';

$notesList = [
    '1. Model.',
    '2. Exchange Rate periode ' . date('d-m-Y') . ' Rp. ' . number_format((float)($main_data['exchange_rate_value'] ?? 0), 0, ',', '.') . ' / USD.',
    '3. Harga belum termasuk PPN 11%, biaya pengetesan, jig, checking fixture, dan biaya finishing (jika ada).',
    '4. Qty forecast: ' . htmlspecialchars($main_data['qty_forecast_month'] ?? '-') . '.',
    '5. Pembayaran ' . htmlspecialchars($main_data['payment_term'] ?? '-') . ' hari setelah penerimaan invoice.',
    '6. Berat part, cycle time, cavity & tonase mesin akan diperbaharui kembali setelah hasil trial dinyatakan OK.',
    '7. Packing menggunakan returnable dan non-returnable.',
    '8. Validasi penawaran harga: 30 hari.',
    '9. Tahun Masspro ' . htmlspecialchars($main_data['rate_draft_id'] ?? '-') . '.'
];

if (!empty($main_data['quotation_note'])) {
    $storedNotes = preg_replace('/\r\n|\r/', "\n", (string)$main_data['quotation_note']);
    $storedNoteLines = preg_split('/\n+/', $storedNotes, -1, PREG_SPLIT_NO_EMPTY);

    foreach ($storedNoteLines as $storedNoteLine) {
        if (preg_match('/^\s*(\d+)\.\s*(.+?)\s*$/', $storedNoteLine, $noteMatch) && (int)$noteMatch[1] >= 10) {
            $notesList[] = $noteMatch[1] . '. ' . $noteMatch[2];
        }
    }
}

$html .= '
    <table class="notes-layout-table">
        <tr>
            <td style="vertical-align: top; width: 68%;">
                <table class="notes-container">
                    <thead>
                        <tr><th class="notes-header">NOTE:</th></tr>
                    </thead>
                    <tbody>';
foreach ($notesList as $noteLine) {
    $html .= '<tr><td class="note-line">' . htmlspecialchars($noteLine) . '</td></tr>';
}
$html .= '
                    </tbody>
                </table>
            </td>

            <td style="vertical-align: top; width: 32%;">
                <div class="signature-wrapper">
                    <table class="signature-table">
                        <thead>
                            <tr>
                                <th class="signature-title">DIBUAT</th>
                                <th class="signature-title">DIKETAHUI</th>
                                <th class="signature-title">DISETUJUI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="signature-box"></td>
                                <td class="signature-box"></td>
                                <td class="signature-box"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>';

$stylesheet = file_get_contents(__DIR__ . '/exportpdf.css');

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L',
    'margin_left' => 3,
    'margin_right' => 3,
    'margin_top' => 6,
    'margin_bottom' => 6
]);

if ($stylesheet) {
    $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
}

$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

$filename = "Quotation_" . str_replace(['/', '\\'], '-', $quotation_no) . ".pdf";
$mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
exit();