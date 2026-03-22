<?php
session_start();
include '../../config/database.php';

// Ambil input
$email = trim($_POST['email']);
$password = $_POST['password'];
$role = $_POST['role'];

// =====================
// VALIDASI INPUT
// =====================

if (empty($email) || empty($password) || empty($role)) {
    header("Location: ../../views/auth/register.php?error=Semua field wajib diisi");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../../views/auth/register.php?error=Format email tidak valid");
    exit;
}

if (strlen($password) < 6) {
    header("Location: ../../views/auth/register.php?error=Password minimal 6 karakter");
    exit;
}

// =====================
// CEK EMAIL
// =====================

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header("Location: ../../views/auth/register.php?error=Email sudah digunakan");
    exit;
}

// =====================
// STATUS ROLE
// =====================

$status = 'active';

if ($role == 'mitra' || $role == 'dpa') {
    $status = 'pending';
}

// =====================
// HASH PASSWORD
// =====================

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// =====================
// INSERT USER
// =====================

$stmt = $conn->prepare("INSERT INTO users (email, password, role, status) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $email, $hashed_password, $role, $status);

if (!$stmt->execute()) {
    header("Location: ../../views/auth/register.php?error=Terjadi kesalahan");
    exit;
}

$user_id = $stmt->insert_id;

// =====================
// INSERT KE TABEL ROLE
// =====================

if ($role == 'mahasiswa') {
    $conn->query("INSERT INTO mahasiswa (user_id) VALUES ($user_id)");
}

if ($role == 'mitra') {
    $conn->query("INSERT INTO mitra (user_id) VALUES ($user_id)");
}

if ($role == 'dpa') {
    $conn->query("INSERT INTO dpa (user_id) VALUES ($user_id)");
}

// =====================
// REDIRECT AKHIR (UX FIX)
// =====================

if ($role == 'mahasiswa') {
    header("Location: ../../views/auth/login.php?registered=1");
} else {
    header("Location: ../../views/auth/waiting_verification.php");
}

exit;