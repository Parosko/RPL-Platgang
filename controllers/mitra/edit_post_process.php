<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMitra();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/mitra/my_posts.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$peluang_id = isset($_POST['peluang_id']) ? (int)$_POST['peluang_id'] : 0;
$judul = isset($_POST['judul']) ? $_POST['judul'] : '';
$deskripsi = isset($_POST['deskripsi']) ? $_POST['deskripsi'] : '';
$tipe = isset($_POST['tipe']) ? $_POST['tipe'] : '';
$lokasi = isset($_POST['lokasi']) ? $_POST['lokasi'] : '';
$kuota = isset($_POST['kuota']) ? (int)$_POST['kuota'] : 0;
$min_ipk = isset($_POST['min_ipk']) ? (float)$_POST['min_ipk'] : 0;
$min_semester = isset($_POST['min_semester']) ? (int)$_POST['min_semester'] : 1;
$fakultas = isset($_POST['fakultas']) ? $_POST['fakultas'] : '';
$deadline = isset($_POST['deadline']) ? $_POST['deadline'] : '';

// Validate inputs
if (!$peluang_id || !$judul || !$deskripsi || !$tipe || !$kuota || !$deadline) {
    $_SESSION['error'] = 'Semua field yang wajib harus diisi.';
    header('Location: ../../views/mitra/edit_post.php?id=' . $peluang_id);
    exit;
}

// Verify that this post belongs to the mitra
$query = "SELECT id FROM peluang WHERE id = ? AND mitra_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $peluang_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Postingan tidak ditemukan atau Anda tidak memiliki akses.';
    header('Location: ../../views/mitra/my_posts.php');
    exit;
}

// Update the post
$query = "UPDATE peluang SET judul = ?, deskripsi = ?, tipe = ?, lokasi = ?, kuota = ?, 
          min_ipk = ?, min_semester = ?, fakultas = ?, deadline = ?
          WHERE id = ? AND mitra_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ssssiiddsii', 
    $judul, $deskripsi, $tipe, $lokasi, $kuota,
    $min_ipk, $min_semester, $fakultas, $deadline,
    $peluang_id, $user_id
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = 'Postingan berhasil diperbarui.';
    header('Location: ../../views/mitra/applicants.php?id=' . $peluang_id);
    exit;
} else {
    $_SESSION['error'] = 'Terjadi kesalahan saat memperbarui postingan: ' . mysqli_error($conn);
    header('Location: ../../views/mitra/edit_post.php?id=' . $peluang_id);
    exit;
}
?>
