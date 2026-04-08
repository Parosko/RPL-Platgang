<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMitra();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: my_posts.php');
    exit;
}

$peluang_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get post details to verify ownership
$query = "SELECT id, judul FROM peluang WHERE id = ? AND mitra_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $peluang_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Postingan tidak ditemukan.';
    header('Location: my_posts.php');
    exit;
}

$post = mysqli_fetch_assoc($result);

// Get post status (including closed_at)
$query = "SELECT id, judul, deskripsi, tipe, lokasi, kuota, min_ipk, min_semester, fakultas, deadline, closed_at FROM peluang WHERE id = ? AND mitra_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $peluang_id, $user_id);
mysqli_stmt_execute($stmt);
$post_full = mysqli_stmt_get_result($stmt);
$post_data = mysqli_fetch_assoc($post_full);

// Check if post is closed
$is_post_closed = !empty($post_data['closed_at']);
$current_date = date('Y-m-d H:i:s');
$is_deadline_passed = $post_data['deadline'] < $current_date;

// Get post requirements and all applications for this post
$query = "SELECT l.id, l.tanggal_apply, l.status, l.is_recommended, l.result_published,
                 m.nim, m.nama, m.fakultas, m.ipk, m.semester,
                 u.email,
                 p.min_ipk, p.min_semester, p.fakultas as required_fakultas, p.tipe
          FROM lamaran l
          JOIN mahasiswa m ON l.mahasiswa_id = m.id
          JOIN users u ON m.user_id = u.id
          JOIN peluang p ON l.peluang_id = p.id
          WHERE l.peluang_id = ?
          ORDER BY l.tanggal_apply DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $peluang_id);
mysqli_stmt_execute($stmt);
$applications = mysqli_stmt_get_result($stmt);

// Check if there are unpublished results
$unpublished_query = "SELECT COUNT(*) as count FROM lamaran WHERE peluang_id = ? AND status IN ('accepted', 'rejected') AND result_published = 0";
$unpublished_stmt = mysqli_prepare($conn, $unpublished_query);
mysqli_stmt_bind_param($unpublished_stmt, 'i', $peluang_id);
mysqli_stmt_execute($unpublished_stmt);
$unpublished_result = mysqli_stmt_get_result($unpublished_stmt);
$unpublished_count = mysqli_fetch_assoc($unpublished_result)['count'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftar - <?php echo htmlspecialchars($post['judul']); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/mitra.css">
    <link rel="stylesheet" href="../../assets/css/layout-utilities.css">
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Daftar Pelamar</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-briefcase me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body"><?php echo htmlspecialchars($post['judul']); ?></span>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= BASE_URL ?>/views/mitra/edit_post.php?id=<?php echo $peluang_id; ?>" class="btn btn-soft-outline px-3">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                
                <?php if (!$is_post_closed && !$is_deadline_passed): ?>
                    <button type="button" class="btn btn-soft-warning px-3" 
                        onclick="closePost(<?php echo $peluang_id; ?>, '<?php echo htmlspecialchars(addslashes($post['judul'])); ?>')">
                        <i class="bi bi-lock me-1"></i> Tutup
                    </button>
                <?php elseif ($is_post_closed): ?>
                    <button type="button" class="btn btn-navy px-3" 
                        onclick="reopenPost(<?php echo $peluang_id; ?>, '<?php echo htmlspecialchars(addslashes($post['judul'])); ?>')">
                        <i class="bi bi-unlock me-1"></i> Buka Kembali
                    </button>
                <?php endif; ?>

                <button type="button" class="btn btn-soft-danger px-3" 
                    onclick="deletePost(<?php echo $peluang_id; ?>, '<?php echo htmlspecialchars(addslashes($post['judul'])); ?>')">
                    <i class="bi bi-trash me-1"></i> Hapus
                </button>
                
                <button type="button" class="btn btn-navy px-3" 
                    onclick="<?php echo ($unpublished_count > 0) ? "publishResults($peluang_id, '" . htmlspecialchars(addslashes($post['judul'])) . "')" : 'showNoResultsMessage()'; ?>">
                    <i class="bi bi-send-check me-1"></i> Publikasi Hasil
                </button>
                
                <a href="my_posts.php" class="btn btn-soft-outline px-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
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

        <div class="row mb-4">
            <?php 
            $req_query = "SELECT min_ipk, min_semester, fakultas FROM peluang WHERE id = ?";
            $req_stmt = mysqli_prepare($conn, $req_query);
            mysqli_stmt_bind_param($req_stmt, 'i', $peluang_id);
            mysqli_stmt_execute($req_stmt);
            $req_result = mysqli_stmt_get_result($req_stmt);
            $requirements = mysqli_fetch_assoc($req_result);
            ?>
            <div class="col-lg-12 mb-3">
                <div class="mitra-edit-card mb-0 p-3 d-flex flex-wrap gap-4 align-items-center">
                    <div class="text-muted" style="font-size: 0.85rem; font-weight: 500; color: var(--text-subtle);">
                        <i class="bi bi-list-check me-1"></i> PERSYARATAN:
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="mitra-badge border">
                            IPK Min: <?php echo $requirements['min_ipk'] ?? 'Tidak ada'; ?>
                        </span>
                        <span class="mitra-badge border">
                            Semester Min: <?php echo $requirements['min_semester'] ?? 'Tidak ada'; ?>
                        </span>
                        <span class="mitra-badge border">
                            Fakultas: <?php echo $requirements['fakultas'] ?? 'Semua Fakultas'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if ($unpublished_count > 0): ?>
            <div class="col-lg-12 mb-3">
                <div class="custom-notice notice-warning mb-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-exclamation-triangle-fill notice-icon fs-5"></i>
                        <h6 class="notice-title mb-0 fw-bold">Perhatian! Ada <?php echo $unpublished_count; ?> evaluasi belum dipublikasi.</h6>
                    </div>
                    <p class="notice-desc mb-0" style="font-size: 0.9rem;">
                        Klik tombol "Publikasi Hasil" di atas untuk mengirim notifikasi ke pelamar. Harap lengkapi pesan di bawah ini terlebih dahulu.
                    </p>
                </div>

                <div class="mitra-edit-card mb-0">
                    <div class="mitra-edit-header">
                        <h5 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Pesan Khusus untuk Pelamar</h5>
                        <p class="form-text mb-0 mt-1">Pesan ini akan dikirimkan sebagai notifikasi kepada kandidat yang diterima dan ditolak.</p>
                    </div>
                    <div class="mitra-edit-body">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="pesan_accepted" class="custom-label">
                                    Pesan untuk Kandidat Diterima <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control custom-input" id="pesan_accepted" rows="3" required
                                          placeholder="Contoh: Selamat! Kami senang menyambut Anda..."></textarea>
                                <div class="form-text">Minimal 10 karakter</div>
                            </div>
                            <div class="col-md-6">
                                <label for="pesan_rejected" class="custom-label">
                                    Pesan untuk Kandidat Ditolak <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control custom-input" id="pesan_rejected" rows="3" required
                                          placeholder="Contoh: Terima kasih telah melamar. Setelah pertimbangan..."></textarea>
                                <div class="form-text">Minimal 10 karakter</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="mitra-edit-card mb-4">
            <div class="mitra-edit-body p-3">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="custom-label">Status</label>
                        <select class="form-select custom-input" id="filterStatus" onchange="filterApplications()">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="accepted">Diterima</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="custom-label">Persyaratan</label>
                        <select class="form-select custom-input" id="filterRequirement" onchange="filterApplications()">
                            <option value="">Semua</option>
                            <option value="meets">Memenuhi Syarat</option>
                            <option value="lacks">Kurang Syarat</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="custom-label">Filter IPK Min</label>
                        <input type="number" step="0.01" class="form-control custom-input" id="filterIPK" placeholder="Contoh: 3.5" onchange="filterApplications()">
                    </div>
                    <div class="col-md-3">
                        <label class="custom-label">Cari Pelamar</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0" style="border-color: #CBD5E1; color: #64748B;">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control custom-input border-start-0 ps-0" id="filterName" placeholder="Nama pelamar..." onkeyup="filterApplications()">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <?php if (mysqli_num_rows($applications) > 0):
                mysqli_data_seek($applications, 0);
                while ($app = mysqli_fetch_assoc($applications)): 
                    // Check which requirements are missing
                    $lacks_ipk = $app['min_ipk'] && $app['ipk'] < $app['min_ipk'];
                    $lacks_semester = $app['min_semester'] && $app['semester'] < $app['min_semester'];
                    $lacks_faculty = $app['required_fakultas'] && $app['fakultas'] !== $app['required_fakultas'];
                    $lacks_any = $lacks_ipk || $lacks_semester || $lacks_faculty;
                    $meets_all = !$lacks_any;
                    
                    // Setup avatar initials
                    $name_parts = explode(' ', trim($app['nama']));
                    $initials = strtoupper(substr($name_parts[0], 0, 1));
                    if(isset($name_parts[1])) {
                        $initials .= strtoupper(substr($name_parts[1], 0, 1));
                    }
                    ?>
                    <div class="col-md-6 col-xl-4 applicant-card" 
                         data-status="<?php echo $app['status']; ?>" 
                         data-name="<?php echo strtolower($app['nama']); ?>"
                         data-ipk="<?php echo $app['ipk']; ?>"
                         data-meets="<?php echo $meets_all ? 'meets' : 'lacks'; ?>">
                         
                        <div class="mitra-profile-card h-100 d-flex flex-column mb-0 <?php echo $lacks_any ? 'border-warning' : ''; ?>" style="<?php echo $lacks_any ? 'border-width: 2px;' : ''; ?>">
                            
                            <div class="mitra-profile-header p-3 d-flex justify-content-between align-items-start">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="mitra-avatar shadow-sm" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                        <?php echo $initials; ?>
                                    </div>
                                    <div class="mitra-header-info">
                                        <h6 class="mitra-name mb-0 fs-6"><?php echo htmlspecialchars($app['nama']); ?></h6>
                                        <div class="mitra-email mt-1">
                                            <span><i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($app['nim']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mitra-profile-body p-3 flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <div>
                                        <span class="mitra-badge <?php 
                                            echo ($app['status'] == 'accepted') ? 'status-success' : 
                                                (($app['status'] == 'rejected') ? 'status-danger' : 'status-warning'); 
                                        ?>">
                                            <?php if($app['status'] == 'accepted') echo '<i class="bi bi-check-circle-fill me-1"></i>'; ?>
                                            <?php if($app['status'] == 'rejected') echo '<i class="bi bi-x-circle-fill me-1"></i>'; ?>
                                            <?php if($app['status'] == 'pending') echo '<i class="bi bi-clock-fill me-1"></i>'; ?>
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                    </div>
                                    <?php if ($app['result_published']): ?>
                                        <span class="mitra-badge border bg-white">
                                            <i class="bi bi-envelope-check text-primary"></i> Ternotifikasi
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="info-label mb-1">Fakultas</div>
                                        <div class="info-value text-truncate" title="<?php echo htmlspecialchars($app['fakultas']); ?>">
                                            <?php echo htmlspecialchars($app['fakultas']); ?>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="info-label mb-1">IPK</div>
                                        <div class="info-value"><?php echo $app['ipk']; ?></div>
                                    </div>
                                    <div class="col-3">
                                        <div class="info-label mb-1">Sem</div>
                                        <div class="info-value"><?php echo $app['semester']; ?></div>
                                    </div>
                                </div>

                                <?php if ($lacks_any): ?>
                                    <div class="custom-notice notice-warning p-2 mt-2 d-flex flex-column gap-1">
                                        <span class="fw-bold" style="font-size: 0.8rem;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Kurang Syarat:</span>
                                        <ul class="mb-0 ps-3" style="font-size: 0.75rem;">
                                            <?php if ($lacks_ipk): ?>
                                                <li>IPK (<?php echo $app['ipk']; ?> < <?php echo $app['min_ipk']; ?>)</li>
                                            <?php endif; ?>
                                            <?php if ($lacks_semester): ?>
                                                <li>Semester (<?php echo $app['semester']; ?> < <?php echo $app['min_semester']; ?>)</li>
                                            <?php endif; ?>
                                            <?php if ($lacks_faculty): ?>
                                                <li>Fakultas (<?php echo htmlspecialchars($app['fakultas']); ?> ≠ <?php echo htmlspecialchars($app['required_fakultas']); ?>)</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <div class="p-2 mt-2 rounded d-flex align-items-center gap-2 status-success border-0 bg-opacity-50">
                                        <i class="bi bi-check-circle-fill" style="font-size: 0.9rem;"></i>
                                        <span style="font-size: 0.8rem; font-weight: 500;">Memenuhi semua persyaratan</span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($app['is_recommended']): ?>
                                    <div class="mt-2 text-center py-1 rounded" style="background-color: #E0F2FE; border: 1px dashed #0284C7; color: #0369A1; font-size: 0.8rem; font-weight: 600;">
                                        <i class="bi bi-star-fill me-1"></i> Direkomendasikan DPA
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="p-3 border-top d-flex gap-2 justify-content-between align-items-center mt-auto" style="background-color: #F8FAFC;">
                                <a href="view_applicant.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-soft-outline flex-grow-1 fw-medium">
                                    Detail
                                </a>
                                
                                <div class="d-flex gap-2">
                                    <?php if ($app['status'] == 'pending'): ?>
                                        <button type="button" class="btn btn-sm btn-navy px-3" onclick="acceptApplication(<?php echo $app['id']; ?>)">
                                            <i class="bi bi-check-lg"></i> Terima
                                        </button>
                                        <button type="button" class="btn btn-sm btn-soft-danger px-3" onclick="rejectApplication(<?php echo $app['id']; ?>)">
                                            <i class="bi bi-x-lg"></i> Tolak
                                        </button>
                                    <?php elseif ($app['status'] == 'accepted'): ?>
                                        <button type="button" class="btn btn-sm btn-soft-danger px-3" onclick="rejectApplication(<?php echo $app['id']; ?>)">
                                            Batalkan
                                        </button>
                                    <?php elseif ($app['status'] == 'rejected'): ?>
                                        <button type="button" class="btn btn-sm btn-navy px-3" onclick="acceptApplication(<?php echo $app['id']; ?>)">
                                            Terima
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <div class="col-12">
                    <div class="mitra-empty-state d-flex flex-column align-items-center justify-content-center py-5 px-3">
                        <i class="bi bi-inboxes empty-icon mb-3"></i>
                        <h5 class="mb-2 fw-semibold">Belum Ada Pelamar</h5>
                        <p class="text-muted text-center" style="max-width: 400px; font-size: 0.9rem;">
                            Saat ini belum ada mahasiswa yang melamar untuk postingan ini. Coba kembali lagi nanti!
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function filterApplications() {
    const status = document.getElementById('filterStatus').value.toLowerCase();
    const requirement = document.getElementById('filterRequirement').value.toLowerCase();
    const ipk = parseFloat(document.getElementById('filterIPK').value) || 0;
    const name = document.getElementById('filterName').value.toLowerCase();
    
    const cards = document.querySelectorAll('.applicant-card');
    cards.forEach(card => {
        const cardStatus = card.dataset.status.toLowerCase();
        const cardMeets = card.dataset.meets.toLowerCase();
        const cardIPK = parseFloat(card.dataset.ipk);
        const cardName = card.dataset.name.toLowerCase();
        
        const statusMatch = !status || cardStatus === status;
        const requirementMatch = !requirement || cardMeets === requirement;
        const ipkMatch = !ipk || cardIPK >= ipk;
        const nameMatch = !name || cardName.includes(name);
        
        card.style.display = (statusMatch && requirementMatch && ipkMatch && nameMatch) ? '' : 'none';
    });
}

function closePost(postId, postTitle) {
    if (confirm(`Apakah Anda yakin ingin menutup postingan "${postTitle}"? Semua lamaran yang belum diterima akan ditolak secara otomatis.`)) {
        fetch('<?= BASE_URL ?>/controllers/mitra/close_post_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'peluang_id=' + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error);
        });
    }
}

function reopenPost(postId, postTitle) {
    if (confirm(`Apakah Anda yakin ingin membuka kembali postingan "${postTitle}"? Applicants dapat mendaftar lagi.`)) {
        fetch('<?= BASE_URL ?>/controllers/mitra/reopen_post_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'peluang_id=' + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error);
        });
    }
}

function deletePost(postId, postTitle) {
    if (confirm(`Apakah Anda yakin ingin menghapus postingan "${postTitle}" secara permanen? Tindakan ini tidak dapat dibatalkan dan semua data terkait akan dihapus.`)) {
        fetch('<?= BASE_URL ?>/controllers/mitra/delete_post_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'peluang_id=' + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = 'my_posts.php';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error);
        });
    }
}

function acceptApplication(lamaranId) {
    if (confirm('Apakah Anda yakin ingin menerima lamaran ini?')) {
        fetch('../../controllers/mitra/manage_application_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=accept&lamaran_id=' + lamaranId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function rejectApplication(lamaranId) {
    if (confirm('Apakah Anda yakin ingin menolak lamaran ini?')) {
        fetch('../../controllers/mitra/manage_application_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=reject&lamaran_id=' + lamaranId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function showNoResultsMessage() {
    alert('Tidak ada hasil yang dapat dipublikasi. Pastikan ada pelamar yang sudah diterima atau ditolak sebelum mempublikasi hasil.');
}

function publishResults(peluangId, postTitle) {
    const pesanAccepted = document.getElementById('pesan_accepted').value.trim();
    const pesanRejected = document.getElementById('pesan_rejected').value.trim();
    
    if (!pesanAccepted || !pesanRejected) {
        alert('Anda wajib mengisi kedua pesan (untuk kandidat diterima dan ditolak) sebelum mempublikasi hasil.');
        return;
    }
    
    if (pesanAccepted.length < 10) {
        alert('Pesan untuk kandidat diterima minimal 10 karakter.');
        document.getElementById('pesan_accepted').focus();
        return;
    }
    
    if (pesanRejected.length < 10) {
        alert('Pesan untuk kandidat ditolak minimal 10 karakter.');
        document.getElementById('pesan_rejected').focus();
        return;
    }
    
    if (confirm('Apakah Anda yakin ingin mempublikasi hasil untuk "' + postTitle + '"? Notifikasi dengan pesan khusus akan dikirim ke semua pelamar dan postingan akan ditutup secara otomatis.')) {
        const formData = new FormData();
        formData.append('action', 'release_results');
        formData.append('peluang_id', peluangId);
        formData.append('pesan_accepted', pesanAccepted);
        formData.append('pesan_rejected', pesanRejected);

        fetch('../../controllers/mitra/publish_results_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Hasil berhasil dipublikasikan!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mempublikasi hasil.');
        });
    }
}
</script>
</body>
</html>