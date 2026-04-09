<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyDPA();

$user_id = $_SESSION['user_id'];

// Get DPA profile & email
$query = "SELECT d.*, u.email FROM dpa d JOIN users u ON d.user_id = u.id WHERE d.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    // Redirect to complete profile
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
    <title>Profil DPA | Sistem Peluang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/dpa.css">
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
                <h1 class="page-title">Profil DPA</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-person-video3 me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Kelola informasi pribadi dan data akademik Anda.</span>
                </div>
            </div>
            <a href="edit_profile.php" class="btn btn-navy px-4">
                <i class="bi bi-pencil-square me-2"></i>Edit Profil
            </a>
        </div>

        <div class="dpa-profile-card">
            
            <div class="dpa-profile-header d-flex flex-wrap align-items-center gap-4">
                <div class="dpa-avatar">
                    <?php 
                    $nama = htmlspecialchars($profile['nama'] ?? 'D');
                    echo strtoupper(substr($nama, 0, 1)); 
                    ?>
                </div>
                <div class="dpa-header-info">
                    <span class="dpa-badge mb-2">Dosen Pembimbing Akademik</span>
                    <h2 class="dpa-name mb-1"><?php echo htmlspecialchars($profile['nama'] ?? 'Belum diisi'); ?></h2>
                    <div class="dpa-email d-flex align-items-center gap-2">
                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($profile['email'] ?? 'Belum diisi'); ?>
                    </div>
                </div>
            </div>

            <div class="dpa-profile-body">
                <h5 class="section-title mb-4">Informasi Akademik & Kontak</h5>
                
                <div class="dpa-info-grid">
                    
                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-person-vcard"></i></div>
                        <div class="info-content">
                            <span class="info-label">NIP / NIDN</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['nip'] ?? 'Belum diisi'); ?></span>
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
                        <div class="info-icon"><i class="bi bi-telephone"></i></div>
                        <div class="info-content">
                            <span class="info-label">Kontak / Telepon</span>
                            <span class="info-value"><?php echo htmlspecialchars($profile['kontak'] ?? 'Belum diisi'); ?></span>
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