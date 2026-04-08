<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

onlyMitra();

$error = isset($_GET['error']) ? $_GET['error'] : null;
$success = isset($_GET['success']) ? true : false;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Postingan | Sistem Peluang</title>

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

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Postingan berhasil dibuat dan langsung tampil di beranda.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Buat Postingan Peluang</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-megaphone me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Publikasikan informasi magang atau kursus untuk mahasiswa.</span>
                </div>
            </div>
            <a href="../dashboard.php" class="btn btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <div class="mitra-edit-card">
            <div class="mitra-edit-header">
                <h5 class="mb-0">Detail Informasi Peluang</h5>
            </div>

            <div class="mitra-edit-body">
                <form action="../../controllers/mitra/create_post_process.php" method="POST">
                    
                    <div class="mb-4">
                        <label class="form-label custom-label">Judul Peluang <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control custom-input" placeholder="Contoh: Magang Web Developer / Kursus UI/UX Design" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label custom-label">Tipe Peluang <span class="text-danger">*</span></label>
                            <select name="tipe" class="form-select custom-input" required>
                                <option value="" selected disabled>Pilih Tipe</option>
                                <option value="magang">Magang</option>
                                <option value="kursus">Kursus</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label custom-label">Lokasi</label>
                            <input type="text" name="lokasi" class="form-control custom-input" placeholder="Contoh: Jakarta Selatan / Remote (WFA)">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label class="form-label custom-label">Kuota Penerimaan</label>
                            <input type="number" name="kuota" class="form-control custom-input" min="1" placeholder="Contoh: 10">
                        </div>

                        <div class="col-md-4 mb-4">
                            <label class="form-label custom-label">Minimal IPK</label>
                            <input type="number" step="0.01" min="0" max="4" name="min_ipk" class="form-control custom-input" placeholder="Contoh: 3.50">
                            <div class="form-text">Gunakan titik (.). Maks: 4.00</div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <label class="form-label custom-label">Minimal Semester</label>
                            <input type="number" name="min_semester" class="form-control custom-input" min="1" placeholder="Contoh: 5">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label custom-label">Fakultas Prioritas (Opsional)</label>
                            <input type="text" name="fakultas" class="form-control custom-input" placeholder="Contoh: Ilmu Komputer">
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label custom-label">Batas Akhir Pendaftaran (Deadline) <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="deadline" class="form-control custom-input" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label custom-label">Deskripsi Lengkap <span class="text-danger">*</span></label>
                        <textarea name="deskripsi" class="form-control custom-input" rows="6" placeholder="Jelaskan deskripsi pekerjaan, benefit, dan persyaratan tambahan..." required></textarea>
                    </div>

                    <hr class="my-4" style="border-color: #E5E7EB;">

                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        <button type="submit" class="btn btn-navy px-4">
                            <i class="bi bi-send-fill me-2"></i>Publikasikan
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