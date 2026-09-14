<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM tbl_users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (isset($_POST['delete_account'])) {
    $sql = "DELETE FROM tbl_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        if ($stmt->execute()) {
            write_audit_log('tbl_users', $user_id, 'delete', 'id', $user['username'], "Menghapus akun pengguna secara permanen");
            
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
            header("Location: login.php?msg=account_deleted");
            exit;
        }
    }
}

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_account'])) {
    $fullname = trim($_POST['name']);
    $password = $_POST['password'] ?? '';

    if (empty($fullname)) {
        $error = 'Nama tidak boleh kosong.';
    } elseif (!empty($password) && strlen($password) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } else {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE tbl_users SET fullname = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('ssi', $fullname, $hashed, $user_id);
            } else {
                $error = 'SQL Error: ' . $conn->error;
            }
        } else {
            $sql = "UPDATE tbl_users SET fullname = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param('si', $fullname, $user_id);
            } else {
                $error = 'SQL Error: ' . $conn->error;
            }
        }
        
        if (!$error && $stmt && $stmt->execute()) {
            $success = 'Profil berhasil diperbarui!';
            $_SESSION['fullname'] = $fullname;
            write_audit_log('tbl_users', $user_id, 'update', 'fullname', $user['fullname'], "Memperbarui data profil/password akun");
            $query = "SELECT * FROM tbl_users WHERE id = ?";
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
            }
        } elseif (!$error) {
            $error = 'Gagal memperbarui profil.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Identitas - QuotationApp</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="header.css?v=1">
    <link rel="stylesheet" href="sidebar.css?v=1">
    <link rel="stylesheet" href="profile.css?v=1">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="container">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="profile-card">
                <div class="profile-header">
                    <h2>Edit Identitas Akun</h2>
                    <p>Perbarui nama tampilan dan kata sandi keamanan Anda</p>
                </div>

                <form method="post" action="profile.php">
                    <div class="avatar-wrapper">
                        <div class="avatar-circle">
                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['fullname']) ?>&background=1abc9c&color=fff&size=128&bold=true" alt="Avatar">
                        </div>
                        <div class="avatar-badge"><?= ucfirst($_SESSION['role'] ?? 'User') ?></div>
                    </div>

                    <div class="profile-form-grid">
                        <div class="profile-group">
                            <label for="name">Nama Lengkap *</label>
                            <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['fullname']) ?>" required placeholder="Masukkan nama lengkap Anda...">
                        </div>

                        <div class="profile-group">
                            <label for="password">Password Baru</label>
                            <input type="password" name="password" id="password" minlength="8" autocomplete="new-password" placeholder="Kosongkan jika tidak ingin diganti...">
                        </div>
                    </div>

                    <div class="profile-action-buttons">
                        <button class="btn-save" type="submit">Simpan Perubahan</button>
                    </div>
                </form>

                <div class="danger-zone">
                    <h3>⚠️ Danger Zone</h3>
                    <p>Tindakan ini akan menghapus akun Anda secara permanen dari QuotationApp dan Anda tidak akan bisa login kembali menggunakan akun ini.</p>
                    <form method="post" action="profile.php" id="deleteAccountForm">
                        <button type="submit" name="delete_account" class="btn-delete">❌ Hapus Akun Saya</button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <?php if ($success || $error): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: <?= json_encode($success ? 'success' : 'error') ?>,
                    title: <?= json_encode($success ?: $error) ?>,
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true
                });
            });
        </script>
    <?php endif; ?>

    <script>
        const deleteAccountForm = document.getElementById('deleteAccountForm');
        if (deleteAccountForm) {
            deleteAccountForm.addEventListener('submit', function (event) {
                event.preventDefault();

                Swal.fire({
                    title: 'Hapus akun secara permanen?',
                    text: 'Semua data akun Anda akan dihapus dan tindakan ini tidak dapat dibatalkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus akun',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteAccountForm.submit();
                    }
                });
            });
        }
    </script>

</body>
</html>