<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
$user_id  = $_SESSION['user_id'] ?? null;
$fullname = $_SESSION['fullname'] ?? '';
$role     = $_SESSION['role'] ?? '';
?>

<div class="header">
    <div class="logo-section">
        <a href="index.php" class="logo">QUOTATIONAPP</a>
        <button id="sidebar-toggle" aria-expanded="true" aria-label="Toggle sidebar">☰</button>
    </div>

    <div class="right-section">
        <?php if(!$user_id): ?>
            <button class="login-btn" id="loginBtn">Login</button>
        <?php else: ?>
            <div class="user-info">
                <div class="user-detail">
                    <div class="user-name"><?= htmlspecialchars($fullname); ?></div>
                    <div class="user-role"><?= ucfirst($role); ?></div>
                </div>
                <div class="user-avatar" id="userAvatar">
                    <?= strtoupper(substr($fullname,0,1)); ?>
                </div>
                <div class="user-menu" id="userMenu">
                    <a href="profile.php">Edit Identitas</a>
                    <a href="activity_log.php" class="activity-menu-link">
                        <span>Catatan Aktivitas</span>
                        <span id="activity-badge" class="activity-badge">0</span>
                    </a>
                    
                    <?php 
                    $checkRole = strtolower($role);

                    if ($checkRole === 'general manager' || $checkRole === 'admin'): 
                    ?>
                        <a href="add-user.php">Tambah User</a>
                        <a href="manage_users.php" style="background: #f1f5f9; font-weight: 600;">Manajemen User</a>
                    <?php endif; ?>
                    
                    <a href="javascript:void(0);" id="logoutBtn" class="logout-btn">Keluar</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="logoutOverlay" class="logout-overlay">
    <div class="logout-glass-box">
        <div class="logout-spinner"></div>
        <div class="logout-title">Log Out Berhasil</div>
        <div class="logout-subtitle">Mengamankan sesi akun Anda...</div>
    </div>
</div>

<div id="sessionConflictModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.58); z-index:99999; align-items:center; justify-content:center; padding:20px;">
    <div style="width:min(420px,100%); background:#fff; border-radius:18px; box-shadow:0 25px 60px rgba(0,0,0,0.25); padding:20px 22px; text-align:center;">
        <div style="width:58px; height:58px; border-radius:50%; background:#fef2f2; border:2px solid #fecaca; color:#b91c1c; display:flex; align-items:center; justify-content:center; font-size:30px; font-weight:700; margin:0 auto 14px;">!</div>
        <div style="font-size:1.1rem; font-weight:700; color:#111827; margin-bottom:8px;">Sesi Login Anda Dibatalkan</div>
        <div style="font-size:0.95rem; color:#374151; line-height:1.6; margin-bottom:18px;">
            Akun Anda sedang dipakai di perangkat lain.<br>
            Klik <strong>OK</strong> untuk keluar dari sesi ini.
        </div>
        <button id="sessionConflictOkBtn" style="border:none; background:#111827; color:#fff; border-radius:10px; font-weight:700; padding:12px 18px; width:100%; cursor:pointer;">OK</button>
    </div>
</div>

<div id="sessionConflictToast" style="display:none; position:fixed; right:20px; bottom:20px; z-index:9999; background:#b91c1c; color:white; padding:12px 18px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,.2); max-width:320px; font-size:14px;">
    Akun Anda sedang dipakai di perangkat lain. Silakan login ulang.
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const avatar = document.getElementById('userAvatar');
const menu = document.getElementById('userMenu');
if(avatar){
    avatar.addEventListener('click', function(e){
        e.stopPropagation();
        menu.classList.toggle('active');
    });
    document.addEventListener('click', function(){
        menu.classList.remove('active');
    });
}

const logoutBtn = document.getElementById('logoutBtn');
const logoutOverlay = document.getElementById('logoutOverlay');

const currentAppRole = <?= json_encode(strtolower(trim((string)$role))); ?>;
if (currentAppRole === 'marketing' || currentAppRole === 'manager') {
    const notificationStorageKey = 'pack_trans_activity_last_id';
    let packTransNotificationLastId = Number(sessionStorage.getItem(notificationStorageKey) || 0);
    let packTransNotificationInitialized = packTransNotificationLastId > 0;

    const showPackTransNotification = (event) => {
        if (typeof Swal === 'undefined') return;

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'info',
            title: 'Packing Transport diperbarui',
            text: event.description,
            showConfirmButton: true,
            confirmButtonText: 'Buka riwayat',
            timer: 8000,
            timerProgressBar: true,
            showCloseButton: true
        }).then((result) => {
            if (result.isConfirmed) window.location.href = 'manage_pack_trans.php';
        });
    };

    const checkPackTransNotifications = async () => {
        try {
            const response = await fetch(`get_pack_trans_notifications.php?since=${encodeURIComponent(packTransNotificationLastId)}`, { cache: 'no-store' });
            const result = await response.json();
            if (!result.success) return;

            if (!packTransNotificationInitialized) {
                packTransNotificationLastId = Number(result.latest_id || 0);
                sessionStorage.setItem(notificationStorageKey, String(packTransNotificationLastId));
                packTransNotificationInitialized = true;
                return;
            }

            (result.events || []).forEach((event) => {
                if (Number(event.id) > packTransNotificationLastId) {
                    showPackTransNotification(event);
                    packTransNotificationLastId = Number(event.id);
                }
            });
            sessionStorage.setItem(notificationStorageKey, String(Math.max(packTransNotificationLastId, Number(result.latest_id || 0))));
        } catch (error) {
            // Notification polling must not interrupt the current page.
        }
    };

    checkPackTransNotifications();
    setInterval(checkPackTransNotifications, 5000);
}

if(logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        logoutOverlay.classList.add('show');
        
        fetch('logout.php')
            .then(() => {
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 1500);
            })
            .catch(err => {
                window.location.href = 'dashboard.php';
            });
    });
}

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

if (window.location.search.includes('error=session_conflict')) {
    showSessionConflictModal();
}

const updateActivityBadge = (count) => {
    const badgeEls = document.querySelectorAll('#activity-badge, #badge-activity');
    const safeCount = Number(count || 0);
    badgeEls.forEach((badge) => {
        badge.textContent = safeCount > 99 ? '99+' : String(safeCount);
        badge.classList.toggle('visible', safeCount > 0);
    });
};

const loadActivityBadge = async () => {
    try {
        const response = await fetch('get_unread_count.php', { cache: 'no-store' });
        const data = await response.json();
        updateActivityBadge(data.activity || 0);
    } catch (error) {
        
    }
};

loadActivityBadge();
setInterval(loadActivityBadge, 5000);

const sessionConflictToast = document.getElementById('sessionConflictToast');
if (sessionConflictToast) {
    const showSessionConflict = () => {
        sessionConflictToast.style.display = 'block';
        sessionConflictToast.style.opacity = '1';
        setTimeout(() => {
            sessionConflictToast.style.display = 'none';
        }, 5000);
    };

    const checkSession = async () => {
        try {
            const response = await fetch('check_session.php', {
                method: 'GET',
                cache: 'no-store'
            });
            const result = await response.json();
            if (!result.valid) {
                showSessionConflictModal();
                showSessionConflict();
            }
        } catch (error) {
            
        }
    };

    const userMenuExists = !!document.getElementById('userAvatar');
    if (userMenuExists) {
        setInterval(checkSession, 15000);
    }
}
</script>