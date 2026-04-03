<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

onlyMahasiswa();

$user_id = $_SESSION['user_id'];
$is_incomplete = isset($_GET['incomplete']) && $_GET['incomplete'] === '1';

$query = "SELECT m.*, u.email FROM mahasiswa m JOIN users u ON m.user_id = u.id WHERE m.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: ../dashboard.php');
    exit;
}

$profile = mysqli_fetch_assoc($result);

// Check if profile is complete
$profile_is_complete = !empty($profile['nim']) && !empty($profile['nama']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profil Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Edit Profil Mahasiswa</h4>
                <small><?php echo htmlspecialchars($_SESSION['email']); ?></small>
            </div>
            <?php if (!$is_incomplete): ?>
                <a href="../mahasiswa/profile.php" class="btn btn-secondary">Kembali</a>
            <?php endif; ?>
        </div>

        <?php if ($is_incomplete && !$profile_is_complete): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>⚠️ Perhatian!</strong> Anda harus melengkapi profil terlebih dahulu sebelum dapat mengakses fitur lain. 
                Minimal isikan <strong>NIM</strong> dan <strong>Nama</strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" id="closeAlertBtn" style="display: none;"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post" action="../../controllers/mahasiswa/edit_profile_process.php" id="editProfileForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email (tidak bisa diubah)</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIM <span class="text-danger">*</span></label>
                            <input type="text" name="nim" id="nimInput" class="form-control" value="<?php echo htmlspecialchars($profile['nim'] ?? ''); ?>" required>
                            <small class="text-muted">Wajib diisi</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="namaInput" class="form-control" value="<?php echo htmlspecialchars($profile['nama'] ?? ''); ?>" required>
                            <small class="text-muted">Wajib diisi</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fakultas</label>
                            <input type="text" name="fakultas" class="form-control" value="<?php echo htmlspecialchars($profile['fakultas'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Prodi</label>
                            <input type="text" name="prodi" class="form-control" value="<?php echo htmlspecialchars($profile['prodi'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Angkatan</label>
                            <input type="text" name="angkatan" class="form-control" value="<?php echo htmlspecialchars($profile['angkatan'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Semester</label>
                            <input type="number" name="semester" class="form-control" value="<?php echo htmlspecialchars($profile['semester'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">IPK</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            max="4" 
                            name="ipk" 
                            class="form-control"
                            value="<?php echo htmlspecialchars($profile['ipk'] ?? ''); ?>"
                            placeholder="Contoh: 3.50"
                        >
                        <small class="text-muted">
                            Gunakan titik (.) contoh: 3.50 (maksimal 4.00)
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
const isIncomplete = <?php echo $is_incomplete ? 'true' : 'false'; ?>;
const profileIsComplete = <?php echo $profile_is_complete ? 'true' : 'false'; ?>;

// Prevent navigation away if profile is incomplete
if (isIncomplete && !profileIsComplete) {
    // Disable browser back button
    window.history.pushState(null, null, window.location.href);
    window.onpopstate = function() {
        window.history.pushState(null, null, window.location.href);
        alert('Anda harus melengkapi profil terlebih dahulu (NIM dan Nama)');
    };

    // Prevent navigation by clicking links
    document.addEventListener('click', function(e) {
        const target = e.target.closest('a');
        if (target && !target.hasAttribute('onclick')) {
            // Allow form submission
            if (!target.closest('form')) {
                e.preventDefault();
                alert('Anda harus melengkapi profil terlebih dahulu (NIM dan Nama)');
            }
        }
    });

    // Warn before leaving page
    window.onbeforeunload = function() {
        if (!profileIsComplete) {
            return 'Anda belum melengkapi profil (NIM dan Nama). Apakah Anda yakin ingin keluar?';
        }
    };
}

// After successful save, redirect to profile
document.getElementById('editProfileForm').addEventListener('submit', function() {
    // Form will be submitted and redirected by PHP
});
</script>

</body>
</html>