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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Notifikasi | Sistem Peluang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/detail_notification.css"> 
</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/layouts/sidebar.php'; ?>

    <div class="content">

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Detail Notifikasi</h1>
                <p class="page-subtitle mt-1">Rincian informasi dari pemberitahuan sistem.</p>
            </div>
            <a href="notifications.php" class="btn btn-action-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>

        <div class="detail-card">
            
            <div class="detail-header d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-wrapper <?php echo $notification['tipe_notifikasi'] === 'recommendation' ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary'; ?>">
                        <i class="bi <?php echo $notification['tipe_notifikasi'] === 'recommendation' ? 'bi-star-fill' : 'bi-envelope-paper-fill'; ?>"></i>
                    </div>
                    <div>
                        <span class="badge-enterprise <?php echo $notification['tipe_notifikasi'] === 'recommendation' ? 'badge-primary' : 'badge-secondary'; ?> mb-1">
                            <?php 
                            switch($notification['tipe_notifikasi']) {
                                case 'recommendation':
                                    echo 'Rekomendasi';
                                    break;
                                case 'result':
                                    echo 'Hasil Lamaran';
                                    break;
                                default:
                                    echo 'Pemberitahuan Sistem';
                            }
                            ?>
                        </span>
                        <div class="text-meta">
                            <i class="bi bi-clock"></i>
                            <?php 
                            $date = new DateTime($notification['created_at']);
                            echo $date->format('d M Y, H:i');
                            ?>
                        </div>
                    </div>
                </div>

                <?php if ($notification['pengirim_nama']): ?>
                    <div class="sender-info text-md-end">
                        <span class="info-label d-block">Dikirim Oleh</span>
                        <strong class="d-block text-headline"><?php echo htmlspecialchars($notification['pengirim_nama']); ?></strong>
                        <span class="text-meta"><?php echo ucfirst(htmlspecialchars($notification['pengirim_role'])); ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="main-message-section">
                <h4 class="message-text"><?php echo htmlspecialchars($notification['pesan']); ?></h4>
            </div>

            <?php if ($related_info): ?>
                <div class="related-info-section">
                    <h6 class="section-title"><i class="bi bi-link-45deg"></i> Informasi Terkait</h6>
                    
                    <div class="related-info-grid">
                        <?php if ($notification['tipe_notifikasi'] === 'recommendation'): ?>
                            
                            <div class="info-group">
                                <span class="info-label">Peluang</span>
                                <a href="../views/posts/detail.php?id=<?php echo $related_info['peluang_id']; ?>" class="info-value-link">
                                    <?php echo htmlspecialchars($related_info['peluang_judul']); ?> <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.8em;"></i>
                                </a>
                            </div>
                            
                            <div class="info-group">
                                <span class="info-label">Dosen Pembimbing (DPA)</span>
                                <span class="info-value"><?php echo htmlspecialchars($related_info['dpa_nama']); ?></span>
                            </div>

                            <div class="info-group">
                                <span class="info-label">Organisasi / Mitra</span>
                                <span class="info-value"><?php echo htmlspecialchars($related_info['nama_organisasi'] ?? 'Tidak diketahui'); ?></span>
                            </div>

                            <?php if ($_SESSION['role'] === 'mahasiswa'): ?>
                                <div class="info-group">
                                    <span class="info-label">Status Anda Saat Ini</span>
                                    <div>
                                        <span class="badge-status <?php echo $related_info['status_lamaran'] === 'accepted' ? 'status-success' : ($related_info['status_lamaran'] === 'rejected' ? 'status-danger' : 'status-warning'); ?>">
                                            <?php 
                                            if ($related_info['status_lamaran']) {
                                                switch($related_info['status_lamaran']) {
                                                    case 'accepted': echo '<i class="bi bi-check-circle me-1"></i>Diterima'; break;
                                                    case 'rejected': echo '<i class="bi bi-x-circle me-1"></i>Ditolak'; break;
                                                    default: echo '<i class="bi bi-hourglass-split me-1"></i>Diproses';
                                                }
                                            } else {
                                                echo '<i class="bi bi-dash-circle me-1"></i>Belum Melamar';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($related_info['pesan_dosen'])): ?>
                                <div class="quote-box mt-3" style="grid-column: 1 / -1;">
                                    <i class="bi bi-quote quote-icon"></i>
                                    <div class="quote-content">
                                        <span class="info-label">Catatan Rekomendasi DPA:</span>
                                        <p><?php echo nl2br(htmlspecialchars($related_info['pesan_dosen'])); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                        <?php elseif ($notification['tipe_notifikasi'] === 'result'): ?>
                            
                            <div class="info-group">
                                <span class="info-label">Peluang</span>
                                <a href="../views/posts/detail.php?id=<?php echo $related_info['peluang_id']; ?>" class="info-value-link">
                                    <?php echo htmlspecialchars($related_info['peluang_judul']); ?> <i class="bi bi-box-arrow-up-right ms-1" style="font-size: 0.8em;"></i>
                                </a>
                            </div>

                            <div class="info-group">
                                <span class="info-label">Organisasi / Mitra</span>
                                <span class="info-value"><?php echo htmlspecialchars($related_info['nama_organisasi'] ?? 'Tidak diketahui'); ?></span>
                            </div>

                            <div class="info-group">
                                <span class="info-label">Status Keputusan</span>
                                <div>
                                    <span class="badge-status <?php echo $related_info['status'] === 'accepted' ? 'status-success' : 'status-danger'; ?>">
                                        <?php echo $related_info['status'] === 'accepted' ? '<i class="bi bi-check-circle me-1"></i>Diterima' : '<i class="bi bi-x-circle me-1"></i>Ditolak'; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="info-group">
                                <span class="info-label">Tanggal Melamar</span>
                                <span class="info-value"><?php echo date('d M Y, H:i', strtotime($related_info['tanggal_apply'])); ?></span>
                            </div>

                            <?php if (!empty($related_info['pesan_mitra'])): ?>
                                <div class="quote-box mt-3" style="grid-column: 1 / -1;">
                                    <i class="bi bi-quote quote-icon"></i>
                                    <div class="quote-content">
                                        <span class="info-label">Catatan dari <?php echo htmlspecialchars($related_info['nama_organisasi'] ?? 'Mitra'); ?>:</span>
                                        <p><?php echo nl2br(htmlspecialchars($related_info['pesan_mitra'])); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="detail-actions">
                <?php if ($related_info): ?>
                    <a href="../views/posts/detail.php?id=<?php echo $related_info['peluang_id']; ?>" class="btn-action-primary">
                        Lihat Detail Peluang <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                <?php endif; ?>
            </div>
            
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>