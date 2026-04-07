<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php'; // Tambahan untuk BASE_URL/assets

onlyMitra();

$user_id = $_SESSION['user_id'];

$query = "SELECT m.*, u.email FROM mitra m JOIN users u ON m.user_id = u.id WHERE m.user_id = ?";
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
    <title>Edit Profil Mitra | Sistem Peluang</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/mitra.css">
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Edit Profil</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-pencil-square me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Perbarui informasi organisasi dan detail kontak Anda.</span>
                </div>
            </div>
            <a href="profile.php" class="btn btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <div class="mitra-edit-card">
            <div class="mitra-edit-header">
                <h5 class="mb-0">Formulir Informasi Mitra</h5>
            </div>
            
            <div class="mitra-edit-body">
                <form method="post" action="../../controllers/mitra/edit_profile_process.php">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label class="form-label custom-label">Email Instansi</label>
                                <input class="form-control custom-input" type="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" readonly>
                                <div class="form-text">Email terikat dengan akun dan tidak dapat diubah.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label custom-label">Nama Organisasi / Perusahaan <span class="text-danger">*</span></label>
                                <input class="form-control custom-input" type="text" name="nama_organisasi" value="<?php echo htmlspecialchars($profile['nama_organisasi'] ?? ''); ?>" required placeholder="Masukkan nama resmi instansi">
                            </div>

                            <div class="mb-4">
                                <label class="form-label custom-label">Nomor Kontak / Telepon</label>
                                <input class="form-control custom-input" type="text" name="kontak" value="<?php echo htmlspecialchars($profile['kontak'] ?? ''); ?>" placeholder="Contoh: 021-1234567 atau 0812xxxx">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-4 h-100 d-flex flex-column">
                                <label class="form-label custom-label">Deskripsi Organisasi</label>
                                <textarea class="form-control custom-input flex-grow-1" name="deskripsi" rows="6" placeholder="Ceritakan singkat tentang profil organisasi, visi misi, atau bidang industri Anda..."><?php echo htmlspecialchars($profile['deskripsi'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4" style="border-color: #E5E7EB;">

                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-outline-secondary">Batal</button>
                        <button type="submit" class="btn btn-navy px-4">
                            <i class="bi bi-save me-2"></i>Simpan Perubahan
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>