<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

onlyAdmin();

// Verify post ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'ID postingan tidak valid.';
    header('Location: ' . BASE_URL . '/views/admin/posts.php');
    exit;
}

$post_id = (int)$_GET['id'];

// Check if post exists and is deactivated
$check_query = "SELECT id, closed_at FROM peluang WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, 'i', $post_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) == 0) {
    $_SESSION['error'] = 'Postingan tidak ditemukan.';
    header('Location: ' . BASE_URL . '/views/admin/posts.php');
    exit;
}

$post_data = mysqli_fetch_assoc($check_result);

if (empty($post_data['closed_at'])) {
    $_SESSION['error'] = 'Postingan ini belum dinonaktifkan.';
    header('Location: ' . BASE_URL . '/views/admin/posts.php');
    exit;
}

// Reactivate the post by setting closed_at to NULL
$update_query = "UPDATE peluang SET closed_at = NULL WHERE id = ?";
$update_stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($update_stmt, 'i', $post_id);

if (mysqli_stmt_execute($update_stmt)) {
    $_SESSION['success'] = 'Postingan berhasil diaktifkan kembali.';
} else {
    $_SESSION['error'] = 'Gagal mengaktifkan postingan: ' . mysqli_error($conn);
}

header('Location: ' . BASE_URL . '/views/admin/posts.php');
exit;
?>
