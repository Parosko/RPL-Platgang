<?php
include_once __DIR__ . '/../../config/config.php';
include_once __DIR__ . '/../../core/notification_helper.php';
include_once __DIR__ . '/../../config/database.php';

$role = $_SESSION['role'];
$unread_count = getUnreadNotificationCount($conn, $_SESSION['user_id']);
?>

<div class="sidebar d-flex flex-column p-3">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="margin: 0;">Platform</h5>
        <a href="<?= BASE_URL ?>/views/notifications.php" class="notification-bell" title="Notifikasi">
            🔔
            <?php if ($unread_count > 0): ?>
                <span class="notification-badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </a>
    </div>

    <ul class="nav nav-pills flex-column mb-auto">

        <!-- GLOBAL -->
        <li class="nav-item mb-2">
            <a href="<?= BASE_URL ?>/views/dashboard.php" class="nav-link text-dark">
                Beranda
            </a>
        </li>

        <!-- MAHASISWA -->
        <?php if ($role == 'mahasiswa'): ?>
            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/views/mahasiswa/history.php" class="nav-link text-dark">
                    Riwayat Pendaftaran
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/views/mahasiswa/profile.php" class="nav-link text-dark">
                    Profil
                </a>
            </li>
        <?php endif; ?>

        <!-- MITRA -->
        <?php if ($role == 'mitra'): ?>
            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/views/mitra/my_posts.php" class="nav-link text-dark">
                    Postingan Saya
                </a>
            </li>

            <!-- 🔥 TOMBOL BARU -->
            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/views/mitra/create_post.php" class="nav-link text-dark">
                    Tambah Postingan
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/views/mitra/profile.php" class="nav-link text-dark">
                    Profil
                </a>
            </li>
        <?php endif; ?>

        <!-- DPA -->
        <?php if ($role == 'dpa'): ?>
            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/views/dpa/mahasiswa.php" class="nav-link text-dark">
                    Mahasiswa Bimbingan
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/views/dpa/profile.php" class="nav-link text-dark">
                    Profil
                </a>
            </li>
        <?php endif; ?>

        <!-- ADMIN -->
        <?php if ($role == 'admin'): ?>
            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/views/admin/users.php" class="nav-link text-dark">
                    Kelola Pengguna
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/views/admin/posts.php" class="nav-link text-dark">
                    Kelola Postingan
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/views/admin/assign.php" class="nav-link text-dark">
                    Kelola DPA
                </a>
            </li>
        <?php endif; ?>

    </ul>

    <hr>

    <div>
        <a href="<?= BASE_URL ?>/controllers/auth/logout.php" class="btn btn-outline-dark w-100">
            Logout
        </a>
    </div>

</div>