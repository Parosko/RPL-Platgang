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

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

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
                <h4>Tugaskan Mahasiswa ke DPA</h4>
                <small>
                    DPA: <strong><?php echo htmlspecialchars($dpa['nama']); ?></strong> | 
                    Login sebagai: <?php echo htmlspecialchars($_SESSION['email']); ?> (admin)
                </small>
            </div>
            <a href="<?= BASE_URL ?>/views/admin/dpa_detail.php?id=<?php echo $dpa_id; ?>" class="btn btn-secondary">Kembali</a>
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

        <!-- Search and Filter Section -->
        <div class="row mb-4">
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
                        Semua
                    </button>
                    <button type="button" class="btn btn-outline-secondary filter-btn <?php echo $filter === 'not_assigned' ? 'active' : ''; ?>" onclick="filterMahasiswa('not_assigned')">
                        Belum Ditugaskan
                    </button>
                    <button type="button" class="btn btn-outline-secondary filter-btn <?php echo $filter === 'assigned' ? 'active' : ''; ?>" onclick="filterMahasiswa('assigned')">
                        Sudah Ditugaskan
                    </button>
                </div>
            </div>
        </div>

        <?php if (empty($mahasiswa_list)): ?>
            <div class="alert alert-info">
                Tidak ada mahasiswa yang sesuai dengan kriteria pencarian dan filter Anda.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%">NIM</th>
                            <th style="width: 20%">Nama Mahasiswa</th>
                            <th style="width: 15%">Fakultas</th>
                            <th style="width: 15%">Prodi</th>
                            <th style="width: 10%">Semester</th>
                            <th style="width: 10%">IPK</th>
                            <th style="width: 15%">Status Penugasan</th>
                            <th class="text-end" style="width: 15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="mahasiswaList">
                        <?php foreach ($mahasiswa_list as $mahasiswa): ?>
                            <tr class="mahasiswa-row" 
                                data-search="<?php echo htmlspecialchars(strtolower($mahasiswa['nim'] . ' ' . $mahasiswa['nama'])); ?>"
                                data-status="<?php echo $mahasiswa['dpa_id'] ? 'assigned' : 'not_assigned'; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($mahasiswa['nim']); ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($mahasiswa['nama']); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($mahasiswa['fakultas'] ?? 'N/A'); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($mahasiswa['prodi'] ?? 'N/A'); ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark"><?php echo htmlspecialchars($mahasiswa['semester'] ?? 'N/A'); ?></span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars((string)($mahasiswa['ipk'] ?? 'N/A')); ?>
                                </td>
                                <td>
                                    <?php if ($mahasiswa['dpa_id']): ?>
                                        <span class="badge bg-success">
                                            Ditugaskan ke: <?php echo htmlspecialchars($mahasiswa['assigned_dpa_name'] ?? 'N/A'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">
                                            Belum Ditugaskan
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($mahasiswa['dpa_id'] !== $dpa_id): ?>
                                        <button type="button" 
                                                class="btn btn-primary btn-sm"
                                                onclick="assignMahasiswa(<?php echo $mahasiswa['id']; ?>, <?php echo $dpa_id; ?>, '<?php echo htmlspecialchars(addslashes($mahasiswa['nama'])); ?>')">
                                            <i class="bi bi-check-circle"></i> Tugaskan
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-primary">Sudah Ditugaskan ke DPA ini</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="noResults" class="alert alert-warning mt-3" style="display: none;">
                Tidak ada mahasiswa yang sesuai dengan pencarian Anda.
            </div>
        <?php endif; ?>

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
