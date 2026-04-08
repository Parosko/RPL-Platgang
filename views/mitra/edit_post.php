<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

onlyMitra();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: my_posts.php');
    exit;
}

$peluang_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get post details
$query = "SELECT * FROM peluang WHERE id = ? AND mitra_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $peluang_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Postingan tidak ditemukan.';
    header('Location: my_posts.php');
    exit;
}

$post = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Postingan - <?php echo htmlspecialchars($post['judul']); ?> | Sistem Peluang</title>
    
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
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Edit Postingan</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-pencil-square me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Perbarui informasi peluang yang sudah Anda publikasikan.</span>
                </div>
            </div>
            <a href="my_posts.php" class="btn btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <div class="mitra-edit-card">
            <div class="mitra-edit-header">
                <h5 class="mb-0">Detail Informasi Peluang</h5>
            </div>

            <div class="mitra-edit-body">
                <form method="POST" action="<?= BASE_URL ?>/controllers/mitra/edit_post_process.php">
                    <input type="hidden" name="peluang_id" value="<?php echo $peluang_id; ?>">

                    <div class="mb-4">
                        <label for="judul" class="form-label custom-label">Judul Peluang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control custom-input" id="judul" name="judul" 
                            value="<?php echo htmlspecialchars($post['judul']); ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="tipe" class="form-label custom-label">Tipe Peluang <span class="text-danger">*</span></label>
                            <select class="form-select custom-input" id="tipe" name="tipe" required>
                                <option value="magang" <?php echo $post['tipe'] == 'magang' ? 'selected' : ''; ?>>Magang</option>
                                <option value="kursus" <?php echo $post['tipe'] == 'kursus' ? 'selected' : ''; ?>>Kursus</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="lokasi" class="form-label custom-label">Lokasi</label>
                            <input type="text" class="form-control custom-input" id="lokasi" name="lokasi" 
                                value="<?php echo htmlspecialchars($post['lokasi'] ?? ''); ?>" placeholder="Contoh: Jakarta Selatan / Remote">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label for="kuota" class="form-label custom-label">Kuota Penerimaan</label>
                            <input type="number" class="form-control custom-input" id="kuota" name="kuota" 
                                value="<?php echo $post['kuota']; ?>" min="1" required>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label for="min_ipk" class="form-label custom-label">Minimal IPK</label>
                            <input type="number" step="0.01" min="0" max="4" class="form-control custom-input" id="min_ipk" name="min_ipk" 
                                value="<?php echo $post['min_ipk'] ?? '0'; ?>">
                            <div class="form-text">Gunakan titik (.). Maks: 4.00</div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label for="min_semester" class="form-label custom-label">Minimal Semester</label>
                            <input type="number" class="form-control custom-input" id="min_semester" name="min_semester" 
                                value="<?php echo $post['min_semester'] ?? '1'; ?>" min="1">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="fakultas" class="form-label custom-label">Fakultas Prioritas (Opsional)</label>
                            <input type="text" class="form-control custom-input" id="fakultas" name="fakultas" 
                                value="<?php echo htmlspecialchars($post['fakultas'] ?? ''); ?>" placeholder="Kosongkan untuk semua fakultas">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="deadline" class="form-label custom-label">Batas Akhir Pendaftaran <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control custom-input" id="deadline" name="deadline" 
                                value="<?php echo date('Y-m-d\TH:i', strtotime($post['deadline'])); ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="deskripsi" class="form-label custom-label">Deskripsi Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control custom-input" id="deskripsi" name="deskripsi" rows="6" required><?php echo htmlspecialchars($post['deskripsi']); ?></textarea>
                    </div>

                    <hr class="my-4" style="border-color: #E5E7EB;">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="my_posts.php" class="btn btn-outline-secondary">Batal</a>
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