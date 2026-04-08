<?php
session_start();
include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postingan Saya | Sistem Peluang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/mitra.css">
</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Postingan Saya</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-card-list me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Kelola dan pantau peluang yang telah Anda publikasikan.</span>
                </div>
            </div>
            <a href="create_post.php" class="btn btn-navy px-4">
                <i class="bi bi-plus-lg me-2"></i>Buat Postingan Baru
            </a>
        </div>

        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded border mb-4">
            <div class="d-flex align-items-center gap-3 w-100">
                <i class="bi bi-filter text-muted fs-5 d-none d-sm-block"></i>
                <label class="form-label mb-0 fw-medium text-secondary d-none d-sm-block">Urutkan Berdasarkan:</label>
                <select class="form-select form-select-sm border-0 shadow-sm" style="max-width: 250px;" onchange="sortPosts(this.value)">
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
            <div class="text-muted small text-nowrap ms-3">
                Total: <strong><?= count($posts) ?></strong> peluang
            </div>
        </div>

        <?php if (empty($posts)): ?>
            <div class="mitra-empty-state text-center py-5">
                <i class="bi bi-inbox empty-icon mb-3 d-block"></i>
                <h5 class="mb-2">Belum ada postingan</h5>
                <p class="text-muted mb-4">Anda belum mempublikasikan peluang magang atau kursus apa pun.</p>
                <a href="create_post.php" class="btn btn-navy">
                    <i class="bi bi-plus-lg me-2"></i>Buat Peluang Pertama
                </a>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($posts as $post): ?>
                    <?php $show_close_button = true; ?>
                    <?php include __DIR__ . '/../components/mitra_post_card.php'; ?>
                <?php endforeach; ?>
            </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>