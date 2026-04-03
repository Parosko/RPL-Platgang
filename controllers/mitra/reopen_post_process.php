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
$query = "SELECT id, closed_at FROM peluang WHERE id = ? AND mitra_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $peluang_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Postingan tidak ditemukan atau Anda tidak memiliki akses.']);
    exit;
}

$post = mysqli_fetch_assoc($result);

if (empty($post['closed_at'])) {
    echo json_encode(['success' => false, 'message' => 'Postingan tidak tertutup, tidak perlu dibuka kembali.']);
    exit;
}

// Reopen the post by setting closed_at to NULL
$query = "UPDATE peluang SET closed_at = NULL WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $peluang_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true,
        'message' => 'Postingan berhasil dibuka kembali. Applicants dapat mendaftar lagi.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . mysqli_error($conn)]);
}
?>
