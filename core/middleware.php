<?php

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /platform-karir/views/auth/login.php");
        exit;
    }
}

function checkRole($role) {
    checkLogin();

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: /platform-karir/views/auth/login.php");
        exit;
    }
}

// Shortcut biar tetap enak dipakai
function onlyMahasiswa() {
    checkRole('mahasiswa');
}

function onlyMitra() {
    checkRole('mitra');
}

function onlyDPA() {
    checkRole('dpa');
}

function onlyAdmin() {
    checkRole('admin');
}