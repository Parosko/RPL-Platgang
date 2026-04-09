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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">

</head>

<body>

<div class="d-flex">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Kelola Postingan</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-file-text-fill me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Kelola semua postingan di sistem.</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="text-center px-3 py-2" style="background: var(--info-light); border-radius: var(--radius-lg); border: 1px solid var(--border-base);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Post</div>
                    <div class="fw-bold" style="color: var(--info); font-size: 1.25rem;"><?php echo count($posts); ?></div>
                </div>
                <div class="text-center px-3 py-2" style="background: var(--success-light); border-radius: var(--radius-lg); border: 1px solid var(--success-border);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Approved</div>
                    <div class="fw-bold" style="color: var(--success); font-size: 1.25rem;"><?php echo count(array_filter($posts, fn($p) => $p['status'] == 'approved')); ?></div>
                </div>
                <div class="text-center px-3 py-2" style="background: var(--warning-light); border-radius: var(--radius-lg); border: 1px solid var(--warning-border);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Pending</div>
                    <div class="fw-bold" style="color: var(--warning); font-size: 1.25rem;"><?php echo count(array_filter($posts, fn($p) => $p['status'] == 'pending')); ?></div>
                </div>
            </div>
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

        <!-- Filters Section -->
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
            <div class="admin-empty-state text-center p-5">
                <i class="bi bi-file-text empty-icon mb-3"></i>
                <h5 class="mb-2">Belum Ada Postingan</h5>
                <p class="text-muted mb-0">Saat ini belum ada postingan di sistem.</p>
            </div>
        <?php else: ?>
            <div class="admin-profile-card">
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="25%">Judul</th>
                                <th width="15%">Mitra</th>
                                <th width="10%">Tipe</th>
                                <th width="10%">Status</th>
                                <th width="15%">Deadline</th>
                                <th width="25%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <?php
                                $current_date = date('Y-m-d H:i:s');
                                $is_manually_closed = !empty($post['closed_at']);
                                $is_deadline_passed = $post['deadline'] < $current_date;
                                $is_post_open = !$is_manually_closed && !$is_deadline_passed;
                                $status = $is_post_open ? 'Terbuka' : 'Ditutup';
                                $approval_status = ucfirst($post['status']);
                                
                                // Determine badge classes
                                $type_class = $post['tipe'] == 'magang' ? 'status-info' : 'status-warning';
                                $approval_class = $post['status'] == 'approved' ? 'status-success' : ($post['status'] == 'pending' ? 'status-warning' : 'status-danger');
                                $status_class = $is_post_open ? 'status-success' : 'status-secondary';
                                
                                // Determine row class based on deactivation status or approval status
                                $row_class = $is_manually_closed ? 'deactivated' : $post['status'];
                                ?>
                                <tr class="post-row <?= $row_class; ?>" data-type="<?= $post['tipe']; ?>" data-status="<?= $post['status']; ?>">
                                    <td class="judul fw-semibold" style="color: #0F172A;">
                                        <div>
                                            <?php echo htmlspecialchars($post['judul']); ?>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <?php echo htmlspecialchars(substr($post['deskripsi'], 0, 60)); ?>...
                                        </small>
                                    </td>
                                    <td class="mitra text-muted">
                                        <div>
                                            <strong><?php echo htmlspecialchars($post['nama_organisasi'] ?? 'Unknown'); ?></strong>
                                        </div>
                                        <small class="text-muted d-block">
                                            <?php echo htmlspecialchars($post['mitra_email']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge-status <?= $type_class; ?>">
                                            <?php echo ucfirst(htmlspecialchars($post['tipe'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status <?= $approval_class; ?>">
                                            <?php echo $approval_status; ?>
                                        </span>
                                        <div class="mt-1">
                                            <span class="badge-status <?= $status_class; ?>">
                                                <?php echo $status; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="deadline text-muted">
                                        <?php echo date('d M Y', strtotime($post['deadline'])); ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="../posts/detail.php?id=<?php echo $post['id']; ?>&back=admin_posts" 
                                           class="btn btn-navy btn-sm me-2"
                                           target="_blank">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>

                                        <?php if ($is_manually_closed): ?>
                                            <button type="button" 
                                                    class="btn btn-success btn-sm"
                                                    onclick="reactivatePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['judul'])); ?>')">
                                                <i class="bi bi-arrow-clockwise"></i> Aktifkan
                                            </button>
                                        <?php else: ?>
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm"
                                                    onclick="deactivatePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['judul'])); ?>')">
                                                <i class="bi bi-trash"></i> Nonaktifkan
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="noResults" class="admin-empty-state text-center p-5 mt-3" style="display: none;">
                <i class="bi bi-search empty-icon mb-3"></i>
                <h5 class="mb-2">Tidak Ada Hasil</h5>
                <p class="text-muted mb-0">Tidak ada postingan yang sesuai dengan pencarian Anda.</p>
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
