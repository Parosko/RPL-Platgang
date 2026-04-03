<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMitra();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$peluang_id = isset($_POST['peluang_id']) ? (int)$_POST['peluang_id'] : 0;

if (!$peluang_id) {
    echo json_encode(['success' => false, 'message' => 'ID postingan tidak ditemukan.']);
    exit;
}

// Verify that this post belongs to the mitra
$query = "SELECT id FROM peluang WHERE id = ? AND mitra_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $peluang_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Postingan tidak ditemukan atau Anda tidak memiliki akses.']);
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Close the post
    $query = "UPDATE peluang SET closed_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $peluang_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal menutup postingan: ' . mysqli_error($conn));
    }

    // Get all non-accepted applications
    $query = "SELECT l.id, l.mahasiswa_id FROM lamaran l
              WHERE l.peluang_id = ? AND l.status != 'accepted'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $peluang_id);
    mysqli_stmt_execute($stmt);
    $applications = mysqli_stmt_get_result($stmt);

    $rejected_count = 0;

    // Reject all non-accepted applications
    while ($app = mysqli_fetch_assoc($applications)) {
        $query = "UPDATE lamaran SET status = 'rejected' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $app['id']);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal mengubah status lamaran: ' . mysqli_error($conn));
        }

        // Get mahasiswa user_id to create notification
        $query2 = "SELECT m.user_id FROM mahasiswa m WHERE m.id = ?";
        $stmt2 = mysqli_prepare($conn, $query2);
        mysqli_stmt_bind_param($stmt2, 'i', $app['mahasiswa_id']);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        
        if ($mahasiswa = mysqli_fetch_assoc($result2)) {
            $notif_message = 'Postingan yang Anda lamar telah ditutup dan lamaran Anda ditolak.';
            $query3 = "INSERT INTO notifikasi (user_id, pesan) VALUES (?, ?)";
            $stmt3 = mysqli_prepare($conn, $query3);
            mysqli_stmt_bind_param($stmt3, 'is', $mahasiswa['user_id'], $notif_message);
            mysqli_stmt_execute($stmt3);
        }

        $rejected_count++;
    }

    // Commit transaction
    mysqli_commit($conn);

    echo json_encode([
        'success' => true,
        'message' => "Postingan berhasil ditutup. {$rejected_count} lamaran ditolak.",
        'rejected_count' => $rejected_count
    ]);

} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
