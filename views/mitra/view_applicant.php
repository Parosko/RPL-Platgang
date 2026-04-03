<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMitra();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: applications.php');
    exit;
}

$lamaran_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get application details
$query = "SELECT l.id, l.tanggal_apply, l.status, l.is_recommended,
                 p.id as peluang_id, p.judul, p.deskripsi, p.tipe, p.lokasi, p.kuota, p.min_ipk, p.min_semester, p.fakultas as required_fakultas, p.deadline,
                 m.nim, m.nama, m.fakultas as applicant_fakultas, m.prodi, m.ipk, m.semester, m.user_id as mahasiswa_user_id,
                 u.email
          FROM lamaran l
          JOIN peluang p ON l.peluang_id = p.id
          JOIN mahasiswa m ON l.mahasiswa_id = m.id
          JOIN users u ON m.user_id = u.id
          WHERE l.id = ? AND p.mitra_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $lamaran_id, $user_id);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    Login sebagai: <?php echo htmlspecialchars($_SESSION['email']); ?> (mitra)
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
                        <small class="text-muted">Email: <?php echo htmlspecialchars($application['email']); ?></small>
                        <hr>
                        <strong>Fakultas:</strong> <?php echo htmlspecialchars($application['applicant_fakultas']); ?><br>
                        <strong>Prodi:</strong> <?php echo htmlspecialchars($application['prodi']); ?><br>
                        <strong>IPK:</strong> <?php echo $application['ipk']; ?><br>
                        <strong>Semester:</strong> <?php echo $application['semester']; ?>
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
                                <strong>Kuota:</strong> <?php echo $application['kuota']; ?><br>
                                <strong>Min IPK:</strong> <?php echo $application['min_ipk']; ?><br>
                                <strong>Min Semester:</strong> <?php echo $application['min_semester']; ?><br>
                                <strong>Fakultas:</strong> <?php echo !empty($application['required_fakultas']) ? htmlspecialchars($application['required_fakultas']) : 'Semua Fakultas'; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Deadline:</strong> <?php echo date('d-m-Y H:i', strtotime($application['deadline'])); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="card mb-3">
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

                <!-- Requirements vs Applicant Comparison -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h6>Persyaratan vs Data Mahasiswa</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>IPK</h6>
                                <p>
                                    <strong>Persyaratan:</strong> ≥ <?php echo $application['min_ipk']; ?><br>
                                    <strong>Mahasiswa:</strong> 
                                    <span class="<?php echo ($application['ipk'] >= $application['min_ipk']) ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $application['ipk']; ?>
                                        <?php if ($application['ipk'] < $application['min_ipk']): ?>
                                            <i class="fas fa-exclamation-triangle"></i> Tidak memenuhi
                                        <?php else: ?>
                                            <i class="fas fa-check-circle"></i> Memenuhi
                                        <?php endif; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6>Semester</h6>
                                <p>
                                    <strong>Persyaratan:</strong> ≥ <?php echo $application['min_semester']; ?><br>
                                    <strong>Mahasiswa:</strong> 
                                    <span class="<?php echo ($application['semester'] >= $application['min_semester']) ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo $application['semester']; ?>
                                        <?php if ($application['semester'] < $application['min_semester']): ?>
                                            <i class="fas fa-exclamation-triangle"></i> Tidak memenuhi
                                        <?php else: ?>
                                            <i class="fas fa-check-circle"></i> Memenuhi
                                        <?php endif; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Fakultas</h6>
                                <p>
                                    <strong>Persyaratan:</strong> <?php echo !empty($application['required_fakultas']) ? htmlspecialchars($application['required_fakultas']) : 'Semua Fakultas'; ?><br>
                                    <strong>Mahasiswa:</strong> 
                                    <span class="<?php echo (empty($application['required_fakultas']) || $application['required_fakultas'] == $application['applicant_fakultas']) ? 'text-success' : 'text-danger'; ?>">
                                        <?php echo htmlspecialchars($application['applicant_fakultas']); ?>
                                        <?php if (!empty($application['required_fakultas']) && $application['required_fakultas'] != $application['applicant_fakultas']): ?>
                                            <i class="fas fa-exclamation-triangle"></i> Tidak memenuhi
                                        <?php else: ?>
                                            <i class="fas fa-check-circle"></i> Memenuhi
                                        <?php endif; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6>Prodi</h6>
                                <p>
                                    <strong>Persyaratan:</strong> Tidak ada persyaratan spesifik<br>
                                    <strong>Mahasiswa:</strong> <?php echo htmlspecialchars($application['prodi']); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <?php if ($application['status'] == 'pending'): ?>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" onclick="acceptApplication(<?php echo $lamaran_id; ?>)">
                            Terima Lamaran
                        </button>
                        <button class="btn btn-danger" onclick="rejectApplication(<?php echo $lamaran_id; ?>)">
                            Tolak Lamaran
                        </button>
                    </div>
                <?php elseif ($application['status'] == 'accepted'): ?>
                    <div class="d-flex gap-2">
                        <button class="btn btn-warning" onclick="rejectApplication(<?php echo $lamaran_id; ?>)">
                            Batalkan Penerimaan
                        </button>
                    </div>
                <?php elseif ($application['status'] == 'rejected'): ?>
                    <div class="d-flex gap-2">
                        <button class="btn btn-success" onclick="acceptApplication(<?php echo $lamaran_id; ?>)">
                            Pertimbangkan Kembali
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
