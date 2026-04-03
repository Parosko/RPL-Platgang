<?php
include_once __DIR__ . '/../../config/config.php';

$role = $_SESSION['role'];
?>

<div class="sidebar d-flex flex-column p-3">

    <h5 class="mb-4">Platform</h5>

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
                    Manajemen User
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/views/admin/posts.php" class="nav-link text-dark">
                    Kelola Postingan
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="<?= BASE_URL ?>/views/admin/assign.php" class="nav-link text-dark">
                    Assign Mahasiswa
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