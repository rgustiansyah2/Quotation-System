<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/database.php';

$userRole = strtolower(trim((string)($_SESSION['role'] ?? '')));
$allowedRoles = ['general affair', 'admin'];

if (!isset($_SESSION['user_id']) || !in_array($userRole, $allowedRoles, true)) {
    header("Location: index.php");
    exit;
}

function logActivity($conn, $userId, $action, $tableName, $columnName = null, $description = '', $oldValue = null) {
    $stmt = $conn->prepare("INSERT INTO `tbl_activity_log` (user_id, action, table_name, column_name, description, old_value, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("isssss", $userId, $action, $tableName, $columnName, $description, $oldValue);
        $stmt->execute();
        $stmt->close();
    }
}

$userId = (int)($_SESSION['user_id'] ?? 0);

$message = $_SESSION['flash_message'] ?? "";
$messageType = $_SESSION['flash_type'] ?? "";
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
$transportEvents = $_SESSION['transport_events'] ?? [];
unset($_SESSION['transport_events']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cost'])) {
    $customer_name = trim($_POST['customer_name']);
    $keterangan    = trim($_POST['keterangan']);
    $biaya_sewa    = floatval($_POST['biaya_sewa']);

    $stmt = $conn->prepare("INSERT INTO `tbl_transport_cost` (customer_name, keterangan, biaya_sewa, is_archived) VALUES (?, ?, ?, 0)");
    if ($stmt) {
        $stmt->bind_param("ssd", $customer_name, $keterangan, $biaya_sewa);
        if ($stmt->execute()) {
            $insertedId = $conn->insert_id;
            $_SESSION['flash_message'] = "Master transport cost berhasil disimpan!";
            $_SESSION['flash_type'] = "success";
            $transportEvents[] = ['event' => 'new_transport_cost', 'data' => ['id' => $insertedId, 'customer_name' => $customer_name, 'keterangan' => $keterangan, 'biaya_sewa' => $biaya_sewa]];

            $logDesc = "Menambahkan master transport cost customer '{$customer_name}' ({$keterangan}) Rp " . number_format($biaya_sewa, 0, ',', '.');
            logActivity($conn, $userId, 'insert', 'tbl_transport_cost', 'customer_name', $logDesc);
        } else {
            $_SESSION['flash_message'] = "Gagal menyimpan data: " . $stmt->error;
            $_SESSION['flash_type'] = "error";
        }
        $stmt->close();
    }
    $_SESSION['transport_events'] = $transportEvents;
    header("Location: cost_transport.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cost'])) {
    $id = intval($_POST['cost_id']);

    $custInfo = "ID: {$id}";
    $getRes = $conn->query("SELECT customer_name, keterangan, biaya_sewa FROM tbl_transport_cost WHERE id = {$id}");
    if ($getRes && $row = $getRes->fetch_assoc()) {
        $custInfo = "Customer: {$row['customer_name']} | Ket: {$row['keterangan']} | Biaya: Rp " . number_format($row['biaya_sewa'], 0, ',', '.');
    }

    $stmt = $conn->prepare("DELETE FROM `tbl_transport_cost` WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Data acuan transport berhasil dihapus!";
            $_SESSION['flash_type'] = "success";
            $transportEvents[] = ['event' => 'delete_transport_cost', 'data' => ['id' => $id]];

            $logDesc = "Menghapus master transport cost (ID: {$id})";
            logActivity($conn, $userId, 'delete', 'tbl_transport_cost', 'id', $logDesc, $custInfo);
        }
        $stmt->close();
    }
    $_SESSION['transport_events'] = $transportEvents;
    header("Location: cost_transport.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action_btn'])) {
    $bulkAction = $_POST['bulk_action'] ?? '';
    $selectedCosts = $_POST['selected_cost'] ?? [];

    if (!empty($bulkAction) && is_array($selectedCosts) && count($selectedCosts) > 0) {
        $validIds = array_map('intval', $selectedCosts);
        $validIds = array_filter($validIds, fn($id) => $id > 0);

        if (!empty($validIds)) {
            $idsString = implode(',', $validIds);

            if ($bulkAction === 'archive') {
                $query = "UPDATE tbl_transport_cost SET is_archived = 1, updated_at = NOW() WHERE id IN ($idsString)";
                if ($conn->query($query)) {
                    $_SESSION['flash_message'] = count($validIds) . " Data berhasil dipindahkan ke Arsip!";
                    $_SESSION['flash_type'] = "success";
                    foreach ($validIds as $archivedId) {
                        $transportEvents[] = ['event' => 'archive_transport_cost', 'data' => ['id' => $archivedId]];
                    }

                    $logDesc = "Mengarsipkan " . count($validIds) . " data master transport cost";
                    logActivity($conn, $userId, 'update', 'tbl_transport_cost', 'is_archived', $logDesc, "Target ID: {$idsString}");
                } else {
                    $_SESSION['flash_message'] = "Gagal mengarsipkan: " . $conn->error;
                    $_SESSION['flash_type'] = "error";
                }
            } elseif ($bulkAction === 'delete') {
                $query = "DELETE FROM tbl_transport_cost WHERE id IN ($idsString)";
                if ($conn->query($query)) {
                    $_SESSION['flash_message'] = count($validIds) . " Data berhasil dihapus permanen!";
                    $_SESSION['flash_type'] = "success";
                    foreach ($validIds as $deletedId) {
                        $transportEvents[] = ['event' => 'delete_transport_cost', 'data' => ['id' => $deletedId]];
                    }

                    $logDesc = "Menghapus permanen " . count($validIds) . " data master transport cost";
                    logActivity($conn, $userId, 'delete', 'tbl_transport_cost', 'id', $logDesc, "Target ID: {$idsString}");
                }
            }
        }
    } else {
        $_SESSION['flash_message'] = "Pilih minimal satu data dan tentukan aksinya!";
        $_SESSION['flash_type'] = "warning";
    }
    $_SESSION['transport_events'] = $transportEvents;
    header("Location: cost_transport.php");
    exit;
}

$costs = [];
$res = $conn->query("SELECT * FROM tbl_transport_cost WHERE is_archived = 0 OR is_archived IS NULL ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) { $costs[] = $row; }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Cost Transport - GA Divisi</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="manage.css">
    <link rel="stylesheet" href="cost_transport.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="transport-page">
    <div class="transport-container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content transport-main">
            <div class="transport-header">
                <div>
                    <span class="header-kicker">GA Operations</span>
                    <h3>Master Biaya Sewa Truck</h3>
                </div>
                <a href="archive.php" class="btn-archive">Lihat Arsip</a>
            </div>

            <form method="POST" class="form-inline transport-form">
                <div class="form-group-ga">
                    <label>Nama Customer</label>
                    <input type="text" name="customer_name" placeholder="Contoh: PT. AJI" required>
                </div>
                <div class="form-group-ga">
                    <label>Keterangan / Rute / Jenis Truck</label>
                    <input type="text" name="keterangan" placeholder="Contoh: Sewa CDD Rute Jakarta-Bekasi" required>
                </div>
                <div class="form-group-ga">
                    <label>Total Biaya Sewa Truck /Day (Rp)</label>
                    <input type="number" name="biaya_sewa" placeholder="0" required>
                </div>
                <button type="submit" name="add_cost" class="btn-add">Simpan</button>
            </form>

            <form method="POST" action="cost_transport.php" id="bulk-transport-form" class="bulk-form">
                <div class="bulk-toolbar" style="display: flex; gap: 10px; align-items: center; margin-bottom: 15px;">
                    <select name="bulk_action" id="bulk_action">
                        <option value="">-- Pilih aksi --</option>
                        <option value="archive">Arsipkan</option>
                        <option value="delete">Hapus</option>
                    </select>
                    <button type="submit" name="bulk_action_btn" value="1" class="btn-add btn-add--small">Terapkan</button>
                </div>

                <div class="table-wrap">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th class="checkbox-col" width="40">
                                    <input type="checkbox" id="check-all-costs" onchange="toggleAllCostCheckboxes(this.checked)">
                                </th>
                                <th width="50">No</th>
                                <th>Nama Customer</th>
                                <th>Keterangan / Armada / Rute</th>
                                <th>Total Biaya Truck /Day</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($costs)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: #94a3b8; padding: 20px;">Belum ada data cost transport aktif.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($costs as $index => $c): ?>
                                    <tr>
                                        <td class="checkbox-col" align="center">
                                            <input type="checkbox" name="selected_cost[]" value="<?= $c['id']; ?>" class="cost-check-item">
                                        </td>
                                        <td><?= $index + 1; ?></td>
                                        <td><strong><?= htmlspecialchars($c['customer_name']); ?></strong></td>
                                        <td><?= htmlspecialchars($c['keterangan']); ?></td>
                                        <td><span class="price-tag">Rp <?= number_format($c['biaya_sewa'], 0, ',', '.'); ?></span></td>
                                        <td>
                                            <button type="button" class="btn-del" onclick="confirmDelete(<?= $c['id']; ?>, '<?= htmlspecialchars($c['customer_name']); ?>')">Hapus</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>

            <form id="form-single-delete" method="POST" style="display:none;">
                <input type="hidden" name="delete_cost" value="1">
                <input type="hidden" name="cost_id" id="single-delete-id">
            </form>
        </div>
    </div>
</div>

<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true
    });

    function showSwalToast(title, icon = 'success') {
        const transportBroadcast = new BroadcastChannel('matrix_update');
        const pendingTransportEvents = <?= json_encode($transportEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        pendingTransportEvents.forEach((transportEvent) => transportBroadcast.postMessage(transportEvent));
        Toast.fire({ icon: icon, title: title });
    }

    <?php if ($message): ?>
        showSwalToast('<?= addslashes($message); ?>', '<?= $messageType; ?>');
    <?php endif; ?>

    function confirmDelete(id, customerName) {
        Swal.fire({
            title: 'Hapus Transport Cost?',
            text: `Data acuan untuk "${customerName}" akan dihapus permanen!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('single-delete-id').value = id;
                document.getElementById('form-single-delete').submit();
            }
        });
    }

    function toggleAllCostCheckboxes(isChecked) {
        document.querySelectorAll('.cost-check-item').forEach((checkbox) => {
            checkbox.checked = isChecked;
        });
    }
</script>
</body>
</html>
<?php
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>