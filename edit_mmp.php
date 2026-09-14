<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array(strtolower(trim((string)($_SESSION['role'] ?? ''))), ['purchasing', 'admin'], true)) {
    header("Location: index.php");
    exit;
}

include 'config/database.php';

$mmp_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query_head = "SELECT * FROM tbl_mmp_head WHERE mmp_id = ?";
$stmt_head = $conn->prepare($query_head);
$stmt_head->bind_param("i", $mmp_id);
$stmt_head->execute();
$res_head = $stmt_head->get_result()->fetch_assoc();

if(!$res_head){
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='kelola_mmp.php';</script>";
    exit;
}

write_audit_log(
    'tbl_mmp_head',
    $mmp_id,
    'insert',
    'view_edit_form',
    null,
    "Membuka halaman edit MMP ID: {$mmp_id} ({$res_head['keterangan_umum']})"
);

$query_det = "SELECT * FROM tbl_mmp_det WHERE mmp_id = ?";
$stmt_det = $conn->prepare($query_det);
$stmt_det->bind_param("i", $mmp_id);
$stmt_det->execute();
$res_det = $stmt_det->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Monitoring Material Price</title>
    <link rel="stylesheet" href="dashboard.css?v=1">
    <link rel="stylesheet" href="header.css?v=1">
    <link rel="stylesheet" href="sidebar.css?v=1">
    <link rel="stylesheet" href="mmp.css">
    <link rel="stylesheet" href="edit_mmp.css?v=2">
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="page-title edit-page-heading">
        <span>Edit Monitoring Material Price (MMP)</span>
        <a href="kelola_mmp.php" class="btn-back-mmp">&larr; Kembali</a>
    </div>
    
    <form action="update_mmp.php" method="POST">
        <input type="hidden" name="mmp_id" value="<?php echo $mmp_id; ?>">
        
        <div class="mmp-header-card">
            <div class="form-group-title">
                <label for="judul_mmp">Judul Acuan / No. Dokumen MMP:</label>
                <input type="text" id="judul_mmp" name="judul_mmp" required value="<?php echo htmlspecialchars($res_head['keterangan_umum']); ?>">
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <button type="button" class="btn-custom btn-add" onclick="tambahBarisMmp()">+ Tambah Item Material</button>
        </div>

        <div class="table-card">
            <table class="table-mmp">
                <thead>
                    <tr>
                        <th rowspan="2">Customer</th>
                        <th rowspan="2">Part Number</th>
                        <th rowspan="2">Part Name</th>
                        <th colspan="2">Material</th>
                        <th rowspan="2">Supplier</th>
                        <th rowspan="2">Item Code</th>
                        <th colspan="3">Harga Material / Kg</th>
                        <th rowspan="2">Berat Part<br>(Kg)</th>
                        <th rowspan="2">Remark</th>
                        <th rowspan="2" style="width: 70px;">Aksi</th>
                    </tr>
                    <tr>
                        <th>Quotation</th>
                        <th>Aktual</th>
                        <th>Marketing</th>
                        <th>Purchasing</th>
                        <th>Diff (%)</th> 
                    </tr>
                </thead>
                <tbody id="tbody_mmp">
                    <?php 
                    $loopIndex = 0;
                    while($det = $res_det->fetch_assoc()): 
                        $loopIndex++;
                    ?>
                    <tr id="row_<?php echo $loopIndex; ?>">
                        <td><input type="text" name="customer[]" required value="<?php echo htmlspecialchars($det['customer']); ?>"></td>
                        <td><input type="text" name="part_number[]" required value="<?php echo htmlspecialchars($det['part_number']); ?>"></td>
                        <td><input type="text" name="part_name[]" required value="<?php echo htmlspecialchars($det['part_name']); ?>"></td>
                        <td><input type="text" name="mat_quotation[]" value="<?php echo htmlspecialchars($det['mat_quotation']); ?>"></td>
                        <td><input type="text" name="mat_aktual[]" value="<?php echo htmlspecialchars($det['mat_aktual']); ?>"></td>
                        <td><input type="text" name="supplier[]" value="<?php echo htmlspecialchars($det['supplier']); ?>"></td>
                        <td><input type="text" name="item_code[]" value="<?php echo htmlspecialchars($det['item_code']); ?>"></td>
                        
                        <td><input type="number" step="0.01" id="mkr_<?php echo $loopIndex; ?>" name="harga_mkr[]" oninput="hitungMmp(<?php echo $loopIndex; ?>)" value="<?php echo $det['harga_mkr']; ?>"></td>
                        <td><input type="number" step="0.01" id="pch_<?php echo $loopIndex; ?>" name="harga_pch[]" oninput="hitungMmp(<?php echo $loopIndex; ?>)" value="<?php echo $det['harga_pch']; ?>"></td>
                        <td><input type="text" id="diff_kg_<?php echo $loopIndex; ?>" name="diff_kg[]" class="readonly-bg" readonly value="<?php echo $det['diff_kg']; ?>"></td>
                        
                        <td><input type="number" step="0.001" id="berat_<?php echo $loopIndex; ?>" name="berat_part[]" oninput="hitungMmp(<?php echo $loopIndex; ?>)" value="<?php echo $det['berat_part']; ?>"></td>
                        
                        <td><input type="text" name="remark[]" value="<?php echo htmlspecialchars($det['remark']); ?>"></td>
                        <td style="text-align: center;"><button type="button" class="btn-delete" onclick="hapusBarisMmp(<?php echo $loopIndex; ?>)">Hapus</button></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn-custom btn-submit">Update & Simpan Perubahan</button>
    </form>
</div>

<script>
let rowCounter = <?php echo $loopIndex; ?>;

function tambahBarisMmp() {
    rowCounter++;
    const tbody = document.getElementById('tbody_mmp');
    const tr = document.createElement('tr');
    tr.id = `row_${rowCounter}`;
    tr.innerHTML = `
        <td><input type="text" name="customer[]" required placeholder="..."></td>
        <td><input type="text" name="part_number[]" required placeholder="..."></td>
        <td><input type="text" name="part_name[]" required placeholder="..."></td>
        <td><input type="text" name="mat_quotation[]" placeholder="..."></td>
        <td><input type="text" name="mat_aktual[]" placeholder="..."></td>
        <td><input type="text" name="supplier[]" placeholder="..."></td>
        <td><input type="text" name="item_code[]" placeholder="..."></td>
        <td><input type="number" step="0.01" id="mkr_${rowCounter}" name="harga_mkr[]" oninput="hitungMmp(${rowCounter})" value="0"></td>
        <td><input type="number" step="0.01" id="pch_${rowCounter}" name="harga_pch[]" oninput="hitungMmp(${rowCounter})" value="0"></td>
        <td><input type="text" id="diff_kg_${rowCounter}" name="diff_kg[]" class="readonly-bg" readonly value="0.00%"></td>
        <td><input type="number" step="0.001" id="berat_${rowCounter}" name="berat_part[]" oninput="hitungMmp(${rowCounter})" value="0.000"></td>
        <td><input type="text" name="remark[]" placeholder="..."></td>
        <td style="text-align: center;"><button type="button" class="btn-delete" onclick="hapusBarisMmp(${rowCounter})">Hapus</button></td>
    `;
    tbody.appendChild(tr);
}

function hapusBarisMmp(id) {
    const row = document.getElementById(`row_${id}`);
    if(row) row.remove();
}

function hitungMmp(id) {
    const mkr = parseFloat(document.getElementById(`mkr_${id}`).value) || 0;
    const pch = parseFloat(document.getElementById(`pch_${id}`).value) || 0;
    const berat = parseFloat(document.getElementById(`berat_${id}`).value) || 0;

    if (mkr !== 0) {
        const diffHargaPersen = ((mkr - pch) / mkr) * 100;
        document.getElementById(`diff_kg_${id}`).value = diffHargaPersen.toFixed(2) + "%";
    } else {
        document.getElementById(`diff_kg_${id}`).value = "0.00%";
    }
}

window.onload = function() {
    for (let i = 1; i <= rowCounter; i++) {
        if(document.getElementById(`mkr_${i}`)) {
            hitungMmp(i);
        }
    }
};
</script>
</body>
</html>