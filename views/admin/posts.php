<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

onlyAdmin();

// Get all posts (for moderation)
$query = "SELECT p.*, u.email as mitra_email, m.nama_organisasi
          FROM peluang p
          JOIN users u ON p.mitra_id = u.id
          LEFT JOIN mitra m ON u.id = m.user_id
          ORDER BY p.created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$posts = [];

while ($row = mysqli_fetch_assoc($result)) {
    $posts[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Postingan - Admin</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">

</head>

<body>

<div class="d-flex">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Kelola Postingan</h4>
                <small>
                    Login sebagai: <?php echo htmlspecialchars($_SESSION['email']); ?> (admin)
                </small>
            </div>
            <a href="<?= BASE_URL ?>/views/dashboard.php" class="btn btn-secondary">Kembali</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <hr>

        <div class="mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="form-control" 
                        placeholder="Cari judul atau mitra..."
                    >
                </div>
                <div class="col-md-4">
                    <select id="typeFilter" class="form-select">
                        <option value="">Semua Tipe</option>
                        <option value="magang">Magang</option>
                        <option value="kursus">Kursus</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
        </div>

        <?php if (empty($posts)): ?>
            <div class="alert alert-info">
                Belum ada postingan di sistem.
            </div>
        <?php else: ?>
            <div id="postsList">
                <?php foreach ($posts as $post): ?>
                    <?php
                    $current_date = date('Y-m-d H:i:s');
                    $is_manually_closed = !empty($post['closed_at']);
                    $is_deadline_passed = $post['deadline'] < $current_date;
                    $is_post_open = !$is_manually_closed && !$is_deadline_passed;
                    $status = $is_post_open ? 'Terbuka' : 'Ditutup';
                    $status_badge = $is_post_open ? 'bg-success' : 'bg-secondary';
                    $approval_status = ucfirst($post['status']);
                    $approval_badge = $post['status'] == 'approved' ? 'bg-success' : ($post['status'] == 'pending' ? 'bg-warning' : 'bg-danger');
                    
                    // Determine row class based on deactivation status or approval status
                    $row_class = $is_manually_closed ? 'deactivated' : $post['status'];
                    $post_type = ucfirst(htmlspecialchars($post['tipe']));
                    $post_type_badge = $post['tipe'] == 'magang' ? 'bg-primary' : 'bg-info';
                    ?>
                    <div class="post-row <?= $row_class; ?>" data-type="<?= $post['tipe']; ?>" data-status="<?= $post['status']; ?>">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h5 class="mb-2">
                                    <?php echo htmlspecialchars($post['judul']); ?>
                                </h5>

                                <div class="mb-2">
                                    <span class="badge <?= $post_type_badge; ?> status-badge me-2">
                                        <?php echo $post_type; ?>
                                    </span>
                                    <span class="badge <?= $approval_badge; ?> status-badge me-2">
                                        <?php echo $approval_status; ?>
                                    </span>
                                    <span class="badge <?= $status_badge; ?> status-badge">
                                        <?php echo $status; ?>
                                    </span>
                                </div>

                                <small class="text-muted d-block">
                                    <strong>Mitra:</strong> <?php echo htmlspecialchars($post['nama_organisasi'] ?? 'Unknown'); ?>
                                </small>
                                <small class="text-muted d-block">
                                    <strong>Email:</strong> <?php echo htmlspecialchars($post['mitra_email']); ?>
                                </small>
                                <small class="text-muted d-block">
                                    <strong>Deskripsi:</strong> <?php echo htmlspecialchars(substr($post['deskripsi'], 0, 100)); ?>...
                                </small>
                                <small class="text-muted d-block">
                                    <strong>Dibuat:</strong> <?php echo $post['created_at']; ?> | 
                                    <strong>Deadline:</strong> <?php echo $post['deadline']; ?>
                                </small>
                            </div>

                            <div class="col-md-5 text-end">
                                <a href="<?= BASE_URL ?>/views/posts/detail.php?id=<?php echo $post['id']; ?>" 
                                   class="btn btn-info btn-sm me-2"
                                   target="_blank">
                                    <i class="bi bi-eye"></i> Lihat Detail
                                </a>

                                <?php if ($is_manually_closed): ?>
                                    <button type="button" 
                                            class="btn btn-success btn-sm"
                                            onclick="reactivatePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['judul'])); ?>')">
                                        <i class="bi bi-arrow-clockwise"></i> Aktifkan Kembali
                                    </button>
                                <?php else: ?>
                                    <button type="button" 
                                            class="btn btn-danger btn-sm"
                                            onclick="deactivatePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['judul'])); ?>')">
                                        <i class="bi bi-trash"></i> Nonaktifkan
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="noResults" class="alert alert-warning mt-3" style="display: none;">
                Tidak ada postingan yang sesuai dengan pencarian Anda.
            </div>
        <?php endif; ?>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Combined filtering function
function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const posts = document.querySelectorAll('.post-row');
    let visibleCount = 0;

    posts.forEach(post => {
        const text = post.textContent.toLowerCase();
        const postType = post.getAttribute('data-type');
        const postStatus = post.getAttribute('data-status');

        // Check search term match
        const matchesSearch = text.includes(searchTerm);
        
        // Check type filter
        const matchesType = !typeFilter || postType === typeFilter;
        
        // Check status filter
        const matchesStatus = !statusFilter || postStatus === statusFilter;

        // Show post only if all filters match
        if (matchesSearch && matchesType && matchesStatus) {
            post.style.display = 'block';
            visibleCount++;
        } else {
            post.style.display = 'none';
        }
    });

    document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
}

// Add event listeners to all filters
document.getElementById('searchInput').addEventListener('keyup', applyFilters);
document.getElementById('typeFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);

// Deactivate post function
function deactivatePost(postId, postTitle) {
    if (confirm(`Apakah Anda yakin ingin menonaktifkan postingan "${postTitle}"?`)) {
        window.location.href = `<?= BASE_URL ?>/controllers/admin/deactivate_post_process.php?id=${postId}`;
    }
}

// Reactivate post function
function reactivatePost(postId, postTitle) {
    if (confirm(`Apakah Anda yakin ingin mengaktifkan kembali postingan "${postTitle}"?`)) {
        window.location.href = `<?= BASE_URL ?>/controllers/admin/reactivate_post_process.php?id=${postId}`;
    }
}
</script>

</body>
</html>
