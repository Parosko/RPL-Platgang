<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyAdmin();

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$profile = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil Admin</title>

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
                <h4>Profil Admin</h4>
                <small><?php echo htmlspecialchars($_SESSION['email']); ?></small>
            </div>
        </div>

        <hr>

        <div class="card">
            <div class="card-body">
                <h5>Informasi Admin</h5>
                <div class="mb-3">
                    <strong>Email:</strong> <?php echo htmlspecialchars($profile['email']); ?><br>
                    <strong>Role:</strong> <?php echo htmlspecialchars($profile['role']); ?><br>
                    <strong>Status:</strong> <?php echo htmlspecialchars($profile['status']); ?><br>
                    <strong>Bergabung:</strong> <?php echo $profile['created_at']; ?><br>
                </div>
                <a href="edit_profile.php" class="btn btn-primary">Edit Profil</a>
            </div>
        </div>

    </div>

</div>

</body>
</html>