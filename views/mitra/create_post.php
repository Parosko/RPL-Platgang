<?php
session_start();
include '../../core/middleware.php';

onlyMitra();

$error = isset($_GET['error']) ? $_GET['error'] : null;
$success = isset($_GET['success']) ? true : false;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buat Postingan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
</head>

<body>

<div class="d-flex">

    <?php include '../layouts/sidebar.php'; ?>

    <div class="content">

        <div class="mb-4">
            <h4>Buat Postingan</h4>
            <small>Isi informasi peluang yang ingin kamu tawarkan</small>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                Postingan berhasil dibuat dan langsung tampil di beranda.
            </div>
        <?php endif; ?>

        <div class="card p-4">

            <form action="../../controllers/mitra/create_post_process.php" method="POST">

                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" name="judul" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipe</label>
                    <select name="tipe" class="form-select" required>
                        <option value="">Pilih</option>
                        <option value="magang">Magang</option>
                        <option value="kursus">Kursus</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Jakarta / Remote">
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kuota</label>
                        <input type="number" name="kuota" class="form-control" min="1" placeholder="Contoh: 10">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Minimal IPK</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            max="4" 
                            name="min_ipk" 
                            class="form-control"
                            placeholder="Contoh: 3.50"
                        >
                        <small class="text-muted">
                            Gunakan titik (.) contoh: 3.50 (maksimal 4.00)
                        </small>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Minimal Semester</label>
                        <input type="number" name="min_semester" class="form-control" min="1" placeholder="Contoh: 5">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fakultas (opsional)</label>
                    <input type="text" name="fakultas" class="form-control" placeholder="Contoh: Teknik Informatika">
                </div>

                <div class="mb-3">
                    <label class="form-label">Deadline</label>
                    <input type="datetime-local" name="deadline" class="form-control" required>
                </div>

                <div class="d-flex justify-content-between">

                    <a href="../dashboard.php" class="btn btn-outline-secondary">
                        Kembali
                    </a>

                    <button class="btn btn-dark">
                        Publish
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>