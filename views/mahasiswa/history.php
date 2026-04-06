<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../core/profile_check.php';

onlyMahasiswa();
redirectIfProfileIncomplete($conn, __FILE__);

$user_id = $_SESSION['user_id'];

// Get mahasiswa id
$query = "SELECT id FROM mahasiswa WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Data mahasiswa tidak ditemukan.';
    header('Location: ../../views/dashboard.php');
    exit;
}

$mahasiswa = mysqli_fetch_assoc($result);
$mahasiswa_id = $mahasiswa['id'];

// Get applications
$query = "SELECT l.id as lamaran_id, l.tanggal_apply, l.status, l.result_published, p.id, p.judul, p.deskripsi, p.tipe, p.lokasi, p.deadline, u.email as mitra_email, m.nama_organisasi
          FROM lamaran l
          JOIN peluang p ON l.peluang_id = p.id
          JOIN users u ON p.mitra_id = u.id
          LEFT JOIN mitra m ON u.id = m.user_id
          WHERE l.mahasiswa_id = ?
          ORDER BY l.tanggal_apply DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $mahasiswa_id);
mysqli_stmt_execute($stmt);
$applications = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Lamaran | Sistem Peluang</title>
    
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
        
        <div class="page-header mb-4">
            <h1 class="page-title fs-3 mb-1">Riwayat Lamaran</h1>
            <div class="page-subtitle mt-2 d-flex align-items-center">
                <i class="bi bi-clock-history me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                <span class="text-body">Lacak status pendaftaran peluang yang telah Anda ikuti.</span>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row g-4">
            <?php while ($app = mysqli_fetch_assoc($applications)): ?>
                <div class="col-md-6 col-lg-6">
                    <div class="post-card h-100 d-flex flex-column p-4">
                        
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="post-title mb-0 pe-2"><?php echo htmlspecialchars($app['judul']); ?></h6>
                            
                            <?php 
                            // Menentukan kelas dan ikon badge berdasarkan status
                            if ($app['result_published'] == 0) {
                                $badge_class = 'status-warning';
                                $status_text = '<i class="bi bi-hourglass-split"></i> Menunggu';
                            } else {
                                if ($app['status'] == 'accepted') {
                                    $badge_class = 'status-success';
                                    $status_text = '<i class="bi bi-check-circle-fill"></i> Diterima';
                                } else {
                                    $badge_class = 'status-danger';
                                    $status_text = '<i class="bi bi-x-circle-fill"></i> Ditolak';
                                }
                            }
                            ?>
                            <span class="badge-status <?php echo $badge_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </div>
                        
                        <div class="post-subtitle d-flex flex-column gap-2 mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-building"></i> 
                                <span><?php echo htmlspecialchars($app['nama_organisasi'] ?? $app['mitra_email']); ?></span>
                            </div>
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <span><i class="bi bi-briefcase me-1"></i> <?php echo htmlspecialchars($app['tipe']); ?></span>
                                <span><i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($app['lokasi'] ?? 'Tidak ditentukan'); ?></span>
                            </div>
                        </div>
                        
                        <p class="post-desc flex-grow-1 mb-4">
                            <?php echo substr(htmlspecialchars($app['deskripsi']), 0, 120) . '...'; ?>
                        </p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-light">
                            <div class="post-meta">
                                <i class="bi bi-calendar2-check"></i> 
                                Melamar: <?php echo date('d M Y', strtotime($app['tanggal_apply'])); ?>
                            </div>
                            <a href="already_applied.php?id=<?php echo $app['id']; ?>" class="btn btn-soft-outline px-3 py-1" style="font-size: 0.85rem;">
                                Lihat Detail
                            </a>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if (mysqli_num_rows($applications) == 0): ?>
            <div class="mhs-empty-state text-center p-5 mt-4">
                <div class="empty-icon mb-3">
                    <i class="bi bi-folder2-open"></i>
                </div>
                <h5 class="text-dark fw-semibold mb-2">Belum Ada Lamaran</h5>
                <p class="text-muted mb-4">Anda belum mendaftar ke peluang apapun saat ini. Jelajahi peluang yang tersedia sekarang!</p>
                <a href="../dashboard.php" class="btn btn-navy px-4">
                    <i class="bi bi-search me-2"></i>Cari Peluang
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>