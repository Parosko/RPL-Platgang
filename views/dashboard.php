<?php
session_start();

include __DIR__ . '/../core/middleware.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../config/config.php';
include __DIR__ . '/../core/profile_check.php';

checkLogin();
redirectIfProfileIncomplete($conn, __FILE__);

$role = $_SESSION['role'];
$email = $_SESSION['email'];

// Mengambil username berdasarkan role user
$user_id = $_SESSION['user_id'];
$usernameDisplay = '';

switch ($role) {
    case 'mahasiswa':
        $query = "SELECT nama FROM mahasiswa WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $usernameDisplay = $row['nama'];
        }
        break;
        
    case 'mitra':
        $query = "SELECT nama_organisasi FROM mitra WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $usernameDisplay = $row['nama_organisasi'];
        }
        break;
        
    case 'dpa':
        $query = "SELECT nama FROM dpa WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            $usernameDisplay = $row['nama'];
        }
        break;
        
    case 'admin':
        $usernameDisplay = 'Administrator';
        break;
        
    default:
        // Fallback ke potongan email jika tidak ditemukan
        $usernameDisplay = explode('@', $email)[0];
        break;
}

// Jika masih kosong, fallback ke potongan email
if (empty($usernameDisplay)) {
    $usernameDisplay = explode('@', $email)[0];
}

// Logika sapaan ramah berdasarkan waktu (WIB)
date_default_timezone_set('Asia/Jakarta');
$hour = date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = "Selamat pagi";
} elseif ($hour >= 12 && $hour < 15) {
    $greeting = "Selamat siang";
} elseif ($hour >= 15 && $hour < 18) {
    $greeting = "Selamat sore";
} else {
    $greeting = "Selamat malam";
}

// Query untuk mengambil daftar peluang aktif
$query = "SELECT p.*, 
                 (SELECT COUNT(*) FROM lamaran l WHERE l.peluang_id = p.id) as applicant_count,
                 m.nama_organisasi as nama_mitra
          FROM peluang p 
          LEFT JOIN mitra m ON p.mitra_id = m.user_id
          WHERE p.status = 'approved' AND p.closed_at IS NULL 
          ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);

$posts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $posts[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | Sistem Peluang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../assets/css/design-system.css"> 
    <link rel="stylesheet" href="../assets/css/global.css">        
    <link rel="stylesheet" href="../assets/css/layout.css">        
    <link rel="stylesheet" href="../assets/css/components.css">    
    <link rel="stylesheet" href="../assets/css/dashboard.css">     
</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/layouts/sidebar.php'; ?>

    <div class="content">

        <div class="page-header">
            <h1 class="page-title">Beranda</h1>
            <div class="page-subtitle mt-2 d-flex align-items-center">
                <i class="bi bi-person-lines-fill me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                <span class="text-body">
                    <?php echo $greeting; ?>, <strong><?php echo htmlspecialchars($usernameDisplay); ?></strong>. Senang melihat Anda kembali!
                </span>
                <span class="badge-role role-<?php echo strtolower($role); ?> ms-3">
                    <?php echo htmlspecialchars($role); ?>
                </span>
            </div>
        </div>

        <h5 class="section-title">Daftar Peluang</h5>

        <div class="post-container">
            <?php if (empty($posts)): ?>
                <div class="alert-empty-state">
                    <i class="bi bi-inbox"></i>
                    <div>
                        <strong>Belum ada peluang saat ini</strong>
                        <span>Postingan yang tersedia akan muncul di daftar ini.</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($posts as $post): ?>
                        <div class="col-12">
                            <?php include __DIR__ . '/components/post_card.php'; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/**
 * Deactivate post function (For Admin Actions)
 * @param {number} postId 
 * @param {string} postTitle 
 */
function deactivatePost(postId, postTitle) {
    if (confirm(`Apakah Anda yakin ingin menonaktifkan postingan "${postTitle}"?`)) {
        window.location.href = `<?= BASE_URL ?>/controllers/admin/deactivate_post_process.php?id=${postId}`;
    }
}
</script>

</body>
</html>