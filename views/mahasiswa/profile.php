<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMahasiswa();

$user_id = $_SESSION['user_id'];

$query = "SELECT m.*, u.email FROM mahasiswa m JOIN users u ON m.user_id = u.id WHERE m.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    // Redirect to complete profile or something
    header('Location: ../../views/dashboard.php');
    exit;
}

$profile = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil Mahasiswa</title>

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
                <h4>Profil Mahasiswa</h4>
                <small><?php echo htmlspecialchars($_SESSION['email']); ?></small>
            </div>
        </div>

        <hr>

        <div class="card">
            <div class="card-body">
                <h5>Informasi Pribadi</h5>
                <div class="row">
                    <div class="col-md-6">
                        <strong>NIM:</strong> <?php echo htmlspecialchars($profile['nim'] ?? 'Belum diisi'); ?><br>
                        <strong>Nama:</strong> <?php echo htmlspecialchars($profile['nama'] ?? 'Belum diisi'); ?><br>
                        <strong>Fakultas:</strong> <?php echo htmlspecialchars($profile['fakultas'] ?? 'Belum diisi'); ?><br>
                    </div>
                    <div class="col-md-6">
                        <strong>Prodi:</strong> <?php echo htmlspecialchars($profile['prodi'] ?? 'Belum diisi'); ?><br>
                        <strong>Angkatan:</strong> <?php echo $profile['angkatan'] ?? 'Belum diisi'; ?><br>
                        <strong>Semester:</strong> <?php echo $profile['semester'] ?? 'Belum diisi'; ?><br>
                        <strong>IPK:</strong> <?php echo $profile['ipk'] ?? 'Belum diisi'; ?><br>
                    </div>
                </div>
                <button class="btn btn-primary mt-3">Edit Profil</button>
            </div>
        </div>

    </div>

</div>

</body>
</html>