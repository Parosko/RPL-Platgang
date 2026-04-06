<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../core/profile_check.php';

onlyMahasiswa();
redirectIfProfileIncomplete($conn, __FILE__);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../views/dashboard.php');
    exit;
}

$peluang_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get mahasiswa id
$query = "SELECT id FROM mahasiswa WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: ../../views/dashboard.php');
    exit;
}

$mahasiswa = mysqli_fetch_assoc($result);
$mahasiswa_id = $mahasiswa['id'];

// Check if applied
$query = "SELECT l.*, p.judul, p.deskripsi, p.tipe, p.lokasi, p.kuota, p.min_ipk, p.min_semester, p.fakultas, p.deadline, p.created_at as peluang_created, u.email as mitra_email, m.nama_organisasi
          FROM lamaran l
          JOIN peluang p ON l.peluang_id = p.id
          JOIN users u ON p.mitra_id = u.id
          LEFT JOIN mitra m ON u.id = m.user_id
          WHERE l.mahasiswa_id = ? AND l.peluang_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $mahasiswa_id, $peluang_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: ../../views/posts/detail.php?id=' . $peluang_id);
    exit;
}

$application = mysqli_fetch_assoc($result);

// Get uploaded documents
$query = "SELECT * FROM dokumen WHERE lamaran_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $application['id']);
mysqli_stmt_execute($stmt);
$documents = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Lamaran - <?php echo htmlspecialchars($application['judul']); ?> | Sistem Peluang</title>
    
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
        
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title fs-3 mb-1">Status Lamaran</h1>
                <p class="page-subtitle text-muted mb-0">
                    <i class="bi bi-bookmark-check me-2"></i><?php echo htmlspecialchars($application['judul']); ?>
                </p>
            </div>
            <a href="../posts/detail.php?id=<?php echo $peluang_id; ?>" class="btn btn-soft-outline px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="mhs-edit-card mb-4">
            <div class="mhs-edit-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="mb-1 text-navy fw-semibold"><?php echo htmlspecialchars($application['judul']); ?></h5>
                    <small class="text-muted">
                        <i class="bi bi-building me-1"></i> Oleh: <?php echo htmlspecialchars($application['nama_organisasi'] ?? $application['mitra_email']); ?>
                    </small>
                </div>
                
                <?php 
                    // Menentukan Status Badge
                    if ($application['result_published'] == 0) {
                        $status_class = 'status-warning';
                        $status_icon = 'bi-hourglass-split';
                        $display_status = 'Pending / Menunggu';
                    } else {
                        $display_status = ucfirst($application['status']);
                        if ($application['status'] == 'accepted') {
                            $status_class = 'status-success';
                            $status_icon = 'bi-check-circle-fill';
                        } else {
                            $status_class = 'status-danger';
                            $status_icon = 'bi-x-circle-fill';
                        }
                    }
                ?>
                <span class="badge-status <?php echo $status_class; ?>">
                    <i class="bi <?php echo $status_icon; ?> me-1"></i> <?php echo $display_status; ?>
                </span>
            </div>

            <div class="mhs-edit-body p-4">
                
                <div class="mb-4">
                    <h6 class="text-muted fw-semibold mb-2">Deskripsi Peluang</h6>
                    <p class="text-dark mb-0" style="font-size: 0.95rem; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($application['deskripsi'])); ?>
                    </p>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted fw-semibold mb-3">Informasi Peluang</h6>
                        <div class="req-box">
                            <div class="req-item">
                                <span class="req-label"><i class="bi bi-tag me-2"></i>Tipe</span>
                                <span class="req-value fw-medium"><?php echo htmlspecialchars($application['tipe']); ?></span>
                            </div>
                            <div class="req-item">
                                <span class="req-label"><i class="bi bi-geo-alt me-2"></i>Lokasi</span>
                                <span class="req-value fw-medium"><?php echo htmlspecialchars($application['lokasi'] ?? 'Tidak ditentukan'); ?></span>
                            </div>
                            <div class="req-item border-0">
                                <span class="req-label"><i class="bi bi-people me-2"></i>Kuota</span>
                                <span class="req-value fw-medium"><?php echo $application['kuota']; ?> Orang</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h6 class="text-muted fw-semibold mb-3">Persyaratan Minimum</h6>
                        <div class="req-box">
                            <div class="req-item">
                                <span class="req-label">IPK Minimum</span>
                                <span class="req-value"><?php echo $application['min_ipk'] ?: '-'; ?></span>
                            </div>
                            <div class="req-item">
                                <span class="req-label">Semester Minimum</span>
                                <span class="req-value"><?php echo $application['min_semester'] ?: '-'; ?></span>
                            </div>
                            <div class="req-item border-0">
                                <span class="req-label">Fakultas</span>
                                <span class="req-value"><?php echo htmlspecialchars($application['fakultas'] ?? 'Semua Fakultas'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-light p-3 rounded-3 mb-4 border" style="border-color: #E2E8F0 !important;">
                    <div class="row text-center text-md-start">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <small class="text-muted d-block mb-1">Tanggal Dibuat</small>
                            <span class="fw-medium text-dark"><i class="bi bi-calendar3 me-1"></i> <?php echo date('d M Y', strtotime($application['peluang_created'])); ?></span>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0 border-md-start border-md-end">
                            <small class="text-muted d-block mb-1">Batas Akhir (Deadline)</small>
                            <span class="fw-medium text-dark"><i class="bi bi-calendar-x me-1"></i> <?php echo date('d M Y', strtotime($application['deadline'])); ?></span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block mb-1">Tanggal Anda Melamar</small>
                            <span class="fw-medium text-primary"><i class="bi bi-calendar-check me-1"></i> <?php echo date('d M Y, H:i', strtotime($application['tanggal_apply'])); ?></span>
                        </div>
                    </div>
                </div>

                <div>
                    <h6 class="text-muted fw-semibold mb-3"><i class="bi bi-folder2-open me-2"></i>Dokumen yang Diunggah</h6>
                    
                    <?php if (mysqli_num_rows($documents) > 0): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php while ($doc = mysqli_fetch_assoc($documents)): ?>
                                <div class="dz-preview-item" style="cursor: default;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="dz-preview-icon"><i class="bi bi-file-earmark-check"></i></div>
                                        <div class="d-flex flex-column">
                                            <span class="dz-preview-name"><?php echo htmlspecialchars($doc['jenis']); ?></span>
                                            <span class="dz-preview-size text-muted">Lampiran Berkas</span>
                                        </div>
                                    </div>
                                    <a href="../../uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-soft-outline px-3">
                                        <i class="bi bi-eye me-1"></i>Lihat File
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="custom-notice notice-warning">
                            <i class="bi bi-info-circle me-2"></i> Tidak ada dokumen yang dilampirkan pada lamaran ini.
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>