<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/database.php';

if (!isset($_SESSION['role']) || (strtolower($_SESSION['role']) !== 'general manager' && strtolower($_SESSION['role']) !== 'admin')) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_tonnage'])) {
    $action = $_POST['action_tonnage'];
    
    if ($action === 'add') {
        $tLabel = trim($_POST['tonnage_label'] ?? '');
        $tVal   = intval($_POST['tonnage_value'] ?? 0);

        if ($tLabel !== '' && $tVal > 0) {
            $tKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $tLabel), '_'));
            $tKey = substr($tKey !== '' ? $tKey : 'tonase', 0, 20);
            $baseKey = $tKey;
            $keyNumber = 2;
            $keyExists = 0;
            $checkKey = $conn->prepare("SELECT COUNT(*) FROM tbl_master_tonnage WHERE tonnage_key = ?");
            do {
                $checkKey->bind_param('s', $tKey);
                $checkKey->execute();
                $checkKey->bind_result($keyExists);
                $checkKey->fetch();
                $checkKey->free_result();
                if ($keyExists > 0) {
                    $suffix = '_' . $keyNumber++;
                    $tKey = substr($baseKey, 0, 20 - strlen($suffix)) . $suffix;
                }
            } while ($keyExists > 0);
            $checkKey->close();

            $orderResult = $conn->query("SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order FROM tbl_master_tonnage");
            $tOrder = $orderResult ? (int)$orderResult->fetch_assoc()['next_order'] : 1;

            $stmtAdd = $conn->prepare("INSERT INTO tbl_master_tonnage (tonnage_key, tonnage_label, tonnage_value, sort_order) VALUES (?, ?, ?, ?)");
            $stmtAdd->bind_param('ssii', $tKey, $tLabel, $tVal, $tOrder);
            if ($stmtAdd->execute()) {
                $_SESSION['flash_msg'] = "Berhasil menambah Tonase Mesin baru: $tLabel";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_msg'] = "Gagal menambah Tonase: " . $conn->error;
                $_SESSION['flash_type'] = "error";
            }
            $stmtAdd->close();
        }
    } elseif ($action === 'delete') {
        $tId = intval($_POST['tonnage_id'] ?? 0);
        if ($tId > 0) {
            $stmtDel = $conn->prepare("DELETE FROM tbl_master_tonnage WHERE id = ?");
            $stmtDel->bind_param('i', $tId);
            if ($stmtDel->execute()) {
                $_SESSION['flash_msg'] = "Berhasil menghapus tipe tonase!";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_msg'] = "Gagal menghapus tonase.";
                $_SESSION['flash_type'] = "error";
            }
            $stmtDel->close();
        }
    }
    header("Location: rate.php");
    exit();
}

// Ambil daftar tonase aktif dari database secara dinamis
$tonnages = [];
$tonnageValues = [];
$masterTonnageList = [];
$editingDraftId = 0;
$editingDraftTitle = '';
$editingBaseReference = 'BASED ON AKTUAL 2023';
$editingManpowerRate = 14.25;
$editingMachineRates = [];

$resTon = $conn->query("SELECT id, tonnage_key, tonnage_label, tonnage_value, sort_order FROM tbl_master_tonnage WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
if ($resTon) {
    while ($tRow = $resTon->fetch_assoc()) {
        $masterTonnageList[] = $tRow;
        $tonnages[$tRow['tonnage_key']] = $tRow['tonnage_label'];
        $tonnageValues[$tRow['tonnage_key']] = [
            'value' => $tRow['tonnage_value'],
            'label' => $tRow['tonnage_label']
        ];
    }
}

if (isset($_GET['get_draft_detail'])) {
    header('Content-Type: application/json');
    $draftId = intval($_GET['get_draft_detail']);
    
    $stmt = $conn->prepare("SELECT tonnage, rate_per_second FROM tbl_rate_master WHERE draft_id = ?");
    $stmt->bind_param('i', $draftId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rates = [];
    while ($row = $result->fetch_assoc()) {
        $rates[$row['tonnage']] = $row['rate_per_second'];
    }
    
    echo json_encode($rates);
    exit;
}

$loggedIn = !empty($_SESSION['user_id']);
$fullname = $_SESSION['fullname'] ?? 'Guest';

$message = $_SESSION['flash_msg'] ?? '';
$messageType = $_SESSION['flash_type'] ?? 'success';
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_owner_draft'])) {
    $draftId = intval($_POST['draft_id'] ?? 0);
    $draftTitle = trim($_POST['draft_title'] ?? '');
    $baseReference = trim($_POST['base_reference'] ?? 'BASED ON AKTUAL 2023');
    $manpowerRate = floatval($_POST['manpower_rate_sec'] ?? 0);
    $machineRates = $_POST['rates'] ?? []; 

    if ($draftTitle === '') {
        $message = "Judul Utama Perhitungan wajib diisi!";
        $messageType = 'error';
    } else {
        $conn->begin_transaction();
        try {
            if ($draftId > 0) {
                $stmt = $conn->prepare("UPDATE tbl_rate_drafts SET draft_title = ?, base_reference = ?, manpower_rate_sec = ? WHERE id = ?");
                $stmt->bind_param('ssdi', $draftTitle, $baseReference, $manpowerRate, $draftId);
                $stmt->execute();
                $newDraftId = $draftId;
                $stmt->close();

                $stmtDeleteRates = $conn->prepare("DELETE FROM tbl_rate_master WHERE draft_id = ?");
                $stmtDeleteRates->bind_param('i', $newDraftId);
                $stmtDeleteRates->execute();
                $stmtDeleteRates->close();
            } else {
                $stmt = $conn->prepare("INSERT INTO tbl_rate_drafts (draft_title, base_reference, manpower_rate_sec) VALUES (?, ?, ?)");
                $stmt->bind_param('ssd', $draftTitle, $baseReference, $manpowerRate);
                $stmt->execute();
                $newDraftId = $stmt->insert_id;
                $stmt->close();
            }

            $stmtRate = $conn->prepare("INSERT INTO tbl_rate_master (draft_id, tonnage, machine_name, rate_per_second) VALUES (?, ?, ?, ?)");
            foreach ($machineRates as $key => $rateSec) {
                $rSec = floatval($rateSec);
                
                if (isset($tonnageValues[$key])) {
                    $tonInt = $tonnageValues[$key]['value'];
                    $mName  = "M/C " . $tonnageValues[$key]['label'];
                } else {
                    $tonInt = intval($key);
                    $mName  = "M/C " . $tonInt . " Ton";
                }
                
                $stmtRate->bind_param('iisd', $newDraftId, $tonInt, $mName, $rSec);
                $stmtRate->execute();
            }
            $stmtRate->close();
            
            $conn->commit();
            write_audit_log(
                'tbl_rate_drafts',
                $newDraftId,
                $draftId > 0 ? 'update' : 'insert',
                'draft_title',
                null,
                ($draftId > 0 ? 'Memperbarui' : 'Membuat') . " Master Rate: " . $draftTitle . " (Ref: " . $baseReference . ")"
            );
            
            if ($draftId > 0) {
                $_SESSION['broadcast_update_rate'] = true;
            } else {
                $_SESSION['broadcast_new_rate'] = [
                    'id' => $newDraftId,
                    'draft_title' => $draftTitle,
                    'base_reference' => $baseReference,
                    'manpower_rate_sec' => $manpowerRate
                ];
            }

            $message = $draftId > 0
                ? "Database Master Rate '$draftTitle' Berhasil Diperbarui!"
                : "Database Master Rate '$draftTitle' Berhasil Disimpan!";
            $messageType = 'success';
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Gagal menyimpan database: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}

if (isset($_GET['delete_draft_id'])) {
    $delId = intval($_GET['delete_draft_id']);
    $stmt = $conn->prepare("DELETE FROM tbl_rate_drafts WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $delId);
        if ($stmt->execute()) {
            write_audit_log('tbl_rate_drafts', $delId, 'delete', 'draft_title', null, "Menghapus Master Rate: " . $delId);
            $_SESSION['broadcast_update_rate'] = true;

            $message = "Data master draft berhasil dihapus.";
            $messageType = 'success';
        } else {
            $message = "Gagal menghapus data.";
            $messageType = 'error';
        }
        $stmt->close();
    }
}

if (isset($_GET['edit_draft_id'])) {
    $editingDraftId = intval($_GET['edit_draft_id']);
    if ($editingDraftId > 0) {
        $stmtEditDraft = $conn->prepare("SELECT draft_title, base_reference, manpower_rate_sec FROM tbl_rate_drafts WHERE id = ?");
        $stmtEditDraft->bind_param('i', $editingDraftId);
        $stmtEditDraft->execute();
        $editDraft = $stmtEditDraft->get_result()->fetch_assoc();
        $stmtEditDraft->close();

        if ($editDraft) {
            $editingDraftTitle = $editDraft['draft_title'];
            $editingBaseReference = $editDraft['base_reference'];
            $editingManpowerRate = $editDraft['manpower_rate_sec'];

            $stmtEditRates = $conn->prepare("SELECT tonnage, rate_per_second FROM tbl_rate_master WHERE draft_id = ?");
            $stmtEditRates->bind_param('i', $editingDraftId);
            $stmtEditRates->execute();
            $editRatesResult = $stmtEditRates->get_result();
            while ($editRate = $editRatesResult->fetch_assoc()) {
                $storedTonnage = (float)$editRate['tonnage'];
                $normalizedTonnage = rtrim(rtrim(number_format($storedTonnage, 6, '.', ''), '0'), '.');
                $editingMachineRates[$normalizedTonnage] = $editRate['rate_per_second'];
            }
            $stmtEditRates->close();
        } else {
            $editingDraftId = 0;
        }
    }
}

$draftsResult = $conn->query("SELECT * FROM tbl_rate_drafts ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Rate Cost / Detik - QUOTATIONAPP</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="header.css?v=1">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="rate.css?v=2">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <div class="main-content">
            
           <?php if ($message): ?>
            <div id="toastMessage" class="toast-container <?= $messageType === 'success' ? 'toast-success' : 'toast-error' ?>">
                <span class="toast-icon"><?= $messageType === 'success' ? '' : '⚠️' ?></span>
                <div class="toast-text">
                    <?= htmlspecialchars($message) ?>
                </div>
                <span class="toast-close" onclick="closeToast()">×</span>
            </div>

            <script>
                function closeToast() {
                    const toast = document.getElementById('toastMessage');
                    if (toast) {
                        toast.classList.add('toast-hide');
                        setTimeout(() => toast.remove(), 400);
                    }
                }
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(closeToast, 3000);
                });
            </script>
        <?php endif; ?>

            <div class="owner-container">
                <div class="excel-style-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2>MULTI-DRAFT MASTER COST RATE PER DETIK</h2>
                        <p style="margin: 4px 0 0 0; color: #64748b; font-size: 13px;">Panel khusus untuk mengatur nilai acuan finansial mesin produksi</p>
                    </div>
                    <button type="button" class="btn-manage-tonnage" onclick="openTonnageModal()">
                        Atur Tipe Mesin
                    </button>
                </div>

                <form method="post" action="rate.php">
                    <input type="hidden" name="save_owner_draft" value="1">
                    <input type="hidden" name="draft_id" value="<?= $editingDraftId ?>">

                    <div class="form-row">
                        <div class="input-box">
                            <label>1. Judul Utama Draft Perhitungan</label>
                            <input type="text" name="draft_title" value="<?= htmlspecialchars($editingDraftTitle) ?>" placeholder="Contoh: DRAFT PERHITUNGAN COST / DETIK (2027)" required>
                        </div>
                        <div class="input-box">
                            <label>2. Sub-Judul Referensi Acuan</label>
                            <input type="text" name="base_reference" value="<?= htmlspecialchars($editingBaseReference) ?>" placeholder="Contoh: BASED ON AKTUAL 2023">
                        </div>
                    </div>

                    <div class="form-row" style="grid-template-columns: 1fr 1fr;">
                        <div class="input-box">
                            <label>3. Manpower Rate per Detik</label>
                            <input type="number" step="0.01" name="manpower_rate_sec" value="<?= htmlspecialchars((string)$editingManpowerRate) ?>" placeholder="Contoh: 14.25">
                        </div>
                        <div></div>
                    </div>

                    <div class="section-divider">4. INPUT MATRIX DATA GRAND TOTAL BIAYA MESIN (Rp / DETIK)</div>
                    
                    <div class="rate-matrix-grid">
                        <?php foreach ($tonnages as $key => $label): ?>
                        <div class="matrix-item">
                            <label>MC TONNAGE: <?= htmlspecialchars($label) ?></label>
                            <div class="input-wrapper">
                                <span>Rp</span>
                                <?php
                                    $masterTonnage = (float)$tonnageValues[$key]['value'];
                                    $normalizedMasterTonnage = rtrim(rtrim(number_format($masterTonnage, 6, '.', ''), '0'), '.');
                                    $savedMachineRate = $editingMachineRates[$normalizedMasterTonnage] ?? '';
                                ?>
                                <input type="number" step="0.01" name="rates[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars(number_format((float)$savedMachineRate, 2, '.', '')) ?>" placeholder="0.00" required>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn-owner-save"><?= $editingDraftId > 0 ? 'Perbarui Perhitungan' : 'Simpan Perhitungan' ?></button>
                    <?php if ($editingDraftId > 0): ?>
                        <a href="rate.php" class="btn-cancel-rate-edit">Batal Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="owner-container" style="padding: 25px;">
                <h3 style="margin-top: 0; color: #334155; font-size: 15px;">Database Versi Draft Terdaftar</h3>
                
                <div class="search-container" style="margin-bottom: 15px; max-width: 320px;">
                    <input type="text" id="search_draft_rate" placeholder="Cari judul draft atau referensi..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                </div>

                <table class="tbl-list" id="table_draft_rate_master">
                    <thead>
                        <tr>
                            <th width="60">ID</th>
                            <th>Judul Acuan Perhitungan</th>
                            <th>Referensi Sub</th>
                            <th>MP Rate/Sec</th>
                            <th width="120" style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($draftsResult->num_rows === 0): ?>
                            <tr class="no-data-row">
                                <td colspan="5" align="center" style="color: #94a3b8; font-style: italic;">Belum ada draft rate yang tersimpan.</td>
                            </tr>
                        <?php else: ?>
                            <?php while ($row = $draftsResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><span class="badge-title"><?= htmlspecialchars($row['draft_title']) ?></span></td>
                                    <td style="color: #64748b; font-size: 13px; font-weight: 500;"><?= htmlspecialchars($row['base_reference']) ?></td>
                                    <td><strong>Rp <?= number_format($row['manpower_rate_sec'], 2, ',', '.') ?></strong></td>
                                    <td align="center">
                                        <div class="draft-action-buttons">
                                            <button type="button" class="btn-view-popup" onclick="openViewModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['draft_title'], ENT_QUOTES) ?>')">
                                                View
                                            </button>   
                                            <a href="rate.php?edit_draft_id=<?= $row['id'] ?>" class="btn-view-popup btn-edit-draft">Edit</a>
                                            <button type="button" class="btn-del-link" style="border:none; background:none; cursor:pointer; padding:0;" onclick="confirmDeleteDraft(<?= $row['id'] ?>, '<?= htmlspecialchars($row['draft_title'], ENT_QUOTES) ?>')">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<div id="manageTonnageModal" class="rate-modal-overlay" style="display: none;">
    <div class="rate-modal-card" style="max-width: 650px;">
        <div class="rate-modal-header">
            <div>
                <h3>Atur Tipe Mesin</h3>
                <p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;">Tambahkan tipe mesin yang akan muncul di daftar biaya.</p>
            </div>
            <span class="rate-modal-close" onclick="closeTonnageModal()">&times;</span>
        </div>
        <div class="rate-modal-body">
            <form method="post" action="rate.php" style="background: #f8fafc; padding: 12px; border: 1px solid #e2e8f0; border-radius: 6px; box-sizing: border-box; width: 100%;">
                <input type="hidden" name="action_tonnage" value="add">
                    <strong style="font-size: 13px; color: #1e293b; display: block; margin-bottom: 8px;">Tambah Mesin Baru</strong>
                
                <div style="display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 8px; width: 100%; box-sizing: border-box;">
                    <input type="text" name="tonnage_label" placeholder="Nama mesin, contoh: 60 TON" required style="width: 100%; box-sizing: border-box; padding: 6px; font-size: 12px; min-width: 0; border: 1px solid #cbd5e1; border-radius: 4px;">
                    <input type="number" name="tonnage_value" placeholder="Tonase" min="1" required style="width: 100%; box-sizing: border-box; padding: 6px; font-size: 12px; min-width: 0; border: 1px solid #cbd5e1; border-radius: 4px;">
                </div>
                
                <button type="submit" class="btn-owner-save" style="margin-top: 10px; padding: 6px 12px; font-size: 12px; width: auto;">Tambah Mesin</button>
            </form>

            <table class="tbl-tonnage-manage">
                <thead>
                    <tr>
                        <th>Nama Mesin</th>
                        <th>Tonase</th>
                        <th width="60" style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($masterTonnageList as $mTon): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($mTon['tonnage_label']) ?></strong></td>
                        <td><?= $mTon['tonnage_value'] ?> Ton</td>
                        <td align="center">
                            <form id="form-delete-tonnage-<?= $mTon['id'] ?>" method="post" action="rate.php">
                                <input type="hidden" name="action_tonnage" value="delete">
                                <input type="hidden" name="tonnage_id" value="<?= $mTon['id'] ?>">
                                <button type="button" 
                                        onclick="confirmDeleteTonnage(<?= $mTon['id'] ?>, '<?= htmlspecialchars($mTon['tonnage_label'], ENT_QUOTES) ?>')" 
                                        style="color: #ef4444; border: none; background: none; cursor: pointer; font-weight: bold; font-size: 12px;">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="rate-modal-footer">
            <button type="button" class="btn-modal-close-action" onclick="closeTonnageModal()">Tutup</button>
        </div>
    </div>
</div>

<div id="viewRateModal" class="rate-modal-overlay">
    <div class="rate-modal-card">
        <div class="rate-modal-header">
            <div>
                <h3 id="modal_draft_title">Detail Matrix Rate</h3>
                <p style="margin: 2px 0 0 0; font-size: 12px; color: #64748b;">Melihat total biaya mesin (Rp/Detik) terdaftar</p>
            </div>
            <span class="rate-modal-close" onclick="closeViewModal()">&times;</span>
        </div>
        <div class="rate-modal-body">
            <div id="modal_matrix_loading" style="text-align: center; padding: 20px; color: #64748b;">
                Memuat data matrix...
            </div>
            
            <div id="modal_matrix_grid" class="modal-matrix-grid" style="display: none;">
                <?php foreach ($tonnages as $key => $label): ?>
                <div class="modal-matrix-item">
                    <label>MC TONNAGE: <?= htmlspecialchars($label) ?></label>
                    <div class="modal-input-mock">
                        <span>Rp</span>
                        <input type="text" id="view_rate_<?= htmlspecialchars($key) ?>" readonly value="0.00">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
        </div>
        <div class="rate-modal-footer">
            <button type="button" class="btn-modal-close-action" onclick="closeViewModal()">Tutup Tampilan</button>
        </div>
    </div>
</div>  

<div id="deleteRateModal" class="rate-delete-modal-overlay">
    <div class="rate-delete-modal-card">
        <h3 class="rate-delete-title">Konfirmasi Hapus Draft</h3>
        <p class="rate-delete-desc">
            Apakah Anda yakin ingin menghapus Master Draft <strong id="deleteTargetDraftTitle" style="color: #ef4444;"></strong>?<br>
            <span class="rate-delete-subtext">Semua data rate mesin di dalam draft ini juga akan terhapus.</span>
        </p>
        <div class="rate-delete-action-btns">
            <button type="button" onclick="closeDeleteRateModal()" class="btn-cancel-rate-del">
                Batal
            </button>
            <a id="btnConfirmDeleteRate" href="#" class="btn-confirm-rate-del">
                Ya, Hapus
            </a>
        </div>
    </div>
</div>

<script>
function openTonnageModal() {
    document.getElementById('manageTonnageModal').style.display = 'flex';
}

function closeTonnageModal() {
    document.getElementById('manageTonnageModal').style.display = 'none';
}

function openViewModal(draftId, draftTitle) {
    document.getElementById('modal_draft_title').innerText = "VIEW: " + draftTitle;
    document.getElementById('viewRateModal').style.display = 'flex';
    
    document.getElementById('modal_matrix_loading').style.display = 'block';
    document.getElementById('modal_matrix_grid').style.display = 'none';
    
    const tonnageValueMap = <?= json_encode(array_combine(array_column($tonnageValues, 'value'), array_keys($tonnageValues))) ?>;

    fetch('rate.php?get_draft_detail=' + draftId)
        .then(response => response.json())
        .then(data => {
            document.querySelectorAll('.modal-matrix-item input').forEach(el => el.value = "0.00");

            for (const [tonnage, rateSec] of Object.entries(data)) {
                let targetKey = tonnageValueMap[tonnage] || tonnage;

                const inputEl = document.getElementById('view_rate_' + targetKey);
                if (inputEl) {
                    inputEl.value = parseFloat(rateSec).toFixed(2);
                }
            }
            document.getElementById('modal_matrix_loading').style.display = 'none';
            document.getElementById('modal_matrix_grid').style.display = 'grid';
        })
        .catch(err => {
            alert('Gagal memuat detail data rate!');
            closeViewModal();
        });
}

function closeViewModal() {
    document.getElementById('viewRateModal').style.display = 'none';
}

function confirmDeleteDraft(draftId, draftTitle) {
    document.getElementById('deleteTargetDraftTitle').textContent = draftTitle;
    document.getElementById('btnConfirmDeleteRate').href = "rate.php?delete_draft_id=" + draftId;
    document.getElementById('deleteRateModal').style.display = 'flex';
}

function closeDeleteRateModal() {
    document.getElementById('deleteRateModal').style.display = 'none';
}

document.addEventListener("DOMContentLoaded", function() {
    const rateBc = new BroadcastChannel('rate_matrix_update');

    <?php if (isset($_SESSION['broadcast_new_rate'])): ?>
        const rateData = <?= json_encode($_SESSION['broadcast_new_rate']); ?>;
        rateBc.postMessage({
            event: 'new_rate_draft_created',
            data: rateData
        });
        console.log("Broadcasting draf rate baru ke quotation.php...");
        <?php unset($_SESSION['broadcast_new_rate']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['broadcast_update_rate'])): ?>
        rateBc.postMessage({
            event: 'rate_matrix_updated'
        });
        console.log("Broadcasting sinyal perubahan matrix ke quotation.php...");
        <?php unset($_SESSION['broadcast_update_rate']); ?>
    <?php endif; ?>

    const searchInput = document.getElementById('search_draft_rate');
    const tableBody = document.querySelector('#table_draft_rate_master tbody');
    
    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function() {
            const filterValue = searchInput.value.toLowerCase().trim();
            const rows = tableBody.querySelectorAll('tr:not(.no-data-row)');
            
            rows.forEach(row => {
                const titleText = row.cells[1] ? row.cells[1].textContent.toLowerCase() : '';
                const referenceText = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';
                
                if (titleText.includes(filterValue) || referenceText.includes(filterValue)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });
    }
});

function confirmDeleteTonnage(id, label) {
    Swal.fire({
        title: 'Hapus Tipe Mesin?',
        text: `Apakah Anda yakin ingin menghapus "${label}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            container: 'my-swal-container' 
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-delete-tonnage-' + id).submit();
        }
    });
}
</script>
</body>
</html>