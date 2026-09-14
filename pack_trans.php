<?php
session_start();
include 'config/database.php';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
} else {
    header('Location: index.php');
    exit;
}

include 'auth.php';

$is_gm = isset($_SESSION['role']) && in_array(strtolower(trim((string)$_SESSION['role'])), ['general manager', 'admin'], true);

$message = '';
$messageType = 'success';

$project_id = 0;
$part_name = '';
$truck_p = 0;
$truck_l = 0;
$truck_t = 0;
$truck_cost = 0;
$transport_pcs_total = 0;
$total_packing_pcs = 0;
$existing_items = []; 
$is_edit_mode = false;
$packTransEvents = $_SESSION['pack_trans_events'] ?? [];
unset($_SESSION['pack_trans_events']);

if (isset($_GET['edit_project_id'])) {
    $project_id = intval($_GET['edit_project_id']);
    if ($project_id > 0) {
        $is_edit_mode = true;
        
        $stmt_proj = $conn->prepare("SELECT * FROM tbl_pack_trans_project WHERE id = ?");
        $stmt_proj->bind_param("i", $project_id);
        $stmt_proj->execute();
        $proj_data = $stmt_proj->get_result()->fetch_assoc();
        
        if ($proj_data) {
            $part_name           = $proj_data['part_name'];
            $truck_p             = $proj_data['truck_p'];
            $truck_l             = $proj_data['truck_l'];
            $truck_t             = $proj_data['truck_t'];
            $truck_cost          = $proj_data['truck_cost'];
            $transport_pcs_total = $proj_data['transport_pcs_total'];
            $total_packing_pcs   = $proj_data['total_packing_pcs'];
            
            $stmt_items = $conn->prepare("SELECT * FROM tbl_pack_trans_items WHERE project_id = ? ORDER BY id ASC");
            $stmt_items->bind_param("i", $project_id);
            $stmt_items->execute();
            $res_items = $stmt_items->get_result();
            while ($row_item = $res_items->fetch_assoc()) {
                $existing_items[] = $row_item;
            }
        }
    }
}

$gaOptions = [];
$getGaData = $conn->query("SELECT * FROM tbl_transport_cost WHERE is_archived = 0 OR is_archived IS NULL ORDER BY customer_name ASC");
if ($getGaData) {
    while($gaRow = $getGaData->fetch_assoc()) {
        $gaOptions[] = $gaRow;
    }
}

if (isset($_GET['get_packing_cost_by_part'])) {
    header('Content-Type: application/json');
    $partName = trim($_GET['get_packing_cost_by_part'] ?? '');
    
    $stmt = $conn->prepare("SELECT total_packing FROM tbl_packing_transactions WHERE part_name = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('s', $partName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $cost = 0;
    if ($row = $result->fetch_assoc()) {
        $cost = floatval($row['total_packing']);
    }
    
    echo json_encode(['success' => true, 'cost_packing' => $cost]);
    exit;
}

$packing_master = [];
$res_pack = $conn->query("SELECT id, `description` AS packing_desc, harga_jual, volume_cm3 FROM tbl_packing_standard WHERE status='active' ORDER BY id ASC");
if ($res_pack) {
    while ($r = $res_pack->fetch_assoc()) {
        $packing_master[] = $r;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product'])) {
    $conn->begin_transaction();
    try {
        $form_project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
        $savedProjectIds = [];

        foreach ($_POST['product'] as $index => $prod_data) {
            $part_name = mysqli_real_escape_string($conn, $prod_data['part_name']);
            if (trim($part_name) === '') continue; 

            $truck_p           = isset($prod_data['truck_p']) ? intval($prod_data['truck_p']) : 0;
            $truck_l           = isset($prod_data['truck_l']) ? intval($prod_data['truck_l']) : 0;
            $truck_t           = isset($prod_data['truck_t']) ? intval($prod_data['truck_t']) : 0;
            $truck_cost        = isset($prod_data['truck_cost']) ? floatval($prod_data['truck_cost']) : 0.00;
            $transport_pcs     = isset($prod_data['transport_pcs_total']) ? floatval($prod_data['transport_pcs_total']) : 0.00;
            $total_packing_pcs = isset($prod_data['total_packing_pcs']) ? floatval($prod_data['total_packing_pcs']) : 0.00;

            if ($form_project_id > 0 && $index === 0) {
                $stmt_proj = $conn->prepare("UPDATE tbl_pack_trans_project SET part_name = ?, truck_p = ?, truck_l = ?, truck_t = ?, truck_cost = ?, transport_pcs_total = ?, total_packing_pcs = ?, is_read = 0, workflow_status = 'complete' WHERE id = ?");
                $stmt_proj->bind_param("siiidddi", $part_name, $truck_p, $truck_l, $truck_t, $truck_cost, $transport_pcs, $total_packing_pcs, $form_project_id);
                $stmt_proj->execute();
                
                $curr_project_id = $form_project_id;

                $stmt_clear_items = $conn->prepare("DELETE FROM tbl_pack_trans_items WHERE project_id = ?");
                $stmt_clear_items->bind_param("i", $curr_project_id);
                $stmt_clear_items->execute();
            } else {
                $stmt_proj = $conn->prepare("INSERT INTO tbl_pack_trans_project (part_name, truck_p, truck_l, truck_t, truck_cost, transport_pcs_total, total_packing_pcs, is_read, workflow_status) VALUES (?, ?, ?, ?, ?, ?, ?, 0, 'new')");
                $stmt_proj->bind_param("siiiddd", $part_name, $truck_p, $truck_l, $truck_t, $truck_cost, $transport_pcs, $total_packing_pcs);
                $stmt_proj->execute();
                
                $curr_project_id = $conn->insert_id;
            }

            $savedProjectIds[] = $curr_project_id;

            if (isset($prod_data['items']) && is_array($prod_data['items'])) {
                foreach ($prod_data['items'] as $item) {
                    $standard_id = intval($item['standard_id']);
                    if ($standard_id <= 0) continue; 

                    $type_system = mysqli_real_escape_string($conn, $item['type_system']);
                    $qty         = intval($item['qty']);
                    $qty_month   = intval($item['qty_month']);
                    
                    $sirkulasi   = isset($item['sirkulasi']) ? intval($item['sirkulasi']) : 12;
                    $bunga_pct   = isset($item['bunga_pct']) ? floatval($item['bunga_pct']) : 11.5;
                    $tahun_dep   = isset($item['tahun_dep']) ? intval($item['tahun_dep']) : 2;
                    
                    $assy_day    = $qty_month / 20;
                    $box_day     = $qty > 0 ? ($assy_day / $qty) : 0;
                    $box_round   = ceil($box_day);
                    $box_keeping = $box_round * $sirkulasi;
                    
                    $harga_jual = 0;
                    $res_price = $conn->query("SELECT harga_jual FROM tbl_packing_standard WHERE id = $standard_id");
                    if($res_price && $r_p = $res_price->fetch_assoc()) {
                        $harga_jual = floatval($r_p['harga_jual']);
                    }

                    $total_bunga_pct = $bunga_pct * $tahun_dep;
                    $faktor_bunga    = 1 + ($total_bunga_pct / 100); 
                    $investment      = $box_keeping * $harga_jual * $faktor_bunga;
                    
                    $qty_2years    = $qty_month * 12 * $tahun_dep;
                    $depresiasi    = $qty_2years > 0 ? ($investment / $qty_2years) : 0;
                    
                    if ($type_system === 'non-returnable') {
                        $total_packing = $qty > 0 ? ($harga_jual / $qty) : 0;
                        $box_day = 0; $box_round = 0; $box_keeping = 0; $investment = 0; $qty_2years = 0; $depresiasi = 0;
                    } else {
                        $total_packing = $depresiasi;
                    }

                    $query_item = "INSERT INTO tbl_pack_trans_items 
                        (project_id, type_system, standard_id, qty, qty_month, assy_day, box_day, box_round, box_keeping, investment, qty_2years, depresiasi, total_packing, transport_pcs) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $stmt_item = $conn->prepare($query_item);
                    $stmt_item->bind_param("isiiiiiiidiidd", 
                        $curr_project_id, $type_system, $standard_id, $qty, $qty_month, $assy_day, 
                        $box_day, $box_round, $box_keeping, $investment, $qty_2years, $depresiasi, $total_packing, $transport_pcs
                    );
                    $stmt_item->execute();
                }
            }
        }
        $conn->commit();

        foreach ($savedProjectIds as $savedProjectId) {
            $savedProjectStmt = $conn->prepare("SELECT id, part_name, total_packing_pcs, transport_pcs_total FROM tbl_pack_trans_project WHERE id = ?");
            if ($savedProjectStmt) {
                $savedProjectStmt->bind_param('i', $savedProjectId);
                $savedProjectStmt->execute();
                $savedProject = $savedProjectStmt->get_result()->fetch_assoc();
                if ($savedProject) {
                    $packTransEvents[] = ['event' => $form_project_id > 0 ? 'update_pack_trans' : 'new_pack_trans', 'data' => $savedProject];
                    write_audit_log(
                        'tbl_pack_trans_project',
                        $savedProjectId,
                        $form_project_id > 0 ? 'update' : 'insert',
                        'packing_transport',
                        null,
                        ($form_project_id > 0 ? 'General Manager memperbarui' : 'Membuat')
                        . ' kalkulasi Packing Transport untuk part: ' . $savedProject['part_name']
                    );
                }
                $savedProjectStmt->close();
            }
        }
        $_SESSION['pack_trans_events'] = $packTransEvents;
        
        if ($form_project_id > 0) {
            header("Location: pack_trans.php?status=updated");
        } else {
            header("Location: pack_trans.php?status=created");
        }
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        $message = "Gagal Menyimpan Data: " . $e->getMessage();
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit_mode ? 'Edit' : 'Buat' ?> Packing & Transport Cost Matrix</title>
    <link rel="stylesheet" href="quotation.css"> 
    <link rel="stylesheet" href="back-to-top.css">
    <link rel="stylesheet" href="customer.css"> 
    <link rel="stylesheet" href="packtrans.css">
</head>
<body>
<div class="page-shell">
    <div id="toast-container" class="toast-container">
        <?php if ($message): ?>
            <div class="toast <?= $messageType ?>">
                <span class="toast-icon"><?= $messageType === 'success' ? '✓' : '✕' ?></span>
                <span class="toast-message"><?= htmlspecialchars($message) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <header class="topbar">
        <div>
            <h1>QUOTATIONAPP</h1>
            <p><?= $is_edit_mode ? 'Edit Perubahan' : 'Multi-Product' ?> Packing & Transport Calculation Sheet</p>
        </div>
        <div class="topbar-right">
            <?php if ($is_edit_mode): ?>
                <a class="back-link" href="manage_pack_trans.php">← Kembali ke Riwayat</a>
            <?php endif; ?>
            <a class="back-link" href="index.php">← Dashboard</a>
        </div>
    </header>

    <main class="quotation-card" style="max-width: 100%;">
        <form method="post" id="bulkMatrixForm">
            <input type="hidden" name="project_id" value="<?= $project_id ?>">

            <div id="products-master-wrapper">
                <div class="product-group-box" id="product-block-0">
                    <input type="hidden" name="product[0][truck_p]" id="hidden-p-0" value="<?= $truck_p ?>">
                    <input type="hidden" name="product[0][truck_l]" id="hidden-l-0" value="<?= $truck_l ?>">
                    <input type="hidden" name="product[0][truck_t]" id="hidden-t-0" value="<?= $truck_t ?>">
                    <input type="hidden" name="product[0][truck_cost]" id="hidden-cost-0" value="<?= $truck_cost ?>">
                    <input type="hidden" name="product[0][transport_pcs_total]" id="hidden-transpcs-0" value="<?= $transport_pcs_total ?>">
                    <input type="hidden" name="product[0][total_packing_pcs]" id="hidden-packpcs-0" value="<?= $total_packing_pcs ?>">

                    <div class="product-header-title">
                        <div style="display: flex; align-items: center; gap: 15px; width: 70%;">
                            <span>DATA PRODUK / PART #1:</span>
                            <input class="quote-input" type="text" name="product[0][part_name]" required placeholder="Masukkan Nama Part / Produk..." value="<?= htmlspecialchars($part_name) ?>" style="width: 300px; padding: 6px 10px; font-size: 0.9rem;">
                        </div>
                        <?php if (!$is_edit_mode): ?>
                            <button type="button" class="btn-custom" style="background:#fee2e2; color:#b91c1c;" onclick="hapusBlokProduk(0)">✕ Hapus Produk Ini</button>
                        <?php endif; ?>
                    </div>

                    <div class="matrix-container">
                        <table class="spreadsheet-table">
                            <thead>
                                <tr>
                                    <th width="120">Type System</th>
                                    <th width="240">Desc (Material Packing Standard)</th>
                                    <th width="70">Qty</th>
                                    <th width="100">Qty / Month</th>
                                    <th width="100">Assy / Day<br><small style="color:#64748b;">(Qty Month / 20)</small></th>
                                    <th width="100">Kebutuhan<br>Box / Day</th>
                                    <th width="130">Pembulatan Kebutuhan<br>Box / Assy </th>
                                    <th width="140">Keeping Jumlah Box<br><small style="color:#2563eb;">(Sirkulasi)</small></th>
                                    <th width="160">Investasi Box<br><small style="color:#2563eb;">(Include Bunga %)</small></th>
                                    <th width="140">Qty Part /<br><small style="color:#2563eb;">(N Tahun)</small></th>
                                    <th width="160">Price Pcs/Box<br>(Depresiasi)</th>
                                    <th width="120">Total Packing</th>
                                    <th width="50">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="item-rows-prod-0">
                                <?php if ($is_edit_mode && !empty($existing_items)): ?>
                                    <?php foreach ($existing_items as $index => $item): 
                                        $selected_standard_id = $item['standard_id'];
                                        $selected_price = 0;
                                        $selected_vol = 0;
                                        
                                        // Cari volume & harga jual yang cocok dari master data database
                                        foreach ($packing_master as $pm) {
                                            if ($pm['id'] == $selected_standard_id) {
                                                $selected_price = $pm['harga_jual'];
                                                $selected_vol = $pm['volume_cm3'];
                                                break;
                                            }
                                        }
                                        
                                        $type_system = $item['type_system'];
                                        $is_returnable = ($type_system === 'returnable');
                                    ?>
                                        <tr class="item-calc-row" data-base-price="<?= $selected_price ?>" data-vol="<?= $selected_vol ?>">
                                            <td>
                                                <select class="input-cell status-select <?= $is_returnable ? 'status-returnable' : 'status-non' ?>" name="product[0][items][<?= $index ?>][type_system]" onchange="handleTypeSystemChange(this)">
                                                    <option value="returnable" class="status-returnable" <?= $is_returnable ? 'selected' : '' ?>>Returnable</option>
                                                    <option value="non-returnable" class="status-non" <?= !$is_returnable ? 'selected' : '' ?>>Non-Returnable</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select class="input-cell master-desc-select" name="product[0][items][<?= $index ?>][standard_id]" onchange="handleMaterialChange(this)">
                                                    <option value="0" data-price="0" data-vol="0">-- Pilih Dari Packing Standard --</option>
                                                    <?php foreach($packing_master as $pm): ?>
                                                        <option value="<?= $pm['id'] ?>" data-price="<?= $pm['harga_jual'] ?>" data-vol="<?= isset($pm['volume_cm3']) ? $pm['volume_cm3'] : 0; ?>" <?= $pm['id'] == $selected_standard_id ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($pm['packing_desc']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td><input type="number" class="input-cell cell-qty-input" name="product[0][items][<?= $index ?>][qty]" value="<?= $item['qty'] ?>"></td>
                                            <td><input type="number" class="input-cell cell-qtymonth-input" name="product[0][items][<?= $index ?>][qty_month]" value="<?= $item['qty_month'] ?>"></td>
                                            <td><input type="number" class="input-cell cell-assyday-input" name="product[0][items][<?= $index ?>][assy_day]" value="<?= $item['assy_day'] ?>"></td>
                                            
                                            <td><input type="text" class="input-cell readonly-cell cell-kebutuhan-box-day" readonly value="<?= $item['box_day'] ?>"></td>
                                            <td><input type="text" class="input-cell readonly-cell cell-pembulatan-box" readonly value="<?= $item['box_round'] ?>"></td>
                                            
                                            <td>
                                                <span class="label-mini-header">Sirkulasi:</span>
                                                <input type="number" class="input-cell cell-sirkulasi-input" name="product[0][items][<?= $index ?>][sirkulasi]" value="<?= isset($item['sirkulasi']) ? $item['sirkulasi'] : 12 ?>" style="width:55px; <?= $is_gm ? 'display:inline-block;' : 'display:none;' ?> margin-right:5px;">
                                                <input type="text" class="input-cell readonly-cell cell-keeping-box" readonly value="<?= $item['box_keeping'] ?>" style="width:70px; display:inline-block;">
                                                <?= !$is_gm ? '<span style="font-size:0.8rem; color:#64748b; margin-right:5px;">(-----)</span>' : '' ?>
                                            </td>

                                            <td>
                                                <span class="label-mini-header">Bunga:</span>
                                                <input type="number" step="0.1" class="input-cell cell-bunga-input" name="product[0][items][<?= $index ?>][bunga_pct]" value="<?= isset($item['bunga_pct']) ? $item['bunga_pct'] : 11.5 ?>" style="width:60px; <?= $is_gm ? 'display:inline-block;' : 'display:none;' ?> margin-right:2px;">
                                                <span style="font-size:0.85rem; color:#475569; <?= $is_gm ? 'display:inline-block;' : 'display:none;' ?> margin-right:5px;">%</span>
                                                <?= !$is_gm ? '<span style="font-size:0.8rem; color:#64748b; margin-right:5px;">(-----)</span>' : '' ?>
                                                <input type="text" class="input-cell readonly-cell cell-investasi-box" readonly value="Rp <?= number_format($item['investment'], 0, ',', '.') ?>" style="width:105px; display:inline-block;">
                                            </td>

                                            <td>
                                                <span class="label-mini-header">Tahun:</span>
                                                <?php $tahun_val = isset($item['tahun_dep']) ? $item['tahun_dep'] : 1; ?>
                                                <select class="input-cell cell-tahun-select" name="product[0][items][<?= $index ?>][tahun_dep]" style="width:55px; <?= $is_gm ? 'display:inline-block;' : 'display:none;' ?> margin-right:5px;">
                                                    <option value="1" <?= $tahun_val == 1 ? 'selected' : '' ?>>1 Thn</option>
                                                    <option value="2" <?= $tahun_val == 2 ? 'selected' : '' ?>>2 Thn</option>
                                                    <option value="3" <?= $tahun_val == 3 ? 'selected' : '' ?>>3 Thn</option>
                                                </select>
                                                <?= !$is_gm ? '<span style="font-size:0.8rem; color:#64748b; margin-right:5px;">(-----)</span>' : '' ?>
                                                <input type="text" class="input-cell readonly-cell cell-qty-part-tahun" readonly value="<?= number_format($item['qty_2years'], 0, ',', '.') ?>" style="width:85px; display:inline-block;">
                                            </td>
                                            
                                            <td><input type="text" class="input-cell readonly-cell cell-depresiasi" readonly value="Rp <?= number_format($item['depresiasi'], 2, ',', '.') ?>"></td>
                                            <td><input type="text" class="input-cell readonly-cell total-highlight-cell" readonly value="Rp <?= number_format($item['total_packing'], 2, ',', '.') ?>"></td>
                                            <td align="center">
                                                <button type="button" class="btn-custom" style="background:#ef4444; color:#fff; padding:3px 8px;" onclick="hapusBarisItem(this)">✕</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="item-calc-row" data-base-price="0" data-vol="0">
                                        <td>
                                            <select class="input-cell status-select status-returnable" name="product[0][items][0][type_system]" onchange="handleTypeSystemChange(this)">
                                                <option value="returnable" class="status-returnable" selected>Returnable</option>
                                                <option value="non-returnable" class="status-non">Non-Returnable</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="input-cell master-desc-select" name="product[0][items][0][standard_id]" onchange="handleMaterialChange(this)">
                                                <option value="0" data-price="0" data-vol="0">-- Pilih Dari Packing Standard --</option>
                                                <?php foreach($packing_master as $pm): ?>
                                                    <option value="<?= $pm['id'] ?>" data-price="<?= $pm['harga_jual'] ?>" data-vol="<?= isset($pm['volume_cm3']) ? $pm['volume_cm3'] : 0; ?>">
                                                        <?= htmlspecialchars($pm['packing_desc']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td><input type="number" class="input-cell cell-qty-input" name="product[0][items][0][qty]" value="0"></td>
                                        <td><input type="number" class="input-cell cell-qtymonth-input" name="product[0][items][0][qty_month]" value="0"></td>
                                        <td><input type="number" class="input-cell cell-assyday-input" name="product[0][items][0][assy_day]" value="0"></td>
                                        
                                        <td><input type="text" class="input-cell readonly-cell cell-kebutuhan-box-day" readonly value="0"></td>
                                        <td><input type="text" class="input-cell readonly-cell cell-pembulatan-box" readonly value="0"></td>
                                        
                                        <td>
                                            <span class="label-mini-header">Sirkulasi:</span>
                                            <input type="number" class="input-cell cell-sirkulasi-input" name="product[0][items][0][sirkulasi]" value="12" style="width:55px; <?= $is_gm ? 'display:inline-block;' : 'display:none;' ?> margin-right:5px;">
                                            <input type="text" class="input-cell readonly-cell cell-keeping-box" readonly value="0" style="width:70px; display:inline-block;">
                                            <?= !$is_gm ? '<span style="font-size:0.8rem; color:#64748b; margin-right:5px;">(-----)</span>' : '' ?>
                                        </td>

                                        <td>
                                            <span class="label-mini-header">Bunga:</span>
                                            <input type="number" step="0.1" class="input-cell cell-bunga-input" name="product[0][items][0][bunga_pct]" value="11.5" style="width:60px; <?= $is_gm ? 'display:inline-block;' : 'display:none;' ?> margin-right:2px;">
                                            <span style="font-size:0.85rem; color:#475569; <?= $is_gm ? 'display:inline-block;' : 'display:none;' ?> margin-right:5px;">%</span>
                                            <?= !$is_gm ? '<span style="font-size:0.8rem; color:#64748b; margin-right:5px;">(-----)</span>' : '' ?>
                                            <input type="text" class="input-cell readonly-cell cell-investasi-box" readonly value="Rp 0" style="width:105px; display:inline-block;">
                                        </td>

                                        <td>
                                            <span class="label-mini-header">Tahun:</span>
                                            <select class="input-cell cell-tahun-select" name="product[0][items][0][tahun_dep]" style="width:55px; <?= $is_gm ? 'display:inline-block;' : 'display:none;' ?> margin-right:5px;">
                                                <option value="1" selected>1 Thn</option>
                                                <option value="2">2 Thn</option>
                                                <option value="3">3 Thn</option>
                                            </select>
                                            <?= !$is_gm ? '<span style="font-size:0.8rem; color:#64748b; margin-right:5px;">(-----)</span>' : '' ?>
                                            <input type="text" class="input-cell readonly-cell cell-qty-part-tahun" readonly value="0" style="width:85px; display:inline-block;">
                                        </td>
                                        
                                        <td><input type="text" class="input-cell readonly-cell cell-depresiasi" readonly value="Rp 0"></td>
                                        <td><input type="text" class="input-cell readonly-cell total-highlight-cell" readonly value="Rp 0"></td>
                                        <td align="center">
                                            <button type="button" class="btn-custom" style="background:#ef4444; color:#fff; padding:3px 8px;" onclick="hapusBarisItem(this)">✕</button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background: #f0fdf4; font-weight: bold; color: #16a34a;">
                                    <td colspan="11" align="right" style="padding-right: 15px;">GRAND TOTAL COST PACKING / PCS:</td>
                                    <td style="background: #dcfce7; padding-left: 5px;" class="grand-packing-text">Rp <?= number_format($total_packing_pcs, 2, ',', '.') ?></td>
                                    <td></td>
                                </tr>
                                <tr style="background: #eff6ff; font-weight: bold; color: #1e40af;">
                                    <td colspan="11" align="right" style="padding-right: 15px;">TOTAL ALOKASI TRANSPORT / PCS: </td>
                                    <td style="background: #dbeafe; padding-left: 5px; display: flex; justify-content: space-between; align-items: center; border: none;">
                                        <span class="grand-transport-text">Rp <?= number_format($transport_pcs_total, 2, ',', '.') ?></span>
                                        <button type="button" class="btn-custom" style="background:#2563eb; color:white; padding: 2px 8px; font-size: 0.75rem;" onclick="openTransportModal(0)">Set</button>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <button type="button" class="btn-custom" style="background:#1e293b; color:#fff; margin-top:10px;" onclick="tambahBarisItem(0)">
                         Tambah Item Desc (Material)
                    </button>
                </div>

            </div>

            <?php if (!$is_edit_mode): ?>
                <button type="button" class="btn-custom" style="background:#2563eb; color:#fff; padding:10px 20px; font-size:0.9rem;" onclick="tambahBlokProdukMaster()">
                     Tambah Produk / Part Proyek Baru
                </button>
            <?php endif; ?>

            <div style="margin-top: 40px; text-align: right; border-top: 2px solid #cbd5e1; padding-top: 20px;">
                <button type="submit" class="btn btn-primary" style="padding: 14px 40px; font-size: 1rem;">
                    <?= $is_edit_mode ? 'Perbarui Data Kalkulasi' : 'Simpan Data Kalkulasi' ?>
                </button>
            </div>
        </form>
    </main>
</div>

<div id="transportModal" class="modal-overlay">
    <div class="modal-card" style="width: 700px; max-width: 95%;">
        <h3 style="margin-top:0; border-bottom:1px solid #e2e8f0; padding-bottom:8px; color: #1e3a8a;">Breakdown Transport Cost</h3>
        
        <table class="spreadsheet-table" style="min-width: 100%; margin: 15px 0;">
            <thead>
                <tr>
                    <th>P (cm) *</th><th>L (cm) *</th><th>T (cm) *</th><th>Volume (cm³)</th><th>80% Kapasitas Truck</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="number" class="input-cell" id="truck_p" value="0" oninput="hitungModalTransport()"></td>
                    <td><input type="number" class="input-cell" id="truck_l" value="0" oninput="hitungModalTransport()"></td>
                    <td><input type="number" class="input-cell" id="truck_t" value="0" oninput="hitungModalTransport()"></td>
                    <td><input type="text" class="input-cell readonly-cell" id="truck_vol" readonly value="0"></td>
                    <td><input type="text" class="input-cell readonly-cell" id="truck_cap_80" readonly value="0"></td>
                </tr>
            </tbody>
        </table>

        <div style="margin-bottom: 15px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
            <label style="font-weight: 600; font-size: 0.85rem; color: #475569; display: block; margin-bottom: 5px;">Gunakan Tarif Acuan GA (Opsional):</label>
            <select id="gaCostSelect" class="form-control" style="width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; outline: none;" onchange="applyGaTariff()">
                <option value="">-- Pilih Tarif Berdasarkan Customer GA --</option>
                <?php foreach ($gaOptions as $opt): ?>
                    <option value="<?= $opt['biaya_sewa']; ?>" data-cost-id="<?= (int)$opt['id']; ?>">
                        <?= htmlspecialchars($opt['customer_name']); ?> - <?= htmlspecialchars($opt['keterangan']); ?> (Rp <?= number_format($opt['biaya_sewa'], 0, ',', '.'); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display:flex; gap:15px; margin-bottom: 20px;">
            <div style="flex:1;">
                <label style="font-weight: 600; font-size: 0.85rem;">Biaya Sewa Truck (Rp) *</label>
                <input class="quote-input" type="number" id="truck_cost" value="" oninput="hitungModalTransport()" style="width:100%; padding:6px; box-sizing: border-box;">
            </div>
            <div style="flex:1;">
                <label style="font-weight: 600; font-size: 0.85rem;">Harga / 1 cm³ (Rp)</label>
                <input class="quote-input readonly-cell" type="text" id="cost_per_cm3" readonly value="Rp 0,00" style="width:100%; padding:6px; font-weight:bold; box-sizing: border-box;">
            </div>
        </div>

        <div class="btn-group" style="justify-content: flex-end; gap: 8px;">
            <button type="button" class="btn btn-secondary" onclick="closeTransportModal()">Batal</button>
            <button type="button" class="btn btn-primary" onclick="applyTransport()">Terapkan ke Produk</button>
        </div>
    </div>
</div>

<div id="unloadModal" class="unload-modal-overlay">
    <div class="unload-card">
        <h3 class="unload-title">Perubahan Belum Disimpan!</h3>
        <p class="unload-desc">Anda telah mengubah data kalkulasi packing/transport. Jika Anda keluar sekarang, perubahan tersebut akan hilang.</p>
        <div class="unload-actions">
            <button type="button" class="btn-unload-stay" onclick="closeUnloadModal()">Tetap di Halaman</button>
            <button type="button" class="btn-unload-leave" id="btnConfirmLeave">Tinggalkan Halaman</button>
        </div>
    </div>
</div>

<script>
let isFormDirty = false;
let pendingNavigationUrl = null;

document.addEventListener('input', function(e) {
    if (e.target.closest('form') || e.target.closest('.quotation-card')) {
        isFormDirty = true;
    }
});

document.addEventListener('change', function(e) {
    if (e.target.closest('form') || e.target.closest('.quotation-card')) {
        isFormDirty = true;
    }
});

const mainForm = document.querySelector('form');
if (mainForm) {
    mainForm.addEventListener('submit', function() {
        isFormDirty = false;
    });
}

document.addEventListener('click', function(e) {
    const link = e.target.closest('a');
    if (link && isFormDirty) {
        const href = link.getAttribute('href');
        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
            e.preventDefault();
            pendingNavigationUrl = href;
            openUnloadModal();
        }
    }
});

window.addEventListener('beforeunload', function(e) {
    if (isFormDirty) {
        e.preventDefault();
        e.returnValue = ''; 
    }
});

function openUnloadModal() {
    document.getElementById('unloadModal').classList.add('active');
}

function closeUnloadModal() {
    document.getElementById('unloadModal').classList.remove('active');
    pendingNavigationUrl = null;
}

document.getElementById('btnConfirmLeave').addEventListener('click', function() {
    isFormDirty = false; // Buka proteksi
    if (pendingNavigationUrl) {
        window.location.href = pendingNavigationUrl;
    } else {
        closeUnloadModal();
    }
});
const isGM = <?= $is_gm ? 'true' : 'false' ?>;
let packing_master = <?= json_encode($packing_master); ?>;
let productIndexCount = 1;
let itemRowIndexMap = { 0: <?= $is_edit_mode ? count($existing_items) : 1 ?> }; 
let currentActiveProductTarget = null; 

function showNotification(msg, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    let icon = '✓';
    if (type === 'error') icon = '✕';
    if (type === 'info') icon = 'ℹ';
    if (type === 'warning') icon = '⚠';

    toast.innerHTML = `<span class="toast-icon">${icon}</span><span class="toast-message">${msg}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => toast.remove(), 400);
    }, 2000);
}

function hitungRumusOtomatis(row) {
    if (!row) return;

    const qtyInput          = row.querySelector('.cell-qty-input');
    const qtyMonthInput     = row.querySelector('.cell-qtymonth-input');
    const assyDayInput      = row.querySelector('.cell-assyday-input');
    const typeSystemSelect  = row.querySelector('.status-select');
    const sirkulasiInput    = row.querySelector('.cell-sirkulasi-input');
    const bungaInput        = row.querySelector('.cell-bunga-input');
    const tahunSelect       = row.querySelector('.cell-tahun-select');

    const kebuBoxDayOut     = row.querySelector('.cell-kebutuhan-box-day');
    const pembKebuOut       = row.querySelector('.cell-pembulatan-box');
    const qtyBoxDayOut      = row.querySelector('.cell-keeping-box');
    const investasiOut      = row.querySelector('.cell-investasi-box');
    const qtyPartThnOut     = row.querySelector('.cell-qty-part-tahun');
    const depresiasiOut     = row.querySelector('.cell-depresiasi');
    const totalPackingOut   = row.querySelector('.total-highlight-cell');

    const qty          = parseFloat(qtyInput.value) || 0;
    const qtyMonth     = parseFloat(qtyMonthInput.value) || 0;
    const typeSystem   = typeSystemSelect ? typeSystemSelect.value : 'returnable';
    const hargaBox     = parseFloat(row.getAttribute('data-base-price')) || 0;

    let assyDay = qtyMonth / 20;
    if (document.activeElement !== assyDayInput) {
        assyDayInput.value = assyDay;
    } else {
        assyDay = parseFloat(assyDayInput.value) || 0;
    }

    const sirkulasi = parseFloat(sirkulasiInput ? sirkulasiInput.value : 12) || 0;
    const tahunN    = parseFloat(tahunSelect ? tahunSelect.value : 1) || 1;
    const bungaPct  = parseFloat(bungaInput ? bungaInput.value : 11.5) || 11.5;                                   

    let kebuBoxDay = 0;
    let pembKebu = 0;
    let qtyBoxDay = 0;
    let investasi = 0;
    let qtyPartThn = 0;
    let depresiasi = 0;
    let totalPacking = 0;

    if (typeSystem === 'returnable') {
        if (qty > 0) kebuBoxDay = assyDay / qty;
        pembKebu = Math.ceil(kebuBoxDay);
        qtyBoxDay = pembKebu * sirkulasi;
        const totalBungaPct = bungaPct * tahunN; 
        investasi = (qtyBoxDay * hargaBox) * (1 + (totalBungaPct / 100));
        qtyPartThn = qtyMonth * 12 * tahunN;
        if (qtyPartThn > 0) depresiasi = investasi / qtyPartThn;
        totalPacking = depresiasi;
    } else {
        if (qty > 0) totalPacking = hargaBox / qty;
    }

    if(kebuBoxDayOut) kebuBoxDayOut.value = kebuBoxDay.toFixed(2);
    if(pembKebuOut)   pembKebuOut.value = pembKebu;
    if(qtyBoxDayOut)  qtyBoxDayOut.value = qtyBoxDay;
    if(investasiOut)  investasiOut.value = "Rp " + Math.round(investasi).toLocaleString('id-ID');
    if(qtyPartThnOut) qtyPartThnOut.value = qtyPartThn.toLocaleString('id-ID');
    if(depresiasiOut) depresiasiOut.value = "Rp " + depresiasi.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    if(totalPackingOut) {
        totalPackingOut.value = "Rp " + totalPacking.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        row.setAttribute('data-total-packing', totalPacking);
    }

    const tbody = row.closest('tbody');
    if (tbody) hitungGrandTotalProduk(tbody);
}

function hitungGrandTotalProduk(tbody) {
    const rows = tbody.querySelectorAll('.item-calc-row');
    let grandTotalPacking = 0;
    rows.forEach(r => { 
        grandTotalPacking += parseFloat(r.getAttribute('data-total-packing')) || 0; 
    });

    const table = tbody.closest('table');
    if (table) {
        const textTarget = table.querySelector('.grand-packing-text');
        if (textTarget) {
            textTarget.innerText = "Rp " + grandTotalPacking.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }
    }

    const tbodyId = tbody.id; 
    const prodId = tbodyId.replace('item-rows-prod-', '');
    const hiddenInputPack = document.getElementById(`hidden-packpcs-${prodId}`);
    if (hiddenInputPack) {
        hiddenInputPack.value = grandTotalPacking.toFixed(2);
    }
}                

function handleMaterialChange(selectNode) {
    const selectedOption = selectNode.options[selectNode.selectedIndex];
    const row = selectNode.closest('tr');
    if(row) {
        row.setAttribute('data-base-price', parseFloat(selectedOption.getAttribute('data-price')) || 0);
        row.setAttribute('data-vol', parseFloat(selectedOption.getAttribute('data-vol')) || 0); 
        hitungRumusOtomatis(row);
    }
}

function handleTypeSystemChange(selectNode) {
    selectNode.className = "input-cell status-select " + (selectNode.value === 'returnable' ? "status-returnable" : "status-non");
    hitungRumusOtomatis(selectNode.closest('tr'));
}

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('input-cell')) {
        const row = e.target.closest('.item-calc-row');
        if (row) hitungRumusOtomatis(row);
    }
});

document.addEventListener('change', function(e) {
    if (e.target.tagName === 'SELECT') {
        const row = e.target.closest('.item-calc-row');
        if (row) hitungRumusOtomatis(row);
    }
});

function openTransportModal(prodId) { 
    currentActiveProductTarget = prodId;
    document.getElementById('truck_p').value = document.getElementById(`hidden-p-${prodId}`).value;
    document.getElementById('truck_l').value = document.getElementById(`hidden-l-${prodId}`).value;
    document.getElementById('truck_t').value = document.getElementById(`hidden-t-${prodId}`).value;
    document.getElementById('truck_cost').value = document.getElementById(`hidden-cost-${prodId}`).value;
    document.getElementById('transportModal').classList.add('active'); 
    hitungModalTransport();
}

function closeTransportModal() { document.getElementById('transportModal').classList.remove('active'); }

function hitungModalTransport() {
    const p = parseFloat(document.getElementById('truck_p').value) || 0;
    const l = parseFloat(document.getElementById('truck_l').value) || 0;
    const t = parseFloat(document.getElementById('truck_t').value) || 0;
    const sewa = parseFloat(document.getElementById('truck_cost').value) || 0;

    const volume = p * l * t;
    document.getElementById('truck_vol').value = volume.toLocaleString('id-ID');

    const cap80 = volume * 0.8;
    document.getElementById('truck_cap_80').value = cap80.toLocaleString('id-ID');

    const hargaCm3 = cap80 > 0 ? (sewa / cap80) : 0;
    document.getElementById('cost_per_cm3').setAttribute('data-raw', hargaCm3);
    document.getElementById('cost_per_cm3').value = "Rp " + hargaCm3.toLocaleString('id-ID', {minimumFractionDigits: 3, maximumFractionDigits: 3});
}

function applyTransport() {
    if (currentActiveProductTarget !== null) {
        const hargaCm3 = parseFloat(document.getElementById('cost_per_cm3').getAttribute('data-raw')) || 0;
        const tbody = document.getElementById(`item-rows-prod-${currentActiveProductTarget}`);
        const firstRow = tbody ? tbody.querySelectorAll('.item-calc-row')[0] : null;
        
        if (firstRow) {
            const qtyInput = firstRow.querySelector('.cell-qty-input');
            const qtyItemPertama = qtyInput ? parseFloat(qtyInput.value) : 1;
            const validQty = qtyItemPertama <= 0 ? 1 : qtyItemPertama;

            let volumeItemPertama = parseFloat(firstRow.getAttribute('data-vol')) || 0;
            if (volumeItemPertama === 0) {
                const inputUser = prompt("Sistem mendeteksi volume material ini 0 cm³.\nSilakan masukkan estimasi Volume luar untuk 1 Box/Packing ini (dalam cm³):", "35000");
                volumeItemPertama = parseFloat(inputUser) || 0;
                firstRow.setAttribute('data-vol', volumeItemPertama);
            }

            const hasilTransportPcs = (hargaCm3 * volumeItemPertama) / validQty;
            const blockBox = document.getElementById(`product-block-${currentActiveProductTarget}`);
            if (blockBox) {
                blockBox.querySelector('.grand-transport-text').innerText = "Rp " + hasilTransportPcs.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }

            document.getElementById(`hidden-p-${currentActiveProductTarget}`).value = document.getElementById('truck_p').value;
            document.getElementById(`hidden-l-${currentActiveProductTarget}`).value = document.getElementById('truck_l').value;
            document.getElementById(`hidden-t-${currentActiveProductTarget}`).value = document.getElementById('truck_t').value;
            document.getElementById(`hidden-cost-${currentActiveProductTarget}`).value = document.getElementById('truck_cost').value;
            document.getElementById(`hidden-transpcs-${currentActiveProductTarget}`).value = hasilTransportPcs;

            showNotification('Alokasi Transport Cost berhasil diterapkan!', 'success');
        }
    }
    closeTransportModal();
}

function tambahBlokProdukMaster() {
    const wrapper = document.getElementById('products-master-wrapper');
    const newBlock = document.createElement('div');
    newBlock.className = 'product-group-box';
    newBlock.id = `product-block-${productIndexCount}`;
    
    itemRowIndexMap[productIndexCount] = 1;

    let optionsHtml = '<option value="0" data-price="0" data-vol="0">-- Pilih Dari Packing Standard --</option>';
    packing_master.forEach(pm => {
        optionsHtml += `<option value="${pm.id}" data-price="${pm.harga_jual}" data-vol="${pm.volume_cm3 || 0}">${pm.packing_desc}</option>`;
    });

    newBlock.innerHTML = `
        <input type="hidden" name="product[${productIndexCount}][truck_p]" id="hidden-p-${productIndexCount}" value="0">
        <input type="hidden" name="product[${productIndexCount}][truck_l]" id="hidden-l-${productIndexCount}" value="0">
        <input type="hidden" name="product[${productIndexCount}][truck_t]" id="hidden-t-${productIndexCount}" value="0">
        <input type="hidden" name="product[${productIndexCount}][truck_cost]" id="hidden-cost-${productIndexCount}" value="0">
        <input type="hidden" name="product[${productIndexCount}][transport_pcs_total]" id="hidden-transpcs-${productIndexCount}" value="0">
        <input type="hidden" name="product[${productIndexCount}][total_packing_pcs]" id="hidden-packpcs-${productIndexCount}" value="0">

        <div class="product-header-title">
            <div style="display: flex; align-items: center; gap: 15px; width: 70%;">
                <span>DATA PRODUK / PART #${productIndexCount + 1}:</span>
                <input class="quote-input" type="text" name="product[${productIndexCount}][part_name]" required placeholder="Masukkan Nama Part / Produk..." style="width: 300px; padding: 6px 10px; font-size: 0.9rem;">
            </div>
            <button type="button" class="btn-custom" style="background:#fee2e2; color:#b91c1c;" onclick="hapusBlokProduk(${productIndexCount})">✕ Hapus Produk Ini</button>
        </div>

        <div class="matrix-container">
            <table class="spreadsheet-table">
                <thead>
                    <tr>
                        <th width="120">Type System</th>
                        <th width="240">Desc (Material Packing Standard)</th>
                        <th width="70">Qty</th>
                        <th width="100">Qty / Month</th>
                        <th width="100">Assy / Day<br><small style="color:#64748b;">(Qty Month / 20)</small></th>
                        <th width="100">Kebutuhan<br>Box / Day</th>
                        <th width="130">Pembulatan Kebutuhan<br>Box / Assy</th>
                        <th width="140">Keeping Jumlah Box<br><small style="color:#2563eb;">(Sirkulasi)</small></th>
                        <th width="160">Investasi Box<br><small style="color:#2563eb;">(Include Bunga %)</small></th>
                        <th width="140">Qty Part /<br><small style="color:#2563eb;">(N Tahun)</small></th>
                        <th width="160">Price Pcs/Box<br>(Depresiasi)</th>
                        <th width="120">Total Packing</th>
                        <th width="50">Aksi</th>
                    </tr>
                </thead>
                <tbody id="item-rows-prod-${productIndexCount}">
                    <tr class="item-calc-row" data-base-price="0" data-vol="0">
                        <td>
                            <select class="input-cell status-select status-returnable" name="product[${productIndexCount}][items][0][type_system]" onchange="handleTypeSystemChange(this)">
                                <option value="returnable" class="status-returnable" selected>Returnable</option>
                                <option value="non-returnable" class="status-non">Non-Returnable</option>
                            </select>
                        </td>
                        <td>
                            <select class="input-cell master-desc-select" name="product[${productIndexCount}][items][0][standard_id]" onchange="handleMaterialChange(this)">
                                ${optionsHtml}
                            </select>
                        </td>
                        <td><input type="number" class="input-cell cell-qty-input" name="product[${productIndexCount}][items][0][qty]" value="1"></td>
                        <td><input type="number" class="input-cell cell-qtymonth-input" name="product[${productIndexCount}][items][0][qty_month]" value="0"></td>
                        <td><input type="number" class="input-cell cell-assyday-input" name="product[${productIndexCount}][items][0][assy_day]" value="0"></td>
                        
                        <td><input type="text" class="input-cell readonly-cell cell-kebutuhan-box-day" readonly value="0"></td>
                        <td><input type="text" class="input-cell readonly-cell cell-pembulatan-box" readonly value="0"></td>
                        
                        <td>
                            <span class="label-mini-header">Sirkulasi:</span>
                            <input type="number" class="input-cell cell-sirkulasi-input" name="product[${productIndexCount}][items][0][sirkulasi]" value="12" style="width:55px; display: ${isGM ? 'inline-block' : 'none'}; margin-right:5px;">
                            <input type="text" class="input-cell readonly-cell cell-keeping-box" readonly value="0" style="width:70px; display:inline-block;">
                            ${!isGM ? '<span style="font-size:0.8rem; color:#64748b; margin-right:5px;">(-----)</span>' : ''}
                        </td>
                        <td>
                            <span class="label-mini-header">Bunga:</span>
                            <input type="number" step="0.1" class="input-cell cell-bunga-input" name="product[${productIndexCount}][items][0][bunga_pct]" value="11.5" style="width:60px; display: ${isGM ? 'inline-block' : 'none'}; margin-right:2px;">
                            <span style="font-size:0.85rem; color:#475569; display: ${isGM ? 'inline-block' : 'none'}; margin-right:5px;">%</span>
                            ${!isGM ? '<span style="font-size:0.8rem; color:#64748b; margin-right:5px;">(-----)</span>' : ''}
                            <input type="text" class="input-cell readonly-cell cell-investasi-box" readonly value="Rp 0" style="width:105px; display:inline-block;">
                        </td>
                        <td>
                            <span class="label-mini-header">Tahun:</span>
                            <select class="input-cell cell-tahun-select" name="product[${productIndexCount}][items][0][tahun_dep]" style="width:55px; display: ${isGM ? 'inline-block' : 'none'}; margin-right:5px;">
                                <option value="1">1 Thn</option>
                                <option value="2" selected>2 Thn</option>
                                <option value="3">3 Thn</option>
                            </select>
                            ${!isGM ? '<span style="font-size:0.8rem; color:#64748b; margin-right:5px;">(-----)</span>' : ''}
                            <input type="text" class="input-cell readonly-cell cell-qty-part-tahun" readonly value="0" style="width:85px; display:inline-block;">
                        </td>
                        
                        <td><input type="text" class="input-cell readonly-cell cell-depresiasi" readonly value="Rp 0"></td>
                        <td><input type="text" class="input-cell readonly-cell total-highlight-cell" readonly value="Rp 0"></td>
                        <td align="center"><button type="button" class="btn-custom" style="background:#ef4444; color:#fff; padding:3px 8px;" onclick="hapusBarisItem(this)">✕</button></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: #f0fdf4; font-weight: bold; color: #16a34a;">
                        <td colspan="11" align="right" style="padding-right: 15px;">GRAND TOTAL COST PACKING / PCS:</td>
                        <td style="background: #dcfce7; padding-left: 5px;" class="grand-packing-text">Rp 0,00</td>
                        <td></td>
                    </tr>
                    <tr style="background: #eff6ff; font-weight: bold; color: #1e40af;">
                        <td colspan="11" align="right" style="padding-right: 15px;">TOTAL ALOKASI TRANSPORT / PCS:</td>
                        <td style="background: #dbeafe; padding-left: 5px; display: flex; justify-content: space-between; align-items: center; border: none;">
                            <span class="grand-transport-text">Rp 0,00</span>
                            <button type="button" class="btn-custom" style="background:#2563eb; color:white; padding: 2px 8px; font-size: 0.75rem;" onclick="openTransportModal(${productIndexCount})">Set</button>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <button type="button" class="btn-custom" style="background:#1e293b; color:#fff; margin-top:10px;" onclick="tambahBarisItem(${productIndexCount})">
            Tambah Item Desc (Material)
        </button>
    `;
    wrapper.appendChild(newBlock);
    showNotification(`Produk #${productIndexCount + 1} berhasil ditambahkan!`, 'info');
    productIndexCount++;
}

function tambahBarisItem(prodId) {
    const tbody = document.getElementById(`item-rows-prod-${prodId}`);
    const localIndex = itemRowIndexMap[prodId];
    const tr = document.createElement('tr');
    tr.className = 'item-calc-row';
    tr.setAttribute('data-base-price', '0');
    tr.setAttribute('data-vol', '0');

    let optionsHtml = '<option value="0" data-price="0" data-vol="0">-- Pilih Dari Packing Standard --</option>';
    packing_master.forEach(pm => {
        optionsHtml += `<option value="${pm.id}" data-price="${pm.harga_jual}" data-vol="${pm.volume_cm3 || 0}">${pm.packing_desc}</option>`;
    });

    tr.innerHTML = `
        <td>
            <select class="input-cell status-select status-returnable" name="product[${prodId}][items][${localIndex}][type_system]" onchange="handleTypeSystemChange(this)">
                <option value="returnable" class="status-returnable" selected>Returnable</option>
                <option value="non-returnable" class="status-non">Non-Returnable</option>
            </select>
        </td>
        <td>
            <select class="input-cell master-desc-select" name="product[${prodId}][items][${localIndex}][standard_id]" onchange="handleMaterialChange(this)">
                ${optionsHtml}
            </select>
        </td>
        <td><input type="number" class="input-cell cell-qty-input" name="product[${prodId}][items][${localIndex}][qty]" value="1"></td>
        <td><input type="number" class="input-cell cell-qtymonth-input" name="product[${prodId}][items][${localIndex}][qty_month]" value="0"></td>
        <td><input type="number" class="input-cell cell-assyday-input" name="product[${prodId}][items][${localIndex}][assy_day]" value="0"></td>
        <td><input type="text" class="input-cell readonly-cell cell-kebutuhan-box-day" readonly value="0"></td>
        <td><input type="text" class="input-cell readonly-cell cell-pembulatan-box" readonly value="0"></td>
        <td>
            <span class="label-mini-header">Sirkulasi:</span>
            <input type="number" class="input-cell cell-sirkulasi-input" name="product[${prodId}][items][${localIndex}][sirkulasi]" value="12" style="width:55px; display: ${isGM ? 'inline-block' : 'none'}; margin-right:5px;">
            <input type="text" class="input-cell readonly-cell cell-keeping-box" readonly value="0" style="width:70px; display:inline-block;">
            ${!isGM ? '<span style="font-size:0.8rem; color:#64748b; margin-right:5px;">(-----)</span>' : ''}
        </td>
        <td>
            <span class="label-mini-header">Bunga:</span>
            <input type="number" step="0.1" class="input-cell cell-bunga-input" name="product[${prodId}][items][${localIndex}][bunga_pct]" value="11.5" style="width:60px; display: ${isGM ? 'inline-block' : 'none'}; margin-right:2px;">
            <span style="font-size:0.85rem; color:#475569; display: ${isGM ? 'inline-block' : 'none'}; margin-right:5px;">%</span>
            ${!isGM ? '<span style="font-size:0.8rem; color:#64748b; margin-right:5px;">(-----)</span>' : ''}
            <input type="text" class="input-cell readonly-cell cell-investasi-box" readonly value="Rp 0" style="width:105px; display:inline-block;">
        </td>
        <td>
            <span class="label-mini-header">Tahun:</span>
            <select class="input-cell cell-tahun-select" name="product[${prodId}][items][${localIndex}][tahun_dep]" style="width:55px; display: ${isGM ? 'inline-block' : 'none'}; margin-right:5px;">
                <option value="1">1 Thn</option>
                <option value="2" selected>2 Thn</option>
                <option value="3">3 Thn</option>
            </select>
            ${!isGM ? '<span style="font-size:0.8rem; color:#64748b; margin-right:5px;">(-----)</span>' : ''}
            <input type="text" class="input-cell readonly-cell cell-qty-part-tahun" readonly value="0" style="width:85px; display:inline-block;">
        </td>
        <td><input type="text" class="input-cell readonly-cell cell-depresiasi" readonly value="Rp 0"></td>
        <td><input type="text" class="input-cell readonly-cell total-highlight-cell" readonly value="Rp 0"></td>
        <td align="center"><button type="button" class="btn-custom" style="background:#ef4444; color:#fff; padding:3px 8px;" onclick="hapusBarisItem(this)">✕</button></td>
    `;
    tbody.appendChild(tr);
    showNotification('Item material baru berhasil ditambahkan.', 'info');
    itemRowIndexMap[prodId]++;
}

function hapusBarisItem(btn) {
    const tbody = btn.closest('tbody');
    if(tbody.rows.length > 1) { 
        btn.closest('tr').remove(); 
        hitungGrandTotalProduk(tbody); 
        showNotification('Item material berhasil dihapus.', 'warning');
    } else { 
        showNotification('Minimal harus ada 1 jenis material!', 'error'); 
    }
}

function hapusBlokProduk(prodId) {
    const wrapper = document.getElementById('products-master-wrapper');
    if(wrapper.children.length > 1) { 
        document.getElementById(`product-block-${prodId}`).remove(); 
        showNotification('Blok produk berhasil dihapus.', 'warning');
    } else { 
        showNotification('Minimal pengerjaan harus ada 1 produk!', 'error'); 
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const allRows = document.querySelectorAll('.item-calc-row');
    allRows.forEach(row => {
        hitungRumusOtomatis(row);
    });

    const toastMessage = document.querySelector('.toast');
    if (toastMessage) {
        toastMessage.style.transition = "opacity 0.5s ease, transform 0.5s ease";
        setTimeout(() => {
            toastMessage.style.opacity = "0";
            toastMessage.style.transform = "translateY(-20px)";
            setTimeout(() => { toastMessage.remove(); }, 500);
        }, 2000); 
    }

    const bc = new BroadcastChannel('matrix_update');
    const savedPackTransEvents = <?= json_encode($packTransEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    savedPackTransEvents.forEach((packTransEvent) => bc.postMessage(packTransEvent));
    bc.onmessage = (event) => {
        if (!event.data) return;

        if (event.data.event === 'new_packing_material') {
            const newItem = event.data.data;
            const ada = packing_master.some(item => item.id == newItem.id);
            if (!ada) {
                packing_master.push({
                    id: newItem.id,
                    packing_desc: newItem.description,
                    harga_jual: newItem.harga_jual,
                    volume_cm3: newItem.volume_cm3
                });
            }
            
            const dropdowns = document.querySelectorAll('.master-desc-select');
            dropdowns.forEach(select => {
                if (!select.querySelector(`option[value="${newItem.id}"]`)) {
                    const option = document.createElement('option');
                    option.value = newItem.id;
                    option.text = newItem.description;
                    option.setAttribute('data-price', newItem.harga_jual);
                    option.setAttribute('data-vol', newItem.volume_cm3 || 0);
                    select.appendChild(option);
                }
            });
            showNotification(`Packing Standard Baru Diterima: ${newItem.description}`, 'info');
        }

        const selectElement = document.getElementById('gaCostSelect');
        if (selectElement) {
            if (event.data.event === 'new_transport_cost') {
                const newCost = event.data.data;
                const targetValue = newCost.biaya_sewa; 
                
                const isExist = selectElement.querySelector(`option[value="${targetValue}"]`);
                if (!isExist) {
                    const option = document.createElement('option');
                    option.value = targetValue;
                    option.dataset.costId = newCost.id;
                    const formattedSewa = Number(newCost.biaya_sewa).toLocaleString('id-ID');
                    option.textContent = `${newCost.customer_name} - ${newCost.keterangan} (Rp ${formattedSewa})`;
                    selectElement.appendChild(option);
                }
                showNotification(`Opsi Tarif GA Baru Diterima dari ${newCost.customer_name}`, 'info');
            }

            if (event.data.event === 'delete_transport_cost' || event.data.event === 'archive_transport_cost') {
                const deletedCost = event.data.data;
                const optionToDelete = selectElement.querySelector(`option[data-cost-id="${deletedCost.id}"]`);
                if (optionToDelete) {
                    optionToDelete.remove();
                    if (selectElement.value == optionToDelete.value) {
                        selectElement.value = "";
                        selectElement.dispatchEvent(new Event('change')); 
                    }
                }
                const actionLabel = event.data.event === 'archive_transport_cost' ? 'diarsipkan' : 'dihapus';
                showNotification(`Opsi Tarif GA telah ${actionLabel}.`, 'warning');
            }
        }
    };
});

function applyGaTariff() {
    const select = document.getElementById('gaCostSelect');
    const selectedSewaValue = select.value;
    const sewaInput = document.getElementById('truck_cost');
    
    if (selectedSewaValue && sewaInput) {
        sewaInput.value = selectedSewaValue;
        hitungModalTransport();
        showNotification('Tarif GA berhasil diterapkan ke perhitungan truck!', 'info');
    }
}
</script>
<script>
setInterval(async function () {
    try {
        const response = await fetch('check_session.php', { cache: 'no-store' });
        const result = await response.json();

        if (!result.valid) {
            window.location.href = 'index.php?error=session_conflict';
        }
    } catch (e) {

    }
}, 5000);
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
<script src="back-to-top.js"></script>
</body>
</html>