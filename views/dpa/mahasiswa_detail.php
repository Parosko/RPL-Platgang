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
$query = "SELECT l.id, l.status, l.tanggal_apply, p.judul, p.deskripsi, p.tipe, p.lokasi, p.deadline,
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

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/dpa.css">
</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Detail Mahasiswa: <?php echo htmlspecialchars($student['nama']); ?></h4>
                <small>
                    <a href="mahasiswa.php" class="back-link">← Kembali ke Daftar Mahasiswa</a>
                </small>
            </div>
        </div>

        <hr>

        <!-- Student Profile Section -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card student-info-card">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase">Informasi Mahasiswa</h6>
                        <div class="student-info">
                            <div class="info-item">
                                <span class="label">NIM</span>
                                <span class="value"><?php echo htmlspecialchars($student['nim'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Nama</span>
                                <span class="value"><?php echo htmlspecialchars($student['nama'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Email</span>
                                <span class="value"><?php echo htmlspecialchars($student['email'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Fakultas</span>
                                <span class="value"><?php echo htmlspecialchars($student['fakultas'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Program Studi</span>
                                <span class="value"><?php echo htmlspecialchars($student['prodi'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Semester</span>
                                <span class="value"><?php echo htmlspecialchars($student['semester'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">IPK</span>
                                <span class="value"><?php echo htmlspecialchars($student['ipk'] ?? '-'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="col-md-8">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number"><?php echo count($applications); ?></div>
                            <div class="stats-label">Total Lamaran</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card success">
                            <div class="stats-number"><?php echo $accepted_count; ?></div>
                            <div class="stats-label">Diterima</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card danger">
                            <div class="stats-number"><?php echo $rejected_count; ?></div>
                            <div class="stats-label">Ditolak</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="stats-card pending">
                            <div class="stats-number"><?php echo $pending_count; ?></div>
                            <div class="stats-label">Menunggu</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <!-- Applications Section -->
        <h5 class="mb-3">Daftar Lamaran</h5>

        <?php if (empty($applications)): ?>
            <div class="alert alert-info">
                Mahasiswa ini belum membuat lamaran apapun.
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
                                    $status_class = 'success';
                                    $status_label = 'Diterima';
                                } elseif ($app['status'] === 'rejected') {
                                    $status_class = 'danger';
                                    $status_label = 'Ditolak';
                                } else {
                                    $status_class = 'warning';
                                    $status_label = 'Menunggu';
                                }
                                ?>
                                <span class="status-badge status-<?php echo $status_class; ?>">
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
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>
