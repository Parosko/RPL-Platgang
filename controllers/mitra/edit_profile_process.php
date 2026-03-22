<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMitra();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/mitra/profile.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$nama_organisasi = trim($_POST['nama_organisasi'] ?? '');
$deskripsi = trim($_POST['deskripsi'] ?? '');
$kontak = trim($_POST['kontak'] ?? '');

if (!$nama_organisasi) {
    $_SESSION['error'] = 'Nama organisasi wajib diisi.';
    header('Location: ../../views/mitra/edit_profile.php');
    exit;
}

$query = "UPDATE mitra SET nama_organisasi = ?, deskripsi = ?, kontak = ? WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'sssi', $nama_organisasi, $deskripsi, $kontak, $user_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = 'Profil berhasil diperbarui.';
} else {
    $_SESSION['error'] = 'Terjadi kesalahan. Silakan coba lagi.';
}

header('Location: ../../views/mitra/profile.php');
exit;
?>