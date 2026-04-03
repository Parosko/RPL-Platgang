<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';

onlyAdmin();

// Get mahasiswa_id and dpa_id from POST or GET
if (!isset($_GET['mahasiswa_id']) || !isset($_GET['dpa_id']) || !is_numeric($_GET['mahasiswa_id']) || !is_numeric($_GET['dpa_id'])) {
    $_SESSION['error'] = 'Parameter tidak valid.';
    header('Location: ' . BASE_URL . '/views/admin/assign.php');
    exit;
}

$mahasiswa_id = (int)$_GET['mahasiswa_id'];
$dpa_id = (int)$_GET['dpa_id'];

// Verify that the mahasiswa exists and is assigned to this DPA
$mahasiswa_check = "SELECT m.*, u.email FROM mahasiswa m JOIN users u ON m.user_id = u.id WHERE m.id = ? AND m.dpa_id = ?";
$mahasiswa_stmt = mysqli_prepare($conn, $mahasiswa_check);
mysqli_stmt_bind_param($mahasiswa_stmt, 'ii', $mahasiswa_id, $dpa_id);
mysqli_stmt_execute($mahasiswa_stmt);
$mahasiswa_result = mysqli_stmt_get_result($mahasiswa_stmt);

if (mysqli_num_rows($mahasiswa_result) == 0) {
    $_SESSION['error'] = 'Mahasiswa tidak ditemukan atau tidak ditugaskan ke DPA ini.';
    header('Location: ' . BASE_URL . '/views/admin/assign.php');
    exit;
}

$mahasiswa = mysqli_fetch_assoc($mahasiswa_result);

// Verify that the DPA exists
$dpa_check = "SELECT d.*, u.email FROM dpa d JOIN users u ON d.user_id = u.id WHERE d.id = ?";
$dpa_stmt = mysqli_prepare($conn, $dpa_check);
mysqli_stmt_bind_param($dpa_stmt, 'i', $dpa_id);
mysqli_stmt_execute($dpa_stmt);
$dpa_result = mysqli_stmt_get_result($dpa_stmt);

if (mysqli_num_rows($dpa_result) == 0) {
    $_SESSION['error'] = 'DPA tidak ditemukan.';
    header('Location: ' . BASE_URL . '/views/admin/assign.php');
    exit;
}

$dpa = mysqli_fetch_assoc($dpa_result);

// Update mahasiswa's dpa_id to NULL (unassign)
$update_query = "UPDATE mahasiswa SET dpa_id = NULL WHERE id = ?";
$update_stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($update_stmt, 'i', $mahasiswa_id);
$update_result = mysqli_stmt_execute($update_stmt);

if ($update_result) {
    $_SESSION['success'] = "Berhasil melepas {$mahasiswa['nama']} dari bimbingan DPA {$dpa['nama']}";
    header('Location: ' . BASE_URL . '/views/admin/dpa_detail.php?id=' . $dpa_id);
} else {
    $_SESSION['error'] = 'Gagal melepas mahasiswa. Silakan coba lagi.';
    header('Location: ' . BASE_URL . '/views/admin/dpa_detail.php?id=' . $dpa_id);
}

exit;
?>
