<?php
session_start();
include 'config/database.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$quoNo = $_GET['quotation_no'] ?? '';
if (empty($quoNo)) {
    die("Error: Parameter Quotation Number tidak ditemukan.");
}

$items = [];
$meta = null;

$stmt = $conn->prepare(
    "SELECT q.*, c.customer_name, c.address as customer_address, c.pic as customer_pic, c.phone as customer_phone, c.email as customer_email
     FROM tbl_quotation q
     LEFT JOIN tbl_customer c ON q.customer_id = c.id
     WHERE q.quotation_no = ?
     ORDER BY q.id ASC"
);

if ($stmt) {
    $stmt->bind_param('s', $quoNo);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (!$meta) {
            $meta = $row;
        }
        $items[] = $row;
    }
    $stmt->close();
}
$conn->close();

if (!$meta) {
    die("Error: Data Quotation tidak ditemukan di database.");
}

// 2. Setup PhpSpreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Quotation Detail');

// Mengaktifkan gridline agar garis cell bawaan excel tetap kelihatan
$sheet->setShowGridlines(true);

// 3. Desain Header / KOP Atas (From & To)
$sheet->setCellValue('A1', 'PRICE BREAKDOWN QUOTATION REPORT');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

// Blok FROM
$sheet->setCellValue('A3', 'FROM:');
$sheet->setCellValue('A4', 'PT Citra Plastik Makmur');
$sheet->setCellValue('A5', 'Jl. Jababeka XIV Blok J4F, Cikarang Utara, Bekasi');
$sheet->getStyle('A3')->getFont()->setBold(true);

// Blok TO (Customer)
$sheet->setCellValue('E3', 'TO (CUSTOMER):');
$sheet->setCellValue('E4', 'Company: ' . $meta['customer_name']);
$sheet->setCellValue('E5', 'Attn/PIC: ' . $meta['customer_pic']);
$sheet->setCellValue('E6', 'Address: ' . $meta['customer_address']);
$sheet->getStyle('E3')->getFont()->setBold(true);

$sheet->setCellValue('A7', 'No. Quotation: ' . $meta['quotation_no']);
$sheet->setCellValue('A8', 'Tanggal Doc: ' . date('d-m-Y', strtotime($meta['quotation_date'])));


$headers = [
    'A10' => 'NO', 'B10' => 'TYPE', 'C10' => 'PART NUMBER', 'D10' => 'PART NAME', 'E10' => 'MATERIAL SPEC',
    'F10' => 'BASIC PRICE', 'G10' => 'WEIGHT/PCS (Gr)', 'H10' => 'IDR PRICE/KG', 'I10' => 'COST/PCS MAT',
    'J10' => 'CYCLE TIME', 'K10' => 'CAV', 'L10' => 'M/C TON', 'M10' => 'RATE/SEC (Rp)', 'N10' => 'COST/PCS PROC',
    'O10' => 'REJECT RATE', 'P10' => 'INSPECTION', 'Q10' => 'PACKING', 'R10' => 'TRANSPORT',
    'S10' => 'COGS', 'T10' => 'O/H PROFIT', 'U10' => 'MOLD MTN', 'V10' => 'TOTAL'
];

foreach ($headers as $cell => $text) {
    $sheet->setCellValue($cell, $text);
}

$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]]
];
$sheet->getStyle('A10:V10')->applyFromArray($headerStyle);
$sheet->getRowDimension('10')->setRowHeight(28);

$startRow = 11;
foreach ($items as $idx => $item) {
    $row = $startRow + $idx;
    
    $isUsd = ($item['currency'] === 'USD' || $item['currency'] === '$');
    $fmtStyle = $isUsd ? '"$"#,##0.00' : 'Rp#,##0.00';

    $sheet->setCellValue('A' . $row, $idx + 1);
    $sheet->setCellValue('B' . $row, strtoupper($item['type']));
    $sheet->setCellValue('C' . $row, $item['part_number']);
    $sheet->setCellValue('D' . $row, $item['part_name']);
    $sheet->setCellValue('E' . $row, $item['material_spec']);
    
    $sheet->setCellValue('F' . $row, $item['basic_price']);
    $sheet->setCellValue('G' . $row, $item['weight_per_pcs']);
    $sheet->setCellValue('H' . $row, $item['idr_price_kg']);
    $sheet->setCellValue('I' . $row, $item['material_cost']);
    $sheet->setCellValue('J' . $row, $item['cycle_time']);
    $sheet->setCellValue('K' . $row, $item['cavity']);
    $sheet->setCellValue('L' . $row, $item['mc_ton']);
    $sheet->setCellValue('M' . $row, $item['rate_hour']); // Menampung data nilai Rp/Detik
    $sheet->setCellValue('N' . $row, $item['process_cost']);
    $sheet->setCellValue('O' . $row, $item['rejection_cost']);
    $sheet->setCellValue('P' . $row, $item['inspection_cost']);
    $sheet->setCellValue('Q' . $row, $item['packing_cost']);
    $sheet->setCellValue('R' . $row, $item['transport_cost']);
    $sheet->setCellValue('S' . $row, $item['cogs']);
    $sheet->setCellValue('T' . $row, $item['oh_profit']);
    $sheet->setCellValue('U' . $row, $item['mold_maintenance']);
    $sheet->setCellValue('V' . $row, $item['selling_price']);

    // format angka excel
    $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode($fmtStyle);
    $sheet->getStyle('H' . $row . ':I' . $row)->getNumberFormat()->setFormatCode('Rp#,##0.00');
    
    $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('Rp#,##0.0000');
    
    $sheet->getStyle('N' . $row)->getNumberFormat()->setFormatCode('Rp#,##0.00');
    $sheet->getStyle('O' . $row . ':V' . $row)->getNumberFormat()->setFormatCode('Rp#,##0.00');
    
    $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0.0000');

    $sheet->getStyle('A' . $row . ':V' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
    $sheet->getStyle('A' . $row . ':B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
}

// Menambahkan baris penutup untuk Note/Catatan dari Database di bagian bawah hasil export Excel gess
$notesRow = $startRow + count($items) + 2;
$sheet->setCellValue('A' . $notesRow, 'CATATAN PENAWARAN (NOTE):');
$sheet->getStyle('A' . $notesRow)->getFont()->setBold(true)->setSize(10);
$sheet->setCellValue('A' . ($notesRow + 1), $meta['quotation_note'] ?: 'Tidak ada catatan khusus.');
$sheet->getStyle('A' . ($notesRow + 1))->getFont()->setItalic(true)->getColor()->setRGB('475569');

foreach (range('A', 'V') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$writer = new Xlsx($spreadsheet);
$fileNameString = 'Report-Quotation-' . str_replace('/', '-', $quoNo) . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $fileNameString . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;