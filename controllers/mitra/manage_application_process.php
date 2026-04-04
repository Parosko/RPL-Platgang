<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../core/notification_helper.php';

onlyMitra();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$lamaran_id = isset($_POST['lamaran_id']) ? (int)$_POST['lamaran_id'] : 0;
$peluang_id = isset($_POST['peluang_id']) ? (int)$_POST['peluang_id'] : 0;

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
    exit;
}

// For accept/reject actions, we need lamaran_id
if (($action == 'accept' || $action == 'reject') && !$lamaran_id) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
    exit;
}

// For release_results action, we need peluang_id
if ($action == 'release_results' && !$peluang_id) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
    exit;
}

// ==================== ACCEPT/REJECT LOGIC ====================
if ($action == 'accept' || $action == 'reject') {
    // Verify that this lamaran belongs to mitra's posting
    $query = "SELECT l.id, p.mitra_id
              FROM lamaran l
              JOIN peluang p ON l.peluang_id = p.id
              WHERE l.id = ? AND p.mitra_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $lamaran_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Lamaran tidak ditemukan atau Anda tidak memiliki akses.']);
        exit;
    }

    // Update status based on action (WITHOUT notifying yet)
    if ($action == 'accept') {
        $new_status = 'accepted';
        $message = 'Lamaran ditandai sebagai diterima. Tekan "Release Result" untuk mengumumkannya.';
    } else {
        $new_status = 'rejected';
        $message = 'Lamaran ditandai sebagai ditolak. Tekan "Release Result" untuk mengumumkannya.';
    }

    // Update database - Note: result_published = 0 means not yet released
    $query = "UPDATE lamaran SET status = ?, result_published = 0 WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'si', $new_status, $lamaran_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . mysqli_error($conn)]);
    }
}

// ==================== RELEASE RESULTS LOGIC ====================
elseif ($action == 'release_results') {
    // Verify that this peluang belongs to mitra
    $query = "SELECT p.id, p.closed_at FROM peluang p WHERE p.id = ? AND p.mitra_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $peluang_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Peluang tidak ditemukan atau Anda tidak memiliki akses.']);
        exit;
    }

    $peluang_data = mysqli_fetch_assoc($result);
    $was_already_closed = !empty($peluang_data['closed_at']);

    // Get all unpublished applications for this posting
    $query = "SELECT l.id, l.status, l.mahasiswa_id, m.user_id
              FROM lamaran l
              JOIN mahasiswa m ON l.mahasiswa_id = m.id
              WHERE l.peluang_id = ? AND l.result_published = 0 AND l.status IN ('accepted', 'rejected')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $peluang_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada hasil yang belum dirilis.']);
        exit;
    }

    // Auto-close the post if it's not already closed
    if (!$was_already_closed) {
        $close_query = "UPDATE peluang SET closed_at = NOW() WHERE id = ?";
        $close_stmt = mysqli_prepare($conn, $close_query);
        mysqli_stmt_bind_param($close_stmt, 'i', $peluang_id);
        mysqli_stmt_execute($close_stmt);
    }

    // Update all unpublished results to published and send notifications
    $published_count = 0;
    while ($lamaran = mysqli_fetch_assoc($result)) {
        // Update to published
        $query2 = "UPDATE lamaran SET result_published = 1 WHERE id = ?";
        $stmt2 = mysqli_prepare($conn, $query2);
        mysqli_stmt_bind_param($stmt2, 'i', $lamaran['id']);
        
        if (mysqli_stmt_execute($stmt2)) {
            // Create notification for mahasiswa
            $notif_message = ($lamaran['status'] == 'accepted') ? 
                'Lamaran Anda diterima!' : 
                'Lamaran Anda ditolak.';
            
            $query3 = "INSERT INTO notifikasi (user_id, pesan) VALUES (?, ?)";
            $stmt3 = mysqli_prepare($conn, $query3);
            mysqli_stmt_bind_param($stmt3, 'is', $lamaran['user_id'], $notif_message);
            mysqli_stmt_execute($stmt3);
            
            $published_count++;
        }
    }

    $close_message = $was_already_closed ? '' : ' Postingan telah ditutup secara otomatis.';
    echo json_encode(['success' => true, 'message' => 'Hasil dirilis! ' . $published_count . ' notifikasi dikirim ke pelamar.' . $close_message]);
}

else {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
}
?>
