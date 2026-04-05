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
<html>
<head>
    <title>Rekomendasikan Peluang</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/dpa.css">
</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Rekomendasikan Peluang</h4>
                <small>Pilih mahasiswa yang akan Anda rekomendasikan untuk peluang ini</small>
            </div>
            <a href="../../views/posts/detail.php?id=<?php echo $post_id; ?>" class="btn btn-secondary">Kembali</a>
        </div>

        <hr>

        <!-- Post Info Card -->
        <div class="card mb-4">
            <div class="card-body">
                <h5><?php echo htmlspecialchars($post['judul']); ?></h5>
                <small class="text-muted">
                    <?php echo htmlspecialchars($post['nama_organisasi'] ?? 'Mitra'); ?>
                </small>
                <p class="mt-2 mb-0">
                    <strong>Min. IPK:</strong> <?php echo $post['min_ipk']; ?> | 
                    <strong>Min. Semester:</strong> <?php echo $post['min_semester']; ?> | 
                    <strong>Kuota:</strong> <?php echo $post['kuota']; ?>
                </p>
            </div>
        </div>

        <!-- Student Selection Form -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="alert alert-info">
                        Anda belum memiliki mahasiswa yang dibimbing.
                    </div>
                <?php else: ?>
                    <form method="POST" action="../../controllers/dpa/recommend_post_process.php">
                        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                        <input type="hidden" name="dpa_id" value="<?php echo $dpa_id; ?>">

                        <div class="student-selection-list">
                            <?php foreach ($students as $student): ?>
                                <div class="student-selection-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="selected_students[]" 
                                               value="<?php echo $student['id']; ?>" 
                                               id="student_<?php echo $student['id']; ?>"
                                               <?php echo $student['already_recommended'] ? 'disabled' : ''; ?>>
                                        <label class="form-check-label" for="student_<?php echo $student['id']; ?>">
                                            <strong><?php echo htmlspecialchars($student['nama']); ?></strong>
                                            <small class="text-muted d-block">
                                                NIM: <?php echo htmlspecialchars($student['nim']); ?> | 
                                                IPK: <?php echo htmlspecialchars($student['ipk']); ?>
                                            </small>
                                            <?php if ($student['already_recommended']): ?>
                                                <span class="badge bg-success">Sudah Direkomendasikan</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-4">
                            <label for="pesan_dosen" class="form-label">
                                <strong>Pesan untuk Mahasiswa (Opsional)</strong>
                                <small class="text-muted d-block">
                                    Tulis pesan khusus yang akan dikirim bersama notifikasi rekomendasi.
                                    Kosongkan jika ingin menggunakan pesan standar.
                                </small>
                            </label>
                            <textarea class="form-control" id="pesan_dosen" name="pesan_dosen" rows="3" 
                                      placeholder="Contoh: Saya merekomendasikan peluang ini karena sesuai dengan minat dan kemampuan Anda..."></textarea>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary" id="submitBtn">Rekomendasikan</button>
                            <a href="../../views/posts/detail.php?id=<?php echo $post_id; ?>" class="btn btn-secondary">Batal</a>
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
            alert.className = 'alert alert-danger mt-3';
            alert.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Pilih minimal satu mahasiswa untuk direkomendasikan.';
            
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
                submitBtn.classList.remove('btn-secondary');
                submitBtn.classList.add('btn-primary');
                
                // Remove any existing error alerts
                const existingAlert = document.querySelector('.alert-danger');
                if (existingAlert) {
                    existingAlert.remove();
                }
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-secondary');
            }
        });
    });
    
    // Initial state
    const initialChecked = document.querySelectorAll('input[name="selected_students[]"]:checked');
    if (initialChecked.length === 0) {
        submitBtn.disabled = true;
        submitBtn.classList.remove('btn-primary');
        submitBtn.classList.add('btn-secondary');
    }
});
</script>

</body>
</html>
