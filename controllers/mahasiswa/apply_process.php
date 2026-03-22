<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMahasiswa();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../../views/dashboard.php');
    exit;
}

$peluang_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get mahasiswa id
$query = "SELECT id FROM mahasiswa WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Data mahasiswa tidak ditemukan. Lengkapi profil Anda terlebih dahulu.';
    header('Location: ../../views/dashboard.php');
    exit;
}

$mahasiswa = mysqli_fetch_assoc($result);
$mahasiswa_id = $mahasiswa['id'];

// Check if post exists and is approved
$query = "SELECT id, status FROM peluang WHERE id = ? AND status = 'approved'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $peluang_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Peluang tidak ditemukan atau tidak tersedia.';
    header('Location: ../../views/dashboard.php');
    exit;
}

$post = mysqli_fetch_assoc($result);

// Check if already applied
$query = "SELECT id FROM lamaran WHERE mahasiswa_id = ? AND peluang_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $mahasiswa_id, $peluang_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $_SESSION['error'] = 'Anda sudah mendaftar untuk peluang ini.';
    header('Location: ../../views/posts/detail.php?id=' . $peluang_id);
    exit;
}

// Insert application
$query = "INSERT INTO lamaran (mahasiswa_id, peluang_id) VALUES (?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $mahasiswa_id, $peluang_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = 'Berhasil mendaftar untuk peluang ini!';
} else {
    $_SESSION['error'] = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
}

header('Location: ../../views/posts/detail.php?id=' . $peluang_id);
exit;
?>