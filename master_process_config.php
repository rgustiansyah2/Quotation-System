<?php
ob_start();
session_start();
require_once 'config/database.php';

$activeTab = $_GET['tab'] ?? 'purging';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $redirectTab = $activeTab;
    $processMasterEvent = null;

    if ($action === 'save_purging') {
        $stmt = $conn->prepare("INSERT INTO tbl_purging_master (mc_ton_min, mc_ton_max, purging_ori_kg, purging_cellpurg_kg) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iidd", $_POST['min'], $_POST['max'], $_POST['ori_kg'], $_POST['cellpurg_kg']);
        
        if($stmt->execute()) {
            $new_id = $stmt->insert_id;
            $description = "Menambahkan purging master data: MC Ton Min: {$_POST['min']}, MC Ton Max: {$_POST['max']}, Purging Ori (Kg): {$_POST['ori_kg']}, Cellpurg (Kg): {$_POST['cellpurg_kg']}";
            write_audit_log('tbl_purging_master', $new_id, 'insert', null, null, $description);
            $processMasterEvent = 'new_process_master';
        }
        $redirectTab = 'purging';

    } elseif ($action === 'save_dandori') {
        $stmt = $conn->prepare("INSERT INTO tbl_dandori_master (mc_ton_min, mc_ton_max, machine_type, dandori_minutes) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $_POST['min'], $_POST['max'], $_POST['machine_type'], $_POST['minutes']);
        
        if($stmt->execute()) {
            $new_id = $stmt->insert_id;
            $description = "Menambahkan dandori master data: MC Ton Min: {$_POST['min']}, MC Ton Max: {$_POST['max']}, Machine Type: {$_POST['machine_type']}, Dandori Minutes: {$_POST['minutes']}";
            write_audit_log('tbl_dandori_master', $new_id, 'insert', null, null, $description);
            $processMasterEvent = 'new_process_master';
        }
        $redirectTab = 'dandori';

    } elseif ($action === 'save_mold') {
        $stmt = $conn->prepare("INSERT INTO tbl_mold_maintenance_master (category_type, key_name, cost_per_month) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $_POST['category_type'], $_POST['key_name'], $_POST['cost']);
        
        if($stmt->execute()) {
            $new_id = $stmt->insert_id;
            $description = "Menambahkan mold maintenance master data: Category Type: {$_POST['category_type']}, Key Name: {$_POST['key_name']}, Cost Per Month: {$_POST['cost']}";
            write_audit_log('tbl_mold_maintenance_master', $new_id, 'insert', null, null, $description);
            $processMasterEvent = 'new_process_master';
        }
        $redirectTab = 'mold';

    } elseif ($action === 'update_mold_detail') {
        $id = $_POST['id'];
        $materials = [];

        if (isset($_POST['material_labels'])) {
            $labels = $_POST['material_labels'];
            $existing_imgs = $_POST['existing_images'] ?? [];

            foreach ($labels as $i => $label) {
                $img_path = $existing_imgs[$i] ?? '';

                if (isset($_FILES['material_images']['name'][$i]) && $_FILES['material_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['material_images']['name'][$i], PATHINFO_EXTENSION);
                    $filename = 'mold_' . time() . '_' . $i . '.' . $ext;
                    
                    if (!is_dir('uploads')) {
                        mkdir('uploads', 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['material_images']['tmp_name'][$i], 'uploads/' . $filename)) {
                        $img_path = 'uploads/' . $filename;
                    }
                }

                if (!empty(trim($label)) || !empty($img_path)) {
                    $materials[] = [
                        'label' => trim($label),
                        'image' => $img_path
                    ];
                }
            }
        }

        $json_data = json_encode($materials);

        $stmt = $conn->prepare("UPDATE tbl_mold_maintenance_master SET flexible_fields = ? WHERE id = ?");
        $stmt->bind_param("si", $json_data, $id);
        $stmt->execute();
        $processMasterEvent = 'update_process_master';
        
        $redirectTab = 'mold';

    } elseif ($action === 'delete_purging') {
        $stmt = $conn->prepare("UPDATE tbl_purging_master SET status='inactive' WHERE id=?");
        $stmt->bind_param("i", $_POST['id']);

        if($stmt->execute()) {
            $description = "Menghapus purging master data dengan ID: {$_POST['id']}";
            write_audit_log('tbl_purging_master', $_POST['id'], 'delete', null, null, $description);
            $processMasterEvent = 'delete_process_master';
        }
        $redirectTab = 'purging';

    } elseif ($action === 'delete_dandori') {
        $stmt = $conn->prepare("UPDATE tbl_dandori_master SET status='inactive' WHERE id=?");
        $stmt->bind_param("i", $_POST['id']);

        if($stmt->execute()) {
            $description = "Menghapus dandori master data dengan ID: {$_POST['id']}";
            write_audit_log('tbl_dandori_master', $_POST['id'], 'delete', null, null, $description);
            $processMasterEvent = 'delete_process_master';
        }
        $redirectTab = 'dandori';

    } elseif ($action === 'delete_mold') {
        $stmt = $conn->prepare("UPDATE tbl_mold_maintenance_master SET status='inactive' WHERE id=?");
        $stmt->bind_param("i", $_POST['id']);

        if($stmt->execute()) {
            $description = "Menghapus mold maintenance master data dengan ID: {$_POST['id']}";
            write_audit_log('tbl_mold_maintenance_master', $_POST['id'], 'delete', null, null, $description);
            $processMasterEvent = 'delete_process_master';
        }
        $redirectTab = 'mold';
    }

    if ($processMasterEvent) {
        $_SESSION['process_master_event'] = $processMasterEvent;
    }
    header("Location: master_process_config.php?tab=" . $redirectTab);
    exit;
}

$purgingList = $conn->query("SELECT * FROM tbl_purging_master WHERE status='active' ORDER BY mc_ton_min ASC");
$dandoriList = $conn->query("SELECT * FROM tbl_dandori_master WHERE status='active' ORDER BY mc_ton_min ASC, machine_type ASC");
$moldList    = $conn->query("SELECT * FROM tbl_mold_maintenance_master WHERE status='active'");
$processMasterEvent = $_SESSION['process_master_event'] ?? null;
unset($_SESSION['process_master_event']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Process Configuration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="sidebar.css?v=1">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="masterprocess.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <script>
    const processMasterChannel = new BroadcastChannel('process_master_update');
    <?php if ($processMasterEvent): ?>
    processMasterChannel.postMessage({ event: <?= json_encode($processMasterEvent) ?> });
    <?php endif; ?>
    </script>

    <div class="main-content">
        <div class="config-card">
            <h3 class="config-title">Master Data: Purging, Dandori & Mold Maintenance</h3>

            <ul class="nav config-tabs" id="configTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link <?= $activeTab === 'purging' ? 'active' : '' ?>" id="purging-tab" data-bs-toggle="tab" data-bs-target="#purging" type="button" role="tab">1. Purging Master</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $activeTab === 'dandori' ? 'active' : '' ?>" id="dandori-tab" data-bs-toggle="tab" data-bs-target="#dandori" type="button" role="tab">2. Dandori Master</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $activeTab === 'mold' ? 'active' : '' ?>" id="mold-tab" data-bs-toggle="tab" data-bs-target="#mold" type="button" role="tab">3. Mold Maintenance Master</button>
                </li>
            </ul>

            <div class="tab-content" id="configTabsContent">
                
                <div class="tab-pane fade <?= $activeTab === 'purging' ? 'show active' : '' ?>" id="purging" role="tabpanel">
                    <div class="form-box">
                        <form method="POST" class="row g-3 align-items-end">
                            <input type="hidden" name="action" value="save_purging">
                            <div class="col-md-3">
                                <label>MC Ton Min</label>
                                <input type="number" name="min" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label>MC Ton Max</label>
                                <input type="number" name="max" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label>Purging Ori (Kg)</label>
                                <input type="number" step="0.01" name="ori_kg" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <label>Cellpurg (Kg)</label>
                                <input type="number" step="0.01" name="cellpurg_kg" class="form-control" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-submit w-100">Simpan</button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-config align-middle">
                            <thead>
                                <tr>
                                    <th>Range MC Ton</th>
                                    <th>Purging Ori (Kg)</th>
                                    <th>Purging Cellpurg (Kg)</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($purgingList && $purgingList->num_rows > 0): ?>
                                    <?php while($r = $purgingList->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['mc_ton_min']) ?> - <?= htmlspecialchars($r['mc_ton_max']) ?> Ton</td>
                                            <td><?= htmlspecialchars($r['purging_ori_kg']) ?> Kg</td>
                                            <td><?= htmlspecialchars($r['purging_cellpurg_kg']) ?> Kg</td>
                                            <td class="text-center">
                                                <form id="form-delete-purging-<?= $r['id'] ?>" method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete_purging">
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('form-delete-purging-<?= $r['id'] ?>', 'data Purging (<?= $r['mc_ton_min'] ?>-<?= $r['mc_ton_max'] ?> Ton)')">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade <?= $activeTab === 'dandori' ? 'show active' : '' ?>" id="dandori" role="tabpanel">
                    <div class="form-box">
                        <form method="POST" class="row g-3 align-items-end">
                            <input type="hidden" name="action" value="save_dandori">
                            <div class="col-md-3">
                                <label>MC Ton Min</label>
                                <input type="number" name="min" class="form-control" placeholder="30" required>
                            </div>
                            <div class="col-md-3">
                                <label>MC Ton Max</label>
                                <input type="number" name="max" class="form-control" placeholder="40" required>
                            </div>
                            <div class="col-md-2">
                                <label>Tipe Mesin</label>
                                <select name="machine_type" class="form-select" required>
                                    <option value="horizontal">Horizontal</option>
                                    <option value="vertical">Vertikal</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Waktu (Menit)</label>
                                <input type="number" name="minutes" class="form-control" placeholder="120" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-submit w-100">Simpan</button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-config align-middle">
                            <thead>
                                <tr>
                                    <th>Range MC Ton</th>
                                    <th>Tipe Mesin</th>
                                    <th>Waktu Dandori (Menit)</th>
                                    <th>Waktu (Jam)</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($dandoriList && $dandoriList->num_rows > 0): ?>
                                    <?php while($r = $dandoriList->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($r['mc_ton_min']) ?> - <?= htmlspecialchars($r['mc_ton_max']) ?> Ton</td>
                                            <td>
                                                <?php if(($r['machine_type'] ?? '') === 'vertical'): ?>
                                                    <span class="badge bg-warning text-dark fw-bold">Vertikal</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Horizontal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><strong><?= htmlspecialchars($r['dandori_minutes']) ?></strong> Menit</td>
                                            <td><?= number_format($r['dandori_minutes']/60, 2) ?> Jam</td>
                                            <td class="text-center">
                                                <form id="form-delete-dandori-<?= $r['id'] ?>" method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete_dandori">
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('form-delete-dandori-<?= $r['id'] ?>', 'data Dandori (<?= $r['mc_ton_min'] ?>-<?= $r['mc_ton_max'] ?> Ton)')">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">Belum ada data.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade <?= $activeTab === 'mold' ? 'show active' : '' ?>" id="mold" role="tabpanel">
                    <div class="form-box">
                        <form method="POST" class="row g-3 align-items-end">
                            <input type="hidden" name="action" value="save_mold">
                            
                            <div class="col-md-4">
                                <label>Kategori</label>
                                <select name="category_type" class="form-select">
                                    <option value="material">Material Cleaning</option>
                                    <option value="runner">Runner System</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label>Key Name</label>
                                <input type="text" name="key_name" class="form-control" placeholder="Contoh: SPECIAL_MAT / HOT_RUNNER" required>
                            </div>
                            <div class="col-md-4">
                                <label>Cost / Bulan (Rp)</label>
                                <input type="number" step="0.01" name="cost" class="form-control" required>
                            </div>

                            <div class="col-12 text-end mt-3">
                                <button type="submit" class="btn btn-primary btn-submit px-4">Simpan</button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-config align-middle">
                            <thead>
                                <tr>
                                    <th>Tipe Kategori</th>
                                    <th>Key / Spesifikasi</th>
                                    <th>Biaya / Bulan</th>
                                    <th class="text-center" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($moldList && $moldList->num_rows > 0): ?>
                                    <?php while($r = $moldList->fetch_assoc()): ?>
                                        <?php 
                                            $formattedCost = 'Rp ' . number_format($r['cost_per_month'], 0, ',', '.');
                                            $flexDataJson = !empty($r['flexible_fields']) ? htmlspecialchars($r['flexible_fields'], ENT_QUOTES) : '[]';
                                        ?>
                                        <tr>
                                            <td><span class="badge bg-secondary"><?= strtoupper(htmlspecialchars($r['category_type'])) ?></span></td>
                                            <td>
                                                <code class="clickable-key text-primary fw-bold" style="cursor: pointer;" 
                                                      onclick="openMoldDetailModal('<?= $r['id'] ?>', '<?= htmlspecialchars($r['key_name'], ENT_QUOTES) ?>', '<?= $flexDataJson ?>')">
                                                    <?= htmlspecialchars($r['key_name']) ?>
                                                </code>
                                            </td>
                                            <td class="text-success fw-bold"><?= $formattedCost ?></td>
                                            <td class="text-center">
                                                <form id="form-delete-mold-<?= $r['id'] ?>" method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete_mold">
                                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('form-delete-mold-<?= $r['id'] ?>', 'data Mold Maintenance (<?= htmlspecialchars($r['key_name'], ENT_QUOTES) ?>)')">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted">Belum ada data.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="moldDetailModal" tabindex="-1" aria-labelledby="moldDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_mold_detail">
                    <input type="hidden" name="id" id="modal_mold_id">

                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <h5 class="modal-title fw-bold" id="moldDetailModalLabel">Detail & List Material</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4">
                        <p class="text-muted mb-3">Kelola daftar material/komponen untuk spesifikasi ini beserta gambar pendukungnya.</p>
                        
                        <div id="modal-material-items-container">
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary fw-bold" onclick="addMaterialRow()">
                                + Tambah Varian / Material Baru
                            </button>
                        </div>
                    </div>

                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Semua Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    let materialRowIndex = 0;

    function addMaterialRow(label = '', imgUrl = '', isExisting = false) {
        const container = document.getElementById('modal-material-items-container');
        const rowId = `mat-row-${materialRowIndex}`;
        
        const defaultImg = (imgUrl && imgUrl !== 'uploads/' && !imgUrl.endsWith('uploads/')) 
            ? imgUrl 
            : 'https://via.placeholder.com/120x90?text=No+Image';

        let contentHTML = '';

        if (isExisting) {
            contentHTML = `
                <div class="row g-3 align-items-center">
                    <!-- Preview Gambar Baku -->
                    <div class="col-md-3 text-center">
                        <img src="${defaultImg}" class="img-thumbnail rounded shadow-sm" style="max-height: 80px; width: 100%; object-fit: cover;">
                    </div>

                    <div class="col-md-7">
                        <span class="badge bg-success mb-1">Tersimpan</span>
                        <h6 class="fw-bold text-dark mb-0">${label}</h6>
                        <input type="hidden" name="existing_images[${materialRowIndex}]" value="${imgUrl}">
                        <input type="hidden" name="material_labels[${materialRowIndex}]" value="${label}">
                    </div>

                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="document.getElementById('${rowId}').remove()" title="Hapus Material Ini">
                            Hapus
                        </button>
                    </div>
                </div>
            `;
        } else {
            contentHTML = `
                <div class="row g-3 align-items-center">
                    <!-- Preview Gambar Input -->
                    <div class="col-md-2 text-center">
                        <img id="preview_${rowId}" src="${defaultImg}" class="img-thumbnail rounded" style="max-height: 80px; width: 100%; object-fit: cover;">
                    </div>
                    
                    <!-- Choose File Input -->
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Pilih Gambar Material</label>
                        <input type="file" name="material_images[${materialRowIndex}]" class="form-control form-control-sm" accept="image/*" onchange="previewMaterialImage(this, 'preview_${rowId}')">
                        <input type="hidden" name="existing_images[${materialRowIndex}]" value="">
                    </div>

                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-secondary mb-1">Material</label>
                        <input type="text" name="material_labels[${materialRowIndex}]" value="${label}" class="form-control form-control-sm" placeholder="Contoh: PMMA Grade A" required>
                    </div>

                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 mt-3" onclick="document.getElementById('${rowId}').remove()" title="Batal Tambah">
                            Hapus
                        </button>
                    </div>
                </div>
            `;
        }

        const rowCard = `
            <div class="card mb-3 border shadow-sm p-3 rounded-3 material-item-row" id="${rowId}">
                ${contentHTML}
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowCard);
        materialRowIndex++;
    }

    function previewMaterialImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openMoldDetailModal(id, keyName, flexJsonStr) {
        document.getElementById('modal_mold_id').value = id;
        document.getElementById('moldDetailModalLabel').innerText = 'Detail & Kelola Material: ' + keyName;

        const container = document.getElementById('modal-material-items-container');
        container.innerHTML = '';
        materialRowIndex = 0;

        let flexData = [];
        try {
            flexData = JSON.parse(flexJsonStr);
        } catch(e) {
            flexData = [];
        }

        if (Array.isArray(flexData) && flexData.length > 0) {
            flexData.forEach(item => {
                addMaterialRow(item.label || '', item.image || '', true);
            });
        } else {
            addMaterialRow('', '', false);
        }

        const modal = new bootstrap.Modal(document.getElementById('moldDetailModal'));
        modal.show();
    }

    function confirmDelete(formId, itemName) {
        Swal.fire({
            title: 'Hapus ' + itemName + '?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-4 shadow',
                confirmButton: 'btn btn-danger px-4 py-2 font-weight-bold',
                cancelButton: 'btn btn-secondary px-4 py-2 me-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
    </script>
</body>
</html>