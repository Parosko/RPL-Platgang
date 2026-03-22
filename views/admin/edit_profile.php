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

if (mysqli_num_rows($result) == 0) {
    header('Location: ../dashboard.php');
    exit;
}

$profile = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profil Admin</title>
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
                <h4>Edit Profil Admin</h4>
                <small><?php echo htmlspecialchars($_SESSION['email']); ?></small>
            </div>
            <a href="../admin/profile.php" class="btn btn-secondary">Kembali</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post" action="../../controllers/admin/edit_profile_process.php">
                    <div class="mb-3">
                        <label class="form-label">Email (tidak bisa diubah)</label>
                        <input class="form-control" type="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <input class="form-control" type="text" value="<?php echo htmlspecialchars($profile['role'] ?? ''); ?>" readonly>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>