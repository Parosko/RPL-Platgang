<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMahasiswa();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../views/dashboard.php');
    exit;
}

$peluang_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get post details
$query = "SELECT p.*, u.email as mitra_email, m.nama_organisasi
          FROM peluang p
          JOIN users u ON p.mitra_id = u.id
          LEFT JOIN mitra m ON u.id = m.user_id
          WHERE p.id = ? AND p.status = 'approved'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $peluang_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    header('Location: ../../views/dashboard.php');
    exit;
}

$post = mysqli_fetch_assoc($result);

// Get user profile
$query = "SELECT m.*, u.email FROM mahasiswa m JOIN users u ON m.user_id = u.id WHERE m.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Lengkapi profil Anda terlebih dahulu.';
    header('Location: ../../views/dashboard.php');
    exit;
}

$profile = mysqli_fetch_assoc($result);

// Check if already applied
$query = "SELECT id FROM lamaran WHERE mahasiswa_id = ? AND peluang_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $profile['user_id'], $peluang_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $_SESSION['error'] = 'Anda sudah mendaftar untuk peluang ini.';
    header('Location: ../../views/posts/detail.php?id=' . $peluang_id);
    exit;
}

// Check basic requirements
$can_apply = true;
$requirement_messages = [];

if ($post['min_ipk'] && $profile['ipk'] < $post['min_ipk']) {
    $can_apply = false;
    $requirement_messages[] = "IPK minimum: {$post['min_ipk']}, IPK Anda: {$profile['ipk']}";
}

if ($post['min_semester'] && $profile['semester'] < $post['min_semester']) {
    $can_apply = false;
    $requirement_messages[] = "Semester minimum: {$post['min_semester']}, Semester Anda: {$profile['semester']}";
}

if ($post['fakultas'] && $profile['fakultas'] !== $post['fakultas']) {
    $can_apply = false;
    $requirement_messages[] = "Fakultas yang dibutuhkan: {$post['fakultas']}, Fakultas Anda: {$profile['fakultas']}";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Peluang - <?php echo htmlspecialchars($post['judul']); ?></title>
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
                <h4>Daftar Peluang</h4>
                <small><?php echo htmlspecialchars($post['judul']); ?></small>
            </div>
            <a href="../../views/posts/detail.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary">Kembali</a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Requirements Comparison -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Persyaratan vs Profil Anda</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Persyaratan Peluang</h6>
                        <ul class="list-group">
                            <li class="list-group-item">IPK Minimum: <?php echo $post['min_ipk'] ?: 'Tidak ditentukan'; ?></li>
                            <li class="list-group-item">Semester Minimum: <?php echo $post['min_semester'] ?: 'Tidak ditentukan'; ?></li>
                            <li class="list-group-item">Fakultas: <?php echo htmlspecialchars($post['fakultas'] ?: 'Semua'); ?></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Profil Anda</h6>
                        <ul class="list-group">
                            <li class="list-group-item <?php echo ($post['min_ipk'] && $profile['ipk'] >= $post['min_ipk']) ? 'list-group-item-success' : ($post['min_ipk'] ? 'list-group-item-danger' : ''); ?>">
                                IPK: <?php echo $profile['ipk'] ?: 'Belum diisi'; ?>
                            </li>
                            <li class="list-group-item <?php echo ($post['min_semester'] && $profile['semester'] >= $post['min_semester']) ? 'list-group-item-success' : ($post['min_semester'] ? 'list-group-item-danger' : ''); ?>">
                                Semester: <?php echo $profile['semester'] ?: 'Belum diisi'; ?>
                            </li>
                            <li class="list-group-item <?php echo (!$post['fakultas'] || $profile['fakultas'] === $post['fakultas']) ? 'list-group-item-success' : 'list-group-item-danger'; ?>">
                                Fakultas: <?php echo htmlspecialchars($profile['fakultas'] ?: 'Belum diisi'); ?>
                            </li>
                        </ul>
                    </div>
                </div>

                <?php if (!$can_apply): ?>
                    <div class="alert alert-warning mt-3">
                        <strong>Perhatian:</strong> Anda belum memenuhi beberapa persyaratan. Tetap lanjutkan jika Anda yakin.
                        <ul>
                            <?php foreach ($requirement_messages as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upload Documents -->
        <div class="card">
            <div class="card-header">
                <h5>Unggah Dokumen</h5>
                <small>Maksimal 3 dokumen (PDF, DOC, DOCX, JPG, PNG - maks 5MB per file)</small>
            </div>
            <div class="card-body">
                <form method="post" action="../../controllers/mahasiswa/apply_process.php" enctype="multipart/form-data">
                    <input type="hidden" name="peluang_id" value="<?php echo $post['id']; ?>">

                    <div id="document-fields">
                        <div class="mb-3 document-field">
                            <label class="form-label">Dokumen 1</label>
                            <input type="file" name="documents[]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png" required>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary mb-3" id="add-document" style="display: none;">Tambah Dokumen</button>

                    <div class="mb-3">
                        <label class="form-label">Catatan Tambahan (Opsional)</label>
                        <textarea name="catatan" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Kirim Lamaran</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('add-document').addEventListener('click', function() {
    const container = document.getElementById('document-fields');
    const fields = container.querySelectorAll('.document-field');
    
    if (fields.length < 3) {
        const newField = document.createElement('div');
        newField.className = 'mb-3 document-field';
        newField.innerHTML = `
            <label class="form-label">Dokumen ${fields.length + 1}</label>
            <input type="file" name="documents[]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
            <button type="button" class="btn btn-sm btn-outline-danger mt-1 remove-document">Hapus</button>
        `;
        container.appendChild(newField);
        
        if (fields.length + 1 >= 3) {
            this.style.display = 'none';
        }
    }
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-document')) {
        const field = e.target.closest('.document-field');
        field.remove();
        
        const fields = document.querySelectorAll('.document-field');
        document.getElementById('add-document').style.display = fields.length < 3 ? 'inline-block' : 'none';
        
        // Renumber labels
        fields.forEach((f, i) => {
            const label = f.querySelector('.form-label');
            label.textContent = `Dokumen ${i + 1}`;
        });
    }
});

// Show add button after page load
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('add-document').style.display = 'inline-block';
});
</script>

</body>
</html>