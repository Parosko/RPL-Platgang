<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

onlyAdmin();

// Get all users (excluding admins)
$query = "SELECT u.*, 
                 CASE WHEN m.id IS NOT NULL THEN m.nama WHEN mt.id IS NOT NULL THEN mt.nama_organisasi WHEN d.id IS NOT NULL THEN d.nama END as full_name
          FROM users u
          LEFT JOIN mahasiswa m ON u.id = m.user_id
          LEFT JOIN mitra mt ON u.id = mt.user_id
          LEFT JOIN dpa d ON u.id = d.user_id
          WHERE u.role != 'admin'
          ORDER BY u.created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$users = [];

while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola User - Admin</title>

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
                <h4>Kelola User</h4>
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
                        placeholder="Cari email atau nama..."
                    >
                </div>
                <div class="col-md-4">
                    <select id="roleFilter" class="form-select">
                        <option value="">Semua Role</option>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="mitra">Mitra</option>
                        <option value="dpa">DPA</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="statusFilter" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        <?php if (empty($users)): ?>
            <div class="alert alert-info">
                Belum ada user di sistem.
            </div>
        <?php else: ?>
            <div id="usersList">
                <?php foreach ($users as $user): ?>
                    <?php
                    $role = ucfirst($user['role']);
                    $role_badge = $user['role'] == 'mahasiswa' ? 'bg-primary' : ($user['role'] == 'mitra' ? 'bg-warning' : 'bg-info');
                    $status = ucfirst($user['status']);
                    $status_badge = $user['status'] == 'active' ? 'bg-success' : ($user['status'] == 'pending' ? 'bg-warning' : 'bg-danger');
                    
                    // Determine row class based on user status
                    $row_class = $user['status'] == 'inactive' ? 'deactivated' : $user['status'];
                    ?>
                    <div class="user-row <?= $row_class; ?>" data-role="<?= $user['role']; ?>" data-status="<?= $user['status']; ?>">
                        <div class="row align-items-center">
                            <div class="col-md-7">
                                <h5 class="mb-2">
                                    <?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?>
                                </h5>

                                <div class="mb-2">
                                    <span class="badge <?= $role_badge; ?> status-badge me-2">
                                        <?php echo $role; ?>
                                    </span>
                                    <span class="badge <?= $status_badge; ?> status-badge">
                                        <?php echo $status; ?>
                                    </span>
                                </div>

                                <small class="text-muted d-block">
                                    <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                                </small>
                                <small class="text-muted d-block">
                                    <strong>Terdaftar:</strong> <?php echo $user['created_at']; ?>
                                </small>
                            </div>

                            <div class="col-md-5 text-end">
                                <?php if ($user['status'] == 'pending'): ?>
                                    <button type="button" 
                                            class="btn btn-success btn-sm"
                                            onclick="verifyUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['email'])); ?>')">
                                        <i class="bi bi-check-circle"></i> Verifikasi
                                    </button>
                                <?php elseif ($user['status'] == 'inactive'): ?>
                                    <button type="button" 
                                            class="btn btn-success btn-sm"
                                            onclick="reactivateUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['email'])); ?>')">
                                        <i class="bi bi-arrow-clockwise"></i> Aktifkan Kembali
                                    </button>
                                <?php else: ?>
                                    <button type="button" 
                                            class="btn btn-danger btn-sm"
                                            onclick="deactivateUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars(addslashes($user['email'])); ?>')">
                                        <i class="bi bi-trash"></i> Nonaktifkan
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="noResults" class="alert alert-warning mt-3" style="display: none;">
                Tidak ada user yang sesuai dengan pencarian Anda.
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
    const roleFilter = document.getElementById('roleFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const users = document.querySelectorAll('.user-row');
    let visibleCount = 0;

    users.forEach(user => {
        const text = user.textContent.toLowerCase();
        const userRole = user.getAttribute('data-role');
        const userStatus = user.getAttribute('data-status');

        // Check search term match
        const matchesSearch = text.includes(searchTerm);
        
        // Check role filter
        const matchesRole = !roleFilter || userRole === roleFilter;
        
        // Check status filter
        const matchesStatus = !statusFilter || userStatus === statusFilter;

        // Show user only if all filters match
        if (matchesSearch && matchesRole && matchesStatus) {
            user.style.display = 'block';
            visibleCount++;
        } else {
            user.style.display = 'none';
        }
    });

    document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
}

// Add event listeners to all filters
document.getElementById('searchInput').addEventListener('keyup', applyFilters);
document.getElementById('roleFilter').addEventListener('change', applyFilters);
document.getElementById('statusFilter').addEventListener('change', applyFilters);

// Verify user account (change status from pending to active)
function verifyUser(userId, userEmail) {
    if (confirm(`Apakah Anda yakin ingin memverifikasi akun user "${userEmail}"?`)) {
        window.location.href = `<?= BASE_URL ?>/controllers/admin/verify_user_process.php?id=${userId}`;
    }
}

// Deactivate user function
function deactivateUser(userId, userEmail) {
    if (confirm(`Apakah Anda yakin ingin menonaktifkan akun user "${userEmail}"?`)) {
        window.location.href = `<?= BASE_URL ?>/controllers/admin/deactivate_user_process.php?id=${userId}`;
    }
}

// Reactivate user function
function reactivateUser(userId, userEmail) {
    if (confirm(`Apakah Anda yakin ingin mengaktifkan kembali akun user "${userEmail}"?`)) {
        window.location.href = `<?= BASE_URL ?>/controllers/admin/reactivate_user_process.php?id=${userId}`;
    }
}
</script>

</body>
</html>
