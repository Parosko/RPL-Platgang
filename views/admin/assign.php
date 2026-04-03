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
                <h4>Kelola DPA</h4>
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
            <input 
                type="text" 
                id="searchInput" 
                class="form-control" 
                placeholder="Cari DPA berdasarkan nama atau email..."
            >
        </div>

        <?php if (empty($dpas)): ?>
            <div class="alert alert-info">
                Belum ada DPA di sistem.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Nama DPA</th>
                            <th>Email</th>
                            <th class="text-center">Total Mahasiswa</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dpaList">
                        <?php foreach ($dpas as $dpa): ?>
                            <tr class="dpa-row" data-search="<?php echo htmlspecialchars(strtolower($dpa['nama'] . ' ' . $dpa['email'])); ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($dpa['nama'] ?? 'N/A'); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($dpa['email']); ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">
                                        <?php echo $dpa['total_students']; ?> Mahasiswa
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/views/admin/dpa_detail.php?id=<?php echo $dpa['id']; ?>" 
                                       class="btn btn-info btn-sm me-2">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    <a href="<?= BASE_URL ?>/views/admin/assign_mahasiswa.php?dpa_id=<?php echo $dpa['id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-circle"></i> Tugaskan
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="noResults" class="alert alert-warning mt-3" style="display: none;">
                Tidak ada DPA yang sesuai dengan pencarian Anda.
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
