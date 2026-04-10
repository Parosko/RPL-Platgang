<?php
include_once __DIR__ . '/../../config/config.php';
include_once __DIR__ . '/../../core/notification_helper.php';
include_once __DIR__ . '/../../config/database.php';

$role = $_SESSION['role'];
$unread_count = getUnreadNotificationCount($conn, $_SESSION['user_id']);

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Mobile Navbar -->
<nav class="mobile-navbar">
    <div class="mobile-navbar-content">
        <div class="mobile-navbar-brand">
            <h5>Simpadu</h5>
        </div>
        <div class="mobile-navbar-actions">
            <a href="<?= BASE_URL ?>/views/notifications.php" class="notification-bell position-relative" title="Notifikasi">
                <i class="bi bi-bell fs-5"></i>
                <?php if ($unread_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary notification-badge">
                        <?php echo $unread_count; ?>
                    </span>
                <?php endif; ?>
            </a>
            <button class="hamburger-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>
</nav>

<!-- Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar d-flex flex-column p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom sidebar-header">
        <h5 class="m-0 fw-bold brand-text" style="font-weight: 700 !important;">Simpadu</h5>
        <a href="<?= BASE_URL ?>/views/notifications.php" class="notification-bell position-relative" title="Notifikasi">
            <i class="bi bi-bell fs-5"></i>
            <?php if ($unread_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary notification-badge">
                    <?php echo $unread_count; ?>
                </span>
            <?php endif; ?>
        </a>
    </div>

    <ul class="nav nav-pills flex-column mb-auto sidebar-nav">
        <li class="nav-item mb-1">
            <a href="<?= BASE_URL ?>/views/dashboard.php" class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>">
                <i class="bi bi-grid me-3"></i> Beranda
            </a>
        </li>
        <li class="nav-item mb-1">
            <a href="<?= BASE_URL ?>/views/search.php" class="nav-link <?= ($current_page == 'search.php') ? 'active' : '' ?>">
                <i class="bi bi-search me-3"></i> Pencarian
            </a>
        </li>

        <?php if ($role == 'mahasiswa'): ?>
            <li class="nav-item mb-1">
                <a href="<?= BASE_URL ?>/views/mahasiswa/history.php" class="nav-link <?= ($current_page == 'history.php') ? 'active' : '' ?>">
                    <i class="bi bi-clock-history me-3"></i> Riwayat Pendaftaran
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= BASE_URL ?>/views/mahasiswa/profile.php" class="nav-link <?= ($current_page == 'profile.php') ? 'active' : '' ?>">
                    <i class="bi bi-person me-3"></i> Profil
                </a>
            </li>
        <?php endif; ?>

        <?php if ($role == 'mitra'): ?>
            <li class="nav-item mb-1">
                <a href="<?= BASE_URL ?>/views/mitra/my_posts.php" class="nav-link <?= ($current_page == 'my_posts.php') ? 'active' : '' ?>">
                    <i class="bi bi-journal-text me-3"></i> Postingan Saya
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= BASE_URL ?>/views/mitra/create_post.php" class="nav-link <?= ($current_page == 'create_post.php') ? 'active' : '' ?>">
                    <i class="bi bi-plus-square me-3"></i> Tambah Postingan
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= BASE_URL ?>/views/mitra/profile.php" class="nav-link <?= (strpos($current_page, 'profile.php') !== false && $role == 'mitra') ? 'active' : '' ?>">
                    <i class="bi bi-person me-3"></i> Profil
                </a>
            </li>
        <?php endif; ?>

        <?php if ($role == 'dpa'): ?>
            <li class="nav-item mb-1">
                <a href="<?= BASE_URL ?>/views/dpa/mahasiswa.php" class="nav-link <?= ($current_page == 'mahasiswa.php') ? 'active' : '' ?>">
                    <i class="bi bi-people me-3"></i> Mahasiswa Bimbingan
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= BASE_URL ?>/views/dpa/profile.php" class="nav-link <?= (strpos($current_page, 'profile.php') !== false && $role == 'dpa') ? 'active' : '' ?>">
                    <i class="bi bi-person me-3"></i> Profil
                </a>
            </li>
        <?php endif; ?>

        <?php if ($role == 'admin'): ?>
            <li class="nav-item mb-1">
                <a href="<?= BASE_URL ?>/views/admin/users.php" class="nav-link <?= ($current_page == 'users.php') ? 'active' : '' ?>">
                    <i class="bi bi-person-badge me-3"></i> Kelola Pengguna
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= BASE_URL ?>/views/admin/posts.php" class="nav-link <?= ($current_page == 'posts.php') ? 'active' : '' ?>">
                    <i class="bi bi-file-earmark-text me-3"></i> Kelola Postingan
                </a>
            </li>
            <li class="nav-item mb-1">
                <a href="<?= BASE_URL ?>/views/admin/assign.php" class="nav-link <?= ($current_page == 'assign.php') ? 'active' : '' ?>">
                    <i class="bi bi-person-lines-fill me-3"></i> Kelola DPA
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <div class="mt-4 pt-3 border-top">
        <a href="<?= BASE_URL ?>/controllers/auth/logout.php" class="btn btn-logout w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>

<script>
// Hamburger Menu Toggle
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.querySelector('.sidebar');
const sidebarOverlay = document.getElementById('sidebarOverlay');

if (sidebarToggle && sidebar && sidebarOverlay) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
        
        // Change hamburger icon
        const icon = sidebarToggle.querySelector('i');
        if (sidebar.classList.contains('show')) {
            icon.classList.remove('bi-list');
            icon.classList.add('bi-x');
        } else {
            icon.classList.remove('bi-x');
            icon.classList.add('bi-list');
        }
    });

    // Close sidebar when clicking overlay
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
        
        // Reset hamburger icon
        const icon = sidebarToggle.querySelector('i');
        icon.classList.remove('bi-x');
        icon.classList.add('bi-list');
    });

    // Close sidebar when pressing Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            
            // Reset hamburger icon
            const icon = sidebarToggle.querySelector('i');
            icon.classList.remove('bi-x');
            icon.classList.add('bi-list');
        }
    });

    // Close sidebar when window is resized above mobile breakpoint
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            
            // Reset hamburger icon
            const icon = sidebarToggle.querySelector('i');
            icon.classList.remove('bi-x');
            icon.classList.add('bi-list');
        }
    });
}
</script>