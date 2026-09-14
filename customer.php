<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
include 'auth.php';

$loggedIn = !empty($_SESSION['user_id']);
$fullname = $_SESSION['fullname'] ?? 'Guest';
$role = $_SESSION['role'] ?? 'User';
$message = '';
$messageType = 'success';
$customerEvents = $_SESSION['customer_events'] ?? [];
unset($_SESSION['customer_events']);

if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM tbl_customer WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $deleteId);
        
        try {
            if ($stmt->execute()) {
                $message = "Data customer berhasil dihapus.";
                $messageType = 'success';
               
                if (function_exists('write_audit_log')) {
                    write_audit_log('tbl_customer', $deleteId, 'delete', null, null, "Menghapus master customer ID: $deleteId");
               }
                $_SESSION['customer_events'] = [['event' => 'delete_customer', 'data' => ['id' => $deleteId]]];
            } else {
                $message = "Gagal menghapus data: " . $stmt->error;
                $messageType = 'error';
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1451 || str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $message = "Gagal menghapus! Perusahaan ini tidak bisa dihapus karena datanya masih terpakai di laporan penawaran (quotation).";
                $messageType = 'error';
            } else {
                $message = "Sistem Error: " . $e->getMessage();
                $messageType = 'error';
            }
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_customer'])) {
    $id = intval($_POST['customer_id'] ?? 0);
    $customerName = trim($_POST['customer_name'] ?? '');
    $pic = trim($_POST['customer_pic'] ?? '');
    $phone = trim($_POST['customer_phone'] ?? '');
    $email = trim($_POST['customer_email'] ?? '');
    $address = trim($_POST['customer_address'] ?? '');
    $currency = trim($_POST['currency'] ?? 'USD');
    $paymentTerm = trim($_POST['payment_term'] ?? '');

    if ($customerName === '') {
        $message = "Nama customer tidak boleh kosong.";
        $messageType = 'error';
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE tbl_customer SET customer_name=?, address=?, pic=?, phone=?, email=?, currency=?, payment_term=?, updated_at=NOW() WHERE id=?");
            $stmt->bind_param('sssssssi', $customerName, $address, $pic, $phone, $email, $currency, $paymentTerm, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO tbl_customer (customer_name, address, pic, phone, email, currency, payment_term, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param('sssssss', $customerName, $address, $pic, $phone, $email, $currency, $paymentTerm);
        }

        if ($stmt && $stmt->execute()) {
            $actionType = ($id > 0) ? 'update' : 'insert';
            $savedId = ($id > 0) ? $id : $conn->insert_id;
            $message = ($id > 0) ? "Data customer berhasil diperbarui." : "Customer baru berhasil ditambahkan.";
            $messageType = 'success';

            if (function_exists('write_audit_log')) {
                $logDesc = ($id > 0) ? "Memperbarui master customer: $customerName" : "Menambahkan master customer baru: $customerName";
                write_audit_log('tbl_customer', $savedId, $actionType, null, null, $logDesc);
            }
            $_SESSION['customer_events'] = [[
                'event' => $id > 0 ? 'update_customer' : 'new_customer',
                'data' => ['id' => $savedId, 'customer_name' => $customerName]
            ]];
        } else {
            $message = "Gagal menyimpan data: " . $conn->error;
            $messageType = 'error';
        }
        if($stmt) $stmt->close();
    }
}

$editData = null;
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT * FROM tbl_customer WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param('i', $editId);
        $stmt->execute();
        $editData = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

$customers = [];
$res = $conn->query("SELECT * FROM tbl_customer ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $customers[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Master - QUOTATIONAPP</title>
    <link rel="stylesheet" href="quotation.css"> 
    <link rel="stylesheet" href="customer.css"> 
    <link rel="stylesheet" href="back-to-top.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="page-shell">
    <header class="topbar">
        <div>
            <h1>QUOTATIONAPP</h1>
        <script>
        const customerChannel = new BroadcastChannel('customer_master_update');
        const pendingCustomerEvents = <?= json_encode($customerEvents, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        pendingCustomerEvents.forEach((customerEvent) => customerChannel.postMessage(customerEvent));
        </script>

            <p>Customer Database Management</p>
        </div>
        <div class="topbar-right">
            <a class="back-link" href="dashboard.php">Dashboard</a>
            <?php if ($loggedIn): ?>
                <div class="user-pill">
                    <?= htmlspecialchars($fullname) ?>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="main-content-area">
        <section class="kop-grid">
            <article class="panel kop-card panel-customer">
                <p class="kop-label"><?= $editData ? 'Edit Data Customer' : 'Tambah Master Customer Baru' ?></p>
                <form method="post" action="customer.php">
                    <input type="hidden" name="save_customer" value="1">
                    <input type="hidden" name="customer_id" value="<?= $editData['id'] ?? 0 ?>">
                    
                    <div class="form-grid">
                        <div>
                            <label>Customer Name (Company) <span class="required-star">*</span></label>
                            <input class="quote-input" type="text" name="customer_name" value="<?= htmlspecialchars($editData['customer_name'] ?? '') ?>" required placeholder="Contoh: PT ABC Indonesia">
                        </div>
                        <div>
                            <label>Attention / PIC Name</label>
                            <input class="quote-input" type="text" name="customer_pic" value="<?= htmlspecialchars($editData['pic'] ?? '') ?>" placeholder="Contoh: Ahmad Subarjo">
                        </div>
                        <div>
                            <label>Phone Number</label>
                            <input class="quote-input" type="text" name="customer_phone" value="<?= htmlspecialchars($editData['phone'] ?? '') ?>" placeholder="Contoh: 021-xxxxxx / 0812-xxx">
                        </div>
                        <div>
                            <label>Email Address</label>
                            <input class="quote-input" type="email" name="customer_email" value="<?= htmlspecialchars($editData['email'] ?? '') ?>" placeholder="Contoh: purchasing@abc.co.id">
                        </div>
                        <div>
                            <label>Default Currency</label>
                            <select class="quote-input" name="currency">
                                <option value="$" <?= ($editData['currency'] ?? '') === '$' || ($editData['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>$ (USD)</option>
                                <option value="Rp" <?= ($editData['currency'] ?? '') === 'Rp' || ($editData['currency'] ?? '') === 'IDR' ? 'selected' : '' ?>>Rp (IDR)</option>
                            </select>
                        </div>
                        <div>
                            <label>Payment Term (Days)</label>
                            <input class="quote-input" type="text" name="payment_term" value="<?= htmlspecialchars($editData['payment_term'] ?? '') ?>" placeholder="Contoh: 30 Days setelah Invoice">
                        </div>
                        <div class="form-full">
                            <label>Company Factory Address</label>
                            <textarea class="quote-input textarea-address" name="customer_address" placeholder="Alamat lengkap pabrik/perusahaan..."><?= htmlspecialchars($editData['address'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary"><?= $editData ? 'Perbarui Customer' : 'Simpan Data Master' ?></button>
                        <?php if ($editData): ?>
                            <a href="customer.php" class="btn btn-secondary">Batal Edit</a>
                        <?php endif; ?>
                    </div>
                </form>
            </article>
        </section>

        <section class="panel calc-box panel-table">
            <div class="calc-toolbar">
                <h3>Daftar Master Customer Aktif</h3>
                <span class="customer-meta-info">Total: <strong><?= count($customers) ?></strong> Perusahaan</span>
            </div>
            
            <div class="calc-table-wrap">
                <table class="quote-table">
                    <thead>
                        <tr>
                            <th width="50">No</th>
                            <th>Nama Perusahaan</th>
                            <th>PIC / Attn</th>
                            <th>Kontak (Telp / Email)</th>
                            <th width="300">Alamat Perusahaan</th>
                            <th width="100" style="text-align:center;">Mata Uang</th>
                            <th>Term</th>
                            <th width="140" style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="8" align="center" class="text-empty-state">
                                    Belum ada master customer terdaftar. Silakan tambahkan customer terlebih dahulu 
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $index => $c): 
                                $curr = $c['currency'];
                                $badgeClass = ($curr === '$' || $curr === 'USD') ? 'badge-usd' : (($curr === 'Rp' || $curr === 'IDR') ? 'badge-idr' : '');
                                $displayCurr = ($curr === 'USD' || $curr === '$') ? '$' : 'Rp';
                            ?>
                                <tr>
                                    <td align="center"><?= $index + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($c['customer_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($c['pic'] ?: '-') ?></td>
                                    <td>
                                        <span class="text-contact-tel">📞 <?= htmlspecialchars($c['phone'] ?: '-') ?></span>
                                        <span class="text-contact-email">✉️ <?= htmlspecialchars($c['email'] ?: '-') ?></span>
                                    </td>
                                    <td class="text-table-address">
                                        <?= htmlspecialchars($c['address'] ?: '-') ?>
                                    </td>
                                    <td align="center">
                                        <span class="badge-curr <?= $badgeClass ?>"><?= $displayCurr ?></span>
                                    </td>
                                    <td class="text-table-term"><?= htmlspecialchars($c['payment_term'] ?: '-') ?></td>
                                    <td align="center">
                                        <a href="customer.php?edit_id=<?= $c['id'] ?>" class="btn-action-edit">Edit</a>
                                        <button type="button" class="btn-action-delete" style="border:none; cursor:pointer;" onclick="confirmDelete(<?= $c['id'] ?>, '<?= htmlspecialchars($c['customer_name'], ENT_QUOTES) ?>')">Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
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

    <?php if ($message): ?>
        Toast.fire({
            icon: '<?= $messageType === "success" ? "success" : "error" ?>',
            title: '<?= addslashes($message) ?>'
        });

        const url = new URL(window.location);
        url.searchParams.delete('edit_id');
        url.searchParams.delete('delete_id');
        window.history.replaceState({}, document.title, url);
    <?php endif; ?>

    function confirmDelete(id, customerName) {
        Swal.fire({
            title: 'Hapus Master Customer?',
            text: `Perusahaan "${customerName}" akan dihapus dari master data.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `customer.php?delete_id=${id}`;
            }
        });
    }
</script>
<script src="back-to-top.js"></script>
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
}, 5000);
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
}, 5000);
</script>
</body>
</html>