<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$userRole = strtolower(trim((string)($_SESSION['role'] ?? '')));

$allowedRoles = ['purchasing', 'admin', 'marketing', 'manager'];
$canManageMmp = in_array($userRole, ['purchasing', 'admin'], true);

if (!isset($_SESSION['user_id']) || !in_array($userRole, $allowedRoles, true)) {
    header("Location: index.php");
    exit;
}

include 'config/database.php';

$query = "SELECT h.mmp_id, h.keterangan_umum, h.tanggal_input, f.fullname 
          FROM tbl_mmp_head h
          LEFT JOIN tbl_users f ON h.user_id = f.id 
          ORDER BY h.mmp_id DESC";
$result = $conn->query($query);

$message = '';
$messageType = 'success';
$mmpEvents = $_SESSION['mmp_events'] ?? [];
unset($_SESSION['mmp_events']);
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'deleted') { $message = "Dokumen MMP Berhasil Dihapus!"; $messageType = "success"; }
    if ($_GET['status'] === 'error') { $message = "Gagal menghapus dokumen MMP!"; $messageType = "error"; }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data MMP</title>
    <link rel="stylesheet" href="dashboard.css?v=1">
    <link rel="stylesheet" href="header.css?v=1">
    <link rel="stylesheet" href="sidebar.css?v=1">
    <link rel="stylesheet" href="kelola_mmp.css?v=2">
</head>
<body>

<div class="toast-container" id="toastContainer"></div>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="page-title">Daftar Judul Acuan MMP</div>    
    <div class="table-card">       
        <div class="search-bar-container">
            <input type="text" id="searchInput" class="search-input" placeholder="Pencarian ..." onkeyup="filterTable()">
        </div>

        <table class="table-list" id="mmpTable">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Judul Acuan / No. Dokumen MMP</th>
                    <th style="width: 150px;">Dibuat Oleh</th>
                    <th style="width: 180px;">Tanggal Input</th>
                    <th style="width: 210px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['mmp_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['keterangan_umum']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['fullname'] ?? 'User'); ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($row['tanggal_input'])); ?></td>
                            <td style="text-align: center;">
                                <button type="button" class="btn-action btn-review" onclick="bukaReview(<?php echo $row['mmp_id']; ?>, '<?php echo urlencode($row['keterangan_umum']); ?>')">Review</button>
                                <?php if ($canManageMmp): ?>
                                    <a href="edit_mmp.php?id=<?php echo $row['mmp_id']; ?>" class="btn-action btn-edit">Edit</a>
                                    <button type="button" class="btn-action btn-delete-head" onclick="showDeleteModal(<?php echo $row['mmp_id']; ?>, '<?php echo htmlspecialchars(addslashes($row['keterangan_umum'])); ?>')">Hapus</button>
                                <?php else: ?>
                                    <span style="font-size: 0.85rem; color: #94a3b8; font-style: italic;">Read-only Mode</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr class="no-data-row"><td colspan="5" style="text-align: center; color: #64748b;">Belum ada data dokumen disimpan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modalReview" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="tutupReview()">&times;</span>
        
        <div class="modal-header-wrapper">
            <div class="modal-title" id="modalTitle">Detail Review MMP</div>
            <button type="button" class="btn-pdf" onclick="downloadPdf()">Download PDF</button>
        </div>

        <div style="overflow-x: auto;">
            <table class="table-pop">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Part Number</th>
                        <th>Item Code</th>
                        <th>Part Name</th>
                        <th>Mat. Quot</th>
                        <th>Mat. Akt</th>
                        <th>Supplier</th>
                        <th>Harga MKR</th>
                        <th>Harga PCH</th>
                        <th>Diff (%)</th>
                        <th>Berat</th>
                        <th>Remark</th>
                    </tr>
                </thead>
                <tbody id="modalTableBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="deleteConfirmModal" class="confirm-modal-overlay">
    <div class="confirm-card">
        <h3 class="confirm-title">Hapus Dokumen MMP?</h3>
        <p class="confirm-desc">Apakah Anda yakin ingin menghapus dokumen MMP <strong id="deleteTargetTitle" style="color:#0f172a;"></strong> beserta seluruh item di dalamnya?</p>
        <div class="confirm-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeDeleteModal()">Batal</button>
            <button type="button" class="btn-modal-confirm" id="btnConfirmDelete">Ya, Hapus Data</button>
        </div>
    </div>
</div>

<script>
let currentMmpId = null;
let targetDeleteId = null;
const kelolaMmpChannel = new BroadcastChannel('matrix_update');
const pendingKelolaMmpEvents = <?= json_encode($mmpEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
pendingKelolaMmpEvents.forEach((mmpEvent) => kelolaMmpChannel.postMessage(mmpEvent));

kelolaMmpChannel.onmessage = (event) => {
    const payload = event.data || {};
    if (payload.event === 'new_mmp' || payload.event === 'update_mmp' || payload.event === 'delete_mmp') {
        window.location.reload();
    }
};

let mmpDataVersion = null;
const checkMmpDataVersion = async () => {
    try {
        const response = await fetch('get_live_data_versions.php', { cache: 'no-store' });
        const result = await response.json();
        if (!result.success) return;
        const currentVersion = String(result.versions.mmp || '0');
        if (mmpDataVersion === null) {
            mmpDataVersion = currentVersion;
        } else if (mmpDataVersion !== currentVersion) {
            window.location.reload();
        }
    } catch (error) {
        // Live refresh must not interrupt MMP usage.
    }
};
checkMmpDataVersion();
setInterval(checkMmpDataVersion, 5000);

function filterTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('mmpTable');
    const trs = table.getElementsByTagName('tr');

    for (let i = 1; i < trs.length; i++) {
        const tr = trs[i];
        if (tr.classList.contains('no-data-row')) continue;

        const tds = tr.getElementsByTagName('td');
        let showRow = false;

        for (let j = 0; j < tds.length - 1; j++) {
            if (tds[j]) {
                const textValue = tds[j].textContent || tds[j].innerText;
                if (textValue.toLowerCase().indexOf(filter) > -1) {
                    showRow = true;
                    break;
                }
            }
        }
        tr.style.display = showRow ? '' : 'none';
    }
}

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `custom-toast ${type}`;
    
    const icon = type === 'success' ? '✅' : '⚠️';
    toast.innerHTML = `<span style="font-size:1.2rem;">${icon}</span> <span>${message}</span>`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('toast-fadeOut');
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

<?php if ($message): ?>
    showToast(<?= json_encode($message) ?>, <?= json_encode($messageType) ?>);
<?php endif; ?>

function bukaReview(mmpId, judulMmp) {
    currentMmpId = mmpId; 
    const modal = document.getElementById('modalReview');
    const title = document.getElementById('modalTitle');
    const tbody = document.getElementById('modalTableBody');
    
    title.innerText = "Review Detail: " + decodeURIComponent(judulMmp);
    tbody.innerHTML = '<tr><td colspan="12" style="text-align: center;">Sedang memuat data...</td></tr>';
    modal.style.display = "block";

    fetch(`get_mmp_detail.php?id=${mmpId}`)
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = '';
            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="12" style="text-align: center;">Tidak ada item data.</td></tr>';
                return;
            }
            data.forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.customer || ''}</td>
                    <td>${item.part_number || ''}</td>
                    <td>${item.item_code || ''}</td>
                    <td>${item.part_name || ''}</td>
                    <td>${item.mat_quotation || ''}</td>
                    <td>${item.mat_aktual || ''}</td>
                    <td>${item.supplier || ''}</td>
                    <td style="text-align:right;">Rp ${parseFloat(item.harga_mkr).toFixed(2)}</td>
                    <td style="text-align:right;">Rp ${parseFloat(item.harga_pch).toFixed(2)}</td>
                    <td style="text-align:right; font-weight:600;">${item.diff_kg} %</td>
                    <td style="text-align:right;">${parseFloat(item.berat_part).toFixed(3)}</td>
                    <td>${item.remark || ''}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            tbody.innerHTML = '<tr><td colspan="12" style="text-align: center; color: red;">Gagal memuat data detail.</td></tr>';
        });
}

function tutupReview() {
    document.getElementById('modalReview').style.display = "none";
    currentMmpId = null;
}

function downloadPdf() {
    if (currentMmpId) {
        window.open(`export_mmp_pdf.php?id=${currentMmpId}`, '_blank');
    }
}

function showDeleteModal(mmpId, judulMmp) {
    targetDeleteId = mmpId;
    document.getElementById('deleteTargetTitle').innerText = `"${judulMmp}"`;
    document.getElementById('deleteConfirmModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteConfirmModal').classList.remove('active');
    targetDeleteId = null;
}

document.getElementById('btnConfirmDelete').addEventListener('click', function() {
    if (targetDeleteId) {
        window.location.href = `hapus_mmp.php?id=${targetDeleteId}`;
    }
});

window.onclick = function(event) {
    const modalReview = document.getElementById('modalReview');
    if (event.target == modalReview) {
        tutupReview();
    }
}
</script>
</body>
</html>