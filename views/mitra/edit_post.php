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
<html>
<head>
    <title>Edit Postingan - <?php echo htmlspecialchars($post['judul']); ?></title>
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
                <h4>Edit Postingan</h4>
                <small>
                    Login sebagai: <?php echo htmlspecialchars($_SESSION['email']); ?> (mitra)
                </small>
            </div>
            <a href="my_posts.php" class="btn btn-secondary">Kembali</a>
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

        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/controllers/mitra/edit_post_process.php">
                    <input type="hidden" name="peluang_id" value="<?php echo $peluang_id; ?>">

                    <div class="mb-3">
                        <label for="judul" class="form-label">Judul Postingan</label>
                        <input type="text" class="form-control" id="judul" name="judul" 
                            value="<?php echo htmlspecialchars($post['judul']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required><?php echo htmlspecialchars($post['deskripsi']); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipe" class="form-label">Tipe</label>
                            <select class="form-select" id="tipe" name="tipe" required>
                                <option value="magang" <?php echo $post['tipe'] == 'magang' ? 'selected' : ''; ?>>Magang</option>
                                <option value="kursus" <?php echo $post['tipe'] == 'kursus' ? 'selected' : ''; ?>>Kursus</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lokasi" class="form-label">Lokasi</label>
                            <input type="text" class="form-control" id="lokasi" name="lokasi" 
                                value="<?php echo htmlspecialchars($post['lokasi'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="kuota" class="form-label">Kuota</label>
                            <input type="number" class="form-control" id="kuota" name="kuota" 
                                value="<?php echo $post['kuota']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="min_ipk" class="form-label">Min. IPK</label>
                            <input type="number" step="0.01" class="form-control" id="min_ipk" name="min_ipk" 
                                value="<?php echo $post['min_ipk'] ?? '0'; ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="min_semester" class="form-label">Min. Semester</label>
                            <input type="number" class="form-control" id="min_semester" name="min_semester" 
                                value="<?php echo $post['min_semester'] ?? '1'; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fakultas" class="form-label">Fakultas</label>
                            <input type="text" class="form-control" id="fakultas" name="fakultas" 
                                value="<?php echo htmlspecialchars($post['fakultas'] ?? ''); ?>" placeholder="Kosongkan untuk semua fakultas">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="deadline" class="form-label">Deadline</label>
                        <input type="datetime-local" class="form-control" id="deadline" name="deadline" 
                            value="<?php echo date('Y-m-d\TH:i', strtotime($post['deadline'])); ?>" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="my_posts.php" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
