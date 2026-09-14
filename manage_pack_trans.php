<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/database.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

include 'auth.php';

$message = '';
$messageType = 'success';
$role = $_SESSION['role'] ?? 'User';
$is_admin_or_gm = in_array(strtolower($role), ['general manager', 'admin']);
$is_gm = in_array(strtolower($role), ['general manager', 'admin'], true);
$is_admin = (strtolower($role) === 'admin');
$manageEvents = $_SESSION['manage_pack_trans_events'] ?? [];
unset($_SESSION['manage_pack_trans_events']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!$is_admin_or_gm) {
        $message = "Akses Ditolak: Anda tidak memiliki otoritas untuk menghapus kalkulasi.";
        $messageType = "error";
    } else {
        $project_id = intval($_POST['project_id']);
        
        $stmt_get = $conn->prepare("SELECT part_name FROM tbl_pack_trans_project WHERE id = ?");
        $stmt_get->bind_param("i", $project_id);
        $stmt_get->execute();
        $part_data = $stmt_get->get_result()->fetch_assoc();
        $part_name = $part_data['part_name'] ?? 'Unknown Part';

        $conn->begin_transaction();
        try {
            $stmt_del_items = $conn->prepare("DELETE FROM tbl_pack_trans_items WHERE project_id = ?");
            $stmt_del_items->bind_param("i", $project_id);
            $stmt_del_items->execute();

            $stmt_del_proj = $conn->prepare("DELETE FROM tbl_pack_trans_project WHERE id = ?");
            $stmt_del_proj->bind_param("i", $project_id);
            $stmt_del_proj->execute();

            write_audit_log(
                'tbl_pack_trans_project',                         
                $project_id,                                       
                'delete',                                          
                'part_name',                                       
                $part_name,                                        
                "Menghapus kalkulasi Packing Transport untuk part: {$part_name}" 
            );

            $conn->commit();
            $_SESSION['manage_pack_trans_events'] = [[
                'event' => 'delete_pack_trans',
                'data' => ['id' => $project_id, 'part_name' => $part_name]
            ]];
            header("Location: manage_pack_trans.php?status=deleted");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Gagal menghapus data: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

$list_result = $conn->query("SELECT id, part_name, transport_pcs_total, total_packing_pcs, created_at, is_read, workflow_status FROM tbl_pack_trans_project ORDER BY is_read ASC, id DESC");

if (isset($_GET['status'])) {
    if ($_GET['status'] === 'created') { $message = "Kalkulasi Baru Berhasil Disimpan!"; $messageType = "success"; }
    if ($_GET['status'] === 'updated') { $message = "Kalkulasi Berhasil Diperbarui!"; $messageType = "success"; }
    if ($_GET['status'] === 'deleted') { $message = "Data Kalkulasi Berhasil Dihapus!"; $messageType = "success"; }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Management Pack Trans - Dashboard</title>
    <link rel="stylesheet" href="quotation.css">
    <link rel="stylesheet" href="back-to-top.css">
    <link rel="stylesheet" href="packtrans.css">
    <link rel="stylesheet" href="managepack.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="toast-container" id="toastContainer"></div>

<div class="page-shell">  
    <header class="topbar">
        <div>
            <h1 style="color: #0f172a !important; margin: 0; font-size: 1.6rem; font-weight: 800; letter-spacing: -0.025em;">QUOTATION<span style="color:#0284c7;">APP</span></h1>
            <p style="color: #64748b !important; margin: 4px 0 0 0; font-size: 0.875rem;">Riwayat & Review Hasil Kalkulasi Aktual</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="index.php" class="action-btn btn-secondary">Ke Dashboard Utama</a>
            <?php if ($is_admin_or_gm): ?>
                <a href="pack_trans.php" class="action-btn btn-view">+ Buat Baru</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="quotation-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 20px; flex-wrap: wrap;">
            <h3 style="color: #0f172a !important; margin: 0;">Daftar Seluruh Hasil Kalkulasi</h3>
            <div style="position: relative; width: 320px;">
                <input type="text" id="liveSearchInput" class="search-control" placeholder="Cari nama part atau proyek..." 
                    onkeyup="filterTableData()"
                >
            </div>
        </div>

        <table class="full-width-table">
            <thead>
                <tr>
                    <th width="80" style="text-align: center;">ID</th>
                    <th>Nama Part / Proyek</th>
                    <th width="180"> Total Packing Cost</th>
                    <th width="250">Transport / Pcs</th>
                    <th width="320" style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="projectTableBody">
                <?php if ($list_result->num_rows > 0): ?>
                    <?php while($row = $list_result->fetch_assoc()): ?>
                    <?php $isNew = ((int)($row['is_read'] ?? 0) === 0); ?>
                    <?php $isComplete = (($row['workflow_status'] ?? 'new') === 'complete'); ?>
                    <tr class="project-row <?= $isNew ? 'new-row' : '' ?>" data-project-id="<?= $row['id'] ?>" data-is-read="<?= (int)($row['is_read'] ?? 0) ?>">
                        <td align="center">
                            <strong style="color: #94a3b8;"><?= $row['id'] ?></strong>
                            <span class="workflow-badge <?= $isComplete ? 'complete-badge' : 'new-badge' ?>">
                                <?= $isComplete ? 'Complete' : 'New' ?>
                            </span>
                        </td>
                        <td class="part-name-cell"><strong style="color: #1e293b !important;"><?= htmlspecialchars($row['part_name']) ?></strong></td>
                        <td><strong style="color: #16a34a; font-size: 1rem;">Rp <?= number_format($row['total_packing_pcs'], 2, ',', '.') ?></strong></td>
                        <td><strong style="color: #0284c7; font-size: 1.05rem;">Rp <?= number_format($row['transport_pcs_total'], 2, ',', '.') ?></strong></td>
                        <td align="center">
                            <div style="display:flex; gap:8px; justify-content: center;">
                                <button type="button" class="action-btn btn-view" onclick="openReviewModal(<?= $row['id'] ?>)">Review</button>
                                
                                <?php if ($is_admin_or_gm): ?>
                                    <a href="pack_trans.php?edit_project_id=<?= $row['id'] ?>" class="action-btn btn-edit-link">Edit</a>
                                    
                                    <form id="deleteForm_<?= $row['id'] ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="project_id" value="<?= $row['id'] ?>">
                                        <button type="button" class="action-btn btn-delete" onclick="showDeleteConfirm(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['part_name'])) ?>')">✕ Hapus</button>
                                    </form>
                                <?php else: ?>
                                    <span style="font-size: 0.85rem; color: #94a3b8; font-style: italic; align-self: center;">Read-only Mode</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr id="noDataRow">
                        <td colspan="4" align="center" style="color: #64748b; padding: 30px;"><i>Belum ada data riwayat Kalkulasi Packing Transport</i></td>
                    </tr>
                <?php endif; ?>
                <tr id="emptySearchRow" style="display: none;">
                    <td colspan="4" align="center" style="color: #64748b; padding: 30px;"><i>Data proyek tidak ditemukan</i></td>
                </tr>
            </tbody>
        </table>
    </main>
</div>

<div id="reviewModal" class="modal-overlay">
    <div class="modal-card" style="max-width: 95% !important;">
        <div class="modal-header">
            <h3 style="margin:0; color:#0284c7;" id="modal_title">...</h3>
            <div>
                <button type="button" class="action-btn btn-delete" style="padding:10px 20px; font-size:0.9rem; background:#CC0220 !important;" onclick="closeReviewModal()">Tutup Review</button>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 2fr 1fr 1fr; gap:20px; background:#f8fafc; padding:20px; border-radius:8px; margin-bottom:20px; border:1px solid #e2e8f0;">
            <div>
                <label style="display:block; font-weight:bold; margin-bottom:8px; color:#475569; font-size:0.85rem;">Nama Part / Produk:</label>
                <input type="text" id="modal_part_name" class="editable-cell readonly-modal-cell" readonly style="text-align:left; padding:10px; font-weight:bold;">
            </div>
            <div>
                <label style="display:block; font-weight:bold; margin-bottom:8px; color:#16a34a; font-size:0.85rem;">Total Packing / Pcs:</label>
                <input type="text" id="modal_packing_pcs" class="editable-cell readonly-modal-cell" readonly style="text-align:left; padding:10px; font-weight:bold; color:#16a34a; background:#f0fdf4;">
            </div>
            <div>
                <label style="display:block; font-weight:bold; margin-bottom:8px; color:#0284c7; font-size:0.85rem;">Total Transport / Pcs:</label>
                <input type="text" id="modal_transport_pcs" class="editable-cell readonly-modal-cell" readonly style="text-align:left; padding:10px; font-weight:bold; color:#0284c7; background:#f0f9ff;">
            </div>
        </div>

        <div class="matrix-container">
            <table class="spreadsheet-table" style="min-width:1550px; width:100%;">
                <thead>
                    <tr>
                        <th width="120">Type System</th>
                        <th>Description</th>
                        <th width="70">Qty</th>
                        <th width="100">Qty / Month</th>
                        <th width="90">Assy / Day</th>
                        <th width="90">Kebutuhan Box / Day</th>
                        <th width="90">Pembulatan Box / Assy</th>
                        <th width="80">Sirkulasi</th>
                        <th width="90">Keeping Jumlah Box</th>
                        <?php if ($is_gm || $is_admin): ?>
                            <th width="80">Bunga (%)</th>
                            <th width="140">Investasi Box</th>
                            <th width="60">Thn Dep</th>
                            <th width="120">Qty Part Thn</th>
                            <th width="130">Depresiasi (Pcs)</th>
                        <?php endif; ?>
                        <th width="140" style="background:#f0fdf4; color:#16a34a;">Total Packing</th>
                    </tr>
                </thead>
                <tbody id="modal_tbody_items">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="deleteConfirmModal" class="confirm-modal-overlay">
    <div class="confirm-card">
        <h3 class="confirm-title">Hapus Pack Transport?</h3>
        <p class="confirm-desc">Apakah Anda yakin ingin menghapus data kalkulasi untuk <strong id="deleteTargetName" style="color:#0f172a;"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
        <div class="confirm-actions">
            <button type="button" class="btn-modal-cancel" onclick="closeDeleteConfirm()">Batal</button>
            <button type="button" class="btn-modal-confirm" id="btnConfirmDelete">Ya, Hapus Data</button>
        </div>
    </div>
</div>

<script>
const isGM = <?= json_encode($is_gm); ?>;
const isAdmin = <?= json_encode($is_admin); ?>;
let activeDeleteFormId = null;
const managePackTransChannel = new BroadcastChannel('matrix_update');
const pendingManageEvents = <?= json_encode($manageEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
pendingManageEvents.forEach((manageEvent) => managePackTransChannel.postMessage(manageEvent));

managePackTransChannel.onmessage = (event) => {
    const payload = event.data || {};
    if (['new_pack_trans', 'update_pack_trans', 'delete_pack_trans'].includes(payload.event)) {
        window.location.reload();
    }
};

let packTransDataVersion = null;
const checkPackTransDataVersion = async () => {
    try {
        const response = await fetch('get_live_data_versions.php', { cache: 'no-store' });
        const result = await response.json();
        if (!result.success) return;
        const currentVersion = String(result.versions.pack_trans || '0');
        if (packTransDataVersion === null) {
            packTransDataVersion = currentVersion;
        } else if (packTransDataVersion !== currentVersion) {
            window.location.reload();
        }
    } catch (error) {
        // Live refresh must not interrupt management usage.
    }
};
checkPackTransDataVersion();
setInterval(checkPackTransDataVersion, 5000);

function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `custom-toast ${type}`;
    
    const icon = type === 'success' ? '✅' : '⚠️';
    toast.innerHTML = `
        <span class="toast-icon">${icon}</span>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('toast-fadeOut');
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

const manageViewerRole = <?= json_encode(strtolower(trim((string)$role))); ?>;
if (manageViewerRole === 'marketing' || manageViewerRole === 'manager') {
    const notificationStorageKey = 'pack_trans_activity_last_id';
    let notificationLastId = Number(sessionStorage.getItem(notificationStorageKey) || 0);
    let notificationInitialized = notificationLastId > 0;

    const checkPackTransEditNotifications = async () => {
        try {
            const response = await fetch(`get_pack_trans_notifications.php?since=${encodeURIComponent(notificationLastId)}`, { cache: 'no-store' });
            const result = await response.json();
            if (!result.success) return;

            if (!notificationInitialized) {
                notificationLastId = Number(result.latest_id || 0);
                sessionStorage.setItem(notificationStorageKey, String(notificationLastId));
                notificationInitialized = true;
                return;
            }

            (result.events || []).forEach((event) => {
                if (Number(event.id) > notificationLastId) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'info',
                            title: 'Packing Transport diperbarui GM',
                            text: event.description,
                            showConfirmButton: true,
                            confirmButtonText: 'Buka riwayat',
                            timer: 8000,
                            timerProgressBar: true,
                            showCloseButton: true
                        }).then((result) => {
                            if (result.isConfirmed) window.location.href = 'manage_pack_trans.php';
                        });
                    } else {
                        showToast(`Packing Transport selesai diedit GM: ${event.description}`, 'info');
                    }
                    notificationLastId = Number(event.id);
                }
            });
            sessionStorage.setItem(notificationStorageKey, String(Math.max(notificationLastId, Number(result.latest_id || 0))));
        } catch (error) {
            
        }
    };

    checkPackTransEditNotifications();
    setInterval(checkPackTransEditNotifications, 5000);
}

<?php if ($message): ?>
    showToast(<?= json_encode($message) ?>, <?= json_encode($messageType) ?>);
<?php endif; ?>

function showDeleteConfirm(id, partName) {
    activeDeleteFormId = `deleteForm_${id}`;
    document.getElementById('deleteTargetName').innerText = partName;
    document.getElementById('deleteConfirmModal').classList.add('active');
}

function closeDeleteConfirm() {
    document.getElementById('deleteConfirmModal').classList.remove('active');
    activeDeleteFormId = null;
}

document.getElementById('btnConfirmDelete').addEventListener('click', function() {
    if (activeDeleteFormId) {
        document.getElementById(activeDeleteFormId).submit();
    }
});

function filterTableData() {
    const input = document.getElementById('liveSearchInput');
    const filter = input.value.toLowerCase().trim();
    const rows = document.getElementsByClassName('project-row');
    const emptySearchRow = document.getElementById('emptySearchRow');
    
    let visibleCount = 0;
    input.style.borderColor = filter !== "" ? "#0284c7" : "#cbd5e1";

    for (let i = 0; i < rows.length; i++) {
        const partNameCell = rows[i].getElementsByClassName('part-name-cell')[0];
        if (partNameCell) {
            const txtValue = partNameCell.textContent || partNameCell.innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                rows[i].style.display = "";
                visibleCount++;
            } else {
                rows[i].style.display = "none";
            }
        }
    }

    if (rows.length > 0) {
        emptySearchRow.style.display = (visibleCount === 0) ? "" : "none";
    }
}

function markProjectAsRead(projectId) {
    fetch('get_unread_count.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: `action=mark_pack_trans_read&id=${encodeURIComponent(projectId)}`
    }).catch(() => {});

    const row = document.querySelector(`.project-row[data-project-id="${projectId}"]`);
    if (row) {
        row.setAttribute('data-is-read', '1');
        row.classList.remove('new-row');
    }
}

function openReviewModal(projectId) {
    fetch(`get_pack_trans_detail.php?id=${projectId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                showToast(data.error, 'error');
                return;
            }

            markProjectAsRead(projectId);
            
            document.getElementById('modal_title').innerText = `Ringkasan Hasil Perhitungan Matrix: ID #${data.project.id}`;
            document.getElementById('modal_part_name').value = data.project.part_name;
            
            const totalPackingPcs = parseFloat(data.project.total_packing_pcs) || 0;
            const transportPcsTotal = parseFloat(data.project.transport_pcs_total) || 0;

            document.getElementById('modal_packing_pcs').value = "Rp " + totalPackingPcs.toLocaleString('id-ID', {minimumFractionDigits: 2});
            document.getElementById('modal_transport_pcs').value = "Rp " + transportPcsTotal.toLocaleString('id-ID', {minimumFractionDigits: 2});

            const tbody = document.getElementById('modal_tbody_items');
            tbody.innerHTML = '';

            data.items.forEach(item => {
                const tr = document.createElement('tr');
                tr.className = 'item-calc-row';

                const isReturnable = item.type_system === 'returnable';
                const badgeStyle = isReturnable ? 'background:#dcfce7; color:#16a34a;' : 'background:#fee2e2; color:#ef4444;';
                
                const sirkulasiValue = item.sirkulasi || 12;
                const bungaValue = item.bunga_pct || 11.5;
                const tahunDepValue = item.tahun_dep || 2;

                let gmColumnsHtml = '';
                if (isGM) {
                    gmColumnsHtml = `
                        <td align="center"><strong style="color:#334155;">${isReturnable ? bungaValue + '%' : '-'}</strong></td>
                        <td><input type="text" class="readonly-cell readonly-modal-cell" readonly value="${isReturnable ? 'Rp ' + parseFloat(item.investment).toLocaleString('id-ID', {minimumFractionDigits:2}) : '-'}"></td>
                        <td align="center"><strong style="color:#334155;">${isReturnable ? tahunDepValue : '-'}</strong></td>
                        <td><input type="text" class="readonly-cell readonly-modal-cell" readonly value="${isReturnable ? parseInt(item.qty_2years).toLocaleString('id-ID') : '-'}"></td>
                        <td><input type="text" class="readonly-cell readonly-modal-cell" readonly value="${isReturnable ? 'Rp ' + parseFloat(item.depresiasi).toLocaleString('id-ID', {minimumFractionDigits:2}) : '-'}"></td>
                    `;
                }

                tr.innerHTML = `
                    <td align="center"><span class="badge-sys" style="${badgeStyle}">${item.type_system.toUpperCase()}</span></td>
                    <td><small><strong style="color:#1e293b;">${item.packing_desc || 'Material Terhapus'}</strong></small></td>
                    
                    <td><input type="text" class="readonly-cell readonly-modal-cell" readonly value="${item.qty}"></td>
                    <td><input type="text" class="readonly-cell readonly-modal-cell" readonly value="${parseInt(item.qty_month).toLocaleString('id-ID')}"></td>
                    
                    <td><input type="text" class="readonly-cell readonly-modal-cell" readonly value="${parseFloat(item.assy_day).toFixed(2)}"></td>
                    <td><input type="text" class="readonly-cell readonly-modal-cell" readonly value="${isReturnable ? parseFloat(item.box_day).toFixed(2) : '-'}"></td>
                    <td><input type="text" class="readonly-cell readonly-modal-cell" readonly value="${isReturnable ? item.box_round : '-'}"></td>
                    
                    <td align="center"><strong style="color:#334155;">${isReturnable ? sirkulasiValue : '-'}</strong></td>
                    <td><input type="text" class="readonly-cell readonly-modal-cell" readonly value="${isReturnable ? item.box_keeping : '-'}"></td>
                    
                    ${gmColumnsHtml}
                    
                    <td style="background:#f0fdf4;">
                        <input type="text" class="readonly-cell total-highlight-cell" readonly value="Rp ${parseFloat(item.total_packing).toLocaleString('id-ID', {minimumFractionDigits:2})}" style="font-weight:bold; color:#16a34a; text-align:right; background:transparent;">
                    </td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('reviewModal').classList.add('active');
        });
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.remove('active');
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
}, 3000);
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
}, 3000);
</script>
<script src="back-to-top.js"></script>
</body>
</html>