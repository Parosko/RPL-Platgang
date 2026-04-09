<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyDPA();

$user_id = $_SESSION['user_id'];

$query = "SELECT d.*, u.email FROM dpa d JOIN users u ON d.user_id = u.id WHERE d.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: ../dashboard.php');
    exit;
}

$profile = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil DPA | Sistem Peluang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/dpa.css">
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Edit Profil DPA</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-pencil-square me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Perbarui informasi pribadi dan data akademik Anda.</span>
                </div>
            </div>
            <a href="profile.php" class="btn btn-soft-outline px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="dpa-edit-card">
            <div class="dpa-edit-header">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Formulir Data DPA</h5>
            </div>
            <div class="dpa-edit-body">
                <form method="post" action="../../controllers/dpa/edit_profile_process.php">
                    <div class="row g-4">
                        
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="custom-label">
                                    <i class="bi bi-envelope me-2"></i>Alamat Email
                                </label>
                                <input class="custom-input" type="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" readonly>
                                <small class="form-text text-muted">Email tidak dapat diubah. Hubungi administrator jika perlu mengubah email.</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="custom-label">
                                    <i class="bi bi-person-vcard me-2"></i>NIP / NIDN
                                </label>
                                <input class="custom-input" type="text" name="nip" value="<?php echo htmlspecialchars($profile['nip'] ?? ''); ?>" placeholder="Masukkan NIP/NIDN Anda">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="custom-label">
                                    <i class="bi bi-person me-2"></i>Nama Lengkap
                                </label>
                                <input class="custom-input" type="text" name="nama" value="<?php echo htmlspecialchars($profile['nama'] ?? ''); ?>" placeholder="Masukkan nama lengkap beserta gelar" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="custom-label">
                                    <i class="bi bi-building me-2"></i>Fakultas
                                </label>
                                <input class="custom-input" type="text" name="fakultas" value="<?php echo htmlspecialchars($profile['fakultas'] ?? ''); ?>" placeholder="Contoh: Fakultas Teknik">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="custom-label">
                                    <i class="bi bi-book me-2"></i>Program Studi
                                </label>
                                <input class="custom-input" type="text" name="prodi" value="<?php echo htmlspecialchars($profile['prodi'] ?? ''); ?>" placeholder="Contoh: Teknik Informatika">
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="custom-label">
                                    <i class="bi bi-telephone me-2"></i>Kontak / Telepon
                                </label>
                                <input class="custom-input" type="tel" name="kontak" value="<?php echo htmlspecialchars($profile['kontak'] ?? ''); ?>" placeholder="Contoh: 081234567890">
                            </div>
                        </div>

                    </div>
                    
                    <div class="d-flex flex-column flex-md-row gap-3 mt-5">
                        <button type="submit" class="btn btn-navy px-4">
                            <i class="bi bi-box-arrow-in-down me-2" style="font-size: 1.1rem;"></i>Simpan Perubahan
                        </button>
                        <a href="profile.php" class="btn btn-soft-outline px-4">
                            <i class="bi bi-x-circle me-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>