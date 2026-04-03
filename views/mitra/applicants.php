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
$query = "SELECT l.id, l.tanggal_apply, l.status, l.is_recommended,
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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pendaftar - <?php echo htmlspecialchars($post['judul']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Pendaftar untuk: <?php echo htmlspecialchars($post['judul']); ?></h4>
                <small>
                    Login sebagai: <?php echo htmlspecialchars($_SESSION['email']); ?> (mitra)
                </small>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/views/mitra/edit_post.php?id=<?php echo $peluang_id; ?>" class="btn btn-primary">
                    Edit Posting
                </a>
                
                <?php if (!$is_post_closed && !$is_deadline_passed): ?>
                    <button type="button" class="btn btn-warning" 
                        onclick="closePost(<?php echo $peluang_id; ?>, '<?php echo htmlspecialchars($post['judul']); ?>')">
                        Tutup Posting
                    </button>
                <?php elseif ($is_post_closed): ?>
                    <button type="button" class="btn btn-info" 
                        onclick="reopenPost(<?php echo $peluang_id; ?>, '<?php echo htmlspecialchars($post['judul']); ?>')">
                        Buka Kembali
                    </button>
                <?php endif; ?>
                
                <a href="my_posts.php" class="btn btn-secondary">Kembali</a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Requirements Info -->
        <?php 
        $req_query = "SELECT min_ipk, min_semester, fakultas FROM peluang WHERE id = ?";
        $req_stmt = mysqli_prepare($conn, $req_query);
        mysqli_stmt_bind_param($req_stmt, 'i', $peluang_id);
        mysqli_stmt_execute($req_stmt);
        $req_result = mysqli_stmt_get_result($req_stmt);
        $requirements = mysqli_fetch_assoc($req_result);
        ?>
        <div class="card mb-3 bg-light">
            <div class="card-body">
                <h6>Persyaratan Posting:</h6>
                <small>
                    <strong>Min. IPK:</strong> <?php echo $requirements['min_ipk'] ?? 'Tidak ada'; ?> | 
                    <strong>Min. Semester:</strong> <?php echo $requirements['min_semester'] ?? 'Tidak ada'; ?> | 
                    <strong>Fakultas:</strong> <?php echo $requirements['fakultas'] ?? 'Semua'; ?>
                </small>
            </div>
        </div>

        <!-- Filter -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select" id="filterStatus" onchange="filterApplications()">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="accepted">Diterima</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="filterRequirement" onchange="filterApplications()">
                            <option value="">Semua Requirement</option>
                            <option value="meets">Memenuhi Semua</option>
                            <option value="lacks">Kurang Requirement</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control" id="filterIPK" placeholder="Filter IPK Min..." onchange="filterApplications()">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="filterName" placeholder="Cari nama..." onkeyup="filterApplications()">
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications List -->
        <div class="row">
            <?php if (mysqli_num_rows($applications) > 0):
                mysqli_data_seek($applications, 0);
                while ($app = mysqli_fetch_assoc($applications)): 
                    // Check which requirements are missing
                    $lacks_ipk = $app['min_ipk'] && $app['ipk'] < $app['min_ipk'];
                    $lacks_semester = $app['min_semester'] && $app['semester'] < $app['min_semester'];
                    $lacks_faculty = $app['required_fakultas'] && $app['fakultas'] !== $app['required_fakultas'];
                    $lacks_any = $lacks_ipk || $lacks_semester || $lacks_faculty;
                    $meets_all = !$lacks_any;
                    ?>
                    <div class="col-md-6 mb-3 applicant-card" 
                         data-status="<?php echo $app['status']; ?>" 
                         data-name="<?php echo strtolower($app['nama']); ?>"
                         data-ipk="<?php echo $app['ipk']; ?>"
                         data-meets="<?php echo $meets_all ? 'meets' : 'lacks'; ?>">
                        <div class="card h-100 <?php echo $lacks_any ? 'border-warning border-2' : 'border-success'; ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6><?php echo htmlspecialchars($app['nama']); ?></h6>
                                        <small class="text-muted">
                                            NIM: <?php echo htmlspecialchars($app['nim']); ?><br>
                                            Email: <?php echo htmlspecialchars($app['email']); ?>
                                        </small>
                                    </div>
                                    <span class="badge 
                                        <?php echo ($app['status'] == 'accepted') ? 'bg-success' : 
                                               (($app['status'] == 'rejected') ? 'bg-danger' : 'bg-warning'); ?>">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </div>
                                
                                <hr class="my-2">
                                
                                <small>
                                    <strong>Fakultas:</strong> <?php echo htmlspecialchars($app['fakultas']); ?><br>
                                    <strong>IPK:</strong> <?php echo $app['ipk']; ?> | <strong>Semester:</strong> <?php echo $app['semester']; ?><br>
                                    <strong>Apply:</strong> <?php echo date('d-m-Y H:i', strtotime($app['tanggal_apply'])); ?>
                                </small>

                                <!-- Requirement Status -->
                                <?php if ($lacks_any): ?>
                                    <div class="mt-2 p-2 bg-warning-light border border-warning rounded" style="background-color: #fff3cd;">
                                        <strong class="text-warning" style="color: #856404; font-size: 0.85rem;">⚠️ Tidak Memenuhi:</strong>
                                        <ul style="margin: 5px 0; padding-left: 20px; font-size: 0.85rem;">
                                            <?php if ($lacks_ipk): ?>
                                                <li>IPK terlalu rendah (<?php echo $app['ipk']; ?> < <?php echo $app['min_ipk']; ?>)</li>
                                            <?php endif; ?>
                                            <?php if ($lacks_semester): ?>
                                                <li>Semester kurang (<?php echo $app['semester']; ?> < <?php echo $app['min_semester']; ?>)</li>
                                            <?php endif; ?>
                                            <?php if ($lacks_faculty): ?>
                                                <li>Fakultas tidak sesuai (<?php echo htmlspecialchars($app['fakultas']); ?> ≠ <?php echo htmlspecialchars($app['required_fakultas']); ?>)</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-2 p-2 bg-success-light border border-success rounded" style="background-color: #d4edda;">
                                        <small style="color: #155724;"><strong>✓ Memenuhi semua persyaratan</strong></small>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($app['is_recommended']): ?>
                                    <br><span class="badge bg-info mt-2">Direkomendasikan DPA</span>
                                <?php endif; ?>
                                
                                <div class="mt-3 d-flex gap-2">
                                    <a href="view_applicant.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-info flex-grow-1">
                                        Detail
                                    </a>
                                    <?php if ($app['status'] == 'pending'): ?>
                                        <button type="button" class="btn btn-sm btn-success" 
                                            onclick="acceptApplication(<?php echo $app['id']; ?>)">
                                            Terima
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="rejectApplication(<?php echo $app['id']; ?>)">
                                            Tolak
                                        </button>
                                    <?php elseif ($app['status'] == 'accepted'): ?>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="rejectApplication(<?php echo $app['id']; ?>)">
                                            Batalkan
                                        </button>
                                    <?php elseif ($app['status'] == 'rejected'): ?>
                                        <button type="button" class="btn btn-sm btn-success" 
                                            onclick="acceptApplication(<?php echo $app['id']; ?>)">
                                            Pertimbang
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile;
            else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <p class="text-muted">Belum ada pendaftar untuk postingan ini.</p>
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
</script>

</body>
</html>
