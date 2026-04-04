<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMitra();

$user_id = $_SESSION['user_id'];

// Get all applications to mitra's postings
$query = "SELECT l.id, l.tanggal_apply, l.status, l.is_recommended, COALESCE(l.result_published, 0) as result_published,
                 p.id as peluang_id, p.judul, p.tipe,
                 m.nim, m.nama as mahasiswa_nama, m.fakultas, m.prodi, m.ipk, m.semester,
                 u.email
          FROM lamaran l
          JOIN peluang p ON l.peluang_id = p.id
          JOIN mahasiswa m ON l.mahasiswa_id = m.id
          JOIN users u ON m.user_id = u.id
          WHERE p.mitra_id = ?
          ORDER BY l.tanggal_apply DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$applications = mysqli_stmt_get_result($stmt);

// Get posts with unpublished results for the release button
$query_unpublished = "SELECT DISTINCT p.id, p.judul, COUNT(l.id) as unpublished_count
                      FROM peluang p
                      LEFT JOIN lamaran l ON p.id = l.peluang_id AND l.result_published = 0 AND l.status IN ('accepted', 'rejected')
                      WHERE p.mitra_id = ? 
                      GROUP BY p.id, p.judul
                      HAVING unpublished_count > 0
                      ORDER BY p.judul";
$stmt_unpub = mysqli_prepare($conn, $query_unpublished);
mysqli_stmt_bind_param($stmt_unpub, 'i', $user_id);
mysqli_stmt_execute($stmt_unpub);
$unpublished_posts = mysqli_stmt_get_result($stmt_unpub);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Lamaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <style>
        .card-status {
            text-align: right;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .badge-recommended {
            background-color: #FFD700;
            color: #000;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Kelola Lamaran</h4>
                <small>
                    Login sebagai: <?php echo htmlspecialchars($_SESSION['email']); ?> (mitra)
                </small>
            </div>
            <a href="../dashboard.php" class="btn btn-secondary">Kembali</a>
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

        <!-- Filter and Search -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <select class="form-select" id="filterStatus" onchange="filterApplications()">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="accepted">Diterima</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="filterPost" onchange="filterApplications()">
                            <option value="">Semua Postingan</option>
                            <?php
                            $posts_query = "SELECT DISTINCT p.id, p.judul FROM peluang p 
                                          WHERE p.mitra_id = ? ORDER BY p.judul";
                            $posts_stmt = mysqli_prepare($conn, $posts_query);
                            mysqli_stmt_bind_param($posts_stmt, 'i', $user_id);
                            mysqli_stmt_execute($posts_stmt);
                            $posts_result = mysqli_stmt_get_result($posts_stmt);
                            while ($post = mysqli_fetch_assoc($posts_result)): ?>
                                <option value="<?php echo $post['id']; ?>">
                                    <?php echo htmlspecialchars($post['judul']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="filterName" placeholder="Cari nama mahasiswa..." onkeyup="filterApplications()">
                    </div>
                </div>
            </div>
        </div>

        <!-- Unreleased Results Alert -->
        <?php 
        mysqli_data_seek($unpublished_posts, 0);
        if (mysqli_num_rows($unpublished_posts) > 0): 
        ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <strong>Anda memiliki hasil evaluasi yang belum dirilis!</strong>
                <div class="mt-2">
                    <?php while ($post = mysqli_fetch_assoc($unpublished_posts)): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><?php echo htmlspecialchars($post['judul']); ?> 
                                <strong>(<?php echo $post['unpublished_count']; ?> hasil)</strong></span>
                            <button type="button" class="btn btn-sm btn-success" 
                                onclick="releaseResults(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['judul']); ?>')">
                                Release Result
                            </button>
                        </div>
                    <?php endwhile; ?>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Applications Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Postingan</th>
                            <th>Tipe</th>
                            <th>IPK / Semester</th>
                            <th>Tanggal Apply</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($applications) > 0):
                            mysqli_data_seek($applications, 0);
                            while ($app = mysqli_fetch_assoc($applications)): ?>
                                <tr class="application-row" data-status="<?php echo $app['status']; ?>" 
                                    data-post="<?php echo $app['peluang_id']; ?>" 
                                    data-name="<?php echo strtolower($app['mahasiswa_nama']); ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($app['mahasiswa_nama']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['email']); ?></small><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['nim']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($app['judul']); ?></td>
                                    <td><?php echo ucfirst($app['tipe']); ?></td>
                                    <td>
                                        IPK: <?php echo $app['ipk']; ?><br>
                                        Semester: <?php echo $app['semester']; ?>
                                    </td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($app['tanggal_apply'])); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php echo ($app['status'] == 'accepted') ? 'bg-success' : 
                                                   (($app['status'] == 'rejected') ? 'bg-danger' : 'bg-warning'); ?>">
                                            <?php 
                                            $display_status = ucfirst($app['status']);
                                            if ($app['status'] != 'pending' && $app['result_published'] == 0) {
                                                $display_status = 'Draft: ' . $display_status;
                                            }
                                            echo $display_status;
                                            ?>
                                        </span>
                                        <?php if ($app['is_recommended']): ?>
                                            <br><span class="badge badge-recommended">Direkomendasikan DPA</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="view_applicant.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-info">Detail</a>
                                            <?php if ($app['status'] == 'pending'): ?>
                                                <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="acceptApplication(<?php echo $app['id']; ?>)">Terima</button>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="rejectApplication(<?php echo $app['id']; ?>)">Tolak</button>
                                            <?php elseif ($app['status'] == 'accepted'): ?>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="rejectApplication(<?php echo $app['id']; ?>)">Batalkan</button>
                                            <?php elseif ($app['status'] == 'rejected'): ?>
                                                <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="acceptApplication(<?php echo $app['id']; ?>)">Pertimbangkan</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p>Belum ada lamaran untuk postingan Anda.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function filterApplications() {
    const status = document.getElementById('filterStatus').value.toLowerCase();
    const post = document.getElementById('filterPost').value.toLowerCase();
    const name = document.getElementById('filterName').value.toLowerCase();
    
    const rows = document.querySelectorAll('.application-row');
    rows.forEach(row => {
        const rowStatus = row.dataset.status.toLowerCase();
        const rowPost = row.dataset.post.toLowerCase();
        const rowName = row.dataset.name.toLowerCase();
        
        const statusMatch = !status || rowStatus === status;
        const postMatch = !post || rowPost === post;
        const nameMatch = !name || rowName.includes(name);
        
        row.style.display = (statusMatch && postMatch && nameMatch) ? '' : 'none';
    });
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

function releaseResults(peluangId, postTitle) {
    if (confirm('Apakah Anda yakin ingin merilis hasil evaluasi untuk "' + postTitle + '"? Notifikasi akan dikirim ke semua pelamar.')) {
        fetch('../../controllers/mitra/manage_application_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=release_results&peluang_id=' + peluangId
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
