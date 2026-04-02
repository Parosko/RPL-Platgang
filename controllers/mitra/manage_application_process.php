<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMitra();

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$lamaran_id = isset($_POST['lamaran_id']) ? (int)$_POST['lamaran_id'] : 0;

if (!$action || !$lamaran_id) {
    echo json_encode(['success' => false, 'message' => 'Parameter tidak lengkap.']);
    exit;
}

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

$lamaran = mysqli_fetch_assoc($result);

// Update status based on action
if ($action == 'accept') {
    $new_status = 'accepted';
    $message = 'Lamaran diterima.';
} elseif ($action == 'reject') {
    $new_status = 'rejected';
    $message = 'Lamaran ditolak.';
} else {
    echo json_encode(['success' => false, 'message' => 'Aksi tidak valid.']);
    exit;
}

// Update database
$query = "UPDATE lamaran SET status = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'si', $new_status, $lamaran_id);

if (mysqli_stmt_execute($stmt)) {
    // Create notification for mahasiswa
    $query2 = "SELECT m.user_id FROM lamaran l
               JOIN mahasiswa m ON l.mahasiswa_id = m.id
               WHERE l.id = ?";
    $stmt2 = mysqli_prepare($conn, $query2);
    mysqli_stmt_bind_param($stmt2, 'i', $lamaran_id);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    $mahasiswa = mysqli_fetch_assoc($result2);
    
    if ($mahasiswa) {
        $notif_message = ($new_status == 'accepted') ? 
            'Lamaran Anda diterima!' : 
            'Lamaran Anda ditolak.';
        
        $query3 = "INSERT INTO notifikasi (user_id, pesan) VALUES (?, ?)";
        $stmt3 = mysqli_prepare($conn, $query3);
        mysqli_stmt_bind_param($stmt3, 'is', $mahasiswa['user_id'], $notif_message);
        mysqli_stmt_execute($stmt3);
    }
    
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . mysqli_error($conn)]);
}
?>
