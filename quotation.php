<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'auth.php';
include 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}


$loggedIn = !empty($_SESSION['user_id']);
$fullname = $_SESSION['fullname'] ?? 'Guest';
$role     = $_SESSION['role'] ?? 'User';

$message     = '';
$messageType = 'success';

$editingQuotationNo = '';
$editingCustomerId  = 0;
$folderName         = '';
$selectedDraftId    = 0;
$selectedMmpId      = 0; 
$savedRows          = [];

$quotation = [
    'no'       => 'QT-' . date('Ymd') . '-',
    'date'     => date('Y-m-d'),
    'validity' => '30',
    'currency' => 'USD',
    'customer' => [
        'name'    => '',
        'pic'     => '',
        'phone'   => '',
        'email'   => '',
        'address' => ''
    ],
    'notes'    => ''
];

$mmpData  = [];
$mmpQuery = "SELECT 
                h.mmp_id AS mmp_id, 
                h.keterangan_umum AS judul_mmp, 
                d.detail_id,  
                d.part_number,
                d.part_name,
                d.mat_quotation, 
                d.mat_aktual,
                d.harga_mkr,
                d.harga_pch,
                COALESCE(d.berat_part, 0) AS weight_per_pcs,
                COALESCE(d.harga_pch, 0) AS idr_price_kg
             FROM tbl_mmp_head h
             LEFT JOIN tbl_mmp_det d ON h.mmp_id = d.mmp_id
             ORDER BY h.mmp_id DESC";

if ($mmpRes = $conn->query($mmpQuery)) {
    while ($row = $mmpRes->fetch_assoc()) {
        $mmpData[] = $row;
    }
    $mmpRes->free();
}

if (!empty($_GET['quotation_no'])) {
    $editingQuotationNo = trim($_GET['quotation_no']);
    $stmt = $conn->prepare(
        "SELECT q.*, c.customer_name, c.address AS customer_address, c.pic AS customer_pic, c.phone AS customer_phone, c.email AS customer_email, c.id AS customer_id
        FROM tbl_quotation q
        LEFT JOIN tbl_customer c ON q.customer_id = c.id
        WHERE q.quotation_no = ?
        ORDER BY q.id ASC"
    );

    if ($stmt) {
        $stmt->bind_param('s', $editingQuotationNo);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (empty($savedRows)) {
                $quotation['no']       = $row['quotation_no'];
                $quotation['date']     = $row['quotation_date'] ?? $quotation['date'];
                $quotation['validity'] = $row['validity_days'] ?? $quotation['validity'];
                $quotation['currency'] = $row['currency'] ?? $quotation['currency'];
                $folderName            = $row['folder_name'] ?? '';
                $selectedDraftId       = intval($row['rate_draft_id'] ?? 0); 
                $selectedMmpId         = intval($row['mmp_id'] ?? 0);
                
                if (!empty($row['customer_id'])) {
                    $editingCustomerId         = (int)$row['customer_id'];
                    $quotation['customer']['name']    = $row['customer_name'] ?? '';
                    $quotation['customer']['pic']     = $row['customer_pic'] ?? '';
                    $quotation['customer']['phone']   = $row['customer_phone'] ?? '';
                    $quotation['customer']['email']   = $row['customer_email'] ?? '';
                    $quotation['customer']['address'] = $row['customer_address'] ?? '';
                }
                $quotation['notes'] = $row['quotation_note'] ?? '';
            }
            $savedRows[] = [
                'type'                  => $row['type'] ?? 'internal',
                'part_number'           => $row['part_number'] ?? '',
                'packing_standard_id'   => $row['packing_standard_id'] ?? 0,
                'part_name'             => $row['part_name'] ?? '',
                'material_spec'         => $row['material_spec'] ?? '',
                'basic_price'           => $row['basic_price'] ?? 0,
                'currency'              => $row['currency'] ?? 'USD',
                'exchange_rate'         => $row['exchange_rate_value'] ?? 0,
                'part_weight'           => $row['part_weight'] ?? 0,
                'runner_weight'         => $row['runner_weight'] ?? 0,
                'pigmen_cost'           => $row['pigmen_cost'] ?? 0,
                'weight_per_pcs'        => $row['weight_per_pcs'] ?? 0,
                'idr_price_kg'          => $row['idr_price_kg'] ?? 0,
                'material_price'        => $row['material_price'] ?? 0,
                'cycle_time'            => $row['cycle_time'] ?? 0,
                'cavity'                => $row['cavity'] ?? 1,
                'mc_ton'                => $row['mc_ton'] ?? 0,
                'qtyForecastMonth'      => $row['qty_forecast_month'] ?? 0,
                'purging_ori_kg'        => $row['purging_ori_kg'] ?? 0,
                'purging_cellpurg_kg'   => $row['purging_cellpurg_kg'] ?? 0,
                'cellpurge_price'       => $row['cellpurge_price'] ?? 0,
                'purging'               => $row['purging'] ?? 0,
                'dandori_minutes'       => $row['dandori_minutes'] ?? 0,
                'dandori'               => $row['dandori'] ?? 0,
                'rate_hour'             => $row['rate_hour'] ?? 0, 
                'process_cost'          => $row['process_cost'] ?? 0,
                'reject_rate'           => $row['reject_percent'] ?? 0,
                'rejection_rate'        => $row['rejection_cost'] ?? 0,
                'other_process_type'    => $row['other_process_type'] ?? '',
                'ct_other'              => $row['ct_other'] ?? 0,
                'other_process_cost'    => $row['other_process_cost'] ?? 0,
                'packing'               => $row['packing_cost'] ?? 0,
                'transport'             => $row['transport_cost'] ?? 0,
                'cogs'                  => $row['cogs'] ?? 0,
                'oh_percent'            => $row['oh_percent'] ?? 0,
                'oh_profit'             => $row['oh_profit'] ?? 0,
                'mold_price'            => $row['mold_price'] ?? 0,
                'depreciation_years'    => $row['depreciation_years'] ?? 0,
                'mold_depreciation_pcs' => $row['mold_depreciation_pcs'] ?? 0,
                'mold_cost_month'       => $row['mold_cost_month'] ?? 0,
                'mold_mtn'              => $row['mold_maintenance'] ?? 0,
                'total'                 => $row['selling_price'] ?? 0,
                'lumpsum_price'         => $row['lumpsum_price'] ?? 0,
                'cr_mode'               => $row['cr_mode'] ?? 'with_bl',
                'cr_base_val'           => $row['cr_base_val'] ?? 0,
                'cr_lta_years_count'    => $row['cr_lta_years_count'] ?? 3,
                'cr_lta_pct_list'       => json_decode($row['cr_lta_pct_json'] ?? '[]', true),
                'cr_lta_res_list'       => json_decode($row['cr_lta_res_json'] ?? '[]', true),
                'cr_pct_bl'             => $row['cr_pct_bl'] ?? 0,
                'cr_final_cost'         => $row['cr_final_cost'] ?? 0,
            ];
        }
        $stmt->close();
    }
}

$packingStandardsMap = [];
if (!empty($editingCustomerId)) {
    $stmtPs = $conn->prepare("SELECT id, part_number FROM tbl_packing_standard WHERE customer_id = ? AND status = 'active'");
    if ($stmtPs) {
        $stmtPs->bind_param('i', $editingCustomerId);
        $stmtPs->execute();
        $psRes = $stmtPs->get_result();
        while ($row = $psRes->fetch_assoc()) {
            $packingStandardsMap[$row['part_number']] = $row;
        }
        $stmtPs->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_quotation']) && $_POST['save_quotation'] == '1') {
    $fixedQuotationNo = trim($_POST['quotation_no'] ?? '');
    if (empty($fixedQuotationNo)) {
        $fixedQuotationNo = 'QT-' . date('Ymd') . '-' . sprintf('%04d', rand(1, 9999)); 
    }
    
    $quotation['no']               = $fixedQuotationNo; 
    $folderName                    = trim($_POST['folder_name'] ?? $folderName);
    $quotation['date']             = trim($_POST['quotation_date'] ?? $quotation['date']);
    $quotation['validity']         = trim($_POST['quotation_validity'] ?? $quotation['validity']);
    $quotation['currency']         = trim($_POST['quotation_currency'] ?? $quotation['currency']);
    $quotation['customer']['name']    = trim($_POST['customer_name'] ?? '');
    $quotation['customer']['pic']     = trim($_POST['customer_pic'] ?? '');
    $quotation['customer']['phone']   = trim($_POST['customer_phone'] ?? '');
    $quotation['customer']['email']   = trim($_POST['customer_email'] ?? '');
    $quotation['customer']['address'] = trim($_POST['customer_address'] ?? '');
    $quotation['notes']            = trim($_POST['quotation_notes'] ?? ''); 

    $selectedDraftId = intval($_POST['rate_draft_id'] ?? 0);
    $selectedMmpId   = intval($_POST['mmp_id'] ?? 0); 

    $customerName    = $quotation['customer']['name'];
    $customerPic     = $quotation['customer']['pic'];
    $customerPhone   = $quotation['customer']['phone'];
    $customerEmail   = $quotation['customer']['email'];
    $customerAddress = $quotation['customer']['address'];
    $paymentTerm     = '';
    $currency        = $quotation['currency'];
    
    $existingQuotationNo = trim($_POST['existing_quotation_no'] ?? '');
    $existingCustomerId  = intval($_POST['existing_customer_id'] ?? 0);
    $selectedCustomerId  = intval($_POST['customer_id'] ?? 0);

    $quotationNoExists = false;
    $stmtQuotationNo = $conn->prepare("SELECT id FROM tbl_quotation WHERE quotation_no = ? LIMIT 1");
    if ($stmtQuotationNo) {
        $stmtQuotationNo->bind_param('s', $fixedQuotationNo);
        $stmtQuotationNo->execute();
        $quotationNoRow = $stmtQuotationNo->get_result()->fetch_assoc();
        $quotationNoExists = $quotationNoRow && !($existingQuotationNo !== '' && $fixedQuotationNo === $existingQuotationNo);
        $stmtQuotationNo->close();
    }

    $rowsJson = $_POST['quotation_rows'] ?? '[]';
    $rows     = json_decode($rowsJson, true);

    if ($quotationNoExists) {
        $message     = 'Gagal menyimpan! Quotation No. sudah digunakan. Silakan gunakan nomor yang berbeda.';
        $messageType = 'error';
    } elseif ($customerName === '') {
        $message     = 'Nama customer tidak boleh kosong.';
        $messageType = 'error';
    } elseif ($selectedDraftId <= 0) {
        $message     = 'Silakan pilih Tahun Masspro terlebih dahulu!';
        $messageType = 'error';
    } elseif (!is_array($rows) || count($rows) === 0) {
        $message     = 'Data baris quotation kosong atau format tidak valid.';
        $messageType = 'error';
    } else {
        $conn->begin_transaction();
        $customerId = 0;

        try {
            if ($existingQuotationNo !== '' && $existingCustomerId > 0) {
                $stmt = $conn->prepare("UPDATE tbl_customer SET customer_name = ?, address = ?, pic = ?, phone = ?, email = ?, currency = ?, payment_term = ?, updated_at = NOW() WHERE id = ?");
                if (!$stmt) throw new Exception("Gagal prepare update customer: " . $conn->error);
                $stmt->bind_param('sssssssi', $customerName, $customerAddress, $customerPic, $customerPhone, $customerEmail, $currency, $paymentTerm, $existingCustomerId);
                $stmt->execute();
                $stmt->close();

                $customerId = $existingCustomerId;
                
                $stmtDelete = $conn->prepare("DELETE FROM tbl_quotation WHERE quotation_no = ?");
                if (!$stmtDelete) throw new Exception("Gagal prepare delete item lama: " . $conn->error);
                $stmtDelete->bind_param('s', $existingQuotationNo);
                $stmtDelete->execute();
                $stmtDelete->close();
            } elseif ($selectedCustomerId > 0) {
                $customerId = $selectedCustomerId;
            } else {
                $stmt = $conn->prepare("INSERT INTO tbl_customer (customer_name, address, pic, phone, email, currency, payment_term, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                if (!$stmt) throw new Exception("Gagal prepare insert customer: " . $conn->error);
                $stmt->bind_param('sssssss', $customerName, $customerAddress, $customerPic, $customerPhone, $customerEmail, $currency, $paymentTerm);
                $stmt->execute();
                $customerId = $stmt->insert_id;
                $stmt->close();
            }

            if ($customerId > 0) {
                $stmtQ = $conn->prepare(
                    "INSERT INTO tbl_quotation (
                        quotation_no, customer_id, rate_draft_id, mmp_id, type, part_number, packing_standard_id, part_name, material_spec, material_price,
                        basic_price, quotation_date, exchange_rate_value, weight_per_pcs, part_weight, runner_weight, pigmen_cost, idr_price_kg, cycle_time, cavity,
                        mc_ton, qty_forecast_month, purging_ori_kg, purging_cellpurg_kg, cellpurge_price, purging, dandori_minutes, dandori, rate_hour, process_cost,
                        reject_percent, rejection_cost, other_process_type, ct_other, other_process_cost, packing_cost, transport_cost, cogs, oh_percent, oh_profit,
                        mold_price, depreciation_years, mold_depreciation_pcs, mold_cost_month, mold_maintenance, selling_price, lumpsum_price, quotation_note, created_by,
                        cr_mode, cr_base_val, cr_lta_years, cr_lta_pct_json, cr_lta_res_json, cr_pct_bl, cr_final_cost
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?
                    )"
                );

                if (!$stmtQ) {
                    throw new Exception("Gagal prepare insert quotation: " . $conn->error);
                }

                $createdBy = intval($_SESSION['user_id'] ?? 0);

                foreach ($rows as $item) {
                    $data = $item['data'] ?? [];
                    $packing_standard_id = intval($data['packing_standard_id'] ?? 0);

                    if (!$packing_standard_id && !empty($data['part_number']) && isset($packingStandardsMap[$data['part_number']])) {
                        $packing_standard_id = $packingStandardsMap[$data['part_number']]['id'];
                    }

                    $itemType           = strval($item['type'] ?? 'internal');
                    $partNumberVal      = strval($data['part_number'] ?? '');
                    $partNameVal        = strval($data['part_name'] ?? '');
                    $materialSpec       = strval($data['material_spec'] ?? '');

                    $materialPrice      = floatval($data['material_price'] ?? 0);
                    $basicPrice         = floatval($data['basic_price'] ?? 0);
                    $quotationDate      = $quotation['date'];
                    $exchangeRate       = floatval($data['exchange_rate'] ?? 0);

                    $weightPcs          = floatval($data['weight_per_pcs'] ?? 0);
                    $partWeight         = floatval($data['part_weight'] ?? 0);
                    $runnerWeight       = floatval($data['runner_weight'] ?? 0);
                    $pigmenCost         = floatval($data['pigmen_cost'] ?? 0);
                    $idrPriceKg         = floatval($data['idr_price_kg'] ?? 0);
                    $cycleTime          = floatval($data['cycle_time'] ?? 0);
                    $cavity             = floatval($data['cavity'] ?? 1);
                    $mcTon              = floatval($data['mc_ton'] ?? 0);
                    $qtyForecastMonth   = floatval($data['qty_forecast_month'] ?? 0);

                    $purgingOriKg       = floatval($data['purging_ori_kg'] ?? 0);
                    $purgingCellKg      = floatval($data['purging_cellpurg_kg'] ?? 0);
                    $cellpurgePrice     = floatval($data['cellpurge_price'] ?? 0);
                    $purging            = floatval($data['purging'] ?? 0);

                    $dandoriMinutes     = intval($data['dandori_minutes'] ?? 0);
                    $dandori            = floatval($data['dandori'] ?? 0);

                    $rateHour           = floatval($data['rate_hour'] ?? 0);
                    $processCost        = floatval($data['process_cost'] ?? 0);
                    $rejectRate         = floatval($data['reject_rate'] ?? 0);
                    $rejectionRate      = floatval($data['rejection_rate'] ?? 0);

                    $otherProcessType   = strval($data['other_process_type'] ?? 'Finishing');
                    $ctOther            = floatval($data['ct_other'] ?? 0);
                    $otherProcessCost   = floatval($data['other_process_cost'] ?? 0);

                    $packing            = floatval($data['packing'] ?? 0);
                    $transport          = floatval($data['transport'] ?? 0);
                    $cogs               = floatval($data['cogs'] ?? 0);
                    $ohPercent          = floatval($data['oh_percent'] ?? 0);
                    $ohProfit           = floatval($data['oh_profit'] ?? 5);

                    $moldPrice          = floatval($data['mold_price'] ?? 0);
                    $depreciationYears  = intval($data['depreciation_years'] ?? 0);
                    $moldDepreciationPcs= floatval($data['mold_depreciation_pcs'] ?? 0);
                    $moldCostMonth      = floatval($data['mold_cost_month'] ?? $data['cost_per_month'] ?? 0);
                    $moldMtn            = strval($data['mold_mtn'] ?? '');
                    $total              = floatval($data['total'] ?? 0);
                    $lumpsumPrice       = floatval($data['lumpsum_price'] ?? 0);
                    $quotationNotes     = $quotation['notes'];

                    $crMode             = strval($data['cr_mode'] ?? 'with_bl');
                    $crBaseVal          = floatval($data['cr_base_val'] ?? 0);
                    $ltaYearsCount      = intval($_POST['ltaYearsCount'] ?? $data['cr_lta_years'] ?? 3);
                    $crLtaPctJson       = json_encode($data['cr_lta_pct_list'] ?? []);
                    $crLtaResJson       = json_encode($data['cr_lta_res_list'] ?? []);
                    $crPctBl            = floatval($data['cr_pct_bl'] ?? 0);
                    $crFinalCost        = floatval($data['cr_final_cost'] ?? 0);

                    $bindValues = [
                        $quotation['no'],
                        $customerId,
                        $selectedDraftId,
                        $selectedMmpId,
                        $itemType,
                        $partNumberVal,
                        $packing_standard_id,
                        $partNameVal,
                        $materialSpec,
                        $materialPrice,
                        $basicPrice,
                        $quotationDate,
                        $exchangeRate,
                        $weightPcs,
                        $partWeight,
                        $runnerWeight,
                        $pigmenCost,
                        $idrPriceKg,
                        $cycleTime,
                        $cavity,
                        $mcTon,
                        $qtyForecastMonth,
                        $purgingOriKg,
                        $purgingCellKg,
                        $cellpurgePrice,
                        $purging,
                        $dandoriMinutes,
                        $dandori,
                        $rateHour,
                        $processCost,
                        $rejectRate,
                        $rejectionRate,
                        $otherProcessType,
                        $ctOther,
                        $otherProcessCost,
                        $packing,
                        $transport,
                        $cogs,
                        $ohPercent,
                        $ohProfit,
                        $moldPrice,
                        $depreciationYears,
                        $moldDepreciationPcs,
                        $moldCostMonth,
                        $moldMtn,
                        $total,
                        $lumpsumPrice,
                        $quotationNotes,
                        $createdBy,
                        $crMode,
                        $crBaseVal,
                        $ltaYearsCount,
                        $crLtaPctJson,
                        $crLtaResJson,
                        $crPctBl,
                        $crFinalCost
                    ];

                    $bindTypes = '';
                    foreach ($bindValues as $val) {
                        if (is_int($val)) {
                            $bindTypes .= 'i';
                        } elseif (is_double($val) || is_float($val)) {
                            $bindTypes .= 'd';
                        } else {
                            $bindTypes .= 's';
                        }
                    }

                    $bindParams = [$stmtQ, $bindTypes];
                    foreach ($bindValues as &$bindValue) {
                        $bindParams[] = &$bindValue;
                    }
                    unset($bindValue);

                    call_user_func_array('mysqli_stmt_bind_param', $bindParams);

                    if (!$stmtQ->execute()) {
                        throw new Exception("Gagal menyimpan baris quotation: " . $stmtQ->error);
                    }
                }
                $stmtQ->close();

                write_audit_log(
                    'tbl_quotation',
                    0,
                    ($existingQuotationNo !== '' && $existingCustomerId > 0) ? 'update' : 'insert',
                    'quotation_no',
                    $fixedQuotationNo,
                    (($existingQuotationNo !== '' && $existingCustomerId > 0) ? 'Memperbarui' : 'Menyimpan')
                    . ' quotation No: ' . $fixedQuotationNo . ' untuk customer: ' . $customerName
                );
                
                $conn->commit(); 
                $messageType = 'success';
                $message     = ($existingQuotationNo !== '' && $existingCustomerId > 0)
                    ? 'Data quotation berhasil diperbarui.'
                    : 'Data quotation dan customer berhasil disimpan ke database.';
            }
        } catch (Exception $e) {
            $conn->rollback();
            $messageType = 'error';
            if ($e->getCode() == 1062) {
                $message = "Gagal Menyimpan! Quotation No. sudah digunakan. Silakan gunakan nomor yang berbeda.";
            } else {
                $message = "Gagal menyimpan quotation: " . $e->getMessage();
            }
        }
    }
}

$packingOptions = [];
$packRes = $conn->query("SELECT id, part_name, item_price FROM tbl_packing_cost WHERE status='active' ORDER BY part_name");
if ($packRes) {
    while ($row = $packRes->fetch_assoc()) {
        $packingOptions[] = $row;
    }
}

$allDraftsData = [];
$draftsQuery = $conn->query("
    SELECT d.id AS draft_id, d.draft_title, d.base_reference, d.manpower_rate_sec,
           m.tonnage, m.rate_per_second
    FROM tbl_rate_drafts d
    LEFT JOIN tbl_rate_master m ON d.id = m.draft_id
    ORDER BY d.id DESC
");

if ($draftsQuery) {
    while ($row = $draftsQuery->fetch_assoc()) {
        $dId = intval($row['draft_id']);
        if (!isset($allDraftsData[$dId])) {
            $allDraftsData[$dId] = [
                'id'                => $dId,
                'draft_title'       => $row['draft_title'],
                'base_reference'    => $row['base_reference'],
                'manpower_rate_sec' => floatval($row['manpower_rate_sec']),
                'matrix'            => []
            ];
        }
        if (!empty($row['tonnage'])) {
            $allDraftsData[$dId]['matrix'][$row['tonnage']] = floatval($row['rate_per_second']);
        }
    }
}

$customerList = [];
$custRes = $conn->query("
    SELECT c.id, c.customer_name, c.address, c.pic, c.phone, c.email 
    FROM tbl_customer c
    INNER JOIN (
        SELECT MAX(id) AS max_id 
        FROM tbl_customer 
        GROUP BY customer_name
    ) max_c ON c.id = max_c.max_id
    ORDER BY c.customer_name ASC
");

if ($custRes) {
    while ($row = $custRes->fetch_assoc()) {
        $customerList[] = $row;
    }
}

$purgingMasterData = [];
$qPurging = $conn->query("SELECT mc_ton_min, mc_ton_max, purging_ori_kg, purging_cellpurg_kg FROM tbl_purging_master WHERE status='active'");
if ($qPurging) {
    while ($r = $qPurging->fetch_assoc()) {
        $purgingMasterData[] = $r;
    }
}

$dandoriMasterData = [];
$qDandori = $conn->query("SELECT mc_ton_min, mc_ton_max, machine_type, dandori_minutes FROM tbl_dandori_master WHERE status='active'");
if ($qDandori) {
    while ($r = $qDandori->fetch_assoc()) {
        $dandoriMasterData[] = $r;
    }
}

$moldMasterData = [];
$qMold = $conn->query("SELECT id, category_type AS tipe_kategori, key_name, cost_per_month FROM tbl_mold_maintenance_master WHERE status='active'");
if ($qMold) {
    while ($r = $qMold->fetch_assoc()) {
        $moldMasterData[] = $r;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Format - QUOTATIONAPP</title>
    <link rel="stylesheet" href="quotation.css">
    <link rel="stylesheet" href="back-to-top.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php
$savedCrMode = 'with_bl';
if (!empty($savedRows) && is_array($savedRows)) {
    $firstRow = reset($savedRows);
    if (!empty($firstRow['cr_mode'])) {
        $savedCrMode = $firstRow['cr_mode'];
    }
}
?>
<div class="page-shell">
    <header class="topbar">
        <div>
            <h1>QUOTATIONAPP</h1>
            <p>Quotation Management System</p>
        </div>
        <div class="topbar-right">
            <a class="back-link" href="dashboard.php">Dashboard</a>
            <?php if ($loggedIn): ?>
                <div class="user-pill">
                    <?= htmlspecialchars($fullname) ?>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <div id="quotationReminderModal" class="luxury-modal hidden-element" aria-hidden="true">
        <div class="luxury-modal-card">
            <div class="luxury-modal-topbar">
                <span class="luxury-badge">Informasi Penting</span>
                <button type="button" class="luxury-close-btn" id="closeQuotationReminder">✕</button>
            </div>
            <h3>Ketentuan Penggunaan CR Allowance</h3>
            <ol class="luxury-list">
                <li>PT Ichikoh Indonesia = Persen × Proses Cost (CR 3 Tahun + BL)</li>
                <li>PT KTB = Persen × COGS</li>
                <li>PT HMMI = Persen × COGS (CR 5 Tahun)</li>
                <li>Other Customer = Without BL</li>
            </ol>
            <p class="luxury-message">
                <strong>Penting:</strong> untuk setiap pengisian pastikan <strong>Quotation No</strong> tidak sama dengan data yang sudah ada. Harap diingat detail perhitungan dan kebutuhan Quotation.
                <br> Jangan sampai salah input perhitungan.
            </p>
            <button type="button" class="luxury-confirm-btn" id="confirmQuotationReminder">Saya Mengerti</button>
        </div>
    </div>

    <main class="quotation-card">
        <form id="quotationForm" method="post" action="quotation.php">
            <input type="hidden" name="quotation_rows" id="quotationRowsInput" value="">
            <input type="hidden" name="save_quotation" id="saveQuotationFlag" value="0">
            <input type="hidden" name="existing_quotation_no" id="existingQuotationNoInput" value="<?= htmlspecialchars($editingQuotationNo) ?>">
            <input type="hidden" name="existing_customer_id" id="existingCustomerIdInput" value="<?= htmlspecialchars((string)$editingCustomerId) ?>">
            <input type="hidden" name="customer_id" id="customerIdInput" value="<?= htmlspecialchars((string)$editingCustomerId) ?>">
            <input type="hidden" name="global_cr_mode" id="globalCrMode" value="<?= htmlspecialchars($savedCrMode ?? 'none') ?>">

            <section class="quotation-head">
                <div>
                    <p class="eyebrow">Quotation</p>
                    <h2>Official Quotation</h2>
                    <p class="subtle">Sistem perhitungan harga resmi secara cepat dan praktis</p>
                </div>
                <div class="quote-meta">
                    <div>
                        <span>Quotation No</span>
                        <input class="quote-input" type="text" name="quotation_no" value="<?= htmlspecialchars($quotation['no']) ?>">
                    </div>
                    <div>
                        <span>Tahun Masspro</span>
                        <select name="rate_draft_id" id="rate_draft_id" class="quote-input" onchange="updateAllProcessRates()" required>
                            <option value="">-- Pilih Tahun Masspro --</option>
                            <?php if (!empty($allDraftsData)): ?>
                                <?php foreach ($allDraftsData as $d): ?>
                                    <option value="<?= $d['id'] ?>" 
                                            data-ref="<?= htmlspecialchars($d['base_reference']) ?>" 
                                            data-mp="<?= $d['manpower_rate_sec'] ?>" 
                                            <?= ($selectedDraftId == $d['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($d['draft_title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="meta-item">
                        <span>MMP</span>
                        <select name="mmp_id" id="acuan_mmp" class="quote-input" onchange="onAcuanMmpChange(this.value)">
                            <option value="">-- Pilih MMP --</option>
                            <?php 
                            $addedMmpIds = [];
                            foreach ($mmpData as $mItem): 
                                if (!in_array($mItem['mmp_id'], $addedMmpIds)):
                                    $addedMmpIds[] = $mItem['mmp_id'];
                                    $labelKeterangan = $mItem['judul_mmp'] ?? $mItem['keterangan_umum'] ?? 'Tanpa Keterangan';
                            ?>
                                <option value="<?= $mItem['mmp_id']; ?>" <?= ($selectedMmpId == $mItem['mmp_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($labelKeterangan); ?>
                                </option>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </select>
                    </div>
                    <div>
                        <span>Date</span>
                        <input class="quote-input" type="date" name="quotation_date" value="<?= htmlspecialchars($quotation['date']) ?>">
                    </div>
                   <div class="cr-btn-container" style="position: relative; display: inline-block;">
                    <?php 
                        $crBtnClass = ($savedCrMode === 'with_bl' || $savedCrMode === 'without_bl') ? 'cr-is-active' : 'cr-is-off';
                    ?>
                    <button type="button" id="btnCrAllowance" class="btn-cr <?= $crBtnClass ?>" onclick="toggleCrPopover(event)">
                        CR Allowance: <span id="crStatusLabel"><?= ($savedCrMode === 'with_bl') ? 'With BL' : (($savedCrMode === 'without_bl') ? 'Without BL' : 'Off') ?></span>
                    </button>

                    <div id="crPopover" class="cr-popover-menu" style="display: none; padding: 10px; min-width: 220px;">
                        <div style="margin-bottom: 8px; font-weight: bold; font-size: 12px; border-bottom: 1px solid #eee; padding-bottom: 4px;">Skema CR</div>
                        <button type="button" class="cr-pop-item <?= ($savedCrMode === 'with_bl') ? 'active' : '' ?>" onclick="selectCrMode('with_bl')">With BL</button>
                        <button type="button" class="cr-pop-item <?= ($savedCrMode === 'without_bl') ? 'active' : '' ?>" onclick="selectCrMode('without_bl')">Without BL</button>
                        <button type="button" class="cr-pop-item cr-pop-off <?= ($savedCrMode !== 'with_bl' && $savedCrMode !== 'without_bl') ? 'active' : '' ?>" onclick="selectCrMode('none')">Turn Off</button>
                        
                        <div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #eee;">
                            <label for="ltaYearsCount" style="font-size: 11px; font-weight: 600; display: block; margin-bottom: 4px;">Periode LTA:</label>
                            <select id="ltaYearsCount" class="quote-input" onchange="onLtaYearsChange(this.value)" style="width: 100%; padding: 4px 6px; font-size: 12px;">
                                <?php for ($y = 1; $y <= 10; $y++): ?>
                                    <option value="<?= $y ?>" <?= ($y == 3) ? 'selected' : '' ?>><?= $y ?> Tahun</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                </div>
            </section>

            <section class="kop-grid">
                <article class="panel kop-card">
                    <p class="kop-label">From</p>
                    <h3>PT Citra Plastik Makmur</h3>
                    <p>Jl. Jababeka XIV A Blok J4F, Kawasan Industri Jababeka 1</p>
                    <p>Kel. Harjamekar, Kec. Cikarang Utara, Kab. Bekasi, Jawa Barat 17530</p>
                    <p>marketing@citraplastik.com</p>
                </article>
                <article class="panel kop-card">
                    <p class="kop-label">To</p>
                    <div>
                        <label>Customer Name *</label>
                        <select class="quote-input cust-select" name="customer_name" id="opt_customer" onchange="autoFillCustomer()" required>
                            <option value="">-- Pilih Customer Terdaftar --</option>
                            <?php if (isset($customerList) && !empty($customerList)): ?>
                                <?php foreach ($customerList as $cust): ?>
                                    <option value="<?= htmlspecialchars($cust['customer_name']) ?>" 
                                            data-id="<?= (int)$cust['id'] ?>"
                                            data-pic="<?= htmlspecialchars($cust['pic']) ?>"
                                            data-address="<?= htmlspecialchars($cust['address']) ?>"
                                            data-phone="<?= htmlspecialchars($cust['phone']) ?>"
                                            data-email="<?= htmlspecialchars($cust['email']) ?>"
                                            <?= ($editingCustomerId == $cust['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cust['customer_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div><label>Attention / PIC</label><input class="quote-input" type="text" name="customer_pic" id="cust_pic" value="<?= htmlspecialchars($quotation['customer']['pic']) ?>"></div>
                    <div><label>Address</label><textarea class="quote-input" name="customer_address" id="cust_address"><?= htmlspecialchars($quotation['customer']['address']) ?></textarea></div>
                    <div><label>Phone</label><input class="quote-input" type="text" name="customer_phone" id="cust_phone" value="<?= htmlspecialchars($quotation['customer']['phone']) ?>"></div>
                    <div><label>Email</label><input class="quote-input" type="email" name="customer_email" id="cust_email" value="<?= htmlspecialchars($quotation['customer']['email']) ?>"></div>
                </article>
            </section>

            <section class="panel calc-box">
                <div class="calc-toolbar">
                    <h3>Calculation Block</h3>
                    <div>
                        <button type="button" class="btn-tambah" onclick="addRow()">Tambah Kolom</button>
                        <button type="button" class="btn-hitung" onclick="openReviewModal()">Hitung & Review</button>
                        <button type="button" class="btn-hapus" onclick="deleteSelectedRows()">Hapus Pilihan</button>
                    </div>
                </div>
                <div class="calc-table-wrap">
                    <table class="quote-table" id="calcTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)"></th>
                                <th>No</th>
                                <th class="col-sm">Type</th>
                                <th>Part Number</th>
                                <th>Part Name</th>
                                <th>Material Spec</th>
                                <th>Basic Price</th>
                                <th>Currency</th>
                                <th class="col-md">Ex Rate</th>
                                <th class="col-md">Nett Weight (gr)</th>
                                <th class="col-md">Runner Weight (gr)</th>
                                <th class="col-md">Pigmen Cost (Rp)</th>
                                <th>Weight/Pcs</th>
                                <th>IDR Price/Kg</th>
                                <th>Material Cost/Pcs</th>
                                <th>Cycle Time (sec)</th>
                                <th class="col-xs">Cavity</th>
                                <th class="col-sm">M/C (ton)</th>
                                <th class="col-md">Qty Forecast / Month</th>
                                <th class="col-md">Purging Ori (Kg)</th>
                                <th class="col-md">Cellpurg (Kg)</th>
                                <th class="col-lg">Cellpurg Price (Rp)</th>
                                <th>Purging / Pcs</th>
                                <th class="col-md">Dandori (Min)</th>
                                <th>Dandori / Pcs</th>
                                <th>Rate/Hour (Rp)</th> 
                                <th>Cost/Pcs Process</th>
                                <th>Reject %</th>
                                <th>Rejection Rate</th>
                                <th class="col-lg">Other Process Type</th>
                                <th class="col-md">CT Other (s)</th>
                                <th class="col-lg">Rate Annealing (Rp)</th>
                                <th class="col-lg">Cost Other Process</th>
                                <th class="col-lg">Packing</th>
                                <th class="col-lg">Transport</th>
                                <th class="col-lg">COGS</th>
                                <th class="col-md">O/H %</th>
                                <th>O/H Profit</th>
                                <th class="col-lg">Harga Mold (Rp)</th>
                                <th class="col-md">Tahun Dep.</th>
                                <th class="col-lg">Depresiasi Mold / Pcs</th>
                                <th>MOLD MAINTENANCE (KEY)</th>
                                <th>MOLD MTN / PCS (RP)</th>
                                <th class="col-lg">Total Price</th>
                                <th class="col-lg">Lumpsum</th>
                                <th class="cr-col col-lg col-cr-header" style="display:none;">Input Nilai CR</th>
                                <th id="ltaHeadersMarker" style="display:none;"></th>
                                <th class="cr-col cr-bl-only col-sm col-cr-header" style="display:none;">BL (%)</th>
                                <th class="cr-col cr-bl-only col-md col-cr-header" style="display:none;">Hasil BL</th>
                                <th class="cr-col col-xl col-cr-final" style="display:none;">Final Cost</th>
                            </tr>
                        </thead>
                        <tbody id="calcRows"></tbody>
                    </table>
                </div>
            </section>

            <section class="summary-grid">
               <article class="panel notes-box">
                    <h3>NOTE :</h3>
                    <textarea name="quotation_notes" id="quotation_notes" class="hidden-element"><?= htmlspecialchars($quotation['notes'] ?? '') ?></textarea>
                    <div id="wrapper_notes_baku" class="notes-baku-wrapper">
                        <div class="note-row">1. Model.</div>
                        <div class="note-row">2. Exchange Rate periode <input type="text" id="note_input_periode" class="quote-input note-input-md" placeholder="....."> Rp. <span id="note_span_rate" class="note-bold">..........</span> / USD.</div>
                        <div class="note-row">3. Harga belum termasuk PPN 11%, biaya pengetesan, jig, <i>checking fixture</i>, dan biaya <i>finishing</i> (jika ada).</div>
                        <div class="note-row">4. Qty forecast: <input type="text" id="note_input_qty" class="quote-input note-input-md" placeholder="Isi Qty Manual...">.</div>
                        <div class="note-row">5. Pembayaran <input type="text" id="note_input_day" class="quote-input note-input-sm" placeholder="Isi hari..."> hari setelah penerimaan invoice.</div>
                        <div class="note-row">6. Berat part, cycle time, cavity & tonase mesin akan diperbaharui kembali setelah hasil trial dinyatakan OK.</div>
                        <div class="note-row">7. Packing menggunakan (<input type="text" id="note_input_pack1" class="quote-input note-input-sm" placeholder="Return...">) <i>returnable</i> dan (<input type="text" id="note_input_pack2" class="quote-input note-input-sm" placeholder="Non-Return...">) <i>non-returnable</i>.</div>
                        <div class="note-row">8. Validasi penawaran harga: 30 hari.</div>
                        <div class="note-row">9. Tahun Masspro <input type="number" id="note_input_masspro" class="quote-input note-input-sm" placeholder="Isi Tahun ...">.</div>
                        <div id="dynamicNotesContainer" class="dynamic-notes-container"></div>
                        <div class="notes-actions">
                            <button type="button" id="addNoteButton" class="btn-note-add">+ Tambah Catatan</button>
                        </div>
                    </div>
                </article>
            </section>

            <section class="footer-note">
                <p>Prepared by: <strong><?= htmlspecialchars($fullname) ?></strong></p>
                <p>Approved by: __________________________</p>
            </section>
        </form>
    </main>
</div>

<div id="reviewModal" class="modal-backdrop hidden-element">
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <h2>Review Perhitungan & Konfirmasi Penawaran</h2>
            <span class="btn-close-modal" onclick="closeReviewModal()">✕ Tutup</span>
        </div>
        <p class="modal-sub">Silakan cek kembali seluruh detail komponen harga sebelum disimpan.</p>
        <div id="modalQuoMeta"></div>
        <div class="modal-table-wrap">
            <table class="modal-review-table" border="1" cellpadding="6">
                <thead>
                    <tr id="modalHeaderRow1">
                        <th rowspan="2">NO</th>
                        <th rowspan="2">TYPE</th>
                        <th rowspan="2">PART NUMBER</th>
                        <th rowspan="2">PART NAME</th>
                        <th rowspan="2">MATERIAL SPEC</th>
                        <th rowspan="2">BASIC PRICE</th>
                        <th colspan="4" class="th-mat">MATERIAL</th>
                        <th colspan="5" class="th-proc">PROCESS</th>
                        <th rowspan="2">REJECTION RATE (Rp)</th>
                        <th rowspan="2" class="th-other">OTHER PROCESS</th>
                        <th rowspan="2">PACKING (Rp)</th>
                        <th rowspan="2">TRANSP (Rp)</th>
                        <th rowspan="2" class="th-cogs">COGS (Rp)</th>
                        <th rowspan="2">O/H PROFIT</th>
                        <th rowspan="2">MOLD DEPR (Rp)</th>
                        <th rowspan="2">MOLD/MTN</th>
                        <th rowspan="2" class="th-total-price">TOTAL PRICE</th>
                        <th rowspan="2" class="th-lumpsum">LUMPSUM PRICE</th>
                        <th class="cr-col cr-bl-dep th-cr" style="display:none;">BASE VAL</th>
                        <th id="dynamicLtaHeaders" colspan="3" class="cr-col cr-wob-dep th-cr" style="display:none;">LTA (%)</th>
                        <th class="cr-col cr-bl-dep th-cr" style="display:none;">BL (%)</th>
                        <th class="cr-col th-cr-final" style="display:none;">FINAL COST</th>
                    </tr>
                    <tr id="modalHeaderRow2">
                        <th class="th-mat-sub">PIGMEN COST (Rp)</th>
                        <th class="th-mat-sub">WEIGHT/PCS (Gr)</th>
                        <th class="th-mat-sub">IDR PRICE/Kg (Rp)</th>
                        <th class="th-mat-sub">MATERIAL COST/PCS (Rp)</th>
                        <th class="th-proc-sub">CYCLE TIME (Sec)</th>
                        <th class="th-proc-sub">CAV (Pcs)</th>
                        <th class="th-proc-sub">M/C (Ton)</th>
                        <th class="th-proc-sub">RATE/HOUR (Rp)</th>
                        <th class="th-proc-sub">COST/PCS (Rp)</th>
                    </tr>
                </thead>
                <tbody id="modalReviewRows"></tbody>
            </table>
        </div>
        <div class="modal-notes-section">
            <label><b>NOTE:</b></label>
            <textarea id="modalQuotationNotes"></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal-recalc" onclick="closeReviewModal()">Hitung Ulang</button>
            <button type="button" class="btn-modal-submit" onclick="submitToReport()">Setuju & Simpan ke Report</button>
        </div>
    </div>
</div>

<?php if (isset($conn)) { $conn->close(); } ?>

<?php if (!empty($message)): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true
        });

        Toast.fire({
            icon: '<?= $messageType === 'error' ? 'error' : 'success' ?>',
            title: '<?= $messageType === 'error' ? 'Gagal!' : 'Berhasil!' ?>',
            text: <?= json_encode($message) ?>
        });
    });
</script>
<?php endif; ?>

<script>
    window.userRole             = <?php echo json_encode($role ?? 'User'); ?>;
    window.purgingMasterData    = <?php echo json_encode($purgingMasterData ?? []); ?>;
    window.dandoriMasterData    = <?php echo json_encode($dandoriMasterData ?? []); ?>;
    window.mmpMasterData        = <?php echo json_encode($mmpData ?? []); ?>;
    window.packingOptions       = <?php echo json_encode($packingOptions ?? []); ?>;
    window.packingStandards     = <?php echo json_encode($packingStandardsMap ?? []); ?>;
    window.existingRows         = <?php echo json_encode($savedRows ?? []); ?>;
    window.moldMasterData       = <?php echo json_encode($moldMasterData ?? []); ?>;
    window.savedCrMode          = <?php echo json_encode($savedCrMode); ?>;
    window.allDraftsData        = <?php echo json_encode($allDraftsData ?? []); ?>;
</script>

<div id="sessionConflictModal" class="session-conflict-modal">
    <div class="session-conflict-card">
        <div class="session-conflict-icon">!</div>
        <div class="session-conflict-title">Sesi Login Anda Dibatalkan</div>
        <div class="session-conflict-message">
            Akun Anda sedang dipakai di perangkat lain.<br>
            Klik <strong>OK</strong> untuk keluar dari sesi ini.
        </div>
        <button id="sessionConflictOkBtn" class="session-conflict-ok-btn">OK</button>
    </div>
</div>

<script src="quotation.js?v=<?= time(); ?>"></script>
<script src="back-to-top.js"></script>
<script>
const sessionConflictModal = document.getElementById('sessionConflictModal');
const sessionConflictOkBtn = document.getElementById('sessionConflictOkBtn');

const showSessionConflictModal = () => {
    if (!sessionConflictModal) return;
    sessionConflictModal.style.display = 'flex';
};

if (sessionConflictOkBtn) {
    sessionConflictOkBtn.addEventListener('click', function() {
        if (sessionConflictModal) {
            sessionConflictModal.style.display = 'none';
        }
        window.location.href = 'index.php';
    });
}

setInterval(async function () {
    try {
        const response = await fetch('check_session.php', { cache: 'no-store' });
        const result = await response.json();

        if (!result.valid) {
            showSessionConflictModal();
        }
    } catch (e) {

    }
}, 5000);
</script>
</body>
</html>