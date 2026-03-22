<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMahasiswa();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/dashboard.php');
    exit;
}

$peluang_id = (int)$_POST['peluang_id'];
$user_id = $_SESSION['user_id'];

// Get mahasiswa id
$query = "SELECT id FROM mahasiswa WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Data mahasiswa tidak ditemukan.';
    header('Location: ../../views/dashboard.php');
    exit;
}

$mahasiswa = mysqli_fetch_assoc($result);
$mahasiswa_id = $mahasiswa['user_id'];

// Check if post exists
$query = "SELECT id FROM peluang WHERE id = ? AND status = 'approved'";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $peluang_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Peluang tidak ditemukan.';
    header('Location: ../../views/dashboard.php');
    exit;
}

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

// Insert lamaran
$query = "INSERT INTO lamaran (mahasiswa_id, peluang_id) VALUES (?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $mahasiswa_id, $peluang_id);

if (!mysqli_stmt_execute($stmt)) {
    $_SESSION['error'] = 'Terjadi kesalahan saat menyimpan lamaran.';
    header('Location: ../../views/mahasiswa/apply.php?id=' . $peluang_id);
    exit;
}

$lamaran_id = mysqli_insert_id($conn);

// Handle file uploads
$upload_dir = __DIR__ . '/../../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'png'];
$max_size = 5 * 1024 * 1024; // 5MB
$uploaded_files = 0;

if (isset($_FILES['documents'])) {
    foreach ($_FILES['documents']['name'] as $key => $name) {
        if (empty($name)) continue;
        
        $tmp_name = $_FILES['documents']['tmp_name'][$key];
        $size = $_FILES['documents']['size'][$key];
        $error = $_FILES['documents']['error'][$key];
        
        if ($error !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Error uploading file: ' . $name;
            header('Location: ../../views/mahasiswa/apply.php?id=' . $peluang_id);
            exit;
        }
        
        if ($size > $max_size) {
            $_SESSION['error'] = 'File ' . $name . ' terlalu besar (maks 5MB).';
            header('Location: ../../views/mahasiswa/apply.php?id=' . $peluang_id);
            exit;
        }
        
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types)) {
            $_SESSION['error'] = 'Tipe file ' . $name . ' tidak diizinkan.';
            header('Location: ../../views/mahasiswa/apply.php?id=' . $peluang_id);
            exit;
        }
        
        // Generate unique filename
        $new_name = uniqid('doc_' . $lamaran_id . '_') . '.' . $ext;
        $file_path = $upload_dir . $new_name;
        
        if (move_uploaded_file($tmp_name, $file_path)) {
            // Insert document record
            $query = "INSERT INTO dokumen (lamaran_id, jenis, file_path) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            $jenis = 'Dokumen ' . ($uploaded_files + 1);
            mysqli_stmt_bind_param($stmt, 'iss', $lamaran_id, $jenis, $new_name);
            mysqli_stmt_execute($stmt);
            $uploaded_files++;
        } else {
            $_SESSION['error'] = 'Gagal mengunggah file: ' . $name;
            header('Location: ../../views/mahasiswa/apply.php?id=' . $peluang_id);
            exit;
        }
    }
}

$_SESSION['success'] = 'Lamaran berhasil dikirim! ' . $uploaded_files . ' dokumen telah diunggah.';
header('Location: ../../views/dashboard.php');
exit;
?>