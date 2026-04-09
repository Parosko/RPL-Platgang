<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyDPA();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/dpa/profile.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$nama = trim($_POST['nama'] ?? '');
$nip = trim($_POST['nip'] ?? '');
$fakultas = trim($_POST['fakultas'] ?? '');
$prodi = trim($_POST['prodi'] ?? '');
$kontak = trim($_POST['kontak'] ?? '');

if (!$nama) {
    $_SESSION['error'] = 'Nama wajib diisi.';
    header('Location: ../../views/dpa/edit_profile.php');
    exit;
}

$query = "UPDATE dpa SET nama = ?, nip = ?, fakultas = ?, prodi = ?, kontak = ? WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'sssssi', $nama, $nip, $fakultas, $prodi, $kontak, $user_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = 'Profil berhasil diperbarui.';
} else {
    $_SESSION['error'] = 'Terjadi kesalahan. Silakan coba lagi.';
}

header('Location: ../../views/dpa/profile.php');
exit;
?>