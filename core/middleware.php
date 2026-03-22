<?php
session_start();

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit;
    }
}

function onlyMahasiswa() {
    checkLogin();
    if ($_SESSION['role'] != 'mahasiswa') {
        header("Location: ../auth/login.php");
        exit;
    }
}

function onlyMitra() {
    checkLogin();
    if ($_SESSION['role'] != 'mitra') {
        header("Location: ../auth/login.php");
        exit;
    }
}

function onlyDPA() {
    checkLogin();
    if ($_SESSION['role'] != 'dpa') {
        header("Location: ../auth/login.php");
        exit;
    }
}

function onlyAdmin() {
    checkLogin();
    if ($_SESSION['role'] != 'admin') {
        header("Location: ../auth/login.php");
        exit;
    }
}