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
                <h4>Detail DPA</h4>
                <small>
                    Login sebagai: <?php echo htmlspecialchars($_SESSION['email']); ?> (admin)
                </small>
            </div>
            <a href="<?= BASE_URL ?>/views/admin/assign.php" class="btn btn-secondary">Kembali</a>
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

        <!-- DPA Information -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Informasi DPA</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Nama:</strong><br>
                                    <?php echo htmlspecialchars($dpa['nama'] ?? 'N/A'); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Email:</strong><br>
                                    <?php echo htmlspecialchars($dpa['email']); ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2">
                                    <strong>Total Mahasiswa:</strong><br>
                                    <span class="badge bg-primary fs-6"><?php echo $total_mahasiswa; ?> Mahasiswa</span>
                                </p>
                                <p class="mb-2">
                                    <strong>Terdaftar Sejak:</strong><br>
                                    <?php echo $dpa['user_created_at']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Aksi</h5>
                    </div>
                    <div class="card-body">
                        <a href="<?= BASE_URL ?>/views/admin/assign_mahasiswa.php?dpa_id=<?php echo $dpa['id']; ?>" 
                           class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-plus-circle"></i> Tugaskan Mahasiswa
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mahasiswa List -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Daftar Mahasiswa (<?php echo $total_mahasiswa; ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($mahasiswa_list)): ?>
                    <div class="alert alert-info mb-0">
                        Belum ada mahasiswa yang ditugaskan ke DPA ini.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>NIM</th>
                                    <th>Nama Mahasiswa</th>
                                    <th>Fakultas</th>
                                    <th>Prodi</th>
                                    <th>IPK</th>
                                    <th>Semester</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mahasiswa_list as $mahasiswa): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($mahasiswa['nim'] ?? 'N/A'); ?></strong>
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
                                            <?php echo htmlspecialchars($mahasiswa['ipk'] ?? 'N/A'); ?>
                                        </td>
                                        <td>
                                            <?php echo $mahasiswa['semester'] ?? 'N/A'; ?>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm"
                                                    onclick="unassignMahasiswa(<?php echo $mahasiswa['id']; ?>, '<?php echo htmlspecialchars(addslashes($mahasiswa['nama'])); ?>', '<?php echo htmlspecialchars(addslashes($dpa['nama'])); ?>')">
                                                <i class="bi bi-x-circle"></i> Lepas
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
