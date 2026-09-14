<?php
header('Content-Type: application/json');

include 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan!']);
    exit;
}

$quotationNo = trim($_POST['quotation_no'] ?? $_POST['quotation_id'] ?? '');
$quotationNote = trim($_POST['quotation_note'] ?? '');
$customerId = intval($_POST['customer_id'] ?? 0);
$rateDraftId = intval($_POST['rate_draft_id'] ?? 0);
$mmpId = intval($_POST['mmp_id'] ?? 0);
$quotationDate = trim($_POST['quotation_date'] ?? date('Y-m-d'));

if ($quotationNo === '') {
    echo json_encode(['status' => 'error', 'message' => 'Nomor quotation tidak boleh kosong.']);
    exit;
}

mysqli_begin_transaction($conn);

try {
    if ($customerId <= 0 && !empty($_POST['customer_name'])) {
        $customerName = mysqli_real_escape_string($conn, trim($_POST['customer_name']));
        $customerAddress = mysqli_real_escape_string($conn, trim($_POST['customer_address'] ?? ''));
        $customerPic = mysqli_real_escape_string($conn, trim($_POST['customer_pic'] ?? ''));
        $customerPhone = mysqli_real_escape_string($conn, trim($_POST['customer_phone'] ?? ''));
        $customerEmail = mysqli_real_escape_string($conn, trim($_POST['customer_email'] ?? ''));
        $customerCurrency = mysqli_real_escape_string($conn, trim($_POST['quotation_currency'] ?? $_POST['currency'] ?? 'USD'));
        $paymentTerm = mysqli_real_escape_string($conn, trim($_POST['payment_term'] ?? ''));

        $customerSql = "INSERT INTO tbl_customer (customer_name, address, pic, phone, email, currency, payment_term, created_at)
                        VALUES ('$customerName', '$customerAddress', '$customerPic', '$customerPhone', '$customerEmail', '$customerCurrency', '$paymentTerm', NOW())";
        if (!mysqli_query($conn, $customerSql)) {
            throw new Exception('Gagal membuat customer: ' . mysqli_error($conn));
        }
        $customerId = (int) mysqli_insert_id($conn);
    }

    $rowsJson = $_POST['quotation_rows'] ?? '[]';
    $rows = json_decode($rowsJson, true);

    if (!is_array($rows) || empty($rows)) {
        $legacyPartNumbers = $_POST['part_number'] ?? [];
        if (!is_array($legacyPartNumbers) || empty($legacyPartNumbers)) {
            throw new Exception('Data quotation_rows kosong atau format tidak valid!');
        }

        $legacyRows = [];
        foreach ($legacyPartNumbers as $index => $partNumber) {
            if (trim((string) $partNumber) === '') {
                continue;
            }
            $legacyRows[] = [
                'type' => $_POST['type'][$index] ?? 'internal',
                'data' => [
                    'part_number' => $_POST['part_number'][$index] ?? '',
                    'part_name' => $_POST['part_name'][$index] ?? '',
                    'material_spec' => $_POST['material_spec'][$index] ?? '',
                    'material_price' => $_POST['material_price'][$index] ?? 0,
                    'basic_price' => $_POST['basic_price'][$index] ?? 0,
                    'currency' => $_POST['currency'][$index] ?? 'USD',
                    'exchange_rate' => $_POST['exchange_rate'][$index] ?? 0,
                    'part_weight' => $_POST['part_weight'][$index] ?? 0,
                    'runner_weight' => $_POST['runner_weight'][$index] ?? 0,
                    'pigmen_cost' => $_POST['pigmen_cost'][$index] ?? 0,
                    'weight_per_pcs' => $_POST['weight_per_pcs'][$index] ?? 0,
                    'idr_price_kg' => $_POST['idr_price_kg'][$index] ?? 0,
                    'cycle_time' => $_POST['cycle_time'][$index] ?? 0,
                    'cavity' => $_POST['cavity'][$index] ?? 1,
                    'mc_ton' => $_POST['mc_ton'][$index] ?? 0,
                    'qty_forecast_month' => $_POST['qty_forecast_month'][$index] ?? 0,
                    'purging_ori_kg' => $_POST['purging_ori_kg'][$index] ?? 0,
                    'purging_cellpurg_kg' => $_POST['purging_cellpurg_kg'][$index] ?? 0,
                    'cellpurge_price' => $_POST['cellpurge_price'][$index] ?? 0,
                    'purging' => $_POST['purging'][$index] ?? 0,
                    'dandori_minutes' => $_POST['dandori_minutes'][$index] ?? 0,
                    'dandori' => $_POST['dandori'][$index] ?? 0,
                    'rate_hour' => $_POST['rate_hour'][$index] ?? 0,
                    'process_cost' => $_POST['process_cost'][$index] ?? 0,
                    'reject_rate' => $_POST['reject_rate'][$index] ?? $_POST['reject_percent'][$index] ?? 0,
                    'rejection_cost' => $_POST['rejection_cost'][$index] ?? 0,
                    'other_process_type' => $_POST['other_process_type'][$index] ?? '',
                    'ct_other' => $_POST['ct_other'][$index] ?? 0,
                    'other_process_cost' => $_POST['other_process_cost'][$index] ?? 0,
                    'packing_cost' => $_POST['packing_cost'][$index] ?? $_POST['packing'][$index] ?? 0,
                    'transport_cost' => $_POST['transport_cost'][$index] ?? $_POST['transport'][$index] ?? 0,
                    'cogs' => $_POST['cogs'][$index] ?? 0,
                    'oh_percent' => $_POST['oh_percent'][$index] ?? 0,
                    'oh_profit' => $_POST['oh_profit'][$index] ?? 0,
                    'mold_price' => $_POST['mold_price'][$index] ?? 0,
                    'depreciation_years' => $_POST['depreciation_years'][$index] ?? 1,
                    'mold_depreciation_pcs' => $_POST['mold_depreciation_pcs'][$index] ?? 0,
                    'mold_cost_month' => $_POST['mold_cost_month'][$index] ?? 0,
                    'mold_maintenance' => $_POST['mold_maintenance'][$index] ?? $_POST['mold_mtn'][$index] ?? 0,
                    'selling_price' => $_POST['selling_price'][$index] ?? 0,
                    'lumpsum_price' => $_POST['lumpsum_price'][$index] ?? $_POST['lumpsum_pcs'][$index] ?? 0,
                    'cr_mode' => $_POST['cr_mode'][$index] ?? 'with_bl',
                    'cr_base_val' => $_POST['cr_base_val'][$index] ?? 0,
                    'cr_lta_years' => $_POST['cr_lta_years_count'][$index] ?? $_POST['cr_lta_years'][$index] ?? 3,
                    'cr_lta_pct_json' => $_POST['cr_lta_pct_json'][$index] ?? '[]',
                    'cr_lta_res_json' => $_POST['cr_lta_res_json'][$index] ?? '[]',
                    'cr_pct_bl' => $_POST['cr_pct_bl'][$index] ?? 0,
                    'cr_final_cost' => $_POST['cr_final_cost'][$index] ?? 0,
                ]
            ];
        }
        $rows = $legacyRows;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO tbl_quotation (
        quotation_no, customer_id, rate_draft_id, mmp_id, type, part_number, packing_standard_id, part_name, material_spec, material_price,
        basic_price, quotation_date, exchange_rate_value, weight_per_pcs, part_weight, runner_weight, pigmen_cost, idr_price_kg, cycle_time, cavity,
        mc_ton, qty_forecast_month, purging_ori_kg, purging_cellpurg_kg, cellpurge_price, purging, dandori_minutes, dandori, rate_hour, process_cost,
        reject_percent, rejection_cost, other_process_type, ct_other, other_process_cost, packing_cost, transport_cost, cogs, oh_percent, oh_profit,
        mold_price, depreciation_years, mold_depreciation_pcs, mold_cost_month, mold_maintenance, selling_price, lumpsum_price, quotation_note, created_by,
        cr_mode, cr_base_val, cr_lta_years, cr_lta_pct_json, cr_lta_res_json, cr_pct_bl, cr_final_cost
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        throw new Exception('Gagal prepare statement: ' . mysqli_error($conn));
    }

    foreach ($rows as $item) {
        $data = $item['data'] ?? [];
        $partNumber = trim((string) ($data['part_number'] ?? ''));
        if ($partNumber === '') {
            continue;
        }

        $values = [
            $quotationNo,
            $customerId,
            $rateDraftId,
            $mmpId,
            $item['type'] ?? 'internal',
            $partNumber,
            intval($data['packing_standard_id'] ?? 0),
            $data['part_name'] ?? '',
            $data['material_spec'] ?? '',
            floatval($data['material_price'] ?? 0),
            floatval($data['basic_price'] ?? 0),
            $quotationDate,
            floatval($data['exchange_rate'] ?? 0),
            floatval($data['weight_per_pcs'] ?? 0),
            floatval($data['part_weight'] ?? 0),
            floatval($data['runner_weight'] ?? 0),
            floatval($data['pigmen_cost'] ?? 0),
            floatval($data['idr_price_kg'] ?? 0),
            floatval($data['cycle_time'] ?? 0),
            intval($data['cavity'] ?? 1),
            floatval($data['mc_ton'] ?? 0),
            intval($data['qty_forecast_month'] ?? 0),
            floatval($data['purging_ori_kg'] ?? 0),
            floatval($data['purging_cellpurg_kg'] ?? 0),
            floatval($data['cellpurge_price'] ?? $data['cellpurg_price'] ?? 0),
            floatval($data['purging'] ?? 0),
            floatval($data['dandori_minutes'] ?? 0),
            floatval($data['dandori'] ?? 0),
            floatval($data['rate_hour'] ?? 0),
            floatval($data['process_cost'] ?? 0),
            floatval($data['reject_rate'] ?? $data['reject_percent'] ?? 0),
            floatval($data['rejection_cost'] ?? 0),
            (string) ($data['other_process_type'] ?? ''),
            floatval($data['ct_other'] ?? 0),
            floatval($data['other_process_cost'] ?? 0),
            floatval($data['packing_cost'] ?? $data['packing'] ?? 0),
            floatval($data['transport_cost'] ?? $data['transport'] ?? 0),
            floatval($data['cogs'] ?? 0),
            floatval($data['oh_percent'] ?? 0),
            floatval($data['oh_profit'] ?? 0),
            floatval($data['mold_price'] ?? 0),
            intval($data['depreciation_years'] ?? 1),
            floatval($data['mold_depreciation_pcs'] ?? 0),
            floatval($data['mold_cost_month'] ?? 0),
            floatval($data['mold_maintenance'] ?? $data['mold_mtn'] ?? 0),
            floatval($data['selling_price'] ?? $data['total'] ?? 0),
            floatval($data['lumpsum_price'] ?? $data['lumpsum_pcs'] ?? 0),
            $quotationNote,
            intval($_SESSION['user_id'] ?? 0),
            (string) ($data['cr_mode'] ?? 'with_bl'),
            floatval($data['cr_base_val'] ?? 0),
            intval($data['cr_lta_years_count'] ?? $data['cr_lta_years'] ?? 3),
            json_encode($data['cr_lta_pct_list'] ?? $data['cr_lta_pct_json'] ?? []),
            json_encode($data['cr_lta_res_list'] ?? $data['cr_lta_res_json'] ?? []),
            floatval($data['cr_pct_bl'] ?? 0),
            floatval($data['cr_final_cost'] ?? 0),
        ];

        $types = 'siis' . 's' . 'i' . 'ss' . 'ddddddddddddddddddddddddddddddddddddddddddssdiisssdd';
        $types = 'siiisssdddddddddddddddddddddddddddddddiiiddddddddssdiidddd';

        $bindValues = array_merge([$types], $values);
        call_user_func_array('mysqli_stmt_bind_param', array_merge([$stmt], $bindValues));

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal menyimpan item ' . $partNumber . ': ' . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_reset($stmt);
    }

    mysqli_stmt_close($stmt);
    mysqli_commit($conn);
    echo json_encode(['status' => 'success', 'message' => 'Data Quotation berhasil disimpan!']);
    exit;
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal menyimpan data quotation.',
        'details' => $e->getMessage()
    ]);
    exit;
}
?>