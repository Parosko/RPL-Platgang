<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMitra();

$user_id = $_SESSION['user_id'];

$query = "SELECT m.*, u.email FROM mitra m JOIN users u ON m.user_id = u.id WHERE m.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    // Redirect to complete profile
    header('Location: ../../views/dashboard.php');
    exit;
}

$profile = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil Mitra</title>

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
                <h4>Profil Mitra</h4>
                <small><?php echo htmlspecialchars($_SESSION['email']); ?></small>
            </div>
        </div>

        <hr>

        <div class="card">
            <div class="card-body">
                <h5>Informasi Organisasi</h5>
                <div class="mb-3">
                    <strong>Nama Organisasi:</strong> <?php echo htmlspecialchars($profile['nama_organisasi'] ?? 'Belum diisi'); ?><br>
                    <strong>Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($profile['deskripsi'] ?? 'Belum diisi')); ?><br>
                    <strong>Kontak:</strong> <?php echo htmlspecialchars($profile['kontak'] ?? 'Belum diisi'); ?><br>
                    <strong>Status Verifikasi:</strong>
                    <span class="badge <?php echo ($profile['status_verifikasi'] == 'approved') ? 'bg-success' : 'bg-warning'; ?>">
                        <?php echo ucfirst($profile['status_verifikasi']); ?>
                    </span>
                </div>
                <button class="btn btn-primary">Edit Profil</button>
            </div>
        </div>

    </div>

</div>

</body>
</html>