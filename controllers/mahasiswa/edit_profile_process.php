<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMahasiswa();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/mahasiswa/profile.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$nim = trim($_POST['nim'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$fakultas = trim($_POST['fakultas'] ?? '');
$prodi = trim($_POST['prodi'] ?? '');
$angkatan = trim($_POST['angkatan'] ?? '');
$semester = trim($_POST['semester'] ?? '');
$ipk = trim($_POST['ipk'] ?? '');

if (!$nim || !$nama) {
    $_SESSION['error'] = 'NIM dan Nama wajib diisi.';
    header('Location: ../../views/mahasiswa/edit_profile.php');
    exit;
}

if (strlen($nim) > 20) {
    $_SESSION['error'] = 'NIM tidak boleh lebih dari 20 karakter.';
    header('Location: ../../views/mahasiswa/edit_profile.php');
    exit;
}

if (strlen($nama) > 100) {
    $_SESSION['error'] = 'Nama tidak boleh lebih dari 100 karakter.';
    header('Location: ../../views/mahasiswa/edit_profile.php');
    exit;
}

if (strlen($fakultas) > 100 || strlen($prodi) > 100) {
    $_SESSION['error'] = 'Fakultas/Prodi tidak boleh lebih dari 100 karakter.';
    header('Location: ../../views/mahasiswa/edit_profile.php');
    exit;
}

if ($angkatan !== '' && !preg_match('/^[0-9]{4}$/', $angkatan)) {
    $_SESSION['error'] = 'Angkatan harus format YYYY.';
    header('Location: ../../views/mahasiswa/edit_profile.php');
    exit;
}

if ($semester !== '' && (!is_numeric($semester) || (int) $semester < 1 || (int) $semester > 24)) {
    $_SESSION['error'] = 'Semester harus angka 1-24.';
    header('Location: ../../views/mahasiswa/edit_profile.php');
    exit;
}

if ($ipk !== '' && (!is_numeric($ipk) || (float) $ipk < 0 || (float) $ipk > 4.00)) {
    $_SESSION['error'] = 'IPK harus antara 0.00 sampai 4.00.';
    header('Location: ../../views/mahasiswa/edit_profile.php');
    exit;
}

// Make sure we pass null for blank numeric columns
$angkatan = $angkatan === '' ? null : $angkatan;
$semester = $semester === '' ? null : $semester;
$ipk = $ipk === '' ? null : $ipk;

$query = "UPDATE mahasiswa SET nim = ?, nama = ?, fakultas = ?, prodi = ?, angkatan = ?, semester = ?, ipk = ? WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'sssssssi', $nim, $nama, $fakultas, $prodi, $angkatan, $semester, $ipk, $user_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = 'Profil berhasil diperbarui.';
} else {
    $_SESSION['error'] = 'Terjadi kesalahan. Coba lagi.';
}

header('Location: ../../views/mahasiswa/profile.php');
exit;
?>