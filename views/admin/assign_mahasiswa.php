<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

onlyAdmin();

// Get DPA ID from URL
if (!isset($_GET['dpa_id']) || !is_numeric($_GET['dpa_id'])) {
    $_SESSION['error'] = 'ID DPA tidak valid.';
    header('Location: ' . BASE_URL . '/views/admin/assign.php');
    exit;
}

$dpa_id = (int)$_GET['dpa_id'];

// Get DPA information
$dpa_query = "SELECT d.*, u.email 
              FROM dpa d
              JOIN users u ON d.user_id = u.id
              WHERE d.id = ?";
$dpa_stmt = mysqli_prepare($conn, $dpa_query);
mysqli_stmt_bind_param($dpa_stmt, 'i', $dpa_id);
mysqli_stmt_execute($dpa_stmt);
$dpa_result = mysqli_stmt_get_result($dpa_stmt);

if (mysqli_num_rows($dpa_result) == 0) {
    $_SESSION['error'] = 'DPA tidak ditemukan.';
    header('Location: ' . BASE_URL . '/views/admin/assign.php');
    exit;
}

$dpa = mysqli_fetch_assoc($dpa_result);

// Get filter and search parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query based on filters
$where_clause = "1=1";
if ($filter === 'assigned') {
    $where_clause = "m.dpa_id IS NOT NULL";
} elseif ($filter === 'not_assigned') {
    $where_clause = "m.dpa_id IS NULL";
}

if (!empty($search)) {
    $search_term = '%' . $search . '%';
    $where_clause .= " AND (m.nim LIKE ? OR m.nama LIKE ?)";
}

// Get mahasiswa list
$mahasiswa_query = "SELECT m.*, u.email as mahasiswa_email, 
                           CASE 
                               WHEN m.dpa_id IS NOT NULL THEN 'Sudah Ditugaskan'
                               ELSE 'Belum Ditugaskan'
                           END as assignment_status,
                           d.nama as assigned_dpa_name
                    FROM mahasiswa m
                    JOIN users u ON m.user_id = u.id
                    LEFT JOIN dpa d ON m.dpa_id = d.id
                    WHERE $where_clause
                    ORDER BY m.nama ASC";

$mahasiswa_stmt = mysqli_prepare($conn, $mahasiswa_query);

if (!empty($search)) {
    mysqli_stmt_bind_param($mahasiswa_stmt, 'ss', $search_term, $search_term);
}

mysqli_stmt_execute($mahasiswa_stmt);
$mahasiswa_result = mysqli_stmt_get_result($mahasiswa_stmt);
$mahasiswa_list = [];

while ($row = mysqli_fetch_assoc($mahasiswa_result)) {
    $mahasiswa_list[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tugaskan Mahasiswa ke DPA - <?php echo htmlspecialchars($dpa['nama']); ?></title>

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
                <h1 class="page-title">Tugaskan Mahasiswa ke DPA</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-person-plus-fill me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">DPA: <?php echo htmlspecialchars($dpa['nama']); ?></span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <?php
                $total_mahasiswa = count($mahasiswa_list);
                $assigned_count = count(array_filter($mahasiswa_list, fn($m) => $m['dpa_id'] !== null));
                $not_assigned_count = $total_mahasiswa - $assigned_count;
                ?>
                <div class="text-center px-3 py-2" style="background: var(--info-light); border-radius: var(--radius-lg); border: 1px solid var(--border-base);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total</div>
                    <div class="fw-bold" style="color: var(--info); font-size: 1.25rem;"><?php echo $total_mahasiswa; ?></div>
                </div>
                <div class="text-center px-3 py-2" style="background: var(--success-light); border-radius: var(--radius-lg); border: 1px solid var(--success-border);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Ditugaskan</div>
                    <div class="fw-bold" style="color: var(--success); font-size: 1.25rem;"><?php echo $assigned_count; ?></div>
                </div>
                <div class="text-center px-3 py-2" style="background: var(--warning-light); border-radius: var(--radius-lg); border: 1px solid var(--warning-border);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Belum</div>
                    <div class="fw-bold" style="color: var(--warning); font-size: 1.25rem;"><?php echo $not_assigned_count; ?></div>
                </div>
                <a href="dpa_detail.php?id=<?php echo $dpa_id; ?>" class="btn btn-soft-outline">
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

        <!-- Search and Filter Section -->
        <div class="mb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="form-control" 
                        placeholder="Cari berdasarkan NIM atau Nama Mahasiswa..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >
                </div>
                <div class="col-md-6">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-secondary filter-btn <?php echo $filter === '' ? 'active' : ''; ?>" onclick="filterMahasiswa('')">
                            <i class="bi bi-grid me-1"></i>Semua
                        </button>
                        <button type="button" class="btn btn-outline-secondary filter-btn <?php echo $filter === 'not_assigned' ? 'active' : ''; ?>" onclick="filterMahasiswa('not_assigned')">
                            <i class="bi bi-person-x me-1"></i>Belum Ditugaskan
                        </button>
                        <button type="button" class="btn btn-outline-secondary filter-btn <?php echo $filter === 'assigned' ? 'active' : ''; ?>" onclick="filterMahasiswa('assigned')">
                            <i class="bi bi-person-check me-1"></i>Sudah Ditugaskan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($mahasiswa_list)): ?>
            <div class="admin-empty-state text-center p-5">
                <i class="bi bi-people empty-icon mb-3"></i>
                <h5 class="mb-2">Tidak Ada Mahasiswa</h5>
                <p class="text-muted mb-0">Tidak ada mahasiswa yang sesuai dengan kriteria pencarian dan filter Anda.</p>
            </div>
        <?php else: ?>
            <div class="admin-profile-card">
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="12%">NIM</th>
                                <th width="18%">Nama</th>
                                <th width="12%">Fakultas</th>
                                <th width="12%">Prodi</th>
                                <th width="8%">Semester</th>
                                <th width="8%">IPK</th>
                                <th width="18%">Status</th>
                                <th width="12%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="mahasiswaList">
                            <?php foreach ($mahasiswa_list as $mahasiswa): ?>
                                <tr class="mahasiswa-row" 
                                    data-search="<?php echo htmlspecialchars(strtolower($mahasiswa['nim'] . ' ' . $mahasiswa['nama'])); ?>"
                                    data-status="<?php echo $mahasiswa['dpa_id'] ? 'assigned' : 'not_assigned'; ?>">
                                    <td class="nim text-muted fw-medium">
                                        <?php echo htmlspecialchars($mahasiswa['nim']); ?>
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
                                        <span class="badge-status status-info">
                                            <?php echo htmlspecialchars($mahasiswa['semester'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status status-open">
                                            <i class="bi bi-star-fill me-1" style="font-size: 0.7rem;"></i> 
                                            <?php echo htmlspecialchars((string)($mahasiswa['ipk'] ?? 'N/A')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($mahasiswa['dpa_id']): ?>
                                            <span class="badge-status status-success">
                                                <i class="bi bi-person-check me-1" style="font-size: 0.7rem;"></i>
                                                Ditugaskan ke: <?php echo htmlspecialchars($mahasiswa['assigned_dpa_name'] ?? 'N/A'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-status status-warning">
                                                <i class="bi bi-person-x me-1" style="font-size: 0.7rem;"></i>
                                                Belum Ditugaskan
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($mahasiswa['dpa_id'] !== $dpa_id): ?>
                                            <button type="button" 
                                                    class="btn btn-navy btn-sm"
                                                    onclick="assignMahasiswa(<?php echo $mahasiswa['id']; ?>, <?php echo $dpa_id; ?>, '<?php echo htmlspecialchars(addslashes($mahasiswa['nama'])); ?>')">
                                                <i class="bi bi-check-circle me-1"></i>Tugaskan
                                            </button>
                                        <?php else: ?>
                                            <span class="badge-status status-info">Sudah Ditugaskan</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div id="noResults" class="alert alert-warning mt-3" style="display: none;">
            Tidak ada mahasiswa yang sesuai dengan pencarian Anda.
        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
const currentDpaId = <?php echo $dpa_id; ?>;

// Filter mahasiswa
function filterMahasiswa(filterType) {
    const params = new URLSearchParams();
    params.set('dpa_id', currentDpaId);
    
    const searchInput = document.getElementById('searchInput').value;
    if (searchInput) {
        params.set('search', searchInput);
    }
    
    if (filterType) {
        params.set('filter', filterType);
    }
    
    window.location.href = '<?= BASE_URL ?>/views/admin/assign_mahasiswa.php?' + params.toString();
}

// Search mahasiswa
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.mahasiswa-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const searchText = row.getAttribute('data-search');
        const status = row.getAttribute('data-status');
        const filterType = '<?php echo $filter; ?>';
        
        let matchesSearch = searchText.includes(searchTerm);
        let matchesFilter = !filterType || status === filterType;
        
        if (matchesSearch && matchesFilter) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
});

// Assign mahasiswa to DPA
function assignMahasiswa(mahasiswaId, dpaId, mahasiswaName) {
    if (confirm(`Apakah Anda yakin ingin menugaskan "${mahasiswaName}" ke DPA ini?`)) {
        window.location.href = '<?= BASE_URL ?>/controllers/admin/assign_mahasiswa_process.php?mahasiswa_id=' + mahasiswaId + '&dpa_id=' + dpaId;
    }
}
</script>

</body>
</html>
