<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

onlyDPA();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Post tidak ditemukan.';
    header('Location: ../../views/dashboard.php');
    exit;
}

$post_id = (int)$_GET['id'];
$dpa_user_id = $_SESSION['user_id'];

// Get post details
$query = "SELECT p.*, m.nama_organisasi FROM peluang p
          LEFT JOIN mitra m ON p.mitra_id = m.user_id
          WHERE p.id = ? AND p.status = 'approved' AND p.closed_at IS NULL";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $post_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Post tidak ditemukan atau tidak aktif.';
    header('Location: ../../views/dashboard.php');
    exit;
}

$post = mysqli_fetch_assoc($result);

// Get DPA ID
$query = "SELECT id FROM dpa WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $dpa_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$dpa = mysqli_fetch_assoc($result);
$dpa_id = $dpa['id'];

// Get all students assigned to this DPA
$query = "SELECT m.id, m.nim, m.nama, m.ipk FROM mahasiswa m
          WHERE m.dpa_id = ?
          ORDER BY m.nama ASC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $dpa_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$students = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Check if already recommended
    $check_query = "SELECT id FROM rekomendasi WHERE dpa_id = ? AND mahasiswa_id = ? AND peluang_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'iii', $dpa_id, $row['id'], $post_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $row['already_recommended'] = mysqli_num_rows($check_result) > 0;
    
    $students[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasikan Peluang | Sistem Peluang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/dpa.css">
</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Rekomendasikan Peluang</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-person-check-fill me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Pilih mahasiswa yang akan Anda rekomendasikan untuk peluang ini.</span>
                </div>
            </div>
            <a href="../../views/posts/detail.php?id=<?php echo $post_id; ?>" class="btn btn-soft-outline px-4">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <div class="dpa-edit-card mb-4">
            <div class="dpa-edit-header">
                <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i>Informasi Peluang</h5>
            </div>
            <div class="dpa-edit-body">
                <h4 style="font-weight: 600; color: #0F172A; margin-bottom: 0.25rem;">
                    <?php echo htmlspecialchars($post['judul']); ?>
                </h4>
                <div class="text-muted mb-3" style="font-size: 0.95rem;">
                    <i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($post['nama_organisasi'] ?? 'Mitra'); ?>
                </div>
                
                <div class="d-flex flex-wrap gap-2">
                    <span class="dpa-badge"><i class="bi bi-star me-1"></i> Min. IPK: <?php echo $post['min_ipk']; ?></span>
                    <span class="dpa-badge"><i class="bi bi-journal-text me-1"></i> Min. Semester: <?php echo $post['min_semester']; ?></span>
                    <span class="dpa-badge"><i class="bi bi-people me-1"></i> Kuota: <?php echo $post['kuota']; ?></span>
                </div>
            </div>
        </div>

        <div class="dpa-edit-card">
            <div class="dpa-edit-header">
                <h5 class="mb-0"><i class="bi bi-card-checklist me-2"></i>Daftar Mahasiswa Bimbingan</h5>
            </div>
            <div class="dpa-edit-body">
                <?php if (empty($students)): ?>
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <div>Anda belum memiliki mahasiswa yang dibimbing.</div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="../../controllers/dpa/recommend_post_process.php">
                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                        <input type="hidden" name="dpa_id" value="<?php echo $dpa_id; ?>">

                        <div class="student-selection-list">
                            <?php foreach ($students as $student): ?>
                                <div class="student-selection-item">
                                    <div class="form-check d-flex align-items-start">
                                        <input class="form-check-input mt-1 me-3" type="checkbox" 
                                               name="selected_students[]" 
                                               value="<?php echo $student['id']; ?>" 
                                               id="student_<?php echo $student['id']; ?>"
                                               <?php echo $student['already_recommended'] ? 'disabled' : ''; ?>>
                                        <label class="form-check-label w-100" for="student_<?php echo $student['id']; ?>">
                                            <strong style="font-size: 1.05rem;"><?php echo htmlspecialchars($student['nama']); ?></strong>
                                            <div class="text-muted mt-1" style="font-size: 0.85rem;">
                                                <i class="bi bi-person-vcard me-1"></i> NIM: <?php echo htmlspecialchars($student['nim']); ?> &nbsp;|&nbsp; 
                                                <i class="bi bi-bar-chart-line me-1"></i> IPK: <?php echo htmlspecialchars($student['ipk']); ?>
                                            </div>
                                            <?php if ($student['already_recommended']): ?>
                                                <span class="badge bg-success mt-2"><i class="bi bi-check-circle me-1"></i>Sudah Direkomendasikan</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-4">
                            <label for="pesan_dosen" class="custom-label">
                                <i class="bi bi-chat-left-text me-2"></i>Pesan untuk Mahasiswa (Opsional)
                            </label>
                            <textarea class="custom-input mt-2" id="pesan_dosen" name="pesan_dosen" rows="4" 
                                      placeholder="Contoh: Saya merekomendasikan peluang ini karena sesuai dengan minat dan kemampuan Anda..."></textarea>
                            <small class="form-text mt-2 d-block">
                                Tulis pesan khusus yang akan dikirim bersama notifikasi rekomendasi. Kosongkan jika ingin menggunakan pesan standar.
                            </small>
                        </div>

                        <div class="d-flex flex-column flex-md-row gap-3 mt-5">
                            <button type="submit" class="btn btn-navy px-4" id="submitBtn">
                                <i class="bi bi-send-check me-2"></i>Kirim Rekomendasi
                            </button>
                            <a href="../../views/posts/detail.php?id=<?php echo $post_id; ?>" class="btn btn-soft-outline px-4">
                                <i class="bi bi-x-circle me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (!form) return; // Guard clause if no students

    const submitBtn = document.getElementById('submitBtn');
    const checkboxes = document.querySelectorAll('input[name="selected_students[]"]:not(:disabled)');
    
    form.addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('input[name="selected_students[]"]:checked');
        
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            
            // Show error message
            const existingAlert = document.querySelector('.alert-danger');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger mt-3 d-flex align-items-center';
            alert.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i> Pilih minimal satu mahasiswa untuk direkomendasikan.';
            
            submitBtn.parentNode.insertBefore(alert, submitBtn);
            
            // Scroll to alert
            alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 5000);
        }
    });
    
    // Real-time validation feedback
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const checkedBoxes = document.querySelectorAll('input[name="selected_students[]"]:checked');
            
            if (checkedBoxes.length > 0) {
                submitBtn.disabled = false;
                
                // Remove any existing error alerts
                const existingAlert = document.querySelector('.alert-danger');
                if (existingAlert) {
                    existingAlert.remove();
                }
            } else {
                submitBtn.disabled = true;
            }
        });
    });
    
    // Initial state setup
    const initialChecked = document.querySelectorAll('input[name="selected_students[]"]:checked');
    if (initialChecked.length === 0) {
        submitBtn.disabled = true;
    }
});
</script>

</body>
</html>