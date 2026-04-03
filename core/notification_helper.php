<?php

/**
 * Get unread notification count for a user
 */
function getUnreadNotificationCount($conn, $user_id) {
    $query = "SELECT COUNT(*) as unread FROM notifikasi WHERE user_id = ? AND status_baca = 0";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['unread'] ?? 0;
}

/**
 * Create a notification for a user
 */
function createNotification($conn, $user_id, $message) {
    $query = "INSERT INTO notifikasi (user_id, pesan, status_baca) VALUES (?, ?, 0)";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $message);
    return mysqli_stmt_execute($stmt);
}

?>
