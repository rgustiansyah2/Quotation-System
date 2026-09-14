<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'config/database.php';

$currentRole = strtolower($_SESSION['role'] ?? '');
$currentUserId = intval($_SESSION['user_id'] ?? 0);

if ($currentRole !== 'admin' && $currentRole !== 'general manager') {
    header("Location: index.php");
    exit();
}

$message = '';
$messageType = '';

function isTargetAdmin($conn, $targetId) {
    $stmt = $conn->prepare("SELECT role FROM tbl_users WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $targetId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            return strtolower($row['role']) === 'admin';
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $targetUserId = intval($_POST['user_id']);
    $newRole = trim($_POST['role']);

    if ($currentRole !== 'admin' && isTargetAdmin($conn, $targetUserId)) {
        $message = "Akses ditolak! Anda tidak memiliki izin untuk mengubah akun Admin.";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare("UPDATE tbl_users SET role = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $newRole, $targetUserId);
            if ($stmt->execute()) {
                $message = "Hak akses (role) user berhasil diperbarui!";
                $messageType = "success";

                if (function_exists('write_audit_log')) {
                    write_audit_log('tbl_users', $targetUserId, 'update', 'role', null, "Mengubah role user ID: $targetUserId menjadi '$newRole'");
                }
            } else {
                $message = "Gagal memperbarui role: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $targetUserId = intval($_POST['user_id']);
    $newPassword = $_POST['new_password'];

    if ($currentRole !== 'admin' && isTargetAdmin($conn, $targetUserId)) {
        $message = "Akses ditolak! Anda tidak memiliki izin untuk mereset password akun Admin.";
        $messageType = "error";
    } elseif (strlen($newPassword) < 8) {
        $message = "Password baru kependekan! Minimal 8 karakter.";
        $messageType = "error";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $stmt = $conn->prepare("UPDATE tbl_users SET password = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $hashedPassword, $targetUserId);
            if ($stmt->execute()) {
                $message = "Password user berhasil diganti!";
                $messageType = "success";

                if (function_exists('write_audit_log')) {
                    write_audit_log('tbl_users', $targetUserId, 'update', 'password', null, "Mengganti password untuk user ID: $targetUserId");
                }
            } else {
                $message = "Gagal mengganti password: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $targetUserId = intval($_POST['user_id']);

    if ($targetUserId === $currentUserId) {
        $message = "Gagal! Anda tidak bisa menghapus akun Anda sendiri yang sedang digunakan.";
        $messageType = "error";
    } elseif ($currentRole !== 'admin' && isTargetAdmin($conn, $targetUserId)) {
        $message = "Akses ditolak! Anda tidak memiliki izin untuk menghapus akun Admin.";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare("DELETE FROM tbl_users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $targetUserId);
            if ($stmt->execute()) {
                $message = "Akun pengguna berhasil dihapus permanen!";
                $messageType = "success";

                if (function_exists('write_audit_log')) {
                    write_audit_log('tbl_users', $targetUserId, 'delete', null, null, "Menghapus akun user ID: $targetUserId");
                }
            } else {
                $message = "Gagal menghapus akun: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        }
    }
}

$userList = [];
$res = $conn->query("SELECT id, fullname, username, role, status, created_at FROM tbl_users ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $userList[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User - Quotation App</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="manage.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <h3>Manajemen Otoritas & Akun User</h3>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>Role Saat Ini</th>
                    <th>Atur Akses (Role)</th>
                    <th>Tindakan Akun</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userList as $index => $user): ?>
                    <?php 
                    $isUserAdmin = (strtolower($user['role']) === 'admin');
                    $isProtected = ($isUserAdmin && $currentRole !== 'admin');
                    ?>
                    <tr>
                        <td><?= $index + 1; ?></td>
                        <td><strong><?= htmlspecialchars($user['fullname']); ?></strong></td>
                        <td><code><?= htmlspecialchars($user['username']); ?></code></td>
                        <td>
                            <span class="badge badge-<?= str_replace([' ', '.'], '', strtolower($user['role'])); ?>">
                                <?= htmlspecialchars($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($isProtected): ?>
                                <span style="font-size: 0.85rem; color: #94a3b8; font-style: italic;">Locked (Admin Only)</span>
                            <?php else: ?>
                                <form id="roleForm_<?= $user['id']; ?>" method="POST" class="inline-form">
                                    <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                    <input type="hidden" name="update_role" value="1">
                                    <select name="role" class="role-select" onchange="confirmRoleChange(<?= $user['id']; ?>, '<?= htmlspecialchars($user['fullname'], ENT_QUOTES); ?>', this)">
                                        <option value="admin" <?= strtolower($user['role']) === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="manager" <?= strtolower($user['role']) === 'manager' ? 'selected' : ''; ?>>Manager</option>
                                        <option value="marketing" <?= strtolower($user['role']) === 'marketing' ? 'selected' : ''; ?>>Marketing</option>
                                        <option value="general manager" <?= strtolower($user['role']) === 'general manager' ? 'selected' : ''; ?>>General Manager</option>
                                        <option value="purchasing" <?= strtolower($user['role']) === 'purchasing' ? 'selected' : ''; ?>>Purchasing</option>
                                        <option value="general affair" <?= strtolower($user['role']) === 'general affair' ? 'selected' : ''; ?>>General Affair</option>
                                    </select>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-cell">
                                <?php if ($isProtected): ?>
                                    <span style="font-size: 0.825rem; color: #ef4444; background: #fee2e2; padding: 4px 8px; border-radius: 4px; font-weight: 500;">
                                        Protected Account
                                    </span>
                                <?php else: ?>
                                    <button type="button" class="btn-action btn-pwd" onclick="promptResetPassword(<?= $user['id']; ?>, '<?= htmlspecialchars($user['fullname'], ENT_QUOTES); ?>')">
                                         Ganti Password
                                    </button>
                                    
                                    <button type="button" class="btn-action btn-delete" onclick="confirmDeleteUser(<?= $user['id']; ?>, '<?= htmlspecialchars($user['fullname'], ENT_QUOTES); ?>')">
                                         Hapus Akun
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<form id="hiddenResetForm" method="POST" style="display:none;">
    <input type="hidden" name="user_id" id="resetUserId">
    <input type="hidden" name="new_password" id="resetNewPassword">
    <input type="hidden" name="reset_password" value="1">
</form>

<form id="hiddenDeleteForm" method="POST" style="display:none;">
    <input type="hidden" name="user_id" id="deleteUserId">
    <input type="hidden" name="delete_user" value="1">
</form>

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
    <?php endif; ?>

    function promptResetPassword(userId, fullname) {
        Swal.fire({
            title: 'Ganti Password User',
            html: `
                <p style="font-size:14px; margin-bottom:12px; color:#4b5563;">
                    Masukkan password baru untuk user: <strong>${fullname}</strong>
                </p>
                <input type="password" id="swal-input-pwd" class="swal2-input" placeholder="Password Baru (min. 8 karakter)" style="margin-top:0;">
            `,
            icon: 'key',
            showCancelButton: true,
            confirmButtonText: 'Simpan Password',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#d1100adc',
            focusConfirm: false,
            preConfirm: () => {
                const pwd = document.getElementById('swal-input-pwd').value.trim();
                if (!pwd) {
                    Swal.showValidationMessage('Password tidak boleh kosong!');
                    return false;
                }
                if (pwd.length < 4) {
                    Swal.showValidationMessage('Password minimal 4 karakter!');
                    return false;
                }
                return pwd;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('resetUserId').value = userId;
                document.getElementById('resetNewPassword').value = result.value;
                document.getElementById('hiddenResetForm').submit();
            }
        });
    }

    function confirmDeleteUser(userId, fullname) {
        Swal.fire({
            title: 'Hapus Akun User?',
            text: `Akun "${fullname}" akan dihapus permanen dari sistem. Tindakan ini tidak dapat dibatalkan!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus Akun!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteUserId').value = userId;
                document.getElementById('hiddenDeleteForm').submit();
            }
        });
    }

    function confirmRoleChange(userId, fullname, selectElem) {
        const selectedRoleText = selectElem.options[selectElem.selectedIndex].text;
        
        Swal.fire({
            title: 'Ubah Role Akses?',
            text: `Apakah Anda yakin ingin mengubah akses "${fullname}" menjadi "${selectedRoleText}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Ubah Role',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`roleForm_${userId}`).submit();
            } else {
                location.reload();
            }
        });
    }
</script>

</body>
</html>