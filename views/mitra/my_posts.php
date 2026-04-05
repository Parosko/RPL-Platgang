<?php
session_start();
include '../../core/middleware.php';
include '../../config/database.php';
include '../../config/config.php';

onlyMitra();

$mitra_id = $_SESSION['user_id'];

// Handle sorting
$sort_by = $_GET['sort'] ?? 'created_at_desc';
$sort_column = 'created_at';
$sort_direction = 'DESC';

switch($sort_by) {
    case 'created_at_asc':
        $sort_column = 'created_at';
        $sort_direction = 'ASC';
        break;
    case 'deadline_asc':
        $sort_column = 'deadline';
        $sort_direction = 'ASC';
        break;
    case 'deadline_desc':
        $sort_column = 'deadline';
        $sort_direction = 'DESC';
        break;
    case 'title_asc':
        $sort_column = 'judul';
        $sort_direction = 'ASC';
        break;
    case 'title_desc':
        $sort_column = 'judul';
        $sort_direction = 'DESC';
        break;
    case 'applicants_desc':
        $sort_column = 'applicant_count';
        $sort_direction = 'DESC';
        break;
    case 'applicants_asc':
        $sort_column = 'applicant_count';
        $sort_direction = 'ASC';
        break;
}

// Ambil postingan milik mitra + count applications
$stmt = $conn->prepare("
    SELECT peluang.*, users.email AS nama_mitra, COUNT(lamaran.id) as applicant_count
    FROM peluang
    LEFT JOIN lamaran ON peluang.id = lamaran.peluang_id
    JOIN users ON peluang.mitra_id = users.id
    WHERE peluang.mitra_id = ?
    GROUP BY peluang.id
    ORDER BY peluang.{$sort_column} {$sort_direction}
");
$stmt->bind_param("i", $mitra_id);
$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);

// Determine which posts are open/closed
foreach ($posts as &$post) {
    $current_date = date('Y-m-d H:i:s');
    $is_closed_manually = !empty($post['closed_at']);
    $is_deadline_passed = $post['deadline'] < $current_date;
    $post['is_closed'] = $is_closed_manually || $is_deadline_passed;
    $post['close_reason'] = $is_closed_manually ? 'manual' : ($is_deadline_passed ? 'deadline' : '');
}

$role = $_SESSION['role'];
$email = $_SESSION['email'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Postingan Saya</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

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

        <!-- Sorting Controls -->
        <div class="mb-4">
            <div class="d-flex align-items-center gap-3">
                <label class="form-label mb-0 fw-semibold">Urutkan:</label>
                <select class="form-select form-select-sm" style="width: auto;" onchange="sortPosts(this.value)">
                    <option value="created_at_desc" <?= $sort_by == 'created_at_desc' ? 'selected' : '' ?>>Terbaru</option>
                    <option value="created_at_asc" <?= $sort_by == 'created_at_asc' ? 'selected' : '' ?>>Terlama</option>
                    <option value="deadline_asc" <?= $sort_by == 'deadline_asc' ? 'selected' : '' ?>>Deadline Terdekat</option>
                    <option value="deadline_desc" <?= $sort_by == 'deadline_desc' ? 'selected' : '' ?>>Deadline Terjauh</option>
                    <option value="title_asc" <?= $sort_by == 'title_asc' ? 'selected' : '' ?>>Judul A-Z</option>
                    <option value="title_desc" <?= $sort_by == 'title_desc' ? 'selected' : '' ?>>Judul Z-A</option>
                    <option value="applicants_desc" <?= $sort_by == 'applicants_desc' ? 'selected' : '' ?>>Pendaftar Terbanyak</option>
                    <option value="applicants_asc" <?= $sort_by == 'applicants_asc' ? 'selected' : '' ?>>Pendaftar Tersedikit</option>
                </select>
            </div>
        </div>

        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                Kamu belum memiliki postingan.
            </div>
        <?php else: ?>

            <?php foreach ($posts as $post): ?>
                <?php $show_close_button = true; ?>
                <?php include '../components/mitra_post_card.php'; ?>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>

<script>
function sortPosts(sortValue) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}
</script>

</body>
</html>