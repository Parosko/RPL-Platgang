<?php
session_start();

include __DIR__ . '/../core/middleware.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../config/config.php';

checkLogin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Notifikasi tidak ditemukan.';
    header('Location: notifications.php');
    exit;
}

$notif_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get notification details with sender information
$query = "SELECT n.*, u.role as pengirim_role,
          CASE 
            WHEN u.role = 'mahasiswa' THEN (SELECT m.nama FROM mahasiswa m WHERE m.user_id = u.id)
            WHEN u.role = 'dpa' THEN (SELECT d.nama FROM dpa d WHERE d.user_id = u.id)
            WHEN u.role = 'mitra' THEN (SELECT mit.nama_organisasi FROM mitra mit WHERE mit.user_id = u.id)
            ELSE NULL
          END as pengirim_nama
          FROM notifikasi n
          LEFT JOIN users u ON n.pengirim_id = u.id
          WHERE n.id = ? AND n.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $notif_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Notifikasi tidak ditemukan.';
    header('Location: notifications.php');
    exit;
}

$notification = mysqli_fetch_assoc($result);

// Mark as read if unread
if ($notification['status_baca'] == 0) {
    $update_query = "UPDATE notifikasi SET status_baca = 1 WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, 'i', $notif_id);
    mysqli_stmt_execute($update_stmt);
}

// Get related information based on notification type
$related_info = null;
if ($notification['tipe_notifikasi'] === 'recommendation' && $notification['related_id']) {
    // Get recommendation details with application status
    $query = "SELECT r.*, p.judul as peluang_judul, m.nama as mahasiswa_nama, 
              d.nama as dpa_nama, mit.nama_organisasi,
              l.status as status_lamaran
              FROM rekomendasi r
              JOIN peluang p ON r.peluang_id = p.id
              JOIN mahasiswa m ON r.mahasiswa_id = m.id
              JOIN dpa d ON r.dpa_id = d.id
              LEFT JOIN mitra mit ON p.mitra_id = mit.user_id
              LEFT JOIN lamaran l ON r.mahasiswa_id = l.mahasiswa_id AND r.peluang_id = l.peluang_id
              WHERE r.id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $notification['related_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $related_info = mysqli_fetch_assoc($result);
} elseif ($notification['tipe_notifikasi'] === 'result' && $notification['related_id']) {
    // Get application result details
    $query = "SELECT l.*, p.judul as peluang_judul, m.nama as mahasiswa_nama, 
              mit.nama_organisasi, ph.pesan_mitra
              FROM lamaran l
              JOIN peluang p ON l.peluang_id = p.id
              JOIN mahasiswa m ON l.mahasiswa_id = m.id
              LEFT JOIN mitra mit ON p.mitra_id = mit.user_id
              LEFT JOIN pesan_hasil ph ON l.id = ph.lamaran_id AND ph.tipe_hasil = l.status
              WHERE l.id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $notification['related_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $related_info = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Notifikasi</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/notifications.css">
</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/layouts/sidebar.php'; ?>

    <div class="content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Detail Notifikasi</h4>
                <small>Informasi lengkap notifikasi</small>
            </div>
            <a href="notifications.php" class="btn btn-secondary">Kembali ke Notifikasi</a>
        </div>

        <hr>

        <div class="card">
            <div class="card-body">
                <!-- Notification Header -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-<?php echo $notification['tipe_notifikasi'] === 'recommendation' ? 'primary' : 'secondary'; ?>">
                            <?php 
                            switch($notification['tipe_notifikasi']) {
                                case 'recommendation':
                                    echo 'Rekomendasi';
                                    break;
                                case 'result':
                                    echo 'Hasil';
                                    break;
                                default:
                                    echo 'Standar';
                            }
                            ?>
                        </span>
                        <small class="text-muted ms-2">
                            <?php 
                            $date = new DateTime($notification['created_at']);
                            echo $date->format('d M Y H:i');
                            ?>
                        </small>
                    </div>
                    <?php if ($notification['pengirim_nama']): ?>
                        <div class="text-end">
                            <small class="text-muted">Dari:</small><br>
                            <strong><?php echo htmlspecialchars($notification['pengirim_nama']); ?></strong>
                            <small class="text-muted d-block"><?php echo htmlspecialchars($notification['pengirim_role']); ?></small>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Main Message -->
                <div class="notification-main-message mb-4">
                    <h5><?php echo htmlspecialchars($notification['pesan']); ?></h5>
                </div>

                <!-- Custom Message (if exists) -->
                <?php if (!empty($notification['pesan_custom'])): ?>
                    <div class="alert alert-info">
                        <h6><i class="fas fa-comment"></i> Pesan Khusus:</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($notification['pesan_custom'])); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Related Information -->
                <?php if ($related_info): ?>
                    <div class="card border-secondary">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-link"></i> Informasi Terkait</h6>
                        </div>
                        <div class="card-body">
                            <?php if ($notification['tipe_notifikasi'] === 'recommendation'): ?>
                                <!-- Recommendation Information -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Peluang:</strong><br>
                                        <a href="../views/posts/detail.php?id=<?php echo $related_info['peluang_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($related_info['peluang_judul']); ?>
                                        </a></p>
                                        
                                        <p><strong>Organisasi:</strong><br>
                                        <?php echo htmlspecialchars($related_info['nama_organisasi'] ?? 'Tidak diketahui'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>DPA:</strong><br>
                                        <?php echo htmlspecialchars($related_info['dpa_nama']); ?></p>
                                        
                                        <?php if ($_SESSION['role'] === 'mahasiswa'): ?>
                                            <p><strong>Status Anda:</strong><br>
                                            <span class="badge bg-<?php echo $related_info['status_lamaran'] === 'accepted' ? 'success' : ($related_info['status_lamaran'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                                <?php 
                                                if ($related_info['status_lamaran']) {
                                                    switch($related_info['status_lamaran']) {
                                                        case 'accepted':
                                                            echo 'Diterima';
                                                            break;
                                                        case 'rejected':
                                                            echo 'Ditolak';
                                                            break;
                                                        default:
                                                            echo 'Diproses';
                                                    }
                                                } else {
                                                    echo 'Belum Melamar';
                                                }
                                                ?>
                                            </span></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($related_info['pesan_dosen'])): ?>
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <strong><i class="fas fa-quote-left"></i> Pesan DPA:</strong><br>
                                        <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($related_info['pesan_dosen'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                            <?php elseif ($notification['tipe_notifikasi'] === 'result'): ?>
                                <!-- Result Information -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Peluang:</strong><br>
                                        <a href="../views/posts/detail.php?id=<?php echo $related_info['peluang_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($related_info['peluang_judul']); ?>
                                        </a></p>
                                        
                                        <p><strong>Organisasi:</strong><br>
                                        <?php echo htmlspecialchars($related_info['nama_organisasi'] ?? 'Tidak diketahui'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Status Lamaran:</strong><br>
                                        <span class="badge bg-<?php echo $related_info['status'] === 'accepted' ? 'success' : 'danger'; ?>">
                                            <?php echo $related_info['status'] === 'accepted' ? 'Diterima' : 'Ditolak'; ?>
                                        </span></p>
                                        
                                        <p><strong>Tanggal Apply:</strong><br>
                                        <?php echo date('d M Y H:i', strtotime($related_info['tanggal_apply'])); ?></p>
                                    </div>
                                </div>
                                
                                <?php if (!empty($related_info['pesan_mitra'])): ?>
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <strong><i class="fas fa-quote-left"></i> Pesan dari <?php echo htmlspecialchars($related_info['nama_organisasi'] ?? 'Organisasi'); ?>:</strong><br>
                                        <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($related_info['pesan_mitra'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="mt-4 d-flex gap-2">
                    <?php if ($related_info): ?>
                        <a href="../views/posts/detail.php?id=<?php echo $related_info['peluang_id']; ?>" class="btn btn-primary">
                            Lihat Detail Peluang
                        </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                        Kembali
                    </button>
                </div>
            </div>
        </div>

    </div>

</div>

</body>
</html>
