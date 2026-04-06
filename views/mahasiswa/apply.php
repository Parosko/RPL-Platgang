<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../core/profile_check.php';

onlyMahasiswa();
redirectIfProfileIncomplete($conn, __FILE__);

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
mysqli_stmt_bind_param($stmt, 'ii', $profile['id'], $peluang_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    header('Location: already_applied.php?id=' . $peluang_id);
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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peluang - <?php echo htmlspecialchars($post['judul']); ?> | Sistem Peluang</title>
    
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
                <h1 class="page-title fs-3 mb-1">Daftar Peluang</h1>
                <p class="page-subtitle text-muted mb-0">
                    <i class="bi bi-briefcase me-2"></i>Melamar untuk: <?php echo htmlspecialchars($post['judul']); ?>
                </p>
            </div>
            <a href="../../views/posts/detail.php?id=<?php echo $post['id']; ?>" class="btn btn-soft-outline px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

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

        <div class="mhs-edit-card mb-4">
            <div class="mhs-edit-header">
                <h5 class="mb-0"><i class="bi bi-person-check me-2"></i>Persyaratan vs Profil Anda</h5>
            </div>
            <div class="mhs-edit-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-muted fw-semibold mb-3">Persyaratan Peluang</h6>
                        <div class="req-box">
                            <div class="req-item">
                                <span class="req-label">IPK Minimum</span>
                                <span class="req-value"><?php echo $post['min_ipk'] ?: 'Tidak ditentukan'; ?></span>
                            </div>
                            <div class="req-item">
                                <span class="req-label">Semester Minimum</span>
                                <span class="req-value"><?php echo $post['min_semester'] ?: 'Tidak ditentukan'; ?></span>
                            </div>
                            <div class="req-item border-0">
                                <span class="req-label">Fakultas</span>
                                <span class="req-value"><?php echo htmlspecialchars($post['fakultas'] ?: 'Semua Fakultas'); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted fw-semibold mb-3">Profil Saat Ini</h6>
                        <div class="req-box">
                            <div class="req-item <?php echo ($post['min_ipk'] && $profile['ipk'] >= $post['min_ipk']) ? 'req-success' : ($post['min_ipk'] ? 'req-danger' : ''); ?>">
                                <span class="req-label">IPK Anda</span>
                                <span class="req-value"><?php echo $profile['ipk'] ?: 'Belum diisi'; ?></span>
                            </div>
                            <div class="req-item <?php echo ($post['min_semester'] && $profile['semester'] >= $post['min_semester']) ? 'req-success' : ($post['min_semester'] ? 'req-danger' : ''); ?>">
                                <span class="req-label">Semester Anda</span>
                                <span class="req-value"><?php echo $profile['semester'] ?: 'Belum diisi'; ?></span>
                            </div>
                            <div class="req-item border-0 <?php echo (!$post['fakultas'] || $profile['fakultas'] === $post['fakultas']) ? 'req-success' : 'req-danger'; ?>">
                                <span class="req-label">Fakultas Anda</span>
                                <span class="req-value"><?php echo htmlspecialchars($profile['fakultas'] ?: 'Belum diisi'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!$can_apply): ?>
                    <div class="custom-notice notice-warning mt-4 mb-0">
                        <div class="d-flex gap-3 align-items-start">
                            <i class="bi bi-exclamation-circle-fill fs-5 mt-1 notice-icon"></i>
                            <div>
                                <h6 class="mb-1 fw-semibold notice-title">Perhatian! Persyaratan belum terpenuhi</h6>
                                <p class="mb-2 small notice-desc">Anda belum memenuhi beberapa persyaratan dasar untuk peluang ini:</p>
                                <ul class="mb-2 ps-3 small notice-list">
                                    <?php foreach ($requirement_messages as $msg): ?>
                                        <li><?php echo htmlspecialchars($msg); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <p class="mb-0 small notice-footer">Anda tetap dapat melanjutkan pendaftaran jika yakin, namun mitra mungkin akan menolak lamaran Anda.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mhs-edit-card mb-4">
            <div class="mhs-edit-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h5 class="mb-0"><i class="bi bi-cloud-arrow-up me-2"></i>Unggah Dokumen</h5>
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Maks 3 dokumen (PDF/DOCX/JPG/PNG - < 5MB/file)</small>
            </div>
            <div class="mhs-edit-body p-4">
                <form id="applyForm" method="post" action="../../controllers/mahasiswa/apply_process.php" enctype="multipart/form-data">
                    <input type="hidden" name="peluang_id" value="<?php echo $post['id']; ?>">

                    <div class="mb-4">
                        <div id="uploadDropzone" class="upload-dropzone">
                            <input type="file" name="documents[]" id="fileInput" class="d-none" multiple accept=".pdf,.doc,.docx,.jpg,.png" required>
                            
                            <div class="dz-content">
                                <i class="bi bi-file-earmark-arrow-up dz-icon"></i>
                                <h6 class="dz-title">Tarik & Lepas dokumen Anda di sini</h6>
                                <p class="dz-subtitle">atau</p>
                                <button type="button" class="btn btn-soft-outline btn-sm dz-btn" onclick="document.getElementById('fileInput').click()">
                                    Telusuri File
                                </button>
                            </div>
                        </div>
                        
                        <div id="filePreviewContainer" class="d-flex flex-column gap-2 mt-3">
                            </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label custom-label">Catatan Tambahan (Opsional)</label>
                        <textarea name="catatan" class="form-control custom-input" rows="3" placeholder="Contoh: Link portofolio, cover letter singkat, atau keterangan lain..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end pt-3 border-top">
                        <button type="submit" class="btn btn-navy px-4 py-2">
                            <i class="bi bi-send me-2"></i>Kirim Lamaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropzone = document.getElementById('uploadDropzone');
    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('filePreviewContainer');
    
    let selectedFiles = [];
    const MAX_FILES = 3;
    const MAX_SIZE = 5 * 1024 * 1024; // 5MB

    // Prevent default browser behavior
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Add highlight class on drag over
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => dropzone.classList.add('dz-dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => dropzone.classList.remove('dz-dragover'), false);
    });

    // Handle dropped files
    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        handleFiles(dt.files);
    });

    // Handle clicked files
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        const newFiles = Array.from(files);
        
        // Validation limits
        if (selectedFiles.length + newFiles.length > MAX_FILES) {
            alert(`Maksimal ${MAX_FILES} dokumen yang diizinkan.`);
            return;
        }

        newFiles.forEach(file => {
            if (file.size > MAX_SIZE) {
                alert(`File "${file.name}" terlalu besar. Maksimal 5MB per file.`);
                return;
            }
            // Prevent duplicates
            if(!selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                selectedFiles.push(file);
            }
        });
        
        updateUI();
    }

    function updateUI() {
        // Clear preview
        previewContainer.innerHTML = '';
        
        // Remove 'required' if at least one file is present
        fileInput.required = selectedFiles.length === 0;

        // Render preview items
        selectedFiles.forEach((file, index) => {
            let iconClass = 'bi-file-earmark-text';
            if(file.type.includes('image')) iconClass = 'bi-file-earmark-image';
            else if(file.type.includes('pdf')) iconClass = 'bi-file-earmark-pdf';

            const sizeMB = (file.size / (1024 * 1024)).toFixed(2);

            const fileBox = document.createElement('div');
            fileBox.className = 'dz-preview-item';
            fileBox.innerHTML = `
                <div class="d-flex align-items-center gap-3 overflow-hidden">
                    <div class="dz-preview-icon"><i class="bi ${iconClass}"></i></div>
                    <div class="d-flex flex-column overflow-hidden">
                        <span class="dz-preview-name text-truncate">${file.name}</span>
                        <span class="dz-preview-size">${sizeMB} MB</span>
                    </div>
                </div>
                <button type="button" class="dz-remove-btn" onclick="removeFile(${index})">
                    <i class="bi bi-x"></i>
                </button>
            `;
            previewContainer.appendChild(fileBox);
        });

        // Sync with the actual hidden input for form submission
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }

    // Expose removeFile globally for the inline onclick
    window.removeFile = function(index) {
        selectedFiles.splice(index, 1);
        updateUI();
    };
});
</script>

</body>
</html>