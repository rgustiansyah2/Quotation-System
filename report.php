<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
include 'config/database.php';

$roleCheck = strtolower($_SESSION['role'] ?? '');
if ($roleCheck === 'general manager' || $roleCheck === 'admin' || $roleCheck === 'manager') {
    $conn->query("UPDATE tbl_quotation SET is_read = 1 WHERE is_read = 0 OR is_read IS NULL");
}

$loggedIn = !empty($_SESSION['user_id']);
$fullname = $_SESSION['fullname'] ?? 'Guest';
$role     = $_SESSION['role'] ?? 'User';
$message  = '';

$quotations = [];
$conn->query("SET SESSION group_concat_max_len = 100000");

$query = "SELECT q.quotation_no, q.quotation_date, q.currency, q.quotation_note,
                 c.customer_name, c.pic, c.address as customer_address, c.phone as customer_phone, c.email as customer_email,
                 d.draft_title,
                 COUNT(q.id) as total_slots,
                 SUM(q.selling_price) as total_value,
                 CONCAT('[', GROUP_CONCAT(
                    JSON_OBJECT(
                        'type', q.type,
                        'part_number', q.part_number,
                        'part_name', q.part_name,
                        'material_spec', q.material_spec,
                        'basic_price', q.basic_price,
                        'currency', q.currency,
                        'pigmen_cost', q.pigmen_cost,
                        'weight_per_pcs', q.weight_per_pcs,
                        'idr_price_kg', q.idr_price_kg,
                        'material_cost', q.material_price,
                        'purging', q.purging,
                        'dandori', q.dandori,
                        'cycle_time', q.cycle_time,
                        'cavity', q.cavity,
                        'mc_ton', q.mc_ton,
                        'rate_hour', q.rate_hour,
                        'process_cost', q.process_cost,
                        'rejection_rate', q.rejection_cost,
                        'other_process_type', q.other_process_type,
                        'ct_other', q.ct_other,
                        'rate_annealing', q.rate_annealing,
                        'other_process_cost', q.other_process_cost,
                        'packing', q.packing_cost,
                        'transport', q.transport_cost,
                        'cogs', q.cogs,
                        'oh_profit', q.oh_profit,
                        'mold_mtn', q.mold_maintenance,
                        'mold_price', q.mold_price,
                        'depreciation_years', q.depreciation_years,
                        'mold_depreciation_pcs', q.mold_depreciation_pcs,
                        'total', q.selling_price,
                        'lumpsum_price', COALESCE(q.lumpsum_pcs, q.lumpsum_price, 0),
                        'cr_mode', q.cr_mode,
                        'cr_base_val', q.cr_base_val,
                        'cr_lta_years', COALESCE(q.cr_lta_years, 3),
                        'cr_lta_pct_json', q.cr_lta_pct_json,
                        'cr_lta_res_json', q.cr_lta_res_json,
                        'cr_pct_bl', q.cr_pct_bl,
                        'cr_final_cost', q.cr_final_cost
                    )
                 ), ']') as items_json
          FROM tbl_quotation q
          LEFT JOIN tbl_customer c ON q.customer_id = c.id
          LEFT JOIN tbl_rate_drafts d ON q.rate_draft_id = d.id
          GROUP BY q.quotation_no
          ORDER BY q.quotation_date DESC, q.id DESC";

$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $quotations[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Report - QuotationApp</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="quotation.css">
    <link rel="stylesheet" href="sidebar.css?v=1">
    <link rel="stylesheet" href="header.css?v=1">
    <link rel="stylesheet" href="customer.css">
    <link rel="stylesheet" href="report.css?v=2"> 
</head>
<body>
    <?php include 'header.php'; ?>
    
    <?php include 'sidebar.php'; ?>

<div class="page-shell">
    <header class="topbar">
        <div>
            <h3>QUOTATIONAPP</h3>
            <p>Quotation Report</p>
        </div>
        <div class="topbar-right">
            <?php if ($loggedIn): ?>
                <div class="user-pill">
                    <?= htmlspecialchars($fullname) ?> · <?= htmlspecialchars($role) ?>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="quotation-card">
        <section class="panel calc-box">
            <div class="calc-toolbar calc-toolbar-flex">
                <h3>Daftar Dokumen Quotation Resmi</h3>
                <span class="customer-meta-info">Total: <strong><?= count($quotations) ?></strong> Dokumen</span>
            </div>

            <div class="search-container">
                <input type="text" id="liveSearchInput" class="search-input" placeholder="Cari No. Quotation atau Customer..." onkeyup="filterQuotationTable()">
            </div>
            
            <div class="calc-table-wrap">
                <table class="quote-table" id="quotationTable">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>No. Quotation</th>
                            <th>Tanggal</th>
                            <th>Nama Customer / Project</th>
                            <th>PIC Attn</th>
                            <th style="text-align:center;">Jumlah Slot</th>
                            <th style="text-align:right;">Total Nilai Penawaran</th>
                            <th width="200" style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($quotations)): ?>
                            <tr class="empty-state-row">
                                <td colspan="8" align="center" class="text-empty-state" style="padding:30px; color:#94a3b8; font-style:italic;">Belum ada data Quotation yang disimpan.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($quotations as $index => $q): 
                                $sym = ($q['currency'] === 'USD' || $q['currency'] === '$') ? '$' : 'Rp ';
                            ?>
                                <tr class="quotation-row">
                                    <td align="center" class="row-index"><?= $index + 1 ?></td>
                                    <td class="search-quo-no"><strong><?= htmlspecialchars($q['quotation_no']) ?></strong></td>
                                    <td><?= date('d-m-Y', strtotime($q['quotation_date'])) ?></td>
                                    <td class="search-cust-name">
                                        <strong><?= htmlspecialchars($q['customer_name']) ?></strong>
                                        <?php if(!empty($q['draft_title'])): ?>
                                            <span class="acuan-text">Acuan: <?= htmlspecialchars($q['draft_title']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($q['pic'] ?: '-') ?></td>
                                    <td align="center">
                                        <span class="badge-slots"><?= $q['total_slots'] ?> Items</span>
                                    </td>
                                    <td align="right" class="row-total-val">
                                        <?= $sym . number_format($q['total_value'], 2, ',', '.') ?>
                                    </td>
                                    <td align="center">
                                        <div class="action-btn-group">
                                            <button type="button" class="btn-review-quo" 
                                                    data-no="<?= htmlspecialchars($q['quotation_no']) ?>"
                                                    data-date="<?= date('d-m-Y', strtotime($q['quotation_date'])) ?>"
                                                    data-customer="<?= htmlspecialchars($q['customer_name']) ?>"
                                                    data-pic="<?= htmlspecialchars($q['pic'] ?? '') ?>"
                                                    data-address="<?= htmlspecialchars($q['customer_address'] ?? '') ?>"
                                                    data-phone="<?= htmlspecialchars($q['customer_phone'] ?? '') ?>"
                                                    data-email="<?= htmlspecialchars($q['customer_email'] ?? '') ?>"
                                                    data-notes="<?= htmlspecialchars($q['quotation_note'] ?? '') ?>"
                                                    data-items="<?= htmlspecialchars($q['items_json'] ?? '[]', ENT_QUOTES, 'UTF-8') ?>"
                                                    onclick="handleReviewClick(this)">
                                                 Review Penawaran
                                            </button>

                                            <a href="export_pdf.php?quotation_no=<?= urlencode($q['quotation_no']) ?>" target="_blank" class="btn-pdf-quo">
                                                Download PDF
                                            </a>
                                            <a href="quotation.php?quotation_no=<?= urlencode($q['quotation_no']) ?>" class="btn-edit-quo">
                                                 Edit Quotation
                                            </a>
                                            <button type="button" class="btn-delete-quo" onclick="hapusQuotation('<?= htmlspecialchars($q['quotation_no'], ENT_QUOTES) ?>')">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="noMatchRow" style="display:none; text-align:center;">
                                <td colspan="8" style="padding:30px; color:#94a3b8; font-style:italic;">Data tidak ditemukan berdasarkan kata kunci pencarian.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<div id="reviewModal" class="modal-overlay">
    <div class="modal-review-card" style="max-width: 95vw; width: 1400px;">
        <div class="modal-review-header">
            <div>
                <h3>Hasil Review Perhitungan Harga Dokumen</h3>
                <p>Pratinjau data penawaran resmi yang sudah tersimpan di database sistem.</p>
            </div>
            <button type="button" onclick="closeReviewModal()" class="btn-close-review">✕ Tutup Review</button>
        </div>
        
        <div class="modal-review-body">
            <div id="modalQuoMeta"></div>
            
            <div class="calc-table-wrap review-table-container" style="overflow-x: auto;">
                <table class="quote-table review-table-mini">
                    <thead>
                        <tr class="review-table-header">
                            <th>No</th>
                            <th>Type</th>
                            <th>Part Number</th>
                            <th>Part Name</th>
                            <th>Material Spec</th>
                            <th>Basic Price</th>
                            <th>Pigmen Cost</th>
                            <th>Weight/Pcs</th>
                            <th>IDR/Kg</th>
                            <th>Material Cost/Pcs</th>
                            <th>Purging (Pcs)</th>
                            <th>Dandori (Pcs)</th>
                            <th>C/T</th>
                            <th>Cav</th>
                            <th>M/C Ton</th>
                            <th>Rate/sec</th>
                            <th>Proc. Cost</th>
                            <th>Reject Cost</th>
                            <th>Other Process</th>
                            <th>Packing</th>
                            <th>Transport</th>
                            <th class="col-cogs">COGS</th>
                            <th>OH/Profit</th>
                            <th>Mold Mtn</th>
                            <th>Depreciation Mold</th>
                            <th class="col-selling-price">Total Price</th>
                            <th class="col-lumpsum">Lump Sum</th>
                        </tr>
                    </thead>
                    <tbody id="modalReviewRows"></tbody>
                </table>
            </div>

            <div class="notes-container" style="margin-top: 15px;">
                <label class="notes-label">Notes:</label>
                <textarea id="modalQuotationNotes" readonly class="notes-textarea"></textarea>
            </div>
        </div>
    </div>
</div>

<div id="deleteModal" class="modal-overlay">
    <div class="modal-delete-card">
        <h3 class="delete-title">Konfirmasi Hapus</h3>
        <p class="delete-desc">
            Apakah Anda yakin ingin menghapus Dokumen Quotation <strong id="deleteTargetQuo" style="color: #ef4444;"></strong> beserta seluruh item komponen di dalamnya?<br><span class="delete-subtext">Tindakan ini tidak dapat dibatalkan.</span>
        </p>
        <div class="delete-action-btns">
            <button type="button" onclick="closeDeleteModal()" class="btn-cancel-del">
                Batal
            </button>
            <a id="btnConfirmDelete" href="#" class="btn-confirm-del">
                Ya, Hapus
            </a>
        </div>
    </div>
</div>

<div id="toastSuccess" class="custom-toast success-toast" style="display: none;">
    <div class="toast-icon">✓</div>
    <div class="toast-body">
        <strong>Berhasil Dihapus!</strong>
        <p id="toastMessage">Dokumen quotation telah berhasil dihapus dari sistem.</p>
    </div>
    <button class="toast-close" onclick="closeToast()">✕</button>
</div>

<script>
    function formatMoney(v, dec = 2) {
        return Number(v || 0).toLocaleString('id-ID', {
            minimumFractionDigits: dec,
            maximumFractionDigits: dec
        });
    }

    function filterQuotationTable() {
        const input = document.getElementById("liveSearchInput");
        const filter = input.value.toLowerCase();
        const rows = document.getElementsByClassName("quotation-row");
        const noMatchRow = document.getElementById("noMatchRow");
        let matchCount = 0;
        let visibleIndex = 1;

        for (let i = 0; i < rows.length; i++) {
            const quoNoCell = rows[i].getElementsByClassName("search-quo-no")[0];
            const custNameCell = rows[i].getElementsByClassName("search-cust-name")[0];
            
            if (quoNoCell || custNameCell) {
                const quoText = quoNoCell.textContent || quoNoCell.innerText;
                const custText = custNameCell.textContent || custNameCell.innerText;
                
                if (quoText.toLowerCase().indexOf(filter) > -1 || custText.toLowerCase().indexOf(filter) > -1) {
                    rows[i].style.display = "";
                    const indexCell = rows[i].getElementsByClassName("row-index")[0];
                    if(indexCell) indexCell.textContent = visibleIndex++;
                    matchCount++;
                } else {
                    rows[i].style.display = "none";
                }
            }
        }

        if (noMatchRow) {
            noMatchRow.style.display = (matchCount === 0 && rows.length > 0) ? "" : "none";
        }
    }

    function handleReviewClick(buttonElement) {
        const no = buttonElement.getAttribute('data-no');
        const date = buttonElement.getAttribute('data-date');
        const customer = buttonElement.getAttribute('data-customer');
        const pic = buttonElement.getAttribute('data-pic');
        const address = buttonElement.getAttribute('data-address');
        const phone = buttonElement.getAttribute('data-phone');
        const email = buttonElement.getAttribute('data-email');
        const notes = buttonElement.getAttribute('data-notes');
        const itemsJson = buttonElement.getAttribute('data-items');

        reviewSavedQuotation(no, date, customer, pic, address, phone, email, notes, itemsJson);
    }
    
    function reviewSavedQuotation(quoNo, quoDate, custName, pic, address, phone, email, notes, itemsJson) {
        document.getElementById('modalQuoMeta').innerHTML = `
            <div class="meta-grid-box">
                <div>
                    <strong class="meta-title">FROM:</strong>
                    <strong>PT Citra Plastik Makmur</strong><br>
                    Jl. Jababeka XIV A Blok J4F, Cikarang Utara, Bekasi<br>
                    <span>No. Quo: <strong>${quoNo}</strong></span> | <span>Tanggal: <strong>${quoDate}</strong></span>
                </div>
                <div>
                    <strong class="meta-title">TO:</strong>
                    Company: <strong>${custName}</strong><br>
                    Attn / PIC: <strong>${pic || '-'}</strong><br>
                    Address: ${address || '-'}<br>
                    Telp/Email: ${phone || '-'} / ${email || '-'}
                </div>
            </div>
        `;

        document.getElementById('modalQuotationNotes').value = notes;
        const tbodyReview = document.getElementById('modalReviewRows');
        tbodyReview.innerHTML = '';

        let items = typeof itemsJson === 'string' ? JSON.parse(itemsJson) : itemsJson;
        items = Array.isArray(items) ? items : [];

        const parseJsonArray = (value) => {
            if (Array.isArray(value)) return value.map(v => (v === null || v === undefined ? 0 : v));
            if (typeof value !== 'string') return [];
            const trimmed = value.trim();
            if (!trimmed) return [];
            try {
                const parsed = JSON.parse(trimmed);
                if (Array.isArray(parsed)) return parsed.map(v => (v === null || v === undefined ? 0 : v));
                return [parsed];
            } catch (e) {
                return trimmed
                    .replace(/^\[|\]$/g, '')
                    .split(',')
                    .map(v => v.trim())
                    .filter(v => v !== '')
                    .map(v => Number(v) || 0);
            }
        };

        const normalizedItems = items.map(item => {
            const resArray = parseJsonArray(item.cr_lta_res_json ?? item.cr_lta_res_list ?? item.cr_lta_res ?? []);
            const pctArray = parseJsonArray(item.cr_lta_pct_json ?? item.cr_lta_pct_list ?? item.cr_lta_pct ?? []);
            return {
                ...item,
                cr_lta_pct_list: pctArray,
                cr_lta_res_list: resArray
            };
        });

        const firstItemCrMode = normalizedItems.length > 0 ? (normalizedItems[0].cr_mode || 'none') : 'none';
        const showCr = (firstItemCrMode === 'with_bl' || firstItemCrMode === 'without_bl');
        const showBl = (firstItemCrMode === 'with_bl');

        const maxLtaCount = normalizedItems.reduce((max, item) => {
            const len = Math.max(item.cr_lta_res_list?.length || 0, item.cr_lta_pct_list?.length || 0);
            return Math.max(max, len);
        }, 0);

        const headerRow = document.querySelector('.review-table-header');
        if (headerRow) {
            let html = `
                <th>No</th>
                <th>Type</th>
                <th>Part Number</th>
                <th>Part Name</th>
                <th>Material Spec</th>
                <th>Basic Price</th>
                <th>Pigmen Cost</th>
                <th>Weight/Pcs</th>
                <th>IDR/Kg</th>
                <th>Material Cost/Pcs</th>
                <th>Purging (Pcs)</th>
                <th>Dandori (Pcs)</th>
                <th>C/T</th>
                <th>Cav</th>
                <th>M/C Ton</th>
                <th>Rate/sec</th>
                <th>Proc. Cost</th>
                <th>Reject Cost</th>
                <th>Other Process</th>
                <th>Packing</th>
                <th>Transport</th>
                <th class="col-cogs">COGS</th>
                <th>OH/Profit</th>
                <th>Mold Mtn</th>
                <th>Depreciation Mold</th>
                <th class="col-selling-price">Total Price</th>
                <th class="col-lumpsum">Lump Sum</th>`;

            if (showCr) {
                html += '<th class="cr-col-header" style="background:#0284c7; color:#fff;">Base CR</th>';
                for (let i = 1; i <= maxLtaCount; i++) {
                    html += `<th class="cr-col-header" style="background:#0284c7; color:#fff;">LTA ${i}</th>`;
                }
            }
            if (showBl) {
                html += '<th class="cr-bl-header" style="background:#15803d; color:#fff;">BL</th>';
            }
            if (showCr) {
                html += '<th class="cr-col-header" style="background:#0284c7; color:#fff;">Final Cost</th>';
            }

            headerRow.innerHTML = html;
        }

        document.querySelectorAll('.cr-col-header').forEach(el => el.style.display = showCr ? '' : 'none');
        document.querySelectorAll('.cr-bl-header').forEach(el => el.style.display = showBl ? '' : 'none');

        normalizedItems.forEach((item, index) => {
            const symbol = (item.currency === 'USD' || item.currency === '$') ? '$' : 'Rp ';
            const tr = document.createElement('tr');
            tr.style.background = item.type === 'external' ? '#f8fafc' : '#ffffff';
            
            const rateSec    = item.rate_per_second || (item.rate_hour ? (item.rate_hour / 3600) : 0);
            const otherCost  = item.other_process_cost || item.inspection || 0;
            const otherType  = item.other_process_type || 'Tanpa Proses';
            const moldDepPcs = item.mold_depreciation_pcs || 0;
            const lumpsumPcs = item.lumpsum_pcs || 0;
            const purging    = item.purging || 0;
            const dandori    = item.dandori || 0;

            const baseCr    = Number(item.cr_base_val || 0);
            const ltaArr    = Array.isArray(item.cr_lta_res_list) ? item.cr_lta_res_list : [];
            const pctArr    = Array.isArray(item.cr_lta_pct_list) ? item.cr_lta_pct_list : [];
            const bl        = Number(item.cr_pct_bl || 0);
            const crFinal   = Number(item.cr_final_cost || item.total || 0);

            let crCells = '';
            if (showCr) {
                crCells += `<td align="right" style="background:#fef3c7; font-weight:600;">${formatMoney(baseCr)}</td>`;
                for (let i = 0; i < maxLtaCount; i++) {
                    const ltaValue = Number(ltaArr[i] || 0);
                    const pctValue = Number(pctArr[i] || 0);
                    crCells += `<td align="right" style="background:#e0f2fe;">${formatMoney(ltaValue)}<br><small>(${pctValue}%)</small></td>`;
                }
            }
            if (showBl) {
                crCells += `<td align="right" style="background:#e0f2fe;">${formatMoney(bl)}</td>`;
            }
            if (showCr) {
                crCells += `<td align="right" style="background:#dcfce7; font-weight:bold; color:#15803d;">${formatMoney(crFinal)}</td>`;
            }

            tr.innerHTML = `
                <td align="center">${index + 1}</td>
                <td style="text-transform:uppercase; font-weight:bold; color:${item.type === 'external' ? '#2563eb' : '#16a34a'}" align="center">${item.type}</td>
                <td><strong>${item.part_number}</strong></td>
                <td>${item.part_name}</td>
                <td>${item.material_spec}</td>
                <td align="right">${symbol}${formatMoney(item.basic_price)}</td>
                <td align="right">${symbol}${formatMoney(item.pigmen_cost)}</td>
                <td align="right" style="background:#fefec8;">${Number(item.weight_per_pcs).toFixed(4)}</td>
                <td align="right" style="background:#fefec8;">${formatMoney(item.idr_price_kg)}</td>
                <td align="right" style="background:#fefec8; font-weight:600;">${formatMoney(item.material_cost || item.material_price)}</td>
                <td align="right" style="background:#fefec8;">${formatMoney(purging)}</td>
                <td align="right" style="background:#eff6ff;">${formatMoney(dandori)}</td>
                <td align="center" style="background:#eff6ff;">${item.cycle_time}</td>
                <td align="center" style="background:#eff6ff;">${item.cavity}</td>
                <td align="center" style="background:#eff6ff;">${item.mc_ton} T</td>
                <td align="right" style="background:#eff6ff;">Rp ${Number(rateSec).toFixed(2)}/s</td>
                <td align="right" style="background:#eff6ff; font-weight:600;">${formatMoney(item.process_cost)}</td>
                <td align="right">${formatMoney(item.rejection_rate)}</td>
                <td align="right" style="background:#fef3c7;" title="${otherType}">${formatMoney(otherCost)}</td>
                <td align="right">${formatMoney(item.packing)}</td>
                <td align="right">${formatMoney(item.transport)}</td>
                <td align="right" style="background:#cbd5e1; font-weight:bold;">${formatMoney(item.cogs)}</td>
                <td align="right">${formatMoney(item.oh_profit)}</td>
                <td align="right">${formatMoney(item.mold_mtn)}</td>
                <td align="right" style="color:#0369a1; font-weight:600;">${formatMoney(moldDepPcs)}</td>
                <td align="right" style="background:#dcfce7; font-weight:bold; color:#15803d;">${formatMoney(item.total)}</td>
                <td align="right" style="background:#fef3c7; font-weight:600; color:#92400e;">${formatMoney(lumpsumPcs)}</td>
                ${crCells}
            `;
            tbodyReview.appendChild(tr);
        });

        document.getElementById('reviewModal').style.display = 'flex';
    }

    function closeReviewModal() {
        document.getElementById('reviewModal').style.display = 'none';
    }

    function hapusQuotation(quotationNo) {
        document.getElementById('deleteTargetQuo').textContent = quotationNo;
        document.getElementById('btnConfirmDelete').href = "delete_quotation.php?quotation_no=" + encodeURIComponent(quotationNo);
        document.getElementById('deleteModal').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const quoNo = urlParams.get('quo');

        if (status === 'deleted_success') {
            const toast = document.getElementById('toastSuccess');
            const toastMsg = document.getElementById('toastMessage');
            
            if (quoNo) {
                toastMsg.textContent = `Dokumen Quotation "${quoNo}" telah berhasil dihapus.`;
            }

            toast.style.display = 'flex';
            window.history.replaceState(null, null, window.location.pathname);

            setTimeout(() => {
                closeToast();
            }, 4000);
        }
    });

    function closeToast() {
        const toast = document.getElementById('toastSuccess');
        if (toast) {
            toast.style.animation = 'slideInRight 0.3s reverse';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 300);
        }
    }

    let reportDataVersion = null;
    const checkReportDataVersion = async () => {
        try {
            const response = await fetch('get_live_data_versions.php', { cache: 'no-store' });
            const result = await response.json();
            if (!result.success) return;
            const currentVersion = String(result.versions.quotation || '0');
            if (reportDataVersion === null) {
                reportDataVersion = currentVersion;
            } else if (reportDataVersion !== currentVersion) {
                window.location.reload();
            }
        } catch (error) {
            
        }
    };
    checkReportDataVersion();
    setInterval(checkReportDataVersion, 5000);
</script>

<?php
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
</body>
</html>