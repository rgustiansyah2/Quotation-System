<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = trim($_POST['role'] ?? 'engineering');

    if ($fullname === '' || $username === '' || $password === '' || $confirmPassword === '') {
        $message = 'Semua field wajib diisi.';
        $messageType = 'error';
    } elseif ($password !== $confirmPassword) {
        $message = 'Konfirmasi password tidak cocok.';
        $messageType = 'error';
    } else {
        $check = $conn->prepare('SELECT id FROM tbl_users WHERE username = ? LIMIT 1');
        $check->bind_param('s', $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = 'Username sudah digunakan, silakan pilih yang lain.';
            $messageType = 'error';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO tbl_users (fullname, username, password, role, status) VALUES (?, ?, ?, ?, "active")');
            $stmt->bind_param('ssss', $fullname, $username, $hashed, $role);

            if ($stmt->execute()) {
                $message = 'Akun berhasil dibuat. Silakan login.';
                $messageType = 'success';
            } else {
                $message = 'Gagal membuat akun. Silakan coba lagi.';
                $messageType = 'error';
            }
            $stmt->close();
        }
        $check->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun</title>
    <style>
        body { margin:0; font-family: Arial, sans-serif; background: linear-gradient(135deg,#0f172a,#1e293b); color:#111827; }
        .page { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
        .card { width:100%; max-width:420px; background:#fff; border-radius:18px; padding:24px; box-shadow:0 12px 30px rgba(15,23,42,.25); }
        h1 { margin-top:0; font-size:1.4rem; color:#1f2937; text-align:center; margin-bottom:24px;   }
        p { color:#475569; font-size:.95rem; }
        .field { display:flex; flex-direction:column; gap:6px; margin-bottom:14px; }
        label { font-size:.92rem; color:#334155; font-weight:600; }
        input, select { padding:12px; border:1px solid #cbd5e1; border-radius:10px; font-size:.95rem; }
        button { width:100%; padding:12px 14px; border:none; border-radius:10px; background:#1abc9c; color:#fff; font-weight:700; cursor:pointer; }
        button:hover { background:#16a085; }
        .msg { padding:10px 12px; border-radius:10px; font-size:.92rem; margin-bottom:12px; }
        .msg.error { background:#fee2e2; color:#991b1b; }
        .msg.success { background:#dcfce7; color:#166534; }
        .link { text-align:center; margin-top:10px; font-size:.92rem; }
        .link a { color:#2563eb; text-decoration:none; }
    </style>
</head>
<body>
<div class="page">
    <div class="card">
        <h1>Buat Akun Login</h1>
        <?php if ($message !== ''): ?>
            <div class="msg <?= $messageType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" action="register.php">
            <div class="field">
                <label for="fullname">Nama Lengkap</label>
                <input id="fullname" name="fullname" type="text" required>
            </div>
            <div class="field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>
            <div class="field">
                <label for="confirm_password">Konfirmasi Password</label>
                <input id="confirm_password" name="confirm_password" type="password" required>
            </div>
            <div class="field">
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="engineering">Engineering</option>
                    <option value="sales">Sales</option>
                    <option value="management">Management</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit">Daftar Akun</button>
        </form>
        <div class="link"><a href="dashboard.html">← Kembali ke Dashboard</a></div>
    </div>
</div>
</body>
</html>
