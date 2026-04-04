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
<html>
<head>
    <title>Notifikasi</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

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
                <h4>Notifikasi</h4>
                <small>
                    <?php echo count(array_filter($notifications, function($n) { return $n['status_baca'] == 0; })); ?> belum dibaca
                </small>
            </div>
            <?php if (!empty(array_filter($notifications, function($n) { return $n['status_baca'] == 0; }))): ?>
                <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">
                    Tandai Semua Sebagai Sudah Dibaca
                </button>
            <?php endif; ?>
        </div>

        <hr>

        <?php if (empty($notifications)): ?>
            <div class="alert alert-info">
                Anda tidak memiliki notifikasi.
            </div>
        <?php else: ?>
            <div class="notification-list">
                <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item <?php echo $notif['status_baca'] == 0 ? 'unread' : 'read'; ?>" 
                         data-notif-id="<?php echo $notif['id']; ?>">
                        <div class="notification-content">
                            <a href="detail_notification.php?id=<?php echo $notif['id']; ?>" class="notification-link">
                                <div class="notification-message">
                                    <?php echo htmlspecialchars($notif['pesan']); ?>
                                </div>
                                
                                <div class="notification-footer">
                                    <small class="notification-date">
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
                                    </small>
                                </div>
                            </a>
                        </div>
                        <?php if ($notif['status_baca'] == 0): ?>
                            <button class="btn btn-xs btn-link" onclick="markAsRead(<?php echo $notif['id']; ?>)">
                                Tandai Dibaca
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</div>

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
            document.querySelector('[data-notif-id="' + notifId + '"]').classList.remove('unread');
            document.querySelector('[data-notif-id="' + notifId + '"]').classList.add('read');
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
    .then(response => response.json())
    .then(data => {
        location.reload();
    });
}
</script>

</body>
</html>
