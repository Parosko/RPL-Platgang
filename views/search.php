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
    'mitra_filter' => '',
    'sort_by' => 'created_at_desc',
    'available_mitras' => []
];

$posts = $search_data['posts'];
$total_posts = $search_data['total_posts'];
$total_pages = $search_data['total_pages'];
$current_page = $search_data['current_page'];
$search_query = $search_data['search_query'];
$status_filter = $search_data['status_filter'];
$mitra_filter = $search_data['mitra_filter'];
$sort_by = $search_data['sort_by'];
$available_mitras = $search_data['available_mitras'];

unset($_SESSION['search_results']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pencarian - Platform Karir Kampus</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
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
                <h4>Pencarian</h4>
                <small>
                    Login sebagai: 
                    <?php echo htmlspecialchars($_SESSION['email']); ?> 
                    (<?php echo $role; ?>)
                </small>
            </div>
        </div>

        <hr>

                    <!-- Search Form -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form method="GET" action="<?= BASE_URL ?>/controllers/search/search_process.php" class="row g-3">
                                        <div class="col-md-6">
                                            <label for="search_query" class="form-label">Cari Postingan</label>
                                            <div class="input-group">
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="search_query" 
                                                       name="q" 
                                                       placeholder="Masukkan kata kunci..."
                                                       value="<?= htmlspecialchars($search_query) ?>">
                                                <button class="btn btn-primary" type="submit">
                                                    <i class="bi bi-search"></i> Cari
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label for="status_filter" class="form-label">Status</label>
                                            <select class="form-select" id="status_filter" name="status">
                                                <option value="">Semua Status</option>
                                                <option value="open" <?= $status_filter === 'open' ? 'selected' : '' ?>>Buka</option>
                                                <option value="closed" <?= $status_filter === 'closed' ? 'selected' : '' ?>>Tutup</option>
                                            </select>
                                        </div>
                                        
                                        <div class="col-md-3">
                                            <label for="sort_by" class="form-label">Urutkan</label>
                                            <select class="form-select" id="sort_by" name="sort">
                                                <option value="created_at_desc" <?= $sort_by === 'created_at_desc' ? 'selected' : '' ?>>Terbaru</option>
                                                <option value="created_at_asc" <?= $sort_by === 'created_at_asc' ? 'selected' : '' ?>>Terlama</option>
                                                <option value="deadline_asc" <?= $sort_by === 'deadline_asc' ? 'selected' : '' ?>>Deadline Terdekat</option>
                                                <option value="deadline_desc" <?= $sort_by === 'deadline_desc' ? 'selected' : '' ?>>Deadline Terjauh</option>
                                                <option value="applicant_count_desc" <?= $sort_by === 'applicant_count_desc' ? 'selected' : '' ?>>Paling Banyak Dilamar</option>
                                            </select>
                                        </div>
                                        
                                        <?php if (!empty($available_mitras)): ?>
                                        <div class="col-md-6">
                                            <label for="mitra_filter" class="form-label">Mitra</label>
                                            <select class="form-select" id="mitra_filter" name="mitra">
                                                <option value="">Semua Mitra</option>
                                                <?php foreach ($available_mitras as $mitra): ?>
                                                    <option value="<?= htmlspecialchars($mitra) ?>" <?= $mitra_filter === $mitra ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($mitra) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary me-2">
                                                <i class="bi bi-funnel"></i> Terapkan Filter
                                            </button>
                                            <a href="<?= BASE_URL ?>/views/search.php" class="btn btn-outline-secondary">
                                                <i class="bi bi-x-circle"></i> Reset
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Summary -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <?php if (!empty($search_query) || !empty($status_filter) || !empty($mitra_filter)): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Menampilkan <strong><?= $total_posts ?></strong> hasil pencarian
                                    <?php if (!empty($search_query)): ?>
                                        untuk "<strong><?= htmlspecialchars($search_query) ?></strong>"
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Search Results -->
                    <div class="row">
                        <div class="col-12">
                            <?php if (empty($posts)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-search display-1 text-muted"></i>
                                    <h4 class="mt-3 text-muted">Tidak ada hasil ditemukan</h4>
                                    <p class="text-muted">Coba ubah kata kunci atau filter pencarian Anda</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($posts as $post): ?>
                                    <?php include __DIR__ . '/components/post_card.php'; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <div class="row mt-4">
                        <div class="col-12">
                            <nav aria-label="Search results pagination">
                                <ul class="pagination justify-content-center">
                                    <?php
                                    $current_params = $_GET;
                                    unset($current_params['page']);
                                    $query_string = http_build_query($current_params);
                                    ?>
                                    
                                    <?php if ($current_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?= $query_string ?>&page=<?= $current_page - 1 ?>">
                                                <i class="bi bi-chevron-left"></i> Sebelumnya
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
                                                Selanjutnya <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
