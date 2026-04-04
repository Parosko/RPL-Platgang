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
<html>
<head>
    <title>Riwayat Lamaran</title>
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
                <h4>Riwayat Lamaran</h4>
                <small>
                    Login sebagai: <?php echo htmlspecialchars($_SESSION['email']); ?> (mahasiswa)
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

        <div class="row">
            <?php while ($app = mysqli_fetch_assoc($applications)): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6><?php echo htmlspecialchars($app['judul']); ?></h6>
                                <span class="badge <?php 
                                    // Show pending if results not published, else show actual status
                                    if ($app['result_published'] == 0) {
                                        echo 'bg-warning';
                                        $display_status = 'Pending';
                                    } else {
                                        $display_status = ucfirst($app['status']);
                                        echo ($app['status'] == 'accepted') ? 'bg-success' : 'bg-danger';
                                    }
                                ?>">
                                    <?php echo $display_status; ?>
                                </span>
                            </div>
                            <small class="text-muted">
                                Oleh: <?php echo htmlspecialchars($app['nama_organisasi'] ?? $app['mitra_email']); ?><br>
                                Tipe: <?php echo htmlspecialchars($app['tipe']); ?> | Lokasi: <?php echo htmlspecialchars($app['lokasi'] ?? 'Tidak ditentukan'); ?><br>
                                Deadline: <?php echo $app['deadline']; ?> | Apply: <?php echo $app['tanggal_apply']; ?>
                            </small>
                            <p class="mt-2"><?php echo substr(htmlspecialchars($app['deskripsi']), 0, 100) . '...'; ?></p>
                            <a href="already_applied.php?id=<?php echo $app['id']; ?>" class="btn btn-primary btn-sm">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if (mysqli_num_rows($applications) == 0): ?>
            <div class="text-center mt-5">
                <p>Anda belum mendaftar ke peluang apapun.</p>
                <a href="../dashboard.php" class="btn btn-primary">Cari Peluang</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>