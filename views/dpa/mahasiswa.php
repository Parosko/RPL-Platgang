<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../config/config.php';
include __DIR__ . '/../../core/profile_check.php';

onlyDPA();

$dpa_user_id = $_SESSION['user_id'];

// Get DPA ID from dpa table
$query = "SELECT id FROM dpa WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $dpa_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("DPA profile not found");
}

$dpa = mysqli_fetch_assoc($result);
$dpa_id = $dpa['id'];

// Get all students assigned to this DPA
$query = "SELECT m.id, m.nim, m.nama, m.prodi, m.ipk, u.email
          FROM mahasiswa m
          JOIN users u ON m.user_id = u.id
          WHERE m.dpa_id = ?
          ORDER BY m.nama ASC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $dpa_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$students = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Count applications for each student
    $count_query = "SELECT COUNT(*) as total FROM lamaran WHERE mahasiswa_id = ?";
    $count_stmt = mysqli_prepare($conn, $count_query);
    mysqli_stmt_bind_param($count_stmt, 'i', $row['id']);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $count = mysqli_fetch_assoc($count_result);
    $row['total_applications'] = $count['total'];
    
    // Count accepted applications
    $accepted_query = "SELECT COUNT(*) as accepted FROM lamaran WHERE mahasiswa_id = ? AND status = 'accepted'";
    $accepted_stmt = mysqli_prepare($conn, $accepted_query);
    mysqli_stmt_bind_param($accepted_stmt, 'i', $row['id']);
    mysqli_stmt_execute($accepted_stmt);
    $accepted_result = mysqli_stmt_get_result($accepted_stmt);
    $accepted = mysqli_fetch_assoc($accepted_result);
    $row['accepted_count'] = $accepted['accepted'];
    
    $students[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mahasiswa Bimbingan</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/dpa.css">
</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Mahasiswa Bimbingan</h4>
                <small>
                    Login sebagai: 
                    <?php echo htmlspecialchars($_SESSION['email']); ?>
                </small>
            </div>
        </div>

        <hr>

        <?php if (empty($students)): ?>
            <div class="alert alert-info">
                Anda belum memiliki mahasiswa yang dibimbing.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover dpa-table">
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Program Studi</th>
                            <th>IPK</th>
                            <th>Total Lamaran</th>
                            <th>Diterima</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td class="nim"><?php echo htmlspecialchars($student['nim'] ?? '-'); ?></td>
                                <td class="nama"><?php echo htmlspecialchars($student['nama'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($student['prodi'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($student['ipk'] ?? '-'); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo $student['total_applications']; ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-success"><?php echo $student['accepted_count']; ?></span>
                                </td>
                                <td>
                                    <a href="mahasiswa_detail.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-primary detail-btn">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

</div>

</body>
</html>
