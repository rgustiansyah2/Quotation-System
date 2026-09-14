<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'config/database.php';

$currentRole = strtolower($_SESSION['role'] ?? '');
if ($currentRole !== 'general manager' && $currentRole !== 'admin') {
    header("Location: index.php");
    exit();
}

$alertType = "";
$alertTitle = "";
$alertMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? 'user';

    if (!empty($fullname) && !empty($username) && !empty($password)) {
        
        // 1. Cek dulu apakah username sudah terpakai di database
        $checkStmt = $conn->prepare("SELECT id FROM tbl_users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $alertType = "error";
            $alertTitle = "Oops... Username Terpakai!";
            $alertMessage = "Username " . htmlspecialchars($username) . " sudah terdaftar di sistem.";
            $checkStmt->close();
        } else {
            $checkStmt->close();

            // 2. Hash password menggunakan bcrypt
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $status = 'active';

            // 3. Eksekusi query INSERT ke dalam tbl_users
            $stmt = $conn->prepare("INSERT INTO tbl_users (fullname, username, password, role, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            
            if ($stmt) {
                $stmt->bind_param("sssss", $fullname, $username, $hashed_password, $role, $status);
                
                if ($stmt->execute()) {
                    $alertType = "success";
                    $alertTitle = "Berhasil!";
                    $alertMessage = "User " . htmlspecialchars($fullname) . " telah berhasil ditambahkan.";
                } else {
                    $alertType = "error";
                    $alertTitle = "Gagal Menyimpan";
                    $alertMessage = "Terjadi kesalahan pada database: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $alertType = "error";
                $alertTitle = "Database Error";
                $alertMessage = "Gagal mempersiapkan query: " . $conn->error;
            }
        }
    } else {
        $alertType = "error";
        $alertTitle = "Data Tidak Lengkap";
        $alertMessage = "Semua kolom input wajib diisi!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User - QuotationApp</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="header.css?v=1">
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="adduser.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="container">
        <div class="main-content">

            <div class="form-container">
                <div class="form-title">Tambah Pengguna Baru</div>
                
                <form action="add-user.php" method="POST">
                    <div class="form-group">
                        <label for="fullname">Nama Lengkap</label>
                        <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Masukkan nama lengkap..." required>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username untuk login..." required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password..." required>
                    </div>

                    <div class="form-group">
                        <label for="role">Hak Akses (Role)</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="marketing">Marketing</option>
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                            <option value="general manager">G. M.</option>
                            <option value="purchasing">PCH</option>
                            <option value="general affair">G. A.</option>                        
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">Simpan Pengguna</button>
                </form>
            </div>

        </div>
    </div>

    <?php if (!empty($alertType)): ?>
    <script>
        Swal.fire({
            title: '<?= $alertTitle; ?>',
            text: '<?= $alertMessage; ?>',
            icon: '<?= $alertType; ?>',
            confirmButtonColor: '#1abc9c', 
            timer: 3000, 
            timerProgressBar: true
        });
    </script>
    <?php endif; ?>
<?php
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
</body>
</html>