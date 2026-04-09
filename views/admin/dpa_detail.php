<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

onlyAdmin();

// Get DPA ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . '/views/admin/dpa.php');
    exit;
}

$dpa_id = (int)$_GET['id'];

// Get DPA information
$dpa_query = "SELECT d.*, u.email, u.created_at as user_created_at
              FROM dpa d
              JOIN users u ON d.user_id = u.id
              WHERE d.id = ?";
$dpa_stmt = mysqli_prepare($conn, $dpa_query);
mysqli_stmt_bind_param($dpa_stmt, 'i', $dpa_id);
mysqli_stmt_execute($dpa_stmt);
$dpa_result = mysqli_stmt_get_result($dpa_stmt);

if (mysqli_num_rows($dpa_result) == 0) {
    header('Location: ' . BASE_URL . '/views/admin/dpa.php');
    exit;
}

$dpa = mysqli_fetch_assoc($dpa_result);

// Get all mahasiswa assigned to this DPA
$mahasiswa_query = "SELECT m.*, u.email as mahasiswa_email
                    FROM mahasiswa m
                    JOIN users u ON m.user_id = u.id
                    WHERE m.dpa_id = ?
                    ORDER BY m.nama ASC";
$mahasiswa_stmt = mysqli_prepare($conn, $mahasiswa_query);
mysqli_stmt_bind_param($mahasiswa_stmt, 'i', $dpa_id);
mysqli_stmt_execute($mahasiswa_stmt);
$mahasiswa_result = mysqli_stmt_get_result($mahasiswa_stmt);
$mahasiswa_list = [];

while ($row = mysqli_fetch_assoc($mahasiswa_result)) {
    $mahasiswa_list[] = $row;
}

$total_mahasiswa = count($mahasiswa_list);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail DPA - <?php echo htmlspecialchars($dpa['nama']); ?></title>

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
                <h1 class="page-title">Detail DPA</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-person-badge-fill me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Melihat detail DPA: <?php echo htmlspecialchars($dpa['nama']); ?></span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="text-center px-3 py-2" style="background: var(--info-light); border-radius: var(--radius-lg); border: 1px solid var(--border-base);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Mahasiswa</div>
                    <div class="fw-bold" style="color: var(--info); font-size: 1.25rem;"><?php echo $total_mahasiswa; ?></div>
                </div>
                <a href="assign.php" class="btn btn-soft-outline">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
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

        <!-- DPA Profile Card -->
        <div class="admin-profile-card mb-4">
            <div class="dpa-profile-header p-4">
                <div class="d-flex align-items-center">
                    <div class="dpa-avatar me-3">
                        <?php echo strtoupper(substr($dpa['nama'] ?? 'D', 0, 1)); ?>
                    </div>
                    <div>
                        <h3 class="mb-1" style="color: #0F172A; font-weight: 600;">
                            <?php echo htmlspecialchars($dpa['nama'] ?? 'N/A'); ?>
                        </h3>
                        <span class="dpa-badge">Dosen Pembimbing Akademik</span>
                    </div>
                    <div class="ms-auto">
                        <a href="assign_mahasiswa.php?dpa_id=<?php echo $dpa['id']; ?>" 
                           class="btn btn-navy">
                            <i class="bi bi-plus-circle me-1"></i> Tugaskan Mahasiswa
                        </a>
                    </div>
                </div>
            </div>
            <div class="p-4">
                <div class="dpa-info-grid">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($dpa['email']); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Total Mahasiswa</div>
                            <div class="info-value">
                                <span class="badge-status status-info">
                                    <?php echo $total_mahasiswa; ?> Mahasiswa
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="bi bi-calendar"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Terdaftar Sejak</div>
                            <div class="info-value"><?php echo date('d M Y', strtotime($dpa['user_created_at'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mahasiswa List -->
        <div class="admin-profile-card">
            <div class="p-4 border-bottom">
                <h5 class="mb-0" style="color: #0F172A; font-weight: 600;">Daftar Mahasiswa (<?php echo $total_mahasiswa; ?>)</h5>
            </div>
            <div class="p-4">
                <?php if (empty($mahasiswa_list)): ?>
                    <div class="admin-empty-state text-center p-5">
                        <i class="bi bi-people empty-icon mb-3"></i>
                        <h5 class="mb-2">Belum Ada Mahasiswa</h5>
                        <p class="text-muted mb-0">Belum ada mahasiswa yang ditugaskan ke DPA ini.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table admin-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="12%">NIM</th>
                                    <th width="20%">Nama</th>
                                    <th width="15%">Fakultas</th>
                                    <th width="15%">Prodi</th>
                                    <th width="8%">IPK</th>
                                    <th width="10%">Semester</th>
                                    <th width="20%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mahasiswa_list as $mahasiswa): ?>
                                    <tr>
                                        <td class="nim text-muted fw-medium">
                                            <?php echo htmlspecialchars($mahasiswa['nim'] ?? 'N/A'); ?>
                                        </td>
                                        <td class="nama fw-semibold" style="color: #0F172A;">
                                            <?php echo htmlspecialchars($mahasiswa['nama']); ?>
                                        </td>
                                        <td class="text-muted">
                                            <?php echo htmlspecialchars($mahasiswa['fakultas'] ?? 'N/A'); ?>
                                        </td>
                                        <td class="text-muted">
                                            <?php echo htmlspecialchars($mahasiswa['prodi'] ?? 'N/A'); ?>
                                        </td>
                                        <td>
                                            <span class="badge-status status-open">
                                                <i class="bi bi-star-fill me-1" style="font-size: 0.7rem;"></i> 
                                                <?php echo htmlspecialchars($mahasiswa['ipk'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted">
                                            <?php echo $mahasiswa['semester'] ?? 'N/A'; ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm"
                                                    onclick="unassignMahasiswa(<?php echo $mahasiswa['id']; ?>, '<?php echo htmlspecialchars(addslashes($mahasiswa['nama'])); ?>', '<?php echo htmlspecialchars(addslashes($dpa['nama'])); ?>')">
                                                <i class="bi bi-person-dash"></i> Lepas
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Unassign mahasiswa function
function unassignMahasiswa(mahasiswaId, mahasiswaName, dpaName) {
    if (confirm(`Apakah Anda yakin ingin melepas "${mahasiswaName}" dari bimbingan "${dpaName}"?`)) {
        window.location.href = '<?= BASE_URL ?>/controllers/admin/unassign_mahasiswa_process.php?mahasiswa_id=' + mahasiswaId + '&dpa_id=' + <?php echo $dpa['id']; ?>;
    }
}
</script>

</body>
</html>
