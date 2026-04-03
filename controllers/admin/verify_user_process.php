<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

onlyAdmin();

// Verify user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'ID user tidak valid.';
    header('Location: ' . BASE_URL . '/views/admin/users.php');
    exit;
}

$user_id = (int)$_GET['id'];

// Check if user exists and is pending
$check_query = "SELECT id, status FROM users WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, 'i', $user_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) == 0) {
    $_SESSION['error'] = 'User tidak ditemukan.';
    header('Location: ' . BASE_URL . '/views/admin/users.php');
    exit;
}

$user_data = mysqli_fetch_assoc($check_result);

if ($user_data['status'] != 'pending') {
    $_SESSION['error'] = 'Akun user ini bukan status pending.';
    header('Location: ' . BASE_URL . '/views/admin/users.php');
    exit;
}

// Verify the user by changing status from 'pending' to 'active'
$update_query = "UPDATE users SET status = 'active' WHERE id = ?";
$update_stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($update_stmt, 'i', $user_id);

if (mysqli_stmt_execute($update_stmt)) {
    $_SESSION['success'] = 'Akun user berhasil diverifikasi.';
} else {
    $_SESSION['error'] = 'Gagal memverifikasi akun user: ' . mysqli_error($conn);
}

header('Location: ' . BASE_URL . '/views/admin/users.php');
exit;
?>
