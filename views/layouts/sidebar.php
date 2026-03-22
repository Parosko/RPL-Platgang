<?php
$role = $_SESSION['role'];
?>


<div class="sidebar d-flex flex-column p-3">

    <h5 class="mb-4">Platform</h5>

    <ul class="nav nav-pills flex-column mb-auto">

        <!-- GLOBAL -->
        <li class="nav-item mb-2">
            <a href="../dashboard.php" class="nav-link text-dark">
                Beranda
            </a>
        </li>

        <!-- MAHASISWA -->
        <?php if ($role == 'mahasiswa'): ?>
            <li class="nav-item mb-2">
                <a href="../mahasiswa/riwayat.php" class="nav-link text-dark">
                    Riwayat Pendaftaran
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="../mahasiswa/profile.php" class="nav-link text-dark">
                    Profil
                </a>
            </li>
        <?php endif; ?>

        <!-- MITRA -->
        <?php if ($role == 'mitra'): ?>
            <li class="nav-item mb-2">
                <a href="../mitra/postingan.php" class="nav-link text-dark">
                    Postingan Saya
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="../mitra/profile.php" class="nav-link text-dark">
                    Profil
                </a>
            </li>
        <?php endif; ?>

        <!-- DPA -->
        <?php if ($role == 'dpa'): ?>
            <li class="nav-item mb-2">
                <a href="../dpa/mahasiswa.php" class="nav-link text-dark">
                    Mahasiswa Bimbingan
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="../dpa/profile.php" class="nav-link text-dark">
                    Profil
                </a>
            </li>
        <?php endif; ?>

        <!-- ADMIN -->
        <?php if ($role == 'admin'): ?>
            <li class="nav-item mb-2">
                <a href="../admin/users.php" class="nav-link text-dark">
                    Manajemen User
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="../admin/posts.php" class="nav-link text-dark">
                    Manajemen Postingan
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="../admin/assign.php" class="nav-link text-dark">
                    Assign Mahasiswa
                </a>
            </li>
        <?php endif; ?>

    </ul>

    <hr>

    <div>
        <a href="../controllers/auth/logout.php" class="btn btn-outline-dark w-100">
            Logout
        </a>
    </div>

</div>