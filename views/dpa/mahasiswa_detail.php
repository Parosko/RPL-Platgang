<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../core/profile_check.php';

onlyDPA();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: mahasiswa.php');
    exit;
}

$student_id = (int)$_GET['id'];
$dpa_user_id = $_SESSION['user_id'];

// Verify that this student belongs to the current DPA
$query = "SELECT m.*, u.email FROM mahasiswa m
          JOIN users u ON m.user_id = u.id
          WHERE m.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: mahasiswa.php');
    exit;
}

$student = mysqli_fetch_assoc($result);

// Verify the student is assigned to this DPA
$query = "SELECT id FROM dpa WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $dpa_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$dpa = mysqli_fetch_assoc($result);

if ($student['dpa_id'] != $dpa['id']) {
    header('Location: mahasiswa.php');
    exit;
}

// Get all applications for this student
$query = "SELECT l.id, l.status, l.tanggal_apply, p.id as peluang_id, p.judul, p.deskripsi, p.tipe, p.lokasi, p.deadline,
                 m.nama_organisasi, u.email as mitra_email
          FROM lamaran l
          JOIN peluang p ON l.peluang_id = p.id
          JOIN mitra m ON p.mitra_id = m.user_id
          JOIN users u ON m.user_id = u.id
          WHERE l.mahasiswa_id = ?
          ORDER BY l.tanggal_apply DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$applications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $applications[] = $row;
}

$accepted_count = 0;
$rejected_count = 0;
$pending_count = 0;

foreach ($applications as $app) {
    if ($app['status'] === 'accepted') {
        $accepted_count++;
    } elseif ($app['status'] === 'rejected') {
        $rejected_count++;
    } elseif ($app['status'] === 'pending') {
        $pending_count++;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Mahasiswa - <?php echo htmlspecialchars($student['nama']); ?></title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

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

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Detail Mahasiswa</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-person-badge me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body"><?php echo htmlspecialchars($student['nama']); ?></span>
                </div>
            </div>
            <a href="mahasiswa.php" class="btn btn-soft-outline px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <div class="dpa-profile-card mb-4">
            <div class="dpa-profile-header d-flex flex-wrap align-items-center gap-4">
                <div class="dpa-avatar">
                    <?php 
                    $nama = htmlspecialchars($student['nama'] ?? 'M');
                    echo strtoupper(substr($nama, 0, 1)); 
                    ?>
                </div>
                <div class="dpa-header-info">
                    <span class="dpa-badge mb-2">Mahasiswa</span>
                    <h2 class="dpa-name mb-1"><?php echo htmlspecialchars($student['nama'] ?? 'Belum diisi'); ?></h2>
                    <div class="dpa-email d-flex align-items-center gap-2">
                        <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($student['email'] ?? 'Belum diisi'); ?>
                    </div>
                </div>
                <div class="d-flex gap-2 ms-auto">
                    <div class="text-center px-3 py-2" style="background: var(--info-light); border-radius: var(--radius-lg); border: 1px solid var(--border-base);">
                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total</div>
                        <div class="fw-bold" style="color: var(--info); font-size: 1.25rem;"><?php echo count($applications); ?></div>
                    </div>
                    <div class="text-center px-3 py-2" style="background: var(--success-light); border-radius: var(--radius-lg); border: 1px solid var(--success-border);">
                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Diterima</div>
                        <div class="fw-bold" style="color: var(--success); font-size: 1.25rem;"><?php echo $accepted_count; ?></div>
                    </div>
                    <div class="text-center px-3 py-2" style="background: var(--danger-light); border-radius: var(--radius-lg); border: 1px solid var(--danger-border);">
                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Ditolak</div>
                        <div class="fw-bold" style="color: var(--danger); font-size: 1.25rem;"><?php echo $rejected_count; ?></div>
                    </div>
                    <div class="text-center px-3 py-2" style="background: var(--warning-light); border-radius: var(--radius-lg); border: 1px solid var(--warning-border);">
                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Menunggu</div>
                        <div class="fw-bold" style="color: var(--warning); font-size: 1.25rem;"><?php echo $pending_count; ?></div>
                    </div>
                </div>
            </div>

            <div class="dpa-profile-body">
                <h5 class="section-title mb-4">Informasi Akademik</h5>
                
                <div class="dpa-info-grid">
                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-hash"></i></div>
                        <div class="info-content">
                            <span class="info-label">NIM</span>
                            <span class="info-value"><?php echo htmlspecialchars($student['nim'] ?? 'Belum diisi'); ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-building"></i></div>
                        <div class="info-content">
                            <span class="info-label">Fakultas</span>
                            <span class="info-value"><?php echo htmlspecialchars($student['fakultas'] ?? 'Belum diisi'); ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-book"></i></div>
                        <div class="info-content">
                            <span class="info-label">Program Studi</span>
                            <span class="info-value"><?php echo htmlspecialchars($student['prodi'] ?? 'Belum diisi'); ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-calendar3"></i></div>
                        <div class="info-content">
                            <span class="info-label">Semester</span>
                            <span class="info-value"><?php echo htmlspecialchars($student['semester'] ?? 'Belum diisi'); ?></span>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-star-fill"></i></div>
                        <div class="info-content">
                            <span class="info-label">IPK</span>
                            <span class="info-value value-highlight"><?php echo htmlspecialchars($student['ipk'] ?? 'Belum diisi'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Section -->
        <h5 class="section-title mb-4">Daftar Lamaran</h5>

        <?php if (empty($applications)): ?>
            <div class="dpa-empty-state text-center p-5">
                <i class="bi bi-briefcase empty-icon mb-3"></i>
                <h5 class="mb-2">Belum Ada Lamaran</h5>
                <p class="text-muted mb-0">Mahasiswa ini belum membuat lamaran apapun.</p>
            </div>
        <?php else: ?>
            <div class="applications-list">
                <?php foreach ($applications as $app): ?>
                    <div class="application-card">
                        <div class="app-header">
                            <div class="app-title">
                                <h6><?php echo htmlspecialchars($app['judul']); ?></h6>
                                <small class="company-name"><?php echo htmlspecialchars($app['nama_organisasi']); ?></small>
                            </div>
                            <div class="app-status">
                                <?php
                                $status_class = '';
                                $status_label = '';
                                if ($app['status'] === 'accepted') {
                                    $status_class = 'status-success';
                                    $status_label = 'Diterima';
                                } elseif ($app['status'] === 'rejected') {
                                    $status_class = 'status-danger';
                                    $status_label = 'Ditolak';
                                } else {
                                    $status_class = 'status-warning';
                                    $status_label = 'Menunggu';
                                }
                                ?>
                                <span class="badge-status <?php echo $status_class; ?>">
                                    <?php echo $status_label; ?>
                                </span>
                            </div>
                        </div>

                        <div class="app-body">
                            <div class="app-info">
                                <div class="info-column">
                                    <span class="label">Tipe</span>
                                    <span class="value"><?php echo htmlspecialchars($app['tipe']); ?></span>
                                </div>
                                <div class="info-column">
                                    <span class="label">Lokasi</span>
                                    <span class="value"><?php echo htmlspecialchars($app['lokasi'] ?? '-'); ?></span>
                                </div>
                                <div class="info-column">
                                    <span class="label">Deadline</span>
                                    <span class="value"><?php echo date('d M Y', strtotime($app['deadline'])); ?></span>
                                </div>
                                <div class="info-column">
                                    <span class="label">Tanggal Melamar</span>
                                    <span class="value"><?php echo date('d M Y H:i', strtotime($app['tanggal_apply'])); ?></span>
                                </div>
                            </div>

                            <div class="app-description">
                                <strong>Deskripsi:</strong><br>
                                <small><?php echo htmlspecialchars($app['deskripsi']); ?></small>
                            </div>

                            <div class="mt-3">
                                <a href="../posts/detail.php?id=<?php echo $app['peluang_id']; ?>" class="btn btn-navy btn-sm">
                                    <i class="bi bi-eye me-1"></i> Lihat Detail Peluang
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>
