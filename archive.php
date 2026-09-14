<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

include 'config/database.php';

$loggedIn = !empty($_SESSION['user_id']);
$fullname = $_SESSION['fullname'] ?? 'Guest';
$role     = $_SESSION['role'] ?? 'User';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_cost'])) {
    $id = intval($_POST['cost_id'] ?? 0);
    if ($id > 0) {
        $conn->query("UPDATE tbl_transport_cost SET is_archived = 0, updated_at = NOW() WHERE id = $id");
    }
    header("Location: archive.php");
    exit();
}

$selectedYear = isset($_GET['year']) && preg_match('/^\d{4}$/', $_GET['year']) ? $_GET['year'] : '';

$yearOptions = [];
$yearsResult = $conn->query("
    SELECT DISTINCT YEAR(COALESCE(created_at, updated_at, NOW())) AS archive_year 
    FROM tbl_transport_cost 
    WHERE is_archived = 1 
    ORDER BY archive_year DESC
");
if ($yearsResult) {
    while ($row = $yearsResult->fetch_assoc()) {
        if (!empty($row['archive_year'])) {
            $yearOptions[] = $row['archive_year'];
        }
    }
}

$whereYear = "";
if (!empty($selectedYear)) {
    $selectedYearClean = $conn->real_escape_string($selectedYear);
    $whereYear = " AND YEAR(COALESCE(created_at, updated_at, NOW())) = '$selectedYearClean' ";
}

$sql = "
    SELECT id, customer_name, keterangan, biaya_sewa, 
           COALESCE(created_at, updated_at, NOW()) AS created_at
    FROM tbl_transport_cost
    WHERE is_archived = 1 $whereYear
    ORDER BY id DESC
";

$archiveRows = [];
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $archiveRows[] = $row;
    }
}

$groupedArchives = [];
foreach ($archiveRows as $row) {
    $year = date('Y', strtotime($row['created_at']));
    $groupedArchives[$year][] = $row;
}
krsort($groupedArchives);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arsip Cost Transport - QuotationApp</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="quotation.css">
    <link rel="stylesheet" href="sidebar.css?v=1">
    <link rel="stylesheet" href="header.css?v=1">
    <link rel="stylesheet" href="archive.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="page-shell">
        <header class="topbar">
            <div>
                <h3>QUOTATIONAPP</h3>
                <p>Arsip Cost Transport</p>
            </div>
            <div class="topbar-right">
                <?php if ($loggedIn): ?>
                    <div class="user-pill">
                        <?= htmlspecialchars($fullname) ?> · <?= htmlspecialchars($role) ?>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <main class="quotation-card">
            <section class="panel calc-box">
                <div class="calc-toolbar calc-toolbar-flex">
                    <div>
                        <h3>Arsip Data Cost Transport</h3>
                    </div>
                    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                        <a href="cost_transport.php" class="btn btn-primary" style="display:inline-block; text-decoration:none; padding:8px 14px; border-radius:10px; background:#0f172a; color:#fff; font-weight:700;">← Kembali ke Cost Transport</a>
                        <span class="customer-meta-info">Total: <strong><?= count($archiveRows) ?></strong> Data</span>
                    </div>
                </div>

                <div class="archive-toolbar">
                    <div class="archive-filter-wrap">
                        <label for="archiveYearSelector">Tahun Arsip</label>
                        <select id="archiveYearSelector" class="archive-year-select" onchange="location.href='archive.php?year=' + this.value">
                            <option value="">Semua Tahun</option>
                            <?php foreach ($yearOptions as $year): ?>
                                <option value="<?= htmlspecialchars($year) ?>" <?= $selectedYear === (string)$year ? 'selected' : '' ?>><?= htmlspecialchars($year) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if (empty($archiveRows)): ?>
                    <div class="empty-archive-state" style="padding: 40px; text-align: center; color: #64748b;">Belum ada data cost transport untuk arsip ini.</div>
                <?php else: ?>
                    <?php foreach ($groupedArchives as $year => $items): ?>
                        <div class="archive-year-panel" style="margin-bottom: 24px;">
                            <div class="archive-year-header" style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                                <h4 style="margin:0; font-size:1.1rem;">Arsip Tahun <?= htmlspecialchars($year) ?></h4>
                                <span><?= count($items) ?> Data</span>
                            </div>

                            <div class="calc-table-wrap">
                                <table class="quote-table archive-table">
                                    <thead>
                                        <tr>
                                            <th width="50">No</th>
                                            <th>Customer</th>
                                            <th>Keterangan / Rute</th>
                                            <th>Tanggal Input</th>
                                            <th style="text-align:right;">Biaya Sewa</th>
                                            <th width="120" style="text-align:center;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $idx => $doc): ?>
                                            <tr>
                                                <td align="center"><?= $idx + 1 ?></td>
                                                <td><strong><?= htmlspecialchars($doc['customer_name'] ?? '-') ?></strong></td>
                                                <td><?= htmlspecialchars($doc['keterangan'] ?? '-') ?></td>
                                                <td><?= date('d-m-Y', strtotime($doc['created_at'])) ?></td>
                                                <td align="right" class="row-total-val">Rp <?= number_format((float)($doc['biaya_sewa'] ?? 0), 2, ',', '.') ?></td>
                                                <td align="center">
                                                    <form method="POST" style="margin:0;">
                                                        <input type="hidden" name="restore_cost" value="1">
                                                        <input type="hidden" name="cost_id" value="<?= (int)$doc['id'] ?>">
                                                        <button type="submit" style="padding:7px 10px; border:none; border-radius:8px; background:#e0f2fe; color:#0369a1; font-weight:700; cursor:pointer;">Pulihkan</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
<?php
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>