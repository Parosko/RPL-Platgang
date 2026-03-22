<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/admin/profile.php');
    exit;
}

// Admin only, no change currently apart from success message
$_SESSION['success'] = 'Profil berhasil diperbarui (tidak ada perubahan field).';
header('Location: ../../views/admin/profile.php');
exit;
?>