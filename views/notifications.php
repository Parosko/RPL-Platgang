<?php
session_start();

include __DIR__ . '/../core/middleware.php';
include __DIR__ . '/../config/database.php';
include __DIR__ . '/../config/config.php';

checkLogin();

$user_id = $_SESSION['user_id'];

// Get all notifications for current user (working with current database structure)
$query = "SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
}

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'mark_as_read' && isset($_POST['notif_id'])) {
        $notif_id = (int)$_POST['notif_id'];
        $update_query = "UPDATE notifikasi SET status_baca = 1 WHERE id = ? AND user_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'ii', $notif_id, $user_id);
        mysqli_stmt_execute($update_stmt);
        
        // Return JSON for AJAX
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    } elseif ($_POST['action'] === 'mark_all_as_read') {
        $update_query = "UPDATE notifikasi SET status_baca = 1 WHERE user_id = ? AND status_baca = 0";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, 'i', $user_id);
        mysqli_stmt_execute($update_stmt);
        
        // Refresh notifications
        $query = "SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $notifications = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $notifications[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi | Sistem Peluang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../assets/css/design-system.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css"> <link rel="stylesheet" href="../assets/css/notifications.css"> </head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/layouts/sidebar.php'; ?>

    <div class="content">

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <h1 class="page-title">Notifikasi</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-bell me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Pembaruan dan aktivitas terbaru untuk akun Anda.</span>
                </div>
            </div>
            
            <?php if (!empty(array_filter($notifications, function($n) { return $n['status_baca'] == 0; }))): ?>
                <button class="btn btn-action-secondary" onclick="markAllAsRead()">
                    <i class="bi bi-check2-all"></i> Tandai Semua Dibaca
                </button>
            <?php endif; ?>
        </div>

        <div class="notification-container mt-4">
            <?php if (empty($notifications)): ?>
                <div class="alert-empty-state">
                    <i class="bi bi-bell-slash"></i>
                    <div>
                        <strong>Tidak ada notifikasi baru</strong>
                        <span>Anda sudah membaca semua pemberitahuan saat ini.</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($notifications as $notif): ?>
                        <div class="notification-card <?php echo $notif['status_baca'] == 0 ? 'is-unread' : 'is-read'; ?>" 
                             data-notif-id="<?php echo $notif['id']; ?>">
                            
                            <div class="d-flex gap-3 align-items-start">
                                <div class="notification-icon">
                                    <i class="bi bi-info-circle-fill"></i>
                                </div>

                                <div class="notification-content flex-grow-1">
                                    <a href="detail_notification.php?id=<?php echo $notif['id']; ?>" class="notification-link text-decoration-none">
                                        <div class="notification-message">
                                            <?php echo htmlspecialchars($notif['pesan']); ?>
                                        </div>
                                        <div class="notification-meta mt-1">
                                            <i class="bi bi-clock"></i>
                                            <span class="notification-date">
                                                <?php 
                                                $date = new DateTime($notif['created_at']);
                                                $now = new DateTime();
                                                $diff = $now->diff($date);
                                                
                                                if ($diff->days > 0) {
                                                    echo $diff->days . ' hari yang lalu';
                                                } elseif ($diff->h > 0) {
                                                    echo $diff->h . ' jam yang lalu';
                                                } elseif ($diff->i > 0) {
                                                    echo $diff->i . ' menit yang lalu';
                                                } else {
                                                    echo 'Baru saja';
                                                }
                                                ?>
                                            </span>
                                        </div>
                                    </a>
                                </div>

                                <div class="notification-actions d-flex align-items-center gap-2">
                                    <?php if ($notif['status_baca'] == 0): ?>
                                        <button class="btn btn-icon-ghost" onclick="markAsRead(<?php echo $notif['id']; ?>)" title="Tandai sudah dibaca">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <span class="unread-dot"></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function markAsRead(notifId) {
    fetch('<?= BASE_URL ?>/views/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_as_read&notif_id=' + notifId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let card = document.querySelector('[data-notif-id="' + notifId + '"]');
            if (card) {
                card.classList.remove('is-unread');
                card.classList.add('is-read');
            }
            location.reload();
        }
    });
}

function markAllAsRead() {
    fetch('<?= BASE_URL ?>/views/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_all_as_read'
    })
    .then(response => {
        // Handle response gracefully
        location.reload();
    });
}
</script>

</body>
</html>