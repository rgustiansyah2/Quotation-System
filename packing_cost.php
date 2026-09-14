<?php
session_start();
include 'config/database.php';

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'export_excel') {
    if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
        die('Library PhpSpreadsheet belum terpasang. Jalankan "composer require phpoffice/phpspreadsheet" terlebih dahulu.');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Packing Cost');

    $headers = [
        'A1' => 'part_name',
        'B1' => 'item_category',
        'C1' => 'weight_gram',
        'D1' => 'qty_per_kg',
        'E1' => 'volume_cm3',
        'F1' => 'unit',
        'G1' => 'purchase_price',
        'H1' => 'item_price',
        'I1' => 'max_capacity_gram',
        'J1' => 'size_detail',
        'K1' => 'revision_no',
        'L1' => 'effective_date',
        'M1' => 'status'
    ];

    foreach ($headers as $cell => $text) {
        $sheet->setCellValue($cell, $text);
    }

    $sheet->getStyle('A1:M1')->getFont()->setBold(true);

    $query = $conn->query("SELECT part_name, item_category, weight_gram, qty_per_kg, volume_cm3, unit, purchase_price, item_price, max_capacity_gram, size_detail, revision_no, effective_date, status FROM tbl_packing_cost ORDER BY item_category, part_name");
    
    $rowNum = 2;
    if ($query) {
        while ($row = $query->fetch_assoc()) {
            $sheet->setCellValue('A' . $rowNum, $row['part_name']);
            $sheet->setCellValue('B' . $rowNum, $row['item_category']);
            $sheet->setCellValue('C' . $rowNum, $row['weight_gram']);
            $sheet->setCellValue('D' . $rowNum, $row['qty_per_kg']);
            $sheet->setCellValue('E' . $rowNum, $row['volume_cm3']);
            $sheet->setCellValue('F' . $rowNum, $row['unit']);
            $sheet->setCellValue('G' . $rowNum, $row['purchase_price']);
            $sheet->setCellValue('H' . $rowNum, $row['item_price']);
            $sheet->setCellValue('I' . $rowNum, $row['max_capacity_gram']);
            $sheet->setCellValue('J' . $rowNum, $row['size_detail']);
            $sheet->setCellValue('K' . $rowNum, $row['revision_no']);
            $sheet->setCellValue('L' . $rowNum, $row['effective_date']);
            $sheet->setCellValue('M' . $rowNum, $row['status']);
            $rowNum++;
        }
    }

    foreach (range('A', 'M') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $filename = "packing_cost_export_" . date('Ymd_His') . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

$message = '';
$messageType = 'success';
$editingId = 0;
$item = [
    'part_name' => '',
    'item_category' => '',
    'weight_gram' => '',
    'qty_per_kg' => '',
    'volume_cm3' => '',
    'unit' => '',
    'purchase_price' => '',
    'item_price' => '',
    'max_capacity_gram' => '',
    'size_detail' => '',
    'revision_no' => 0,
    'effective_date' => date('Y-m-d'),
    'status' => 'active'
];

$action = $_POST['action'] ?? $_GET['action'] ?? 'save';
$editingId = intval($_POST['item_id'] ?? $_GET['item_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $action === 'delete') {
    
    if ($action === 'bulk_delete') {
        $selectedIds = $_POST['selected_ids'] ?? [];
        if (!empty($selectedIds) && is_array($selectedIds)) {
            $ids = array_map('intval', $selectedIds);
            $inClause = implode(',', $ids);
            try {
                if ($conn->query("DELETE FROM tbl_packing_cost WHERE id IN ($inClause)")) {
                    $count = $conn->affected_rows;
                    write_audit_log('tbl_packing_cost', 0, 'delete', 'bulk_delete', null, "Menghapus massal $count item packing cost.");
                    $message = "Berhasil menghapus $count item packing cost.";
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus item terpilih: ' . $conn->error;
                    $messageType = 'error';
                }
            } catch (Exception $e) {
                $message = 'Sebagian data gagal dihapus karena sedang terikat pada transaksi lain.';
                $messageType = 'error';
            }
        } else {
            $message = 'Tidak ada item yang dipilih untuk dihapus.';
            $messageType = 'warning';
        }
    }
    elseif ($action === 'bulk_update') {
        $selectedIds = $_POST['selected_ids'] ?? [];
        $bulkStatus = $_POST['bulk_status'] ?? '';

        if (!empty($selectedIds) && is_array($selectedIds) && in_array($bulkStatus, ['active', 'inactive'], true)) {
            $ids = array_map('intval', $selectedIds);
            $inClause = implode(',', $ids);
            
            $stmt = $conn->prepare("UPDATE tbl_packing_cost SET status = ? WHERE id IN ($inClause)");
            if ($stmt) {
                $stmt->bind_param('s', $bulkStatus);
                if ($stmt->execute()) {
                    $count = $stmt->affected_rows;
                    write_audit_log('tbl_packing_cost', 0, 'update', 'bulk_status', $bulkStatus, "Memperbarui status massal menjadi $bulkStatus pada $count item.");
                    $message = "Berhasil memperbarui status $count item menjadi " . ucfirst($bulkStatus) . ".";
                    $messageType = 'success';
                } else {
                    $message = 'Gagal memperbarui status: ' . $stmt->error;
                    $messageType = 'error';
                }
                $stmt->close();
            }
        } else {
            $message = 'Pilihlah item dan status baru yang valid untuk pembaruan massal.';
            $messageType = 'warning';
        }
    }
    elseif ($action === 'save') {
        $item['part_name'] = trim($_POST['part_name'] ?? '');
        $item['item_category'] = trim($_POST['item_category'] ?? '');
        $item['weight_gram'] = trim($_POST['weight_gram'] ?? '0');
        $item['qty_per_kg'] = trim($_POST['qty_per_kg'] ?? '0');
        $item['volume_cm3'] = trim($_POST['volume_cm3'] ?? '0');
        $item['unit'] = trim($_POST['unit'] ?? '');
        $item['purchase_price'] = trim($_POST['purchase_price'] ?? '0');
        $item['item_price'] = trim($_POST['item_price'] ?? '0');
        $item['max_capacity_gram'] = trim($_POST['max_capacity_gram'] ?? '0');
        $item['size_detail'] = trim($_POST['size_detail'] ?? '');
        $item['revision_no'] = intval($_POST['revision_no'] ?? 0);
        $item['effective_date'] = trim($_POST['effective_date'] ?? date('Y-m-d'));
        $item['status'] = in_array($_POST['status'] ?? 'active', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if ($item['part_name'] === '') {
            $message = 'Nama item (Part Name) wajib diisi.';
            $messageType = 'error';
        } else {
            try {
                $chkStmt = $conn->prepare("SELECT id FROM tbl_packing_cost WHERE LOWER(part_name) = LOWER(?) AND id != ? LIMIT 1");
                $chkStmt->bind_param("si", $item['part_name'], $editingId);
                $chkStmt->execute();
                $chkResult = $chkStmt->get_result();
                
                if ($chkResult->num_rows > 0) {
                    $message = "Gagal menyimpan: Nama item '" . htmlspecialchars($item['part_name']) . "' sudah terdaftar di database!";
                    $messageType = 'warning';
                } else {
                    $chkStmt->close();

                    if ($editingId > 0) {
                        $stmt = $conn->prepare(
                            'UPDATE tbl_packing_cost SET part_name = ?, item_category = ?, weight_gram = ?, qty_per_kg = ?, volume_cm3 = ?, unit = ?, purchase_price = ?, item_price = ?, max_capacity_gram = ?, size_detail = ?, revision_no = ?, effective_date = ?, status = ? WHERE id = ?'
                        );
                        if ($stmt) {
                            $types = str_repeat('s', 13) . 'i';
                            $stmt->bind_param(
                                $types,
                                $item['part_name'],
                                $item['item_category'],
                                $item['weight_gram'],
                                $item['qty_per_kg'],
                                $item['volume_cm3'],
                                $item['unit'],
                                $item['purchase_price'],
                                $item['item_price'],
                                $item['max_capacity_gram'],
                                $item['size_detail'],
                                $item['revision_no'],
                                $item['effective_date'],
                                $item['status'],
                                $editingId
                            );
                            if ($stmt->execute()) {
                                $message = 'Data packing cost berhasil diperbarui.';
                                $messageType = 'success';
                                write_audit_log('tbl_packing_cost', $editingId, 'update', 'part_name', $item['part_name'], "Memperbarui harga/detail packing: " . $item['part_name']);
                            } else {
                                $message = 'Gagal memperbarui data: ' . $stmt->error;
                                $messageType = 'error';
                            }
                            $stmt->close();
                        }
                    } else {
                        $stmt = $conn->prepare(
                            'INSERT INTO tbl_packing_cost (part_name, item_category, weight_gram, qty_per_kg, volume_cm3, unit, purchase_price, item_price, max_capacity_gram, size_detail, revision_no, effective_date, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                        );
                        if ($stmt) {
                            $createdBy = $_SESSION['user_id'];
                            $types = str_repeat('s', 13) . 'i';
                            $stmt->bind_param(
                                $types,
                                $item['part_name'],
                                $item['item_category'],
                                $item['weight_gram'],
                                $item['qty_per_kg'],
                                $item['volume_cm3'],
                                $item['unit'],
                                $item['purchase_price'],
                                $item['item_price'],
                                $item['max_capacity_gram'],
                                $item['size_detail'],
                                $item['revision_no'],
                                $item['effective_date'],
                                $item['status'],
                                $createdBy
                            );
                            if ($stmt->execute()) {
                                write_audit_log('tbl_packing_cost', $conn->insert_id, 'insert', 'part_name', $item['part_name'], "Menambahkan harga/detail packing: " . $item['part_name']);
                                $message = 'Packing cost baru berhasil ditambahkan.';
                                $messageType = 'success';
                                $item = [
                                    'part_name' => '',
                                    'item_category' => '',
                                    'weight_gram' => '',
                                    'qty_per_kg' => '',
                                    'volume_cm3' => '',
                                    'unit' => '',
                                    'purchase_price' => '',
                                    'item_price' => '',
                                    'max_capacity_gram' => '',
                                    'size_detail' => '',
                                    'revision_no' => 0,
                                    'effective_date' => date('Y-m-d'),
                                    'status' => 'active'
                                ];
                                $editingId = 0;
                            } else {
                                $message = 'Gagal menyimpan packing cost: ' . $stmt->error;
                                $messageType = 'error';
                            }
                            $stmt->close();
                        }
                    }
                }
            } catch (Exception $e) {
                $message = 'Terjadi kesalahan database: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } 
    elseif ($action === 'import') {
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $message = 'Unggah file gagal. Pastikan file dipilih dan tidak rusak.';
            $messageType = 'error';
        } else {
            $fileTmp = $_FILES['import_file']['tmp_name'];
            $extension = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));

            $expectedHeaders = ['part_name', 'item_category', 'weight_gram', 'qty_per_kg', 'volume_cm3', 'unit', 'purchase_price', 'item_price', 'max_capacity_gram', 'size_detail', 'revision_no', 'effective_date', 'status'];
            $importRows = [];

            if (in_array($extension, ['xlsx', 'xls'], true)) {
                if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
                    $message = 'Untuk import XLSX, pasang library PhpSpreadsheet terlebih dahulu.';
                    $messageType = 'error';
                } else {
                    try {
                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileTmp);
                        $sheet = $spreadsheet->getActiveSheet();
                        $headerRow = $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . '1', NULL, TRUE, FALSE)[0];
                        $header = array_map(fn($c) => strtolower(trim((string)$c)), $headerRow);

                        if (array_diff($expectedHeaders, $header)) {
                            $message = 'Header XLSX tidak sesuai format standar.';
                            $messageType = 'error';
                        } else {
                            for ($r = 2; $r <= $sheet->getHighestRow(); $r++) {
                                $rowArr = $sheet->rangeToArray('A'.$r.':'.$sheet->getHighestColumn().$r, NULL, TRUE, FALSE)[0];
                                $rowAssoc = array_combine($header, $rowArr);
                                if ($rowAssoc !== false) {
                                    $importRows[] = $rowAssoc;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $message = 'Gagal membaca file XLSX: ' . $e->getMessage();
                        $messageType = 'error';
                    }
                }
            } elseif ($extension === 'csv') {
                $handle = fopen($fileTmp, 'r');
                if ($handle === false) {
                    $message = 'Gagal membuka file CSV.';
                    $messageType = 'error';
                } else {
                    $header = fgetcsv($handle, 0, ',');
                    if ($header === false) {
                        $message = 'File CSV kosong atau tidak valid.';
                        $messageType = 'error';
                        fclose($handle);
                    } else {
                        $header = array_map(fn($col) => strtolower(trim($col)), $header);
                        if (array_diff($expectedHeaders, $header)) {
                            $message = 'Header CSV tidak sesuai format standar.';
                            $messageType = 'error';
                            fclose($handle);
                        } else {
                            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                                if (count(array_filter($row, fn($value) => trim((string) $value) !== '')) === 0) {
                                    continue;
                                }
                                $row = array_pad($row, count($header), '');
                                $record = array_combine($header, $row);
                                if ($record) {
                                    $importRows[] = $record;
                                }
                            }
                            fclose($handle);
                        }
                    }
                }
            } else {
                $message = 'Format file tidak didukung. Harap gunakan file .CSV atau .XLSX';
                $messageType = 'error';
            }

            if (empty($message) && !empty($importRows)) {
                $insertStmt = $conn->prepare(
                    'INSERT INTO tbl_packing_cost (part_name, item_category, weight_gram, qty_per_kg, volume_cm3, unit, purchase_price, item_price, max_capacity_gram, size_detail, revision_no, effective_date, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );
                
                $chkStmt = $conn->prepare("SELECT id FROM tbl_packing_cost WHERE LOWER(part_name) = LOWER(?) LIMIT 1");

                if (!$insertStmt || !$chkStmt) {
                    $message = 'Gagal menyiapkan query import database.';
                    $messageType = 'error';
                } else {
                    $createdBy = $_SESSION['user_id'];
                    $types = str_repeat('s', 13) . 'i';
                    $rowsImported = 0;
                    $skippedCount = 0;
                    $skippedNames = [];

                    foreach ($importRows as $record) {
                        $partName = trim($record['part_name']);
                        if ($partName === '') {
                            continue;
                        }

                        $chkStmt->bind_param("s", $partName);
                        $chkStmt->execute();
                        if ($chkStmt->get_result()->num_rows > 0) {
                            $skippedCount++;
                            if (count($skippedNames) < 5) {
                                $skippedNames[] = $partName;
                            }
                            continue;
                        }

                        $itemCategory = trim($record['item_category']);
                        $weightGram = trim($record['weight_gram']) !== '' ? trim($record['weight_gram']) : '0';
                        $qtyPerKg = trim($record['qty_per_kg']) !== '' ? trim($record['qty_per_kg']) : '0';
                        $volumeCm3 = trim($record['volume_cm3']) !== '' ? trim($record['volume_cm3']) : '0';
                        $unit = trim($record['unit']);
                        $purchasePrice = trim($record['purchase_price']) !== '' ? trim($record['purchase_price']) : '0';
                        $itemPrice = trim($record['item_price']) !== '' ? trim($record['item_price']) : '0';
                        $maxCapacityGram = trim($record['max_capacity_gram']) !== '' ? trim($record['max_capacity_gram']) : '0';
                        $sizeDetail = trim($record['size_detail']);
                        $revisionNo = intval(trim($record['revision_no']) ?? 0);
                        $effectiveDate = trim($record['effective_date']);
                        if ($effectiveDate === '' || strtotime($effectiveDate) === false) {
                            $effectiveDate = date('Y-m-d');
                        }
                        $status = in_array(trim($record['status']), ['active', 'inactive'], true) ? trim($record['status']) : 'active';

                        $insertStmt->bind_param(
                            $types,
                            $partName,
                            $itemCategory,
                            $weightGram,
                            $qtyPerKg,
                            $volumeCm3,
                            $unit,
                            $purchasePrice,
                            $itemPrice,
                            $maxCapacityGram,
                            $sizeDetail,
                            $revisionNo,
                            $effectiveDate,
                            $status,
                            $createdBy
                        );

                        if ($insertStmt->execute()) {
                            $rowsImported++;
                        }
                    }

                    $insertStmt->close();
                    $chkStmt->close();

                    $message = "Import Selesai.<br><b>Berhasil:</b> $rowsImported data.";
                    if ($skippedCount > 0) {
                        $moreMsg = count($skippedNames) < $skippedCount ? " dan lainnya..." : "";
                        $message .= "<br><span style='color:#e11d48;'><b>Dilewati (Duplikat):</b> $skippedCount data (" . implode(', ', $skippedNames) . "$moreMsg)</span>";
                        $messageType = 'warning';
                    } else {
                        $messageType = 'success';
                    }

                    if ($rowsImported > 0) {
                        write_audit_log('tbl_packing_cost', 0, 'insert', 'excel_csv_import', null, "Berhasil mengunduh/import massal " . $rowsImported . " item data packing cost melalui file.");
                    }
                }
            }
        }
    } 
    elseif ($action === 'delete' && $editingId > 0) {
        try {
            $deletedName = "ID: " . $editingId;
            $check = $conn->query("SELECT part_name FROM tbl_packing_cost WHERE id = $editingId");
            if ($check && $row = $check->fetch_assoc()) {
                $deletedName = $row['part_name'];
            }

            $stmt = $conn->prepare('DELETE FROM tbl_packing_cost WHERE id = ?');
            if ($stmt) {
                $stmt->bind_param('i', $editingId);
                if ($stmt->execute()) {
                    write_audit_log('tbl_packing_cost', $editingId, 'delete', 'part_name', $deletedName, "Menghapus harga/detail packing: " . $deletedName);
                    $message = 'Item Packing Cost "' . htmlspecialchars($deletedName) . '" berhasil dihapus.';
                    $messageType = 'success';
                } else {
                    $message = 'Gagal menghapus packing cost: ' . $stmt->error;
                    $messageType = 'error';
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $message = 'Gagal menghapus! Data mungkin sedang digunakan pada transaksi/quotation lain.';
            $messageType = 'error';
        }
        $editingId = 0;
    }
}

if (!empty($_GET['edit_id'])) {
    $editingId = intval($_GET['edit_id']);
    $stmt = $conn->prepare('SELECT * FROM tbl_packing_cost WHERE id = ? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('i', $editingId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $item = [
                'part_name' => $row['part_name'],
                'item_category' => $row['item_category'],
                'weight_gram' => $row['weight_gram'],
                'qty_per_kg' => $row['qty_per_kg'],
                'volume_cm3' => $row['volume_cm3'],
                'unit' => $row['unit'],
                'purchase_price' => $row['purchase_price'],
                'item_price' => $row['item_price'],
                'max_capacity_gram' => $row['max_capacity_gram'],
                'size_detail' => $row['size_detail'],
                'revision_no' => $row['revision_no'],
                'effective_date' => $row['effective_date'] ?? date('Y-m-d'),
                'status' => $row['status'] ?? 'active'
            ];
        }
        $stmt->close();
    }
}

$items = [];
$itemRes = $conn->query('SELECT * FROM tbl_packing_cost ORDER BY item_category, part_name');
if ($itemRes) {
    while ($row = $itemRes->fetch_assoc()) {
        $items[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Packing Cost</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="header.css?v=1">
    <link rel="stylesheet" href="sidebar.css?v=1">
    <link rel="stylesheet" href="packingcost.css?v=1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="page-shell">
            <div class="page-card">
                <h1>Master Packing Cost</h1>
                <p>Kelola item packing cost yang digunakan pada perhitungan quotation.</p>

                <div class="import-section">
                    <form method="post" action="packing_cost.php" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="import">
                        <div class="import-grid">
                            <div class="form-group">
                                <label>Import CSV / XLSX Packing Cost</label>
                                <input type="file" name="import_file" accept=".csv,.xlsx" required>
                            </div>
                            <div class="form-group" style="margin-bottom:0;">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-secondary" style="min-width:180px;">Upload File</button>
                            </div>
                        </div>
                        <p style="font-size:.95rem; color:#475569; margin-top:8px; margin-bottom:0;">Format: part_name,item_category,weight_gram,qty_per_kg,volume_cm3,unit,purchase_price,item_price,max_capacity_gram,size_detail,revision_no,effective_date,status (CSV atau XLSX)</p>
                    </form>
                </div>

                <form method="post" action="packing_cost.php">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="item_id" value="<?= htmlspecialchars($editingId) ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Nama Item</label>
                            <input type="text" name="part_name" value="<?= htmlspecialchars($item['part_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Kategori</label>
                            <input type="text" name="item_category" value="<?= htmlspecialchars($item['item_category']) ?>" placeholder="Contoh: PLASTIK, BOX KARTON, PROTECTION">
                        </div>
                        <div class="form-group">
                            <label>Size / Keterangan</label>
                            <input type="text" name="size_detail" value="<?= htmlspecialchars($item['size_detail']) ?>" placeholder="Contoh: 10x20x...">
                        </div>
                        <div class="form-group">
                            <label>Berat (gram)</label>
                            <input type="number" step="0.0001" name="weight_gram" value="<?= htmlspecialchars($item['weight_gram']) ?>" placeholder="0.0000">
                        </div>
                        <div class="form-group">
                            <label>Jumlah / Kg (lembar)</label>
                            <input type="number" step="0.0001" name="qty_per_kg" value="<?= htmlspecialchars($item['qty_per_kg']) ?>" placeholder="0.0000">
                        </div>
                        <div class="form-group">
                            <label>Volume (cm3)</label>
                            <input type="number" step="0.0001" name="volume_cm3" value="<?= htmlspecialchars($item['volume_cm3']) ?>" placeholder="0.0000">
                        </div>
                        <div class="form-group">
                            <label>Satuan</label>
                            <input type="text" name="unit" value="<?= htmlspecialchars($item['unit']) ?>" placeholder="Contoh: PCS, SET, KG">
                        </div>
                        <div class="form-group">
                            <label>Harga pembelian (Rp)</label>
                            <input type="number" step="0.01" name="purchase_price" value="<?= htmlspecialchars($item['purchase_price']) ?>" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label>Harga / lembar (Rp)</label>
                            <input type="number" step="0.01" name="item_price" value="<?= htmlspecialchars($item['item_price']) ?>" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label>Daya Tampung Maksimal (gr)</label>
                            <input type="number" step="0.0001" name="max_capacity_gram" value="<?= htmlspecialchars($item['max_capacity_gram']) ?>" placeholder="0.0000">
                        </div>
                        <div class="form-group">
                            <label>Revision</label>
                            <input type="number" name="revision_no" value="<?= htmlspecialchars($item['revision_no']) ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label>Tanggal Efektif</label>
                            <input type="date" name="effective_date" value="<?= htmlspecialchars($item['effective_date']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="active" <?= $item['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $item['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?= $editingId ? 'Perbarui Item' : 'Tambah Item' ?></button>
                        
                        <?php if ($editingId): ?>
                            <button type="button" 
                                    class="btn btn-danger" 
                                    onclick="confirmDeleteItem(<?= $editingId ?>, '<?= htmlspecialchars($item['part_name'], ENT_QUOTES) ?>')">
                                Hapus Item
                            </button>
                            <a href="packing_cost.php" class="btn btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="top-table-controls">
                    <div class="search-container" style="flex:1; max-width:320px;">
                        <input type="text" id="search_packing_cost" placeholder="Cari nama item, kategori, atau size..." style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                    </div>
                   <a href="packing_cost.php?action=export_excel" class="btn btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px; font-weight:600; background-color:#10b981; color:#fff; border:none; padding:10px 16px;">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>
                        Export Excel
                    </a>
                </div>

                <form id="bulkActionForm" method="post" action="packing_cost.php">
                    <input type="hidden" name="action" id="bulk_action_input" value="">
                    <input type="hidden" name="bulk_status" id="bulk_status_input" value="">

                    <div class="bulk-actions-bar">
                        <span id="selected_count_label" style="font-weight:600; font-size:0.9rem; color:#334155;">0 item dipilih</span>
                        <div class="bulk-buttons">
                            <button type="button" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" onclick="submitBulkUpdate('active')">Set Active</button>
                            <button type="button" class="btn btn-secondary" style="padding: 6px 12px; font-size: 0.85rem;" onclick="submitBulkUpdate('inactive')">Set Inactive</button>
                            <button type="button" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;" onclick="submitBulkDelete()">Hapus Terpilih</button>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table id="table_packing_cost_master">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;">
                                        <input type="checkbox" id="check_all_items" style="cursor:pointer; width:16px; height:16px;">
                                    </th>
                                    <th>No</th>
                                    <th class="sortable" onclick="sortTable(2)">Nama Item <span class="sort-icon">▲▼</span></th>
                                    <th class="sortable" onclick="sortTable(3)">Kategori <span class="sort-icon">▲▼</span></th>
                                    <th class="sortable" onclick="sortTable(4)">Size <span class="sort-icon">▲▼</span></th>
                                    <th class="sortable" onclick="sortTable(5, true)">Berat(g) <span class="sort-icon">▲▼</span></th>
                                    <th class="sortable" onclick="sortTable(6, true)">Jumlah/Kg <span class="sort-icon">▲▼</span></th>
                                    <th class="sortable" onclick="sortTable(7, true)">Volume(cm3) <span class="sort-icon">▲▼</span></th>
                                    <th class="sortable" onclick="sortTable(8, true)">Harga Beli <span class="sort-icon">▲▼</span></th>
                                    <th class="sortable" onclick="sortTable(9, true)">Harga/Lembar <span class="sort-icon">▲▼</span></th>
                                    <th class="sortable" onclick="sortTable(10, true)">Daya Maks <span class="sort-icon">▲▼</span></th>
                                    <th class="sortable" onclick="sortTable(11)">Status <span class="sort-icon">▲▼</span></th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($items)): ?>
                                    <?php foreach ($items as $index => $row): ?>
                                        <?php 
                                        $qty_val = floatval($row['qty_per_kg']); 
                                        $final_qty = (($qty_val - floor($qty_val)) > 0.5) ? ceil($qty_val) : floor($qty_val);

                                        $price_val = floatval($row['item_price']); 
                                        $final_price = (($price_val - floor($price_val)) > 0.5) ? ceil($price_val) : floor($price_val);

                                        $max_val = floatval($row['max_capacity_gram']);
                                        $final_max = (($max_val - floor($max_val)) > 0.5) ? ceil($max_val) : floor($max_val);
                                        ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <input type="checkbox" name="selected_ids[]" value="<?= $row['id'] ?>" class="item-checkbox" style="cursor:pointer; width:16px; height:16px;">
                                            </td>
                                            <td class="row-number"><?= $index + 1 ?></td>
                                            <td><?= htmlspecialchars($row['part_name']) ?></td>
                                            <td><?= htmlspecialchars($row['item_category']) ?></td>
                                            <td><?= htmlspecialchars($row['size_detail']) ?></td>
                                            <td><?= htmlspecialchars(number_format($row['weight_gram'], 4, ',', '.')) ?></td>
                                            <td><?= htmlspecialchars(number_format($final_qty, 0, ',', '.')) ?></td>
                                            <td><?= htmlspecialchars(number_format($row['volume_cm3'], 4, ',', '.')) ?></td>
                                            <td><?= htmlspecialchars(number_format($row['purchase_price'], 2, ',', '.')) ?></td>
                                            <td><?= htmlspecialchars(number_format($final_price, 0, ',', '.')) ?></td>
                                            <td><?= htmlspecialchars(number_format($final_max, 0, ',', '.')) ?></td>
                                            <td><span class="status-chip status-<?= $row['status'] === 'active' ? 'active' : 'inactive' ?>"><?= htmlspecialchars(ucfirst($row['status'])) ?></span></td>
                                            <td class="action-links">
                                                <a href="packing_cost.php?edit_id=<?= $row['id'] ?>">Edit</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="no-data-row"><td colspan="13" class="text-center">Belum ada data packing cost.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
<?php if ($message): ?>
    Swal.fire({
        icon: '<?= $messageType ?>',
        title: '<?= $messageType === "success" ? "Berhasil!" : ($messageType === "warning" ? "Perhatian!" : "Terjadi Kesalahan!") ?>',
        html: '<?= addslashes($message) ?>',
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Tutup'
    });

    const url = new URL(window.location);
    url.searchParams.delete('edit_id');
    url.searchParams.delete('action');
    window.history.replaceState({}, document.title, url);
<?php endif; ?>

function confirmDeleteItem(id, itemName) {
    Swal.fire({
        title: 'Hapus Item Packing?',
        html: `Apakah Anda yakin ingin menghapus item <b>"${itemName}"</b>?<br><small style="color:#ef4444;">Tindakan ini tidak dapat dibatalkan.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `packing_cost.php?item_id=${id}&action=delete`;
        }
    });
}

let sortDirections = {};
function sortTable(columnIndex, isNumeric = false) {
    const table = document.getElementById("table_packing_cost_master");
    const tbody = table.querySelector("tbody");
    const rows = Array.from(tbody.querySelectorAll("tr:not(.no-data-row)"));
    
    sortDirections[columnIndex] = !sortDirections[columnIndex];
    const isAscending = sortDirections[columnIndex];

    rows.sort((rowA, rowB) => {
        let cellA = rowA.cells[columnIndex].textContent.trim();
        let cellB = rowB.cells[columnIndex].textContent.trim();

        if (isNumeric) {
            cellA = parseFloat(cellA.replace(/\./g, '').replace(',', '.')) || 0;
            cellB = parseFloat(cellB.replace(/\./g, '').replace(',', '.')) || 0;
            return isAscending ? cellA - cellB : cellB - cellA;
        } else {
            return isAscending 
                ? cellA.localeCompare(cellB, undefined, {sensitivity: 'base'})
                : cellB.localeCompare(cellA, undefined, {sensitivity: 'base'});
        }
    });

    rows.forEach((row, index) => {
        tbody.appendChild(row);
        const numCell = row.querySelector('.row-number');
        if (numCell) numCell.textContent = index + 1;
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const checkAll = document.getElementById('check_all_items');
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const countLabel = document.getElementById('selected_count_label');

    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
        if (countLabel) {
            countLabel.textContent = `${checkedCount} item dipilih`;
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            const visibleCheckboxes = document.querySelectorAll('#table_packing_cost_master tbody tr:not([style*="display: none"]) .item-checkbox');
            visibleCheckboxes.forEach(cb => cb.checked = checkAll.checked);
            updateSelectedCount();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateSelectedCount();
            if (!this.checked && checkAll) {
                checkAll.checked = false;
            }
        });
    });

    const searchInput = document.getElementById('search_packing_cost');
    const tableBody = document.querySelector('#table_packing_cost_master tbody');
    
    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function() {
            const filterValue = searchInput.value.toLowerCase().trim();
            const rows = tableBody.querySelectorAll('tr:not(.no-data-row)');
            let visibleCount = 0;

            rows.forEach(row => {
                const partName = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';
                const category = row.cells[3] ? row.cells[3].textContent.toLowerCase() : '';
                const sizeDetail = row.cells[4] ? row.cells[4].textContent.toLowerCase() : '';

                if (partName.includes(filterValue) || category.includes(filterValue) || sizeDetail.includes(filterValue)) {
                    row.style.display = "";
                    visibleCount++;
                    
                    const numberCell = row.querySelector('.row-number');
                    if (numberCell) {
                        numberCell.textContent = visibleCount;
                    }
                } else {
                    row.style.display = "none";
                }
            });
        });
    }
});

function submitBulkDelete() {
    const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
    if (checkedCount === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Ada Item Pilih',
            text: 'Silakan centang item yang ingin dihapus terlebih dahulu.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    Swal.fire({
        title: 'Hapus Massal?',
        html: `Apakah Anda yakin ingin menghapus <b>${checkedCount} item</b> terpilih?<br><small style="color:#ef4444;">Data yang dihapus tidak bisa dikembalikan.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('bulk_action_input').value = 'bulk_delete';
            document.getElementById('bulkActionForm').submit();
        }
    });
}

function submitBulkUpdate(newStatus) {
    const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
    if (checkedCount === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Ada Item Pilih',
            text: 'Silakan centang item yang ingin diubah statusnya terlebih dahulu.',
            confirmButtonColor: '#2563eb'
        });
        return;
    }

    document.getElementById('bulk_action_input').value = 'bulk_update';
    document.getElementById('bulk_status_input').value = newStatus;
    document.getElementById('bulkActionForm').submit();
}
</script>

<?php
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
</body>
</html>
