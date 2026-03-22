<?php
session_start();
include '../../config/database.php';
include '../../core/middleware.php';

onlyMitra();

// Ambil data dari form
$judul = trim($_POST['judul']);
$deskripsi = trim($_POST['deskripsi']);
$tipe = $_POST['tipe'];
$lokasi = trim($_POST['lokasi']);
$kuota = $_POST['kuota'];
$min_ipk = $_POST['min_ipk'];
$min_semester = $_POST['min_semester'];
$fakultas = trim($_POST['fakultas']);
$deadline = $_POST['deadline'];

// Ambil mitra_id dari session
$mitra_id = $_SESSION['user_id'];

// Validasi sederhana
if (empty($judul) || empty($deskripsi) || empty($tipe) || empty($deadline)) {
    header("Location: ../../views/mitra/create_post.php?error=Semua field wajib harus diisi");
    exit;
}

// Validasi tipe
if (!in_array($tipe, ['magang', 'kursus'])) {
    header("Location: ../../views/mitra/create_post.php?error=Tipe tidak valid");
    exit;
}

// Insert ke database (AMAN dari SQL Injection)
$stmt = $conn->prepare("
    INSERT INTO peluang 
    (mitra_id, judul, deskripsi, tipe, lokasi, kuota, min_ipk, min_semester, fakultas, deadline, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");

$stmt->bind_param(
    "issssiidss",
    $mitra_id,
    $judul,
    $deskripsi,
    $tipe,
    $lokasi,
    $kuota,
    $min_ipk,
    $min_semester,
    $fakultas,
    $deadline
);

// Eksekusi
if ($stmt->execute()) {
    header("Location: ../../views/mitra/create_post.php?success=1");
} else {
    header("Location: ../../views/mitra/create_post.php?error=Gagal menyimpan data");
}

exit;