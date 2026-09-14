<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$message = '';
$editMode = false;

$form = [
    'id' => 0,
    'description' => '',
    'p_cm' => 0,
    'l_cm' => 0,
    't_cm' => 0,
    'volume_cm3' => 0,
    'harga_supplier' => 0,
    'profit_persen' => 0,
    'harga_jual' => 0,
    'status' => 'active',
    'remark' => ''
];

if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    
    $oldDesc = '';
    $stmtCheck = $conn->prepare("SELECT description FROM tbl_packing_standard WHERE id=?");
    $stmtCheck->bind_param("i", $deleteId);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    if ($rowCheck = $resCheck->fetch_assoc()) {
        $oldDesc = $rowCheck['description'];
    }
    $stmtCheck->close();

    $stmt = $conn->prepare("DELETE FROM tbl_packing_standard WHERE id=?");
    $stmt->bind_param("i", $deleteId);
    if ($stmt->execute()) {
        $log_note = "Menghapus master packing '$oldDesc' (ID: $deleteId)";
        
        if (function_exists('write_audit_log')) {
            write_audit_log('tbl_packing_standard', $deleteId, 'delete', null, null, $log_note);
        }

        header("Location: packing_standard.php?msg=deleted");
        exit;
    }
    $stmt->close();
}

if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM tbl_packing_standard WHERE id=?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $form = $result->fetch_assoc();
        $editMode = true;
    }
    $stmt->close();
}

if (isset($_POST['save']) || isset($_GET['ajax_save'])) {
    $id = intval($_POST['id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $p_cm = floatval($_POST['p_cm'] ?? 0);
    $l_cm = floatval($_POST['l_cm'] ?? 0);
    $t_cm = floatval($_POST['t_cm'] ?? 0);
    $volume_cm3 = floatval($_POST['volume_cm3'] ?? 0);
    $harga_supplier = floatval($_POST['harga_supplier'] ?? 0);
    $profit_persen = floatval($_POST['profit_persen'] ?? 0);
    $harga_jual = floatval($_POST['harga_jual'] ?? 0);
    $status = trim($_POST['status'] ?? 'active');
    $remark = trim($_POST['remark'] ?? '');

    $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    if ($description == '') {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'Description wajib diisi.']);
            exit;
        }
        $message = "Description wajib diisi.";
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE tbl_packing_standard SET description=?, p_cm=?, l_cm=?, t_cm=?, volume_cm3=?, harga_supplier=?, profit_persen=?, harga_jual=?, status=?, remark=?, updated_at=NOW() WHERE id=?");
            $stmt->bind_param("sddddddsssi", $description, $p_cm, $l_cm, $t_cm, $volume_cm3, $harga_supplier, $profit_persen, $harga_jual, $status, $remark, $id);
            $action_type = 'update';
        } else {
            $stmt = $conn->prepare("INSERT INTO tbl_packing_standard (description, p_cm, l_cm, t_cm, volume_cm3, harga_supplier, profit_persen, harga_jual, status, remark) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("sddddddsss", $description, $p_cm, $l_cm, $t_cm, $volume_cm3, $harga_supplier, $profit_persen, $harga_jual, $status, $remark);
            $action_type = 'insert';
        }

        if ($stmt->execute()) {
            $target_id = ($id > 0) ? $id : $conn->insert_id;
            if ($action_type === 'update') {
                $log_note = "Mengubah data master packing '$description' (ID: $target_id)";
            } else {
                $log_note = "Menambahkan master packing baru '$description' (ID: $target_id)";
            }

            if (function_exists('write_audit_log')) {
                write_audit_log('tbl_packing_standard', $target_id, $action_type, null, null, $log_note);
            }
            
            if ($is_ajax) {
                echo json_encode([
                    'success' => true,
                    'mode' => ($id > 0 ? 'updated' : 'saved'),
                    'id' => $target_id,
                    'description' => $description,
                    'harga_jual' => $harga_jual,
                    'volume_cm3' => $volume_cm3
                ]);
                exit;
            }
            
            header("Location: packing_standard.php?msg=" . ($id > 0 ? 'updated' : 'saved'));
            exit;
        } else {
            if ($is_ajax) {
                echo json_encode(['success' => false, 'message' => $stmt->error]);
                exit;
            }
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Packing Standard Master</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="header.css?v=1">
    <link rel="stylesheet" href="sidebar.css?v=1">
    <link rel="stylesheet" href="packingstandard.css?v=<?= time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container-fluid">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-title">Master Data Packing Standard</div>

        <div class="card">
            <div class="section-title"><?= $editMode ? 'Edit Master Packing' : 'Input Master Packing'; ?></div>
            <form method="POST" id="formPacking">
                <input type="hidden" name="id" value="<?= $form['id']; ?>">
                
                <div class="form-grid">
                    <div class="form-group form-group-full">
                        <label>Description / Kode Packing (Acuan Utama) *</label>
                        <input type="text" name="description" required placeholder="Contoh: V-BOX, P-BOX, Pallet" value="<?= htmlspecialchars($form['description']); ?>">
                    </div>

                    <div class="form-group">
                        <label>Panjang (P) cm</label>
                        <input type="number" step="0.1" name="p_cm" id="p_cm" class="calc-trigger" value="<?= $form['p_cm']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Lebar (L) cm</label>
                        <input type="number" step="0.1" name="l_cm" id="l_cm" class="calc-trigger" value="<?= $form['l_cm']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Tinggi (T) cm</label>
                        <input type="number" step="0.1" name="t_cm" id="t_cm" class="calc-trigger" value="<?= $form['t_cm']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Volume (cm³)</label>
                        <input type="number" step="0.01" name="volume_cm3" id="volume_cm3" readonly class="readonly-input" value="<?= $form['volume_cm3']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Harga Supplier (Rp)</label>
                        <input type="number" step="1" name="harga_supplier" id="harga_supplier" class="calc-trigger" value="<?= $form['harga_supplier']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Profit (%)</label>
                        <input type="number" step="0.1" name="profit_persen" id="profit_persen" class="calc-trigger" value="<?= $form['profit_persen']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Harga Jual (Rp)</label>
                        <input type="number" step="1" name="harga_jual" id="harga_jual" readonly class="readonly-input" value="<?= $form['harga_jual']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="active" <?= ($form['status']=='active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?= ($form['status']=='inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="button-container" style="gap: 10px; align-items: center;">
                    <button type="submit" name="save" class="btn-save">
                        <?= $editMode ? 'Update ke Master' : 'Simpan ke Master'; ?>
                    </button>
                    <?php if($editMode): ?>
                        <a href="packing_standard.php" class="btn-reset">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="section-title">Daftar Master Packing</div>
            <div class="search-container" style="margin-bottom: 15px; max-width: 300px;">
                <input type="text" id="search_packing" class="form-control" placeholder="Cari deskripsi / kode packing..." style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.95rem;">
            </div>

            <div class="table-wrapper">
                <table class="data-table" id="table_packing_master">
                    <thead>
                        <tr>
                            <th>Desc</th>
                            <th>P (cm)</th>
                            <th>L (cm)</th>
                            <th>T (cm)</th>
                            <th>Volume (cm³)</th> 
                            <th>Harga Supp</th>
                            <th>Profit (%)</th>
                            <th>Harga Jual</th>
                            <th>Status</th>
                            <th style="text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM tbl_packing_standard ORDER BY description ASC");
                        while($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['description']); ?></strong></td>
                            <td><?= number_format($row['p_cm'], 2); ?></td>
                            <td><?= number_format($row['l_cm'], 2); ?></td>
                            <td><?= number_format($row['t_cm'], 2); ?></td>
                            <td style="color: #64748b; font-weight: 500;"><?= number_format($row['volume_cm3'], 2, ',', '.'); ?></td> 
                            <td>Rp <?= number_format($row['harga_supplier'], 0, ',', '.'); ?></td>
                            <td><?= number_format($row['profit_persen'], 2); ?>%</td>
                            <td style="color:#1abc9c; font-weight:700;">Rp <?= number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge <?= $row['status']=='active' ? 'badge-active' : 'badge-inactive'; ?>">
                                    <?= ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <a href="packing_standard.php?edit=<?= $row['id']; ?>" class="action-link edit-link">Edit</a>
                                <a href="javascript:void(0)" onclick="confirmDelete(<?= $row['id']; ?>, '<?= htmlspecialchars($row['description'], ENT_QUOTES); ?>')" class="action-link delete-link">Del</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

<?php if ($message != ''): ?>
    Toast.fire({
        icon: 'error',
        title: '<?= addslashes($message); ?>'
    });
<?php endif; ?>

<?php if (isset($_GET['msg'])): ?>
    let msgText = '';
    <?php if ($_GET['msg'] === 'saved'): ?>
        msgText = 'Data berhasil disimpan ke master!';
    <?php elseif ($_GET['msg'] === 'updated'): ?>
        msgText = 'Data berhasil diperbarui!';
    <?php elseif ($_GET['msg'] === 'deleted'): ?>
        msgText = 'Data berhasil dihapus!';
    <?php endif; ?>

    Toast.fire({
        icon: 'success',
        title: msgText
    });

    const cleanUrl = new URL(window.location);
    cleanUrl.searchParams.delete('msg');
    window.history.replaceState({}, document.title, cleanUrl);
<?php endif; ?>

function confirmDelete(id, desc) {
    Swal.fire({
        title: 'Hapus Master Packing?',
        text: `Apakah Anda yakin ingin menghapus "${desc}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `packing_standard.php?delete=${id}`;
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const bc = new BroadcastChannel('matrix_update');
    const pInput = document.getElementById('p_cm');
    const lInput = document.getElementById('l_cm');
    const tInput = document.getElementById('t_cm');
    const volInput = document.getElementById('volume_cm3');
    const hgSupInput = document.getElementById('harga_supplier');
    const profitInput = document.getElementById('profit_persen');
    const hgJualInput = document.getElementById('harga_jual');
    const formElement = document.getElementById('formPacking');

    function hitungOtomatis() {
        const p = parseFloat(pInput.value) || 0;
        const l = parseFloat(lInput.value) || 0;
        const t = parseFloat(tInput.value) || 0;
        volInput.value = (p * l * t).toFixed(2);

        const hargaSupplier = parseFloat(hgSupInput.value) || 0;
        const profitPersen = parseFloat(profitInput.value) || 0;
        const hargaJual = hargaSupplier + (hargaSupplier * (profitPersen / 100));
        hgJualInput.value = Math.round(hargaJual);
    }

    document.querySelectorAll('.calc-trigger').forEach(el => {
        el.addEventListener('input', hitungOtomatis);
    });

    const searchInput = document.getElementById('search_packing');
    const tableBody = document.querySelector('#table_packing_master tbody');
    
    if (searchInput && tableBody) {
        searchInput.addEventListener('input', function() {
            const filterValue = searchInput.value.toLowerCase().trim();
            const rows = tableBody.querySelectorAll('tr');
            
            rows.forEach(row => {
                const descCell = row.cells[0];
                if (descCell) {
                    const textValue = descCell.textContent || descCell.innerText;
                    if (textValue.toLowerCase().indexOf(filterValue) > -1) {
                        row.style.display = ""; 
                    } else {
                        row.style.display = "none"; 
                    }
                }
            });
        });
    }

    if (formElement) {
        formElement.addEventListener('submit', function(e) {
            e.preventDefault(); 

            const formData = new FormData(formElement);
            formData.append('save', '1'); 

            fetch('packing_standard.php?ajax_save=1', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    bc.postMessage({
                        event: 'new_packing_material',
                        data: {
                            id: res.id,
                            description: res.description,
                            harga_jual: res.harga_jual,
                            volume_cm3: res.volume_cm3
                        }
                    });

                    Swal.fire({
                        title: 'Berhasil!',
                        text: res.mode === 'saved' ? 'Data berhasil disimpan & disinkronkan!' : 'Data berhasil diperbarui!',
                        icon: 'success',
                        confirmButtonColor: '#1abc9c'
                    }).then(() => {
                        window.location.href = 'packing_standard.php'; 
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Gagal menyimpan: ' + res.message,
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan sistem saat menghubungi server.',
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    }
});
</script>
</body>
</html>