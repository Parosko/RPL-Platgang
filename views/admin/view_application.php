<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: applications.php');
    exit;
}

$lamaran_id = (int)$_GET['id'];

// Get application details
$query = "SELECT l.id, l.tanggal_apply, l.status, l.is_recommended,
                 p.id as peluang_id, p.judul, p.deskripsi, p.tipe, p.lokasi, p.min_ipk, p.deadline,
                 m.nim, m.nama, m.fakultas, m.prodi, m.ipk, m.semester,
                 u.email as mahasiswa_email,
                 mt.nama_organisasi,
                 mu.email as mitra_email
          FROM lamaran l
          JOIN peluang p ON l.peluang_id = p.id
          JOIN mahasiswa m ON l.mahasiswa_id = m.id
          JOIN users u ON m.user_id = u.id
          JOIN mitra mt ON p.mitra_id = mt.user_id
          JOIN users mu ON mt.user_id = mu.id
          WHERE l.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $lamaran_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Lamaran tidak ditemukan.';
    header('Location: applications.php');
    exit;
}

$application = mysqli_fetch_assoc($result);

// Get documents
$query = "SELECT * FROM dokumen WHERE lamaran_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $lamaran_id);
mysqli_stmt_execute($stmt);
$documents = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Lamaran - <?php echo htmlspecialchars($application['nama']); ?></title>
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
                <h4>Detail Lamaran</h4>
                <small>
                    Login sebagai: <?php echo htmlspecialchars($_SESSION['email']); ?> (admin)
                </small>
            </div>
            <a href="applications.php" class="btn btn-secondary">Kembali</a>
        </div>

        <div class="row">
            <!-- Applicant Info -->
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h6>Data Mahasiswa</h6>
                    </div>
                    <div class="card-body">
                        <strong><?php echo htmlspecialchars($application['nama']); ?></strong><br>
                        <small class="text-muted">NIM: <?php echo htmlspecialchars($application['nim']); ?></small><br>
                        <small class="text-muted">Email: <?php echo htmlspecialchars($application['mahasiswa_email']); ?></small>
                        <hr>
                        <strong>Fakultas:</strong> <?php echo htmlspecialchars($application['fakultas']); ?><br>
                        <strong>Prodi:</strong> <?php echo htmlspecialchars($application['prodi']); ?><br>
                        <strong>IPK:</strong> <?php echo $application['ipk']; ?><br>
                        <strong>Semester:</strong> <?php echo $application['semester']; ?>
                    </div>
                </div>

                <!-- Mitra Info -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6>Data Organisasi</h6>
                    </div>
                    <div class="card-body">
                        <strong><?php echo htmlspecialchars($application['nama_organisasi']); ?></strong><br>
                        <small class="text-muted">Email: <?php echo htmlspecialchars($application['mitra_email']); ?></small>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6>Status Lamaran</h6>
                    </div>
                    <div class="card-body">
                        <span class="badge 
                            <?php echo ($application['status'] == 'accepted') ? 'bg-success' : 
                                   (($application['status'] == 'rejected') ? 'bg-danger' : 'bg-warning'); ?>"
                            style="font-size: 16px; padding: 10px;">
                            <?php echo ucfirst($application['status']); ?>
                        </span>
                        <br><br>
                        <small class="text-muted">
                            Tanggal Apply: <?php echo date('d-m-Y H:i', strtotime($application['tanggal_apply'])); ?>
                        </small>
                        <?php if ($application['is_recommended']): ?>
                            <br><span class="badge bg-warning text-dark mt-2">Direkomendasikan oleh DPA</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Post and Documents -->
            <div class="col-md-8">
                <!-- Post Details -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6>Detail Postingan</h6>
                    </div>
                    <div class="card-body">
                        <h6><?php echo htmlspecialchars($application['judul']); ?></h6>
                        <p><?php echo nl2br(htmlspecialchars($application['deskripsi'])); ?></p>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Tipe:</strong> <?php echo ucfirst($application['tipe']); ?><br>
                                <strong>Lokasi:</strong> <?php echo htmlspecialchars($application['lokasi']); ?><br>
                                <strong>Min IPK:</strong> <?php echo $application['min_ipk']; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Deadline:</strong> <?php echo $application['deadline']; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="card">
                    <div class="card-header">
                        <h6>Dokumen</h6>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($documents) > 0): ?>
                            <ul class="list-group">
                                <?php while ($doc = mysqli_fetch_assoc($documents)): ?>
                                    <li class="list-group-item">
                                        <strong><?php echo htmlspecialchars($doc['jenis']); ?></strong>
                                        <a href="../../uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                           target="_blank" class="btn btn-sm btn-primary float-end">
                                            Download
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted">Tidak ada dokumen yang diupload.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info Note -->
                <div class="alert alert-info mt-3">
                    <strong>Catatan Admin:</strong> Halaman ini hanya untuk review. Perubahan status dilakukan oleh organisasi penerima.
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
