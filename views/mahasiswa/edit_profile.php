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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Mahasiswa | Sistem Peluang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/mahasiswa.css">
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">
        
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Edit Profil</h1>
                <p class="page-subtitle mb-0">Perbarui informasi pribadi dan akademik Anda.</p>
            </div>
            <?php if (!$is_incomplete): ?>
                <a href="../mahasiswa/profile.php" class="btn btn-soft-outline px-4">
                    <i class="bi bi-arrow-left me-2"></i>Kembali
                </a>
            <?php endif; ?>
        </div>

        <?php if ($is_incomplete && !$profile_is_complete): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Perhatian!</strong> Anda harus melengkapi profil terlebih dahulu sebelum dapat mengakses fitur lain. Minimal isikan <strong>NIM</strong> dan <strong>Nama</strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" id="closeAlertBtn" style="display: none;"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="mhs-edit-card">
            <div class="mhs-edit-header">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Data Profil Akun</h5>
                <small class="text-muted"><?php echo htmlspecialchars($_SESSION['email']); ?></small>
            </div>
            
            <div class="mhs-edit-body">
                <form method="post" action="../../controllers/mahasiswa/edit_profile_process.php" id="editProfileForm">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label custom-label">Email (Akun Login)</label>
                            <input type="email" class="form-control custom-input bg-light" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" readonly>
                            <div class="form-text">Email tidak dapat diubah.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label custom-label">NIM <span class="text-danger">*</span></label>
                            <input type="text" name="nim" id="nimInput" class="form-control custom-input" value="<?php echo htmlspecialchars($profile['nim'] ?? ''); ?>" required placeholder="Masukkan Nomor Induk Mahasiswa">
                            <div class="form-text">Wajib diisi sebagai identitas akademik.</div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label custom-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" id="namaInput" class="form-control custom-input" value="<?php echo htmlspecialchars($profile['nama'] ?? ''); ?>" required placeholder="Masukkan nama lengkap">
                            <div class="form-text">Sesuaikan dengan kartu identitas mahasiswa.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label custom-label">Fakultas</label>
                            <input type="text" name="fakultas" class="form-control custom-input" value="<?php echo htmlspecialchars($profile['fakultas'] ?? ''); ?>" placeholder="Contoh: Fakultas Ilmu Komputer">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label custom-label">Program Studi</label>
                            <input type="text" name="prodi" class="form-control custom-input" value="<?php echo htmlspecialchars($profile['prodi'] ?? ''); ?>" placeholder="Contoh: Sistem Informasi">
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label class="form-label custom-label">Tahun Angkatan</label>
                            <input type="text" name="angkatan" class="form-control custom-input" value="<?php echo htmlspecialchars($profile['angkatan'] ?? ''); ?>" placeholder="Contoh: 2021">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label custom-label">Semester Berjalan</label>
                            <input type="number" name="semester" class="form-control custom-input" value="<?php echo htmlspecialchars($profile['semester'] ?? ''); ?>" placeholder="Contoh: 5">
                        </div>
                    </div>

                    <div class="row mb-5">
                        <div class="col-md-4">
                            <label class="form-label custom-label">Indeks Prestasi Kumulatif (IPK)</label>
                            <input 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                max="4" 
                                name="ipk" 
                                class="form-control custom-input"
                                value="<?php echo htmlspecialchars($profile['ipk'] ?? ''); ?>"
                                placeholder="Contoh: 3.50"
                            >
                            <div class="form-text">Gunakan format titik (contoh: 3.50). Maks: 4.00</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end pt-3 border-top">
                        <button type="submit" class="btn btn-navy px-4 py-2">
                            <i class="bi bi-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
const isIncomplete = <?php echo $is_incomplete ? 'true' : 'false'; ?>;
const profileIsComplete = <?php echo $profile_is_complete ? 'true' : 'false'; ?>;

// Prevent navigation away if profile is incomplete
if (isIncomplete && !profileIsComplete) {
    window.history.pushState(null, null, window.location.href);
    window.onpopstate = function() {
        window.history.pushState(null, null, window.location.href);
        alert('Anda harus melengkapi profil terlebih dahulu (NIM dan Nama)');
    };

    document.addEventListener('click', function(e) {
        const target = e.target.closest('a');
        if (target && !target.hasAttribute('onclick')) {
            if (!target.closest('form')) {
                e.preventDefault();
                alert('Anda harus melengkapi profil terlebih dahulu (NIM dan Nama)');
            }
        }
    });

    window.onbeforeunload = function() {
        if (!profileIsComplete) {
            return 'Anda belum melengkapi profil (NIM dan Nama). Apakah Anda yakin ingin keluar?';
        }
    };
}
</script>

</body>
</html>