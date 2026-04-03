<?php
/**
 * Check if a mahasiswa has completed their required profile fields
 * Returns true if profile is complete, false otherwise
 */
function isMahasiswaProfileComplete($conn, $user_id) {
    $query = "SELECT nim, nama FROM mahasiswa WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        return false;
    }
    
    $profile = mysqli_fetch_assoc($result);
    
    // Check if both NIM and Nama are filled
    return !empty($profile['nim']) && !empty($profile['nama']);
}

/**
 * Redirect mahasiswa to edit profile if incomplete
 * Should be called after checkLogin()
 */
function redirectIfProfileIncomplete($conn, $current_page = '') {
    // Only apply to mahasiswa role
    if ($_SESSION['role'] !== 'mahasiswa') {
        return;
    }
    
    // Don't redirect if already on edit profile page
    $pages_to_skip = ['edit_profile.php', 'edit_profile_process.php'];
    foreach ($pages_to_skip as $page) {
        if (strpos($current_page, $page) !== false) {
            return;
        }
    }
    
    // Check if profile is complete
    if (!isMahasiswaProfileComplete($conn, $_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/views/mahasiswa/edit_profile.php?incomplete=1');
        exit;
    }
}
?>
