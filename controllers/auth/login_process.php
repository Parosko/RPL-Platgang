<?php
session_start();
include '../../config/database.php';

// Ambil data
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];

// Validasi
if (!$email || !$password) {
    die("Data tidak valid");
}

// Ambil user (ANTI SQL INJECTION)
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {

    // Amankan session
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    // AUTO REDIRECT ROLE
    if ($user['role'] == 'mahasiswa') {
        header("Location: ../../views/mahasiswa/dashboard.php");
    } elseif ($user['role'] == 'mitra') {
        header("Location: ../../views/mitra/dashboard.php");
    } elseif ($user['role'] == 'dpa') {
        header("Location: ../../views/dpa/dashboard.php");
    } elseif ($user['role'] == 'admin') {
        header("Location: ../../views/admin/dashboard.php");
    }

    exit;

} else {
    header("Location: ../../views/auth/login.php?error=1");
    exit;
}