<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../core/profile_check.php';

onlyMahasiswa();
redirectIfProfileIncomplete($conn, __FILE__);

$user_id = $_SESSION['user_id'];

$query = "SELECT m.*, u.email, d.nama as dpa_nama, d.nip as dpa_nip, d.fakultas as dpa_fakultas, d.prodi as dpa_prodi, d.kontak as dpa_kontak 
           FROM mahasiswa m 
           JOIN users u ON m.user_id = u.id 
           LEFT JOIN dpa d ON m.dpa_id = d.id 
           WHERE m.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: ../../views/dashboard.php');
    exit;
}

$profile = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Mahasiswa | Sistem Peluang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/mahasiswa.css">
</head>

<body>

<div class="d-flex">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Profil Saya</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-person-circle me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Kelola informasi pribadi dan data akademik Anda.</span>
                </div>
            </div>
            <a href="edit_profile.php" class="btn btn-navy px-4">
                <i class="bi bi-pencil-square me-2"></i>Edit Profil
            </a>
        </div>

        <div class="mhs-profile-card">
            
            <div class="mhs-profile-header d-flex flex-wrap align-items-center gap-4">
                <div class="mhs-avatar">
                    <?php 
                    $nama = htmlspecialchars($profile['nama'] ?? 'M');
                    echo strtoupper(substr($nama, 0, 1)); 
                    ?>
                </div>
                <div class="mhs-header-info">
                    <span class="mhs-badge mb-2">Mahasiswa</span>
                    <h2 class="mhs-name mb-1"><?php echo htmlspecialchars($profile['nama'] ?? 'Belum diisi'); ?></h2>
                    <div class="mhs-nim d-flex align-items-center gap-1 mb-2">
                        <i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($profile['nim'] ?? 'Belum diisi'); ?>
                    </div>
                    <div class="mhs-email d-flex align-items-center gap-2">
                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($profile['email'] ?? 'Belum diisi'); ?>
                    </div>
                </div>
            </div>

            <div class="mhs-profile-body">
                <h5 class="section-title mb-4">Informasi Akademik</h5>
                
                <div class="mhs-info-grid">
                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-person-workspace"></i></div>
                        <div class="info-content">
                            <span class="info-label">Dosen Pembimbing Akademik (DPA)</span>
                            <span class="info-value">
                                <?php 
                                if (!empty($profile['dpa_nama'])) {
                                    echo htmlspecialchars($profile['dpa_nama']);
                                } else {
                                    echo 'Belum ditetapkan';
                                }
                                ?>
                            </span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-building"></i></div>
                        <div class="info-content">
                            <span class="info-label">Fakultas</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['fakultas'] ?? 'Belum diisi'); ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-book"></i></div>
                        <div class="info-content">
                            <span class="info-label">Program Studi</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['prodi'] ?? 'Belum diisi'); ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-calendar-event"></i></div>
                        <div class="info-content">
                            <span class="info-label">Tahun Angkatan</span>
                            <span class="info-value"><?php echo $profile['angkatan'] ?? 'Belum diisi'; ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-layers"></i></div>
                        <div class="info-content">
                            <span class="info-label">Semester Saat Ini</span>
                            <span class="info-value"><?php echo $profile['semester'] ?? 'Belum diisi'; ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-graph-up"></i></div>
                        <div class="info-content">
                            <span class="info-label">Indeks Prestasi Kumulatif (IPK)</span>
                            <span class="info-value value-highlight"><?php echo $profile['ipk'] ?? 'Belum diisi'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

        </div> 
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>