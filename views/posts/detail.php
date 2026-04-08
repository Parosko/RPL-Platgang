<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../core/profile_check.php';

checkLogin();
redirectIfProfileIncomplete($conn, __FILE__);

$role = $_SESSION['role'];
$email = $_SESSION['email'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$post_id = (int)$_GET['id'];

// Admins can view any post, others can only view approved and non-deactivated posts
if ($role == 'admin') {
    $query = "SELECT p.*, u.email as mitra_email, m.nama_organisasi
              FROM peluang p
              JOIN users u ON p.mitra_id = u.id
              LEFT JOIN mitra m ON u.id = m.user_id
              WHERE p.id = ?";
} else {
    $query = "SELECT p.*, u.email as mitra_email, m.nama_organisasi
              FROM peluang p
              JOIN users u ON p.mitra_id = u.id
              LEFT JOIN mitra m ON u.id = m.user_id
              WHERE p.id = ? AND p.status = 'approved' AND p.closed_at IS NULL";
}

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $post_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: ../dashboard.php');
    exit;
}

$post = mysqli_fetch_assoc($result);

$current_date = date('Y-m-d H:i:s');
$status = ($post['deadline'] >= $current_date) ? 'Open' : 'Closed';

// Check if mahasiswa already applied
$already_applied = false;
if ($role == 'mahasiswa') {
    // Get mahasiswa id first
    $query = "SELECT id FROM mahasiswa WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $mahasiswa = mysqli_fetch_assoc($result);
        $mahasiswa_id = $mahasiswa['id']; // Use mahasiswa.id to match lamaran.mahasiswa_id
        
        $query = "SELECT id FROM lamaran WHERE mahasiswa_id = ? AND peluang_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $mahasiswa_id, $post_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $already_applied = mysqli_num_rows($result) > 0;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peluang - <?php echo htmlspecialchars($post['judul']); ?> | Sistem Peluang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    
    <link rel="stylesheet" href="../../assets/css/posts.css">
</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="content-wrapper">

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title fs-3 mb-1">Detail Peluang</h1>
                <p class="page-subtitle text-muted mb-0">
                    <i class="bi bi-person-badge me-2"></i>Melihat sebagai: <span class="text-capitalize fw-medium"><?php echo $role; ?></span>
                </p>
            </div>
            <a href="../dashboard.php" class="btn btn-soft-outline px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 bg-success text-white" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 bg-danger text-white" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="post-detail-card mb-5">
            <div class="post-header d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
                <div>
                    <h2 class="post-title h4 mb-2 text-navy"><?php echo htmlspecialchars($post['judul']); ?></h2>
                    <div class="d-flex align-items-center gap-2 text-muted">
                        <i class="bi bi-building fs-5"></i>
                        <span class="fw-medium"><?php echo htmlspecialchars($post['nama_organisasi'] ?? $post['mitra_email']); ?></span>
                    </div>
                </div>
                <span class="badge badge-status <?php echo ($status == 'Open') ? 'status-open' : 'status-closed'; ?>">
                    <?php if ($status == 'Open'): ?>
                        <i class="bi bi-circle-fill status-icon-pulse"></i> <?php echo $status; ?>
                    <?php else: ?>
                        <i class="bi bi-lock-fill status-icon"></i> <?php echo $status; ?>
                    <?php endif; ?>
                </span>
            </div>

            <div class="post-body">
                
                <div class="mb-5">
                    <h6 class="text-muted fw-semibold mb-3">Deskripsi Peluang</h6>
                    <p class="text-dark mb-0 post-description">
                        <?php echo nl2br(htmlspecialchars($post['deskripsi'])); ?>
                    </p>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <h6 class="text-muted fw-semibold mb-3">Informasi Umum</h6>
                        <div class="detail-box">
                            <div class="detail-item">
                                <span class="detail-label"><i class="bi bi-briefcase me-2"></i>Tipe Posisi</span>
                                <span class="detail-value"><?php echo htmlspecialchars($post['tipe']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="bi bi-geo-alt me-2"></i>Lokasi</span>
                                <span class="detail-value"><?php echo htmlspecialchars($post['lokasi'] ?? 'Tidak ditentukan'); ?></span>
                            </div>
                            <div class="detail-item border-0">
                                <span class="detail-label"><i class="bi bi-people me-2"></i>Kuota Tersedia</span>
                                <span class="detail-value"><?php echo $post['kuota']; ?> Orang</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="text-muted fw-semibold mb-3">Persyaratan Minimum</h6>
                        <div class="detail-box">
                            <div class="detail-item">
                                <span class="detail-label"><i class="bi bi-mortarboard me-2"></i>IPK Minimum</span>
                                <span class="detail-value"><?php echo $post['min_ipk'] ?: '-'; ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="bi bi-book me-2"></i>Semester Minimum</span>
                                <span class="detail-value"><?php echo $post['min_semester'] ?: '-'; ?></span>
                            </div>
                            <div class="detail-item border-0">
                                <span class="detail-label"><i class="bi bi-building-fill me-2"></i>Fakultas</span>
                                <span class="detail-value"><?php echo htmlspecialchars($post['fakultas'] ?? 'Semua Fakultas'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="timeline-panel mb-5">
                    <div class="row text-center text-md-start g-3">
                        <div class="col-md-6 border-md-end">
                            <small class="text-muted d-block mb-1">Tanggal Dipublikasikan</small>
                            <span class="fw-medium text-dark">
                                <i class="bi bi-calendar-plus me-2 text-primary"></i>
                                <?php echo date('d F Y', strtotime($post['created_at'])); ?>
                            </span>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <small class="text-muted d-block mb-1">Batas Akhir Pendaftaran</small>
                            <span class="fw-medium <?php echo ($status == 'Open') ? 'text-dark' : 'text-danger'; ?>">
                                <i class="bi bi-calendar-x me-2 <?php echo ($status == 'Open') ? 'text-warning' : 'text-danger'; ?>"></i>
                                <?php echo date('d F Y', strtotime($post['deadline'])); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="post-actions d-flex flex-wrap gap-3 pt-4 border-top">
                    <?php if ($role == 'mahasiswa' && $status == 'Open' && !$already_applied): ?>
                        <a href="../mahasiswa/apply.php?id=<?php echo $post['id']; ?>" 
                           class="btn btn-navy px-4 py-2"
                           onclick="return confirm('Apakah Anda yakin ingin mendaftar untuk peluang ini?')">
                            <i class="bi bi-send me-2"></i> Lamar Sekarang
                        </a>
                    <?php elseif ($role == 'mahasiswa' && $already_applied): ?>
                        <a href="../mahasiswa/already_applied.php?id=<?php echo $post['id']; ?>" class="btn btn-soft-outline px-4 py-2 status-info">
                            <i class="bi bi-check-circle-fill me-2"></i> Anda Sudah Melamar
                        </a>
                    <?php elseif ($role == 'dpa' && $status == 'Open'): ?>
                        <a href="<?= BASE_URL ?>/views/dpa/recommend_post.php?id=<?php echo $post['id']; ?>" class="btn btn-warning px-4 py-2 text-white">
                            <i class="bi bi-star me-2"></i> Rekomendasikan ke Mahasiswa
                        </a>
                    <?php elseif ($role == 'admin'): ?>
                        <button class="btn btn-danger px-4 py-2" onclick="deactivatePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['judul'])); ?>')">
                            <i class="bi bi-slash-circle me-2"></i> Nonaktifkan Peluang
                        </button>
                    <?php elseif ($role == 'mitra' && $post['mitra_id'] == $_SESSION['user_id']): ?>
                        <a href="../mitra/applicants.php?id=<?php echo $post['id']; ?>" class="btn btn-navy px-4 py-2">
                            <i class="bi bi-people me-2"></i> Kelola Pendaftar
                        </a>
                        <a href="../mitra/edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-soft-outline px-4 py-2">
                            <i class="bi bi-pencil me-2"></i> Edit Peluang
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function deactivatePost(postId, postTitle) {
    if (confirm(`Peringatan: Apakah Anda yakin ingin menonaktifkan peluang "${postTitle}"? Peluang ini tidak akan bisa dilihat lagi oleh mahasiswa.`)) {
        window.location.href = `../../controllers/admin/deactivate_post_process.php?id=${postId}`;
    }
}
</script>

</body>
</html>
