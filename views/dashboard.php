<?php
session_start();

include __DIR__ . '/../core/middleware.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../config/config.php';
include __DIR__ . '/../core/profile_check.php';

checkLogin();
redirectIfProfileIncomplete($conn, __FILE__);

$role = $_SESSION['role'];
$email = $_SESSION['email'];

$query = "SELECT p.*, 
                 (SELECT COUNT(*) FROM lamaran l WHERE l.peluang_id = p.id) as applicant_count,
                 m.nama_organisasi as nama_mitra
          FROM peluang p 
          LEFT JOIN mitra m ON p.mitra_id = m.user_id
          WHERE p.status = 'approved' AND p.closed_at IS NULL 
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

$posts = [];

while ($row = mysqli_fetch_assoc($result)) {
    $posts[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/layouts/sidebar.php'; ?>

    <div class="content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Dashboard</h4>
                <small>
                    Login sebagai: 
                    <?php echo htmlspecialchars($email); ?> 
                    (<?php echo $role; ?>)
                </small>
            </div>
        </div>

        <hr>

        <h5 class="mb-3">Daftar Peluang</h5>

        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                Belum ada postingan tersedia.
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <?php include __DIR__ . '/components/post_card.php'; ?>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

</div>

<script>
// Deactivate post function (for admin)
function deactivatePost(postId, postTitle) {
    if (confirm(`Apakah Anda yakin ingin menonaktifkan postingan "${postTitle}"?`)) {
        window.location.href = `<?= BASE_URL ?>/controllers/admin/deactivate_post_process.php?id=${postId}`;
    }
}
</script>

</body>
</html>