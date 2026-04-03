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
$query = "SELECT l.*, p.judul, p.deskripsi, p.tipe, p.lokasi, p.kuota, p.min_ipk, p.min_semester, p.fakultas, p.deadline, p.created_at, u.email as mitra_email, m.nama_organisasi
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
<html>
<head>
    <title>Status Lamaran - <?php echo htmlspecialchars($application['judul']); ?></title>
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
                <h4>Status Lamaran</h4>
                <small>
                    Login sebagai: <?php echo htmlspecialchars($_SESSION['email']); ?> (mahasiswa)
                </small>
            </div>
            <a href="../posts/detail.php?id=<?php echo $peluang_id; ?>" class="btn btn-secondary">Kembali</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($_SESSION['success']); ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5><?php echo htmlspecialchars($application['judul']); ?></h5>
                        <small class="text-muted">
                            Oleh: <?php echo htmlspecialchars($application['nama_organisasi'] ?? $application['mitra_email']); ?>
                        </small>
                    </div>
                    <span class="badge bg-info">Applied</span>
                </div>

                <p class="mb-3"><?php echo nl2br(htmlspecialchars($application['deskripsi'])); ?></p>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tipe:</strong> <?php echo htmlspecialchars($application['tipe']); ?><br>
                        <strong>Lokasi:</strong> <?php echo htmlspecialchars($application['lokasi'] ?? 'Tidak ditentukan'); ?><br>
                        <strong>Kuota:</strong> <?php echo $application['kuota']; ?><br>
                    </div>
                    <div class="col-md-6">
                        <strong>Min. IPK:</strong> <?php echo $application['min_ipk']; ?><br>
                        <strong>Min. Semester:</strong> <?php echo $application['min_semester']; ?><br>
                        <strong>Fakultas:</strong> <?php echo htmlspecialchars($application['fakultas'] ?? 'Semua'); ?><br>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Dibuat:</strong> <?php echo $application['created_at']; ?><br>
                    <strong>Deadline:</strong> <?php echo $application['deadline']; ?><br>
                    <strong>Tanggal Apply:</strong> <?php echo $application['tanggal_apply']; ?><br>
                    <strong>Status:</strong> 
                    <span class="badge <?php echo ($application['status'] == 'accepted') ? 'bg-success' : (($application['status'] == 'rejected') ? 'bg-danger' : 'bg-warning'); ?>">
                        <?php echo ucfirst($application['status']); ?>
                    </span>
                </div>

                <h6>Dokumen yang Diupload:</h6>
                <ul>
                    <?php while ($doc = mysqli_fetch_assoc($documents)): ?>
                        <li><?php echo htmlspecialchars($doc['jenis']); ?>: <a href="../../uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank">Lihat</a></li>
                    <?php endwhile; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

</body>
</html>