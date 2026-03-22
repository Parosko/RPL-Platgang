<?php
session_start();
include '../../core/middleware.php';
include '../../config/database.php';

onlyMitra();

$mitra_id = $_SESSION['user_id'];

// Ambil postingan milik mitra + join user
$stmt = $conn->prepare("
    SELECT peluang.*, users.email AS nama_mitra
    FROM peluang
    JOIN users ON peluang.mitra_id = users.id
    WHERE peluang.mitra_id = ?
    ORDER BY peluang.created_at DESC
");
$stmt->bind_param("i", $mitra_id);
$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);

$role = $_SESSION['role'];
$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Postingan Saya</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
</head>

<body>

<div class="d-flex">

    <?php include '../layouts/sidebar.php'; ?>

    <div class="content">

        <div class="mb-4">
            <h4>Postingan Saya</h4>
            <small>Daftar peluang yang sudah kamu buat</small>
        </div>

        <hr>

        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                Kamu belum memiliki postingan.
            </div>
        <?php else: ?>

            <?php foreach ($posts as $post): ?>
                <?php include '../components/post_card.php'; ?>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>

</body>
</html>