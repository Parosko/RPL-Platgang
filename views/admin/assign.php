<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

onlyAdmin();

// Get all DPAs with their student count
$query = "SELECT d.*, u.email, COUNT(m.id) as total_students
          FROM dpa d
          JOIN users u ON d.user_id = u.id
          LEFT JOIN mahasiswa m ON d.id = m.dpa_id
          GROUP BY d.id
          ORDER BY d.nama ASC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$dpas = [];

while ($row = mysqli_fetch_assoc($result)) {
    $dpas[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola DPA - Admin</title>

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
                <h1 class="page-title">Kelola DPA</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-person-badge-fill me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Kelola semua DPA di sistem.</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="text-center px-3 py-2" style="background: var(--info-light); border-radius: var(--radius-lg); border: 1px solid var(--border-base);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total DPA</div>
                    <div class="fw-bold" style="color: var(--info); font-size: 1.25rem;"><?php echo count($dpas); ?></div>
                </div>
                <div class="text-center px-3 py-2" style="background: var(--success-light); border-radius: var(--radius-lg); border: 1px solid var(--success-border);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Mahasiswa</div>
                    <div class="fw-bold" style="color: var(--success); font-size: 1.25rem;"><?php echo array_sum(array_column($dpas, 'total_students')); ?></div>
                </div>
                <div class="text-center px-3 py-2" style="background: var(--warning-light); border-radius: var(--radius-lg); border: 1px solid var(--warning-border);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Rata-rata</div>
                    <div class="fw-bold" style="color: var(--warning); font-size: 1.25rem;"><?php echo count($dpas) > 0 ? round(array_sum(array_column($dpas, 'total_students')) / count($dpas), 1) : 0; ?></div>
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

        <!-- Search Section -->
        <div class="mb-4">
            <input 
                type="text" 
                id="searchInput" 
                class="form-control" 
                placeholder="Cari DPA berdasarkan nama atau email..."
            >
        </div>

        <?php if (empty($dpas)): ?>
            <div class="admin-empty-state text-center p-5">
                <i class="bi bi-person-badge empty-icon mb-3"></i>
                <h5 class="mb-2">Belum Ada DPA</h5>
                <p class="text-muted mb-0">Saat ini belum ada DPA di sistem.</p>
            </div>
        <?php else: ?>
            <div class="admin-profile-card">
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="30%">Nama DPA</th>
                                <th width="25%">Email</th>
                                <th width="15%" class="text-center">Total Mahasiswa</th>
                                <th width="30%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="dpaList">
                            <?php foreach ($dpas as $dpa): ?>
                                <tr class="dpa-row" data-search="<?php echo htmlspecialchars(strtolower($dpa['nama'] . ' ' . $dpa['email'])); ?>">
                                    <td class="nama fw-semibold" style="color: #0F172A;">
                                        <?php echo htmlspecialchars($dpa['nama'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="email text-muted">
                                        <?php echo htmlspecialchars($dpa['email']); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-status status-info">
                                            <?php echo $dpa['total_students']; ?> Mahasiswa
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="dpa_detail.php?id=<?php echo $dpa['id']; ?>" 
                                           class="btn btn-navy btn-sm me-2">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                        <a href="assign_mahasiswa.php?dpa_id=<?php echo $dpa['id']; ?>" 
                                           class="btn btn-success btn-sm">
                                            <i class="bi bi-plus-circle"></i> Tugaskan
                                        </a>
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
                <p class="text-muted mb-0">Tidak ada DPA yang sesuai dengan pencarian Anda.</p>
            </div>
        <?php endif; ?>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.dpa-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const searchText = row.getAttribute('data-search');
        if (searchText.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
});


</script>

</body>
</html>
