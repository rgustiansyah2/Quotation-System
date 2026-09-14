<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if(!isset($_SESSION['user_id'])){
    return;
}

global $conn;

if (!isset($conn) || !$conn) {
    if (file_exists(__DIR__ . '/config/database.php')) {
        require_once __DIR__ . '/config/database.php';
    } elseif (file_exists(__DIR__ . '/koneksi.php')) {
        require_once __DIR__ . '/koneksi.php';
    }
}

$userRole = strtolower($_SESSION['role'] ?? 'user'); 

$unreadQuotation = 0;
$unreadPackTrans = 0;

if ($userRole === 'general manager' || $userRole === 'admin' || $userRole === 'manager') {
    if (isset($conn) && $conn instanceof mysqli) {
        $qQuo = mysqli_query($conn, "SELECT COUNT(DISTINCT quotation_no) AS total FROM tbl_quotation WHERE is_read = 0");
        if ($qQuo) {
            $unreadQuotation = (int)(mysqli_fetch_assoc($qQuo)['total'] ?? 0);
        }

        $qPT = mysqli_query($conn, "SELECT COUNT(*) AS total FROM tbl_pack_trans_project WHERE is_read = 0");
        if ($qPT) {
            $unreadPackTrans = (int)(mysqli_fetch_assoc($qPT)['total'] ?? 0);
        }
    }
}
?>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="back-to-top.css">

<div class="sidebar">
    <h2>MENU</h2>
    
    <?php if ($userRole === 'admin'): ?>
        <a href="index.php">Dashboard</a>
        <a href="quotation.php">Quotation</a>
        <a href="pack_trans.php">Packing Transport</a>
        <a href="packing_cost.php">Packing Cost</a>
        <a href="rate.php">Rate</a>
        <a href="packing_standard.php">Packing Standard</a>
        <a href="customer.php">Customer</a>
        <a href="master_process_config.php">Master Purging, Dandori, MMC</a>
        <a href="mmp.php">Monitoring Material Price</a>
        <a href="cost_transport.php">Cost Transport</a>
        <a href="kelola_mmp.php">Report MMP</a>
        <a href="report.php" class="nav-item">
            <span>Report Quotation</span>
            <span id="badge-quotation" class="nav-badge" style="<?= $unreadQuotation > 0 ? '' : 'display:none;' ?>">
                <?= $unreadQuotation > 99 ? '99+' : $unreadQuotation ?>
            </span>
        </a>

        <a href="manage_pack_trans.php" class="nav-item">
            <span>Report Packing Transport</span>
            <span id="badge-packtrans" class="nav-badge" style="<?= $unreadPackTrans > 0 ? '' : 'display:none;' ?>">
                <?= $unreadPackTrans > 99 ? '99+' : $unreadPackTrans ?>
            </span>
        </a>

    <?php elseif ($userRole === 'purchasing'): ?>
        <a href="index.php">Dashboard</a>
        <a href="mmp.php">Monitoring Material Price</a>
        <a href="packing_cost.php">Packing Cost</a>
        <a href="kelola_mmp.php">Report MMP</a>

    <?php elseif ($userRole === 'general affair'): ?>
        <a href="index.php">Dashboard</a>
        <a href="cost_transport.php">Cost Transport</a>

    <?php else: ?>
        <a href="index.php">Dashboard</a>
        <a href="quotation.php">Quotation</a>
        <a href="pack_trans.php">Packing Transport</a>
        <?php if ($userRole === 'general manager'): ?>
            <a href="rate.php">Rate</a>
        <?php endif; ?>
        <a href="packing_standard.php">Packing Standard</a>
        <a href="customer.php">Customer</a>
        <a href="master_process_config.php">Master Purging, Dandori, MMC</a>
        <a href="kelola_mmp.php">Report MMP</a>
        <a href="report.php" class="nav-item">
            <span>Report Quotation</span>
            <?php if ($userRole === 'general manager' || $userRole === 'manager'): ?>
                <span id="badge-quotation" class="nav-badge" style="<?= $unreadQuotation > 0 ? '' : 'display:none;' ?>">
                    <?= $unreadQuotation > 99 ? '99+' : $unreadQuotation ?>
                </span>
            <?php endif; ?>
        </a>

        <a href="manage_pack_trans.php" class="nav-item">
            <span>Report Packing Transport</span>
            <?php if ($userRole === 'general manager' || $userRole === 'manager'): ?>
                <span id="badge-packtrans" class="nav-badge" style="<?= $unreadPackTrans > 0 ? '' : 'display:none;' ?>">
                    <?= $unreadPackTrans > 99 ? '99+' : $unreadPackTrans ?>
                </span>
            <?php endif; ?>
        </a>
    <?php endif; ?>

    <div class="sidebar-footer">
        <div>&copy; 2026 - <?= date('Y') ?> Citra Plastik Makmur || Powered by <b>Gemini</b> & <b>Github</b> || Developed by Ahmad Rizqi G (IT DEPT)</div>
    </div>
</div>

<script>
    (function(){
        const btn = document.getElementById('sidebar-toggle');
        const sidebar = document.querySelector('.sidebar');
        if(!btn || !sidebar) return;

        function updateLayoutState(isClosed) {
            if(isClosed) {
                sidebar.classList.add('closed');
                document.body.classList.add('sidebar-is-closed');
                btn.setAttribute('aria-expanded','false');
            } else {
                sidebar.classList.remove('closed');
                document.body.classList.remove('sidebar-is-closed');
                btn.setAttribute('aria-expanded','true');
            }
        }

        const initialClosed = localStorage.getItem('sidebarClosed') === '1';
        updateLayoutState(initialClosed);

        btn.addEventListener('click', function(e){
            e.stopPropagation();
            const currentClosed = sidebar.classList.contains('closed');
            const nextState = !currentClosed;
            
            updateLayoutState(nextState);
            localStorage.setItem('sidebarClosed', nextState ? '1' : '0');
        });

        document.addEventListener('scroll', function(e) {
            if (e.target === sidebar) return; 

            if (!sidebar.classList.contains('closed')) {
                updateLayoutState(true);
                localStorage.setItem('sidebarClosed', '1');
            }
        }, true);
    })();

    function checkNotifications() {
        fetch('get_unread_count.php')
            .then(response => response.json())
            .then(data => {
                let qBadge = document.querySelector('#badge-quotation'); 
                if (qBadge) {
                    qBadge.textContent = data.quotation > 99 ? '99+' : data.quotation;
                    qBadge.style.display = data.quotation > 0 ? 'inline-block' : 'none';
                }

                let ptBadge = document.querySelector('#badge-packtrans');
                if (ptBadge) {
                    ptBadge.textContent = data.pack_trans > 99 ? '99+' : data.pack_trans;
                    ptBadge.style.display = data.pack_trans > 0 ? 'inline-block' : 'none';
                }

                let activityBadge = document.querySelector('#badge-activity');
                if (activityBadge) {
                    const count = Number(data.activity || 0);
                    activityBadge.textContent = count > 99 ? '99+' : count;
                    activityBadge.style.display = count > 0 ? 'inline-block' : 'none';
                }
            })
            .catch(err => console.error('Error fetching notifications:', err));
    }

    setInterval(checkNotifications, 3000);
</script>
<script src="back-to-top.js"></script>