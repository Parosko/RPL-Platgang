<?php
session_start();

include __DIR__ . '/../core/middleware.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../config/config.php';
include __DIR__ . '/../core/profile_check.php';

checkLogin();
redirectIfProfileIncomplete($conn, __FILE__);

$role = $_SESSION['role'];

$search_data = $_SESSION['search_results'] ?? [
    'posts' => [],
    'total_posts' => 0,
    'total_pages' => 0,
    'current_page' => 1,
    'search_query' => '',
    'status_filter' => '',
    'tipe_filter' => '',
    'sort_by' => 'created_at_desc',
    'available_mitras' => []
];

$posts = $search_data['posts'];
$total_posts = $search_data['total_posts'];
$total_pages = $search_data['total_pages'];
$current_page = $search_data['current_page'];
$search_query = $search_data['search_query'];
$status_filter = $search_data['status_filter'];
$tipe_filter = $search_data['tipe_filter'];
$sort_by = $search_data['sort_by'];

unset($_SESSION['search_results']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian | Sistem Peluang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css"> <link rel="stylesheet" href="../assets/css/search.css"> </head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/layouts/sidebar.php'; ?>

    <div class="content">

        <div class="page-header">
            <h1 class="page-title">Pencarian</h1>
            <div class="page-subtitle mt-2 d-flex align-items-center">
                <i class="bi bi-search me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                <span class="text-body">Eksplorasi dan temukan peluang yang paling sesuai untuk Anda.</span>
            </div>
        </div>

        <div class="filter-section mb-4">
            <form method="GET" action="<?= BASE_URL ?>/controllers/search/search_process.php" class="row g-3">
                <div class="col-md-4 col-lg-3">
                    <label for="search_query" class="form-label custom-label">Kata Kunci</label>
                    <div class="input-group custom-input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" 
                            class="form-control" 
                            id="search_query" 
                            name="q" 
                            placeholder="Cari posisi, institusi, nama mitra, atau keahlian..."
                            value="<?= htmlspecialchars($search_query) ?>">
                    </div>
                </div>
                
                <div class="col-md-4 col-lg-2">
                    <label for="status_filter" class="form-label custom-label">Status</label>
                    <select class="form-select custom-select" id="status_filter" name="status">
                        <option value="">Semua</option>
                        <option value="open" <?= $status_filter === 'open' ? 'selected' : '' ?>>Buka</option>
                        <option value="closed" <?= $status_filter === 'closed' ? 'selected' : '' ?>>Tutup</option>
                    </select>
                </div>
                
                <div class="col-md-4 col-lg-2">
                    <label for="tipe_filter" class="form-label custom-label">Tipe</label>
                    <select class="form-select custom-select" id="tipe_filter" name="tipe">
                        <option value="">Semua Tipe</option>
                        <option value="magang" <?= $tipe_filter === 'magang' ? 'selected' : '' ?>>Magang</option>
                        <option value="kursus" <?= $tipe_filter === 'kursus' ? 'selected' : '' ?>>Kursus</option>
                    </select>
                </div>
                
                <div class="col-md-4 col-lg-2">
                    <label for="sort_by" class="form-label custom-label">Urutkan Berdasarkan</label>
                    <select class="form-select custom-select" id="sort_by" name="sort">
                        <option value="created_at_desc" <?= $sort_by === 'created_at_desc' ? 'selected' : '' ?>>Terbaru</option>
                        <option value="created_at_asc" <?= $sort_by === 'created_at_asc' ? 'selected' : '' ?>>Terlama</option>
                        <option value="deadline_asc" <?= $sort_by === 'deadline_asc' ? 'selected' : '' ?>>Deadline Terdekat</option>
                        <option value="deadline_desc" <?= $sort_by === 'deadline_desc' ? 'selected' : '' ?>>Deadline Terjauh</option>
                    </select>
                </div>
                
                <div class="col-12 mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-navy">
                        <i class="bi bi-search me-1"></i> Cari
                    </button>
                    <button type="submit" class="btn btn-action-primary">
                        <i class="bi bi-funnel me-1"></i> Terapkan Filter
                    </button>
                    <a href="<?= BASE_URL ?>/views/search.php" class="btn btn-action-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <?php if (!empty($search_query) || !empty($status_filter) || !empty($tipe_filter)): ?>
            <div class="search-summary-alert mb-4">
                <i class="bi bi-check2-circle"></i>
                <div class="summary-text">
                    Menampilkan <strong><?= $total_posts ?></strong> hasil pencarian
                    <?php if (!empty($search_query)): ?>
                        untuk "<strong><?= htmlspecialchars($search_query) ?></strong>"
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="post-container">
            <?php if (empty($posts)): ?>
                <div class="alert-empty-state">
                    <i class="bi bi-search"></i>
                    <div>
                        <strong>Tidak ada hasil ditemukan</strong>
                        <span>Kami tidak dapat menemukan peluang yang cocok. Coba sesuaikan kata kunci atau bersihkan filter Anda.</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($posts as $post): ?>
                        <div class="col-12">
                            <?php include __DIR__ . '/components/post_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="enterprise-pagination mt-5">
            <nav aria-label="Navigasi halaman pencarian">
                <ul class="pagination justify-content-center">
                    <?php
                    $current_params = $_GET;
                    unset($current_params['page']);
                    $query_string = http_build_query($current_params);
                    ?>
                    
                    <?php if ($current_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= $query_string ?>&page=<?= $current_page - 1 ?>">
                                <i class="bi bi-chevron-left me-1"></i> Sebelumnya
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    if ($start_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?' . $query_string . '&page=1">1</a></li>';
                        if ($start_page > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    for ($i = $start_page; $i <= $end_page; $i++) {
                        $active_class = $i == $current_page ? 'active' : '';
                        echo '<li class="page-item ' . $active_class . '">
                                <a class="page-link" href="?' . $query_string . '&page=' . $i . '">' . $i . '</a>
                            </li>';
                    }
                    
                    if ($end_page < $total_pages) {
                        if ($end_page < $total_pages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?' . $query_string . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
                    }
                    ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= $query_string ?>&page=<?= $current_page + 1 ?>">
                                Selanjutnya <i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>