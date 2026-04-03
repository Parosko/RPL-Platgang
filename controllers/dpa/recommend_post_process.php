<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../core/notification_helper.php';

onlyDPA();

// Validate input
if (!isset($_POST['post_id']) || !isset($_POST['dpa_id']) || !is_numeric($_POST['post_id']) || !is_numeric($_POST['dpa_id'])) {
    $_SESSION['error'] = 'Data tidak valid.';
    header('Location: ../../views/dashboard.php');
    exit;
}

$post_id = (int)$_POST['post_id'];
$dpa_id = (int)$_POST['dpa_id'];
$selected_students = isset($_POST['selected_students']) ? $_POST['selected_students'] : [];

// Verify that the DPA matches the current user
$query = "SELECT id FROM dpa WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $dpa_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Anda tidak memiliki akses.';
    header('Location: ../../views/dashboard.php');
    exit;
}

// Verify that the post exists and is approved
$query = "SELECT id, judul FROM peluang WHERE id = ? AND status = 'approved' AND closed_at IS NULL";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $post_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Peluang tidak ditemukan atau tidak aktif.';
    header('Location: ../../views/dashboard.php');
    exit;
}

$post = mysqli_fetch_assoc($result);
$post_title = $post['judul'];

// If no students selected
if (empty($selected_students)) {
    $_SESSION['error'] = 'Pilih minimal satu mahasiswa.';
    header('Location: ../../views/dpa/recommend_post.php?id=' . $post_id);
    exit;
}

// Validate that all selected students belong to this DPA
$student_ids = [];
foreach ($selected_students as $student_id) {
    if (is_numeric($student_id)) {
        $student_ids[] = (int)$student_id;
    }
}

if (empty($student_ids)) {
    $_SESSION['error'] = 'Data tidak valid.';
    header('Location: ../../views/dpa/recommend_post.php?id=' . $post_id);
    exit;
}

// Verify all students belong to this DPA
$ids_string = implode(',', $student_ids);
$query = "SELECT COUNT(*) as count FROM mahasiswa WHERE dpa_id = ? AND id IN ($ids_string)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $dpa_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$count_result = mysqli_fetch_assoc($result);

if ($count_result['count'] != count($student_ids)) {
    $_SESSION['error'] = 'Beberapa mahasiswa tidak termasuk dalam bimbingan Anda.';
    header('Location: ../../views/dpa/recommend_post.php?id=' . $post_id);
    exit;
}

// Insert recommendations
$success_count = 0;
$error_count = 0;

foreach ($student_ids as $mahasiswa_id) {
    // Check if already recommended
    $check_query = "SELECT id FROM rekomendasi WHERE dpa_id = ? AND mahasiswa_id = ? AND peluang_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'iii', $dpa_id, $mahasiswa_id, $post_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) == 0) {
        // Get mahasiswa user_id for notification
        $user_query = "SELECT user_id FROM mahasiswa WHERE id = ?";
        $user_stmt = mysqli_prepare($conn, $user_query);
        mysqli_stmt_bind_param($user_stmt, 'i', $mahasiswa_id);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        $mahasiswa = mysqli_fetch_assoc($user_result);
        $mahasiswa_user_id = $mahasiswa['user_id'];

        // Insert new recommendation
        $insert_query = "INSERT INTO rekomendasi (dpa_id, mahasiswa_id, peluang_id) VALUES (?, ?, ?)";
        $insert_stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'iii', $dpa_id, $mahasiswa_id, $post_id);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $success_count++;
            
            // Create notification for mahasiswa
            $notification_message = "DPA merekomendasikan peluang \"$post_title\" untuk Anda. Silakan periksa peluang ini.";
            createNotification($conn, $mahasiswa_user_id, $notification_message);
            
            // Update is_recommended in lamaran table if student already applied
            $update_query = "UPDATE lamaran SET is_recommended = 1 
                           WHERE mahasiswa_id = ? AND peluang_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'ii', $mahasiswa_id, $post_id);
            mysqli_stmt_execute($update_stmt);
        } else {
            $error_count++;
        }
    }
}

// Set session messages
if ($success_count > 0) {
    $_SESSION['success'] = "Berhasil merekomendasikan peluang kepada $success_count mahasiswa.";
} else {
    $_SESSION['error'] = 'Gagal merekomendasikan peluang.';
}

// Redirect back to post detail
header('Location: ../../views/posts/detail.php?id=' . $post_id);
exit;
?>
