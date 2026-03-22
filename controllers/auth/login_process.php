<?php
session_start();
include '../../config/database.php';

// Ambil input
$email = trim($_POST['email']);
$password = $_POST['password'];

// Validasi input
if (empty($email) || empty($password)) {
    header("Location: ../../views/auth/login.php?error=1");
    exit;
}

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../../views/auth/login.php?error=1");
    exit;
}

// Ambil user
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Validasi user & password
if (!$user || !password_verify($password, $user['password'])) {
    header("Location: ../../views/auth/login.php?error=1");
    exit;
}

// Cek status akun
if ($user['status'] !== 'active') {
    header("Location: ../../views/auth/login.php?error=not_verified");
    exit;
}

// Amankan session
session_regenerate_id(true);

// Simpan session
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];
$_SESSION['email'] = $user['email'];

// Redirect ke dashboard global
header("Location: ../../views/dashboard.php");
exit;