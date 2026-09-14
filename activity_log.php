<?php
session_start();
include 'config/database.php';

if (empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
$filter_action = trim($_GET['action'] ?? '');

function fetchActivityLogs($conn, $search = '', $filter_action = '') {
    $query = "SELECT log.*, user.fullname as operator_name 
              FROM tbl_activity_log log
              LEFT JOIN tbl_users user ON log.user_id = user.id
              WHERE 1=1";

    if ($search !== '') {
        $search_term = "%" . $conn->real_escape_string($search) . "%";
        $query .= " AND (log.table_name LIKE '$search_term' 
                         OR log.column_name LIKE '$search_term' 
                         OR log.description LIKE '$search_term')";
    }

    if ($filter_action !== '') {
        $action_term = $conn->real_escape_string($filter_action);
        $query .= " AND log.action = '$action_term'";
    }
    $query .= " ORDER BY log.created_at DESC";

    $result = $conn->query($query);
    $logs = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }

    return $logs;
}

$logs = fetchActivityLogs($conn, $search, $filter_action);

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId > 0) {
    $markRead = $conn->prepare("UPDATE tbl_users SET last_activity_seen_at = NOW() WHERE id = ? LIMIT 1");
    if ($markRead) {
        $markRead->bind_param('i', $userId);
        $markRead->execute();
        $markRead->close();
    }
}

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    echo json_encode([
        'logs' => $logs,
        'count' => count($logs),
        'generated_at' => date('c')
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="activitylog.css">
    <link rel="stylesheet" href="header.css?v=1">
    <link rel="stylesheet" href="sidebar.css?v=1">
    <link rel="stylesheet" href="dashboard.css?v=1">
</head>
<body>
<?php include 'header.php'; ?>

<?php include 'sidebar.php'; ?>

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark"><i class="fa-solid mt-1 fa-clock-rotate-left me-2 text-primary"></i>System Activity Log</h2>
            <p class="text-muted mb-0">Lacak dan pantau setiap pergerakan & perubahan data aplikasi Anda.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="activity_log.php" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-merge">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama tabel, kolom, atau deskripsi..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="action" class="form-select">
                        <option value="">-- Semua Aksi --</option>
                        <option value="insert" <?= $filter_action === 'insert' ? 'selected' : '' ?>>INSERT (Tambah)</option>
                        <option value="update" <?= $filter_action === 'update' ? 'selected' : '' ?>>UPDATE (Ubah)</option>
                        <option value="delete" <?= $filter_action === 'delete' ? 'selected' : '' ?>>DELETE (Hapus)</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 w-100">
                        <i class="fa-solid fa-filter me-1"></i> Terapkan Filter
                    </button>
                    <?php if ($search !== '' || $filter_action !== ''): ?>
                        <a href="activity_log.php" class="btn btn-light text-danger px-3">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary text-uppercase fs-7">
                        <tr>
                            <th class="ps-4" style="width: 5%">#</th>
                            <th style="width: 15%">Waktu Kejadian</th>
                            <th style="width: 15%">P. I. C. Name</th>
                            <th style="width: 10%">Jenis Aksi</th>
                            <th style="width: 15%">Nama Tabel (Kolom)</th>
                            <th style="width: 40%" class="pe-4">Deskripsi Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-folder-open display-6 d-block mb-3 opacity-50"></i>
                                    Tidak ada riwayat aktivitas log yang ditemukan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $no = 1;
                            foreach ($logs as $log): 
                                $badge_class = 'badge-insert';
                                if ($log['action'] === 'update') $badge_class = 'badge-update';
                                if ($log['action'] === 'delete') $badge_class = 'badge-delete';
                                
                                $operator = !empty($log['operator_name']) ? $log['operator_name'] : '<span class="text-danger italic">System/Deleted User</span>';
                            ?>
                                <tr>
                                    <td class="ps-4 text-muted font-monospace"><?= $no++ ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= date('d M Y', strtotime($log['created_at'])) ?></div>
                                        <small class="text-muted"><i class="fa-regular fa-clock me-1"></i><?= date('H:i:s', strtotime($log['created_at'])) ?> WIB</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                <i class="fa-solid fa-user text-secondary fs-7"></i>
                                            </div>
                                            <span class="fw-medium"><?= $operator ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badge_class ?> text-capitalize px-2.5 py-1.5 rounded-pill fs-8">
                                            <?= $log['action'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-dark font-monospace bg-light border px-2 py-1 rounded fs-7 d-inline-block mb-1">
                                            <?= htmlspecialchars($log['table_name']) ?>
                                        </span>
                                        <?php if(!empty($log['column_name'])): ?>
                                            <br><small class="text-muted">Key Col: <code><?= htmlspecialchars($log['column_name']) ?></code></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4">
                                        <div class="text-dark fw-normal"><?= htmlspecialchars($log['description']) ?></div>
                                        <?php if ($log['action'] === 'update' && !empty($log['old_value'])): ?>
                                            <div class="mt-1 fs-7 text-muted bg-light p-1.5 rounded border-start border-warning font-monospace" style="font-size: 0.85rem;">
                                                <strong>Old Ref/Value:</strong> <?= htmlspecialchars($log['old_value']) ?>
                                            </div>
                                        <?php elseif ($log['action'] === 'delete' && !empty($log['old_value'])): ?>
                                            <div class="mt-1 fs-7 text-muted bg-light p-1.5 rounded border-start border-danger font-monospace" style="font-size: 0.85rem;">
                                                <strong>Deleted Data Identity:</strong> <?= htmlspecialchars($log['old_value']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderActivityRows(logs) {
        const tbody = document.getElementById('activityTableBody');
        if (!tbody) return;

        if (!logs || logs.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fa-regular fa-folder-open display-6 d-block mb-3 opacity-50"></i>
                        Tidak ada riwayat aktivitas log yang ditemukan.
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        logs.forEach((log, index) => {
            const action = (log.action || '').toLowerCase();
            let badgeClass = 'badge-insert';
            if (action === 'update') badgeClass = 'badge-update';
            if (action === 'delete') badgeClass = 'badge-delete';

            const operator = log.operator_name ? escapeHtml(log.operator_name) : '<span class="text-danger italic">System/Deleted User</span>';
            const dateText = log.created_at ? new Date(log.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
            const timeText = log.created_at ? new Date(log.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }) : '-';
            const oldValueHtml = log.old_value ? `<div class="mt-1 fs-7 text-muted bg-light p-1.5 rounded border-start border-warning font-monospace" style="font-size: 0.85rem;"><strong>Old Ref/Value:</strong> ${escapeHtml(log.old_value)}</div>` : '';
            const deleteHtml = log.old_value && action === 'delete' ? `<div class="mt-1 fs-7 text-muted bg-light p-1.5 rounded border-start border-danger font-monospace" style="font-size: 0.85rem;"><strong>Deleted Data Identity:</strong> ${escapeHtml(log.old_value)}</div>` : '';

            html += `
                <tr>
                    <td class="ps-4 text-muted font-monospace">${index + 1}</td>
                    <td>
                        <div class="fw-semibold text-dark">${dateText}</div>
                        <small class="text-muted"><i class="fa-regular fa-clock me-1"></i>${timeText} WIB</small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded-circle p-2 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-user text-secondary fs-7"></i>
                            </div>
                            <span class="fw-medium">${operator}</span>
                        </div>
                    </td>
                    <td>
                        <span class="badge ${badgeClass} text-capitalize px-2.5 py-1.5 rounded-pill fs-8">${escapeHtml(action)}</span>
                    </td>
                    <td>
                        <span class="text-dark font-monospace bg-light border px-2 py-1 rounded fs-7 d-inline-block mb-1">${escapeHtml(log.table_name || '')}</span>
                        ${log.column_name ? `<br><small class="text-muted">Key Col: <code>${escapeHtml(log.column_name)}</code></small>` : ''}
                    </td>
                    <td class="pe-4">
                        <div class="text-dark fw-normal">${escapeHtml(log.description || '')}</div>
                        ${action === 'update' ? oldValueHtml : deleteHtml}
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function fetchLatestActivityLogs() {
        const search = document.querySelector('input[name="search"]') ? document.querySelector('input[name="search"]').value : '';
        const action = document.querySelector('select[name="action"]') ? document.querySelector('select[name="action"]').value : '';

        fetch(`activity_log.php?ajax=1&search=${encodeURIComponent(search)}&action=${encodeURIComponent(action)}`, {
            cache: 'no-store'
        })
        .then(response => response.json())
        .then(data => {
            if (data && Array.isArray(data.logs)) {
                renderActivityRows(data.logs);
            }
        })
        .catch(() => {
            
        });
    }

    setInterval(fetchLatestActivityLogs, 10000);
</script>
</body>
</html>
<?php $conn->close(); ?>