<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

checkLogin();

$role = $_SESSION['role'];
$email = $_SESSION['email'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../dashboard.php');
    exit;
}

$post_id = (int)$_GET['id'];

$query = "SELECT p.*, u.email as mitra_email, m.nama_organisasi
          FROM peluang p
          JOIN users u ON p.mitra_id = u.id
          LEFT JOIN mitra m ON u.id = m.user_id
          WHERE p.id = ? AND p.status = 'approved'";
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
        $mahasiswa_id = $mahasiswa['id'];
        
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
<html>
<head>
    <title>Detail Peluang - <?php echo htmlspecialchars($post['judul']); ?></title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Detail Peluang</h4>
                <small>
                    Login sebagai: <?php echo htmlspecialchars($email); ?> (<?php echo $role; ?>)
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

        <hr>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5><?php echo htmlspecialchars($post['judul']); ?></h5>
                        <small class="text-muted">
                            Oleh: <?php echo htmlspecialchars($post['nama_organisasi'] ?? $post['mitra_email']); ?>
                        </small>
                    </div>
                    <span class="badge <?php echo ($status == 'Open') ? 'bg-success' : 'bg-secondary'; ?>">
                        <?php echo $status; ?>
                    </span>
                </div>

                <p class="mb-3"><?php echo nl2br(htmlspecialchars($post['deskripsi'])); ?></p>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Tipe:</strong> <?php echo htmlspecialchars($post['tipe']); ?><br>
                        <strong>Lokasi:</strong> <?php echo htmlspecialchars($post['lokasi'] ?? 'Tidak ditentukan'); ?><br>
                        <strong>Kuota:</strong> <?php echo $post['kuota']; ?><br>
                    </div>
                    <div class="col-md-6">
                        <strong>Min. IPK:</strong> <?php echo $post['min_ipk']; ?><br>
                        <strong>Min. Semester:</strong> <?php echo $post['min_semester']; ?><br>
                        <strong>Fakultas:</strong> <?php echo htmlspecialchars($post['fakultas'] ?? 'Semua'); ?><br>
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Dibuat:</strong> <?php echo $post['created_at']; ?><br>
                    <strong>Deadline:</strong> <?php echo $post['deadline']; ?>
                </div>

                <div class="d-flex gap-2">
                    <?php if ($role == 'mahasiswa' && $status == 'Open' && !$already_applied): ?>
                        <a href="../../controllers/mahasiswa/apply_process.php?id=<?php echo $post['id']; ?>" 
                           class="btn btn-primary"
                           onclick="return confirm('Apakah Anda yakin ingin mendaftar untuk peluang ini?')">
                            Daftar
                        </a>
                    <?php elseif ($role == 'mahasiswa' && $already_applied): ?>
                        <button class="btn btn-success" disabled>Sudah Terdaftar</button>
                    <?php elseif ($role == 'dpa' && $status == 'Open'): ?>
                        <button class="btn btn-warning">Rekomendasikan</button>
                    <?php elseif ($role == 'admin'): ?>
                        <button class="btn btn-danger">Hapus</button>
                    <?php elseif ($role == 'mitra' && $post['mitra_id'] == $_SESSION['user_id']): ?>
                        <a href="../mitra/applicants.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">
                            Lihat Pendaftar
                        </a>
                        <button class="btn btn-secondary">Edit</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>