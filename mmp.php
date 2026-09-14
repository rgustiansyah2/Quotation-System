<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
$userRole = strtolower(trim((string)($_SESSION['role'] ?? '')));

$allowedRoles = ['purchasing', 'admin'];

if (!isset($_SESSION['user_id']) || !in_array($userRole, $allowedRoles, true)) {
    header("Location: index.php");
    exit;
}

include 'config/database.php';

$message = '';
$messageType = 'success';
$mmpEvents = $_SESSION['mmp_events'] ?? [];
unset($_SESSION['mmp_events']);

if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message']['text'];
    $messageType = $_SESSION['flash_message']['type'];
    unset($_SESSION['flash_message']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Material Price - QUOTATIONAPP</title>
    <link rel="stylesheet" href="dashboard.css?v=1">
    <link rel="stylesheet" href="header.css?v=1">
    <link rel="stylesheet" href="sidebar.css?v=1">
    <link rel="stylesheet" href="mmp.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<div class="main-content">

    <div class="page-title">Monitoring Material Price (MMP)</div>
    
    <form action="simpan_mmp.php" method="POST" id="mmpForm">
        <div class="mmp-header-card">
            <div class="form-group-title">
                <label for="judul_mmp">Judul / Acuan Dokumen MMP:</label>
                <input type="text" id="judul_mmp" name="judul_mmp" required placeholder="Contoh: Periode Juli 2026">
            </div>
        </div>

        <div class="action-bar-top">
            <label for="excelFileInput" class="btn-custom btn-import">
                Import Excel
            </label>
            <input type="file" id="excelFileInput" accept=".xlsx, .xls, .csv" style="display: none;" onchange="importExcel(event)">
            <button type="button" class="btn-custom btn-template" onclick="downloadTemplateExcel()">
                Download Template Excel
            </button>
        </div>

        <div class="table-card">
            <table class="table-mmp">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 120px;">Customer</th>
                        <th rowspan="2" style="width: 110px;">Part Number</th>
                        <th rowspan="2" style="width: 90px;">Item Code</th>
                        <th rowspan="2" style="width: 130px;">Part Name</th>
                        <th colspan="2">Nama Material</th>
                        <th rowspan="2" style="width: 110px;">Supplier</th>
                        <th colspan="3">Harga Material/Kg (Rp)</th>
                        <th rowspan="2" style="width: 80px;">Berat Part (Kg)</th>
                        <th rowspan="2">Remark</th>
                        <th rowspan="2" style="width: 70px;">Aksi</th>
                    </tr>
                    <tr>
                        <th style="width: 90px;">Quotation</th>
                        <th style="width: 90px;">Aktual</th>
                        <th style="width: 90px;">Marketing (Rp)</th>
                        <th style="width: 90px;">Purchasing (Rp)</th>
                        <th style="width: 90px;">Diff (%)</th> 
                    </tr>
                </thead>
                <tbody id="tbody_mmp">
                </tbody>
            </table>

            <div class="action-bar-bottom">
                <button type="button" class="btn-custom btn-add" onclick="tambahBarisMmp()">
                    <strong>+</strong> Tambah Item Material
                </button>
            </div>
        </div>

        <button type="submit" class="btn-custom btn-submit">Simpan</button>
    </form>
</div>

<div id="leaveConfirmModal" class="confirm-modal-overlay" style="display: none;">
    <div class="confirm-card">
        <h3 class="confirm-title">Tinggalkan Halaman MMP?</h3>
        <p class="confirm-desc">Data inputan yang belum disimpan akan terhapus. Apakah Anda yakin ingin keluar?</p>
        <div class="confirm-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeLeaveModal()">Batal</button>
            <button type="button" class="btn-modal-confirm" id="btnConfirmLeave">Ya, Keluar</button>
        </div>
    </div>
</div>

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
            title: '<?= addslashes($message) ?>'
        });
    });
</script>
<?php endif; ?>

<script>
const mmpChannel = new BroadcastChannel('matrix_update');
const pendingMmpEvents = <?= json_encode($mmpEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
pendingMmpEvents.forEach((mmpEvent) => mmpChannel.postMessage(mmpEvent));
</script>

<script>
let rowCounter = 0;

function tambahBarisMmp(data = {}) {
    rowCounter++;
    const tbody = document.getElementById('tbody_mmp');
    const tr = document.createElement('tr');
    tr.id = `row_${rowCounter}`;
    
    const customer = data.customer || '';
    const partNumber = data.part_number || '';
    const itemCode = data.item_code || '';
    const partName = data.part_name || '';
    const matQuotation = data.mat_quotation || '';
    const matAktual = data.mat_aktual || '';
    const supplier = data.supplier || '';
    const hargaMkr = data.harga_mkr !== undefined ? data.harga_mkr : 0;
    const hargaPch = data.harga_pch !== undefined ? data.harga_pch : 0;
    const beratPart = data.berat_part !== undefined ? data.berat_part : 0;
    const remark = data.remark || '';

    tr.innerHTML = `
        <td><input type="text" name="customer[]" required placeholder="..." value="${escapeHtml(customer)}"></td>
        <td><input type="text" name="part_number[]" required placeholder="..." value="${escapeHtml(partNumber)}"></td>
        <td><input type="text" name="item_code[]" placeholder="..." value="${escapeHtml(itemCode)}"></td>
        <td><input type="text" name="part_name[]" required placeholder="..." value="${escapeHtml(partName)}"></td>
        <td><input type="text" name="mat_quotation[]" placeholder="..." value="${escapeHtml(matQuotation)}"></td>
        <td><input type="text" name="mat_aktual[]" placeholder="..." value="${escapeHtml(matAktual)}"></td>
        <td><input type="text" name="supplier[]" placeholder="..." value="${escapeHtml(supplier)}"></td>
        <td><input type="number" step="0.01" id="mkr_${rowCounter}" name="harga_mkr[]" oninput="hitungMmp(${rowCounter})" value="${hargaMkr}"></td>
        <td><input type="number" step="0.01" id="pch_${rowCounter}" name="harga_pch[]" oninput="hitungMmp(${rowCounter})" value="${hargaPch}"></td>
        <td><input type="text" id="diff_kg_${rowCounter}" name="diff_kg[]" class="readonly-bg" readonly value="0.00%"></td>
        <td><input type="number" step="0.001" id="berat_${rowCounter}" name="berat_part[]" oninput="hitungMmp(${rowCounter})" value="${beratPart}"></td>        
        <td><input type="text" name="remark[]" placeholder="..." value="${escapeHtml(remark)}"></td>
        <td style="text-align: center;"><button type="button" class="btn-delete" onclick="hapusBarisMmp(${rowCounter})">Hapus</button></td>
    `;
    tbody.appendChild(tr);
    hitungMmp(rowCounter);
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function hapusBarisMmp(id) {
    const row = document.getElementById(`row_${id}`);
    if(row) row.remove();
}

function hitungMmp(id) {
    const mkrInput = document.getElementById(`mkr_${id}`);
    const pchInput = document.getElementById(`pch_${id}`);
    if (!mkrInput || !pchInput) return;

    const mkr = parseFloat(mkrInput.value) || 0;
    const pch = parseFloat(pchInput.value) || 0;

    if (mkr !== 0) {
        const diffHargaPersen = ((mkr - pch) / mkr) * 100;
        document.getElementById(`diff_kg_${id}`).value = diffHargaPersen.toFixed(2) + "%";
    } else {
        document.getElementById(`diff_kg_${id}`).value = "0.00%";
    }
}

function importExcel(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(evt) {
        try {
            const data = new Uint8Array(evt.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            
            const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

            const tbody = document.getElementById('tbody_mmp');
            if (tbody.children.length === 1) {
                const firstRowInputs = tbody.querySelectorAll('input[type="text"]');
                let isEmpty = true;
                firstRowInputs.forEach(i => { if (i.value.trim() !== '' && i.value !== '0.00%') isEmpty = false; });
                if (isEmpty) tbody.innerHTML = '';
            }

            let insertedCount = 0;
            let startIndex = 0;
            for (let i = 0; i < rows.length; i++) {
                const firstCol = String(rows[i][0] || '').toLowerCase().trim();
                if (firstCol === 'customer') {
                    startIndex = i + 1;
                    break;
                }
            }

            for (let i = startIndex; i < rows.length; i++) {
                const r = rows[i];
                if (!r || r.length === 0 || !r[0]) continue; 

                tambahBarisMmp({
                    customer: r[0] || '',
                    part_number: r[1] || '',
                    item_code: r[2] || '',
                    part_name: r[3] || '',
                    mat_quotation: r[4] || '',
                    mat_aktual: r[5] || '',
                    supplier: r[6] || '',
                    harga_mkr: parseFloat(r[7]) || 0,
                    harga_pch: parseFloat(r[8]) || 0,
                    berat_part: parseFloat(r[9]) || 0,
                    remark: r[10] || ''
                });
                insertedCount++;
            }

            Swal.fire({
                icon: 'success',
                title: 'Import Berhasil!',
                text: `${insertedCount} item berhasil di-import dari Excel.`,
                timer: 2000,
                showConfirmButton: false
            });
        } catch (err) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Import File',
                text: 'Format file tidak sesuai atau rusak. Gunakan format template yang disediakan.'
            });
        }
        e.target.value = '';
    };
    reader.readAsArrayBuffer(file);
}

function downloadTemplateExcel() {
    const headers = [
        ["Customer", "Part Number", "Item Code", "Part Name", "Nama Material (Quotation)", "Nama Material (Aktual)", "Supplier", "Harga Marketing (Rp)", "Harga Purchasing (Rp)", "Berat Part (Kg)", "Remark"],
        ["PT Sample Customer", "PN-12345", "IC-001", "Cover Front", "ABS Natural", "ABS Natural Grade A", "PT Supplier Utama", 35000, 32000, 0.150, "Contoh Data"]
    ];

    const ws = XLSX.utils.aoa_to_sheet(headers);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Template MMP");
    XLSX.writeFile(wb, "Template_Import_MMP.xlsx");
}

window.onload = function() {
    tambahBarisMmp();
};

let isSubmitting = false;
let pendingNavigationUrl = null;

document.getElementById('mmpForm').addEventListener('submit', function() {
    isSubmitting = true;
});

function hasUnsavedChanges() {
    if (isSubmitting) return false;
    const judul = document.getElementById('judul_mmp').value.trim();
    if (judul !== "") return true;

    const inputs = document.querySelectorAll('#tbody_mmp input[type="text"]');
    for (let input of inputs) {
        if (input.value.trim() !== "" && input.value !== "0.00%") return true;
    }
    const numberInputs = document.querySelectorAll('#tbody_mmp input[type="number"]');
    for (let input of numberInputs) {
        if (parseFloat(input.value) > 0) return true;
    }
    return false;
}

document.addEventListener('click', function (e) {
    const targetLink = e.target.closest('a');
    if (targetLink && hasUnsavedChanges()) {
        const href = targetLink.getAttribute('href');
        if (href && href !== '#' && !href.startsWith('javascript:')) {
            e.preventDefault(); 
            pendingNavigationUrl = href;
            showLeaveModal(); 
        }
    }
});

function showLeaveModal() {
    document.getElementById('leaveConfirmModal').style.display = 'flex';
}

function closeLeaveModal() {
    document.getElementById('leaveConfirmModal').style.display = 'none';
    pendingNavigationUrl = null;
}

document.getElementById('btnConfirmLeave').addEventListener('click', function() {
    if (pendingNavigationUrl) {
        isSubmitting = true;
        window.location.href = pendingNavigationUrl;
    }
});
</script>
</body>
</html>