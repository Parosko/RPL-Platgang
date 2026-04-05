<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyDPA();

$user_id = $_SESSION['user_id'];

$query = "SELECT d.*, u.email FROM dpa d JOIN users u ON d.user_id = u.id WHERE d.user_id = ?";
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
    <title>Profil DPA</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Profil DPA</h4>
                <small><?php echo htmlspecialchars($_SESSION['email']); ?></small>
            </div>
        </div>

        <hr>

        <div class="card">
            <div class="card-body">
                <h5>Informasi DPA</h5>
                <div class="mb-3">
                    <strong>Nama:</strong> <?php echo htmlspecialchars($profile['nama'] ?? 'Belum diisi'); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?><br>
                </div>
                <a href="edit_profile.php" class="btn btn-primary">Edit Profil</a>
            </div>
        </div>

    </div>

</div>

</body>
</html>