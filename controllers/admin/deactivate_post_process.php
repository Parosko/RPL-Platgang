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

// Check if post exists
$check_query = "SELECT id FROM peluang WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, 'i', $post_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) == 0) {
    $_SESSION['error'] = 'Postingan tidak ditemukan.';
    header('Location: ' . BASE_URL . '/views/admin/posts.php');
    exit;
}

// Deactivate the post by setting closed_at timestamp
$update_query = "UPDATE peluang SET closed_at = NOW() WHERE id = ?";
$update_stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($update_stmt, 'i', $post_id);

if (mysqli_stmt_execute($update_stmt)) {
    // Auto-reject all pending applications for this post
    $reject_query = "UPDATE lamaran SET status = 'rejected' WHERE peluang_id = ? AND status IN ('pending')";
    $reject_stmt = mysqli_prepare($conn, $reject_query);
    mysqli_stmt_bind_param($reject_stmt, 'i', $post_id);
    mysqli_stmt_execute($reject_stmt);

    $_SESSION['success'] = 'Postingan berhasil dinonaktifkan.';
} else {
    $_SESSION['error'] = 'Gagal menonaktifkan postingan: ' . mysqli_error($conn);
}

header('Location: ' . BASE_URL . '/views/admin/posts.php');
exit;
?>