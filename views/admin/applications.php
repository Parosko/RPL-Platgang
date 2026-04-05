<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyAdmin();

// Get all applications across all postings
$query = "SELECT l.id, l.tanggal_apply, l.status, l.is_recommended,
                 p.id as peluang_id, p.judul, p.tipe,
                 m.nim, m.nama as mahasiswa_nama, m.fakultas, m.ipk, m.semester,
                 u.email as mahasiswa_email,
                 mt.nama_organisasi,
                 mu.email as mitra_email
          FROM lamaran l
          JOIN peluang p ON l.peluang_id = p.id
          JOIN mahasiswa m ON l.mahasiswa_id = m.id
          JOIN users u ON m.user_id = u.id
          JOIN mitra mt ON p.mitra_id = mt.user_id
          JOIN users mu ON mt.user_id = mu.id
          ORDER BY l.tanggal_apply DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_execute($stmt);
$applications = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Semua Lamaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <style>
        .badge-recommended {
            background-color: #FFD700;
            color: #000;
        }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Kelola Semua Lamaran</h4>
                <small>
                    Login sebagai: <?php echo htmlspecialchars($_SESSION['email']); ?> (admin)
                </small>
            </div>
            <a href="../dashboard.php" class="btn btn-secondary">Kembali</a>
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

        <!-- Filter -->
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select" id="filterStatus" onchange="filterApplications()">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="accepted">Diterima</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="filterMahasiswa" placeholder="Cari mahasiswa..." onkeyup="filterApplications()">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="filterMitra" placeholder="Cari organisasi..." onkeyup="filterApplications()">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="filterPostingan" placeholder="Cari postingan..." onkeyup="filterApplications()">
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Organisasi</th>
                            <th>Postingan</th>
                            <th>IPK / Semester</th>
                            <th>Tanggal Apply</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($applications) > 0):
                            mysqli_data_seek($applications, 0);
                            while ($app = mysqli_fetch_assoc($applications)): ?>
                                <tr class="application-row" 
                                    data-status="<?php echo $app['status']; ?>" 
                                    data-mahasiswa="<?php echo strtolower($app['mahasiswa_nama']); ?>"
                                    data-mitra="<?php echo strtolower($app['nama_organisasi']); ?>"
                                    data-postingan="<?php echo strtolower($app['judul']); ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($app['mahasiswa_nama']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['mahasiswa_email']); ?></small><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['nim']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($app['nama_organisasi']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($app['mitra_email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($app['judul']); ?></td>
                                    <td>
                                        IPK: <?php echo $app['ipk']; ?><br>
                                        Sem: <?php echo $app['semester']; ?>
                                    </td>
                                    <td><?php echo date('d-m-Y H:i', strtotime($app['tanggal_apply'])); ?></td>
                                    <td>
                                        <span class="badge 
                                            <?php echo ($app['status'] == 'accepted') ? 'bg-success' : 
                                                   (($app['status'] == 'rejected') ? 'bg-danger' : 'bg-warning'); ?>">
                                            <?php echo ucfirst($app['status']); ?>
                                        </span>
                                        <?php if ($app['is_recommended']): ?>
                                            <br><span class="badge badge-recommended">DPA</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-info">Detail</a>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <p>Tidak ada lamaran.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function filterApplications() {
    const status = document.getElementById('filterStatus').value.toLowerCase();
    const mahasiswa = document.getElementById('filterMahasiswa').value.toLowerCase();
    const mitra = document.getElementById('filterMitra').value.toLowerCase();
    const postingan = document.getElementById('filterPostingan').value.toLowerCase();
    
    const rows = document.querySelectorAll('.application-row');
    rows.forEach(row => {
        const rowStatus = row.dataset.status.toLowerCase();
        const rowMahasiswa = row.dataset.mahasiswa.toLowerCase();
        const rowMitra = row.dataset.mitra.toLowerCase();
        const rowPostingan = row.dataset.postingan.toLowerCase();
        
        const statusMatch = !status || rowStatus === status;
        const mahasiswaMatch = !mahasiswa || rowMahasiswa.includes(mahasiswa);
        const mitraMatch = !mitra || rowMitra.includes(mitra);
        const postinganMatch = !postingan || rowPostingan.includes(postingan);
        
        row.style.display = (statusMatch && mahasiswaMatch && mitraMatch && postinganMatch) ? '' : 'none';
    });
}
</script>

</body>
</html>
