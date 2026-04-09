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
                <h1 class="page-title">Kelola User</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-people-fill me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Kelola semua pengguna di sistem.</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="text-center px-3 py-2" style="background: var(--info-light); border-radius: var(--radius-lg); border: 1px solid var(--border-base);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total User</div>
                    <div class="fw-bold" style="color: var(--info); font-size: 1.25rem;"><?php echo count($users); ?></div>
                </div>
                <div class="text-center px-3 py-2" style="background: var(--success-light); border-radius: var(--radius-lg); border: 1px solid var(--success-border);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Active</div>
                    <div class="fw-bold" style="color: var(--success); font-size: 1.25rem;"><?php echo count(array_filter($users, fn($u) => $u['status'] == 'active')); ?></div>
                </div>
                <div class="text-center px-3 py-2" style="background: var(--warning-light); border-radius: var(--radius-lg); border: 1px solid var(--warning-border);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Pending</div>
                    <div class="fw-bold" style="color: var(--warning); font-size: 1.25rem;"><?php echo count(array_filter($users, fn($u) => $u['status'] == 'pending')); ?></div>
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
            <div class="admin-empty-state text-center p-5">
                <i class="bi bi-people empty-icon mb-3"></i>
                <h5 class="mb-2">Belum Ada User</h5>
                <p class="text-muted mb-0">Saat ini belum ada pengguna di sistem.</p>
            </div>
        <?php else: ?>
            <div class="admin-profile-card">
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="25%">Nama</th>
                                <th width="20%">Email</th>
                                <th width="10%">Role</th>
                                <th width="10%">Status</th>
                                <th width="15%">Terdaftar</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <?php
                                $role = ucfirst($user['role']);
                                $status = ucfirst($user['status']);
                                
                                // Determine badge classes
                                $role_class = $user['role'] == 'mahasiswa' ? 'status-info' : ($user['role'] == 'mitra' ? 'status-warning' : 'status-success');
                                $status_class = $user['status'] == 'active' ? 'status-success' : ($user['status'] == 'pending' ? 'status-warning' : 'status-danger');
                                
                                // Determine row class based on user status
                                $row_class = $user['status'] == 'inactive' ? 'deactivated' : $user['status'];
                                ?>
                                <tr class="user-row <?= $row_class; ?>" data-role="<?= $user['role']; ?>" data-status="<?= $user['status']; ?>">
                                    <td class="nama fw-semibold" style="color: #0F172A;">
                                        <?php echo htmlspecialchars($user['full_name'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="email text-muted">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </td>
                                    <td>
                                        <span class="badge-status <?= $role_class; ?>">
                                            <?php echo $role; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status <?= $status_class; ?>">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                    <td class="tanggal text-muted">
                                        <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                                    </td>
                                    <td class="text-center">
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
                <p class="text-muted mb-0">Tidak ada user yang sesuai dengan pencarian Anda.</p>
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
