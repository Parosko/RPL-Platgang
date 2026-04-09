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
$total_mahasiswa = 0;
$total_lamaran_all = 0;
$total_diterima_all = 0;

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
    
    // Accumulate stats for cards
    $total_mahasiswa++;
    $total_lamaran_all += $count['total'];
    $total_diterima_all += $accepted['accepted'];

    $students[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mahasiswa Bimbingan | Sistem Peluang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/dpa.css">
</head>

<body>

<div class="d-flex">

    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">

        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Mahasiswa Bimbingan</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-people-fill me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body">Daftar seluruh mahasiswa yang berada di bawah bimbingan Anda.</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <div class="text-center px-3 py-2" style="background: var(--info-light); border-radius: var(--radius-lg); border: 1px solid var(--border-base);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Bimbingan</div>
                    <div class="fw-bold" style="color: var(--info); font-size: 1.25rem;"><?php echo $total_mahasiswa; ?></div>
                </div>
                <div class="text-center px-3 py-2" style="background: var(--warning-light); border-radius: var(--radius-lg); border: 1px solid var(--warning-border);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Lamaran</div>
                    <div class="fw-bold" style="color: var(--warning); font-size: 1.25rem;"><?php echo $total_lamaran_all; ?></div>
                </div>
                <div class="text-center px-3 py-2" style="background: var(--success-light); border-radius: var(--radius-lg); border: 1px solid var(--success-border);">
                    <div class="text-muted" style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Total Diterima</div>
                    <div class="fw-bold" style="color: var(--success); font-size: 1.25rem;"><?php echo $total_diterima_all; ?></div>
                </div>
            </div>
        </div>

        <?php if (empty($students)): ?>
            <div class="dpa-empty-state text-center p-5 mt-4">
                <i class="bi bi-people empty-icon mb-3"></i>
                <h5 class="mb-2">Belum Ada Mahasiswa Bimbingan</h5>
                <p class="text-muted mb-0">Saat ini belum ada mahasiswa yang dialokasikan ke Anda.</p>
            </div>
        <?php else: ?>
            <div class="dpa-profile-card">
                <div class="table-responsive">
                    <table class="table dpa-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="15%">NIM</th>
                                <th width="25%">Nama</th>
                                <th width="20%">Program Studi</th>
                                <th width="10%">IPK</th>
                                <th width="10%" class="text-center">Lamaran</th>
                                <th width="10%" class="text-center">Diterima</th>
                                <th width="10%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td class="nim text-muted fw-medium"><?php echo htmlspecialchars($student['nim'] ?? '-'); ?></td>
                                    <td class="nama fw-semibold" style="color: #0F172A;">
                                        <?php echo htmlspecialchars($student['nama'] ?? '-'); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['prodi'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge-status status-open">
                                            <i class="bi bi-star-fill me-1" style="font-size: 0.7rem;"></i> 
                                            <?php echo htmlspecialchars($student['ipk'] ?? '-'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1">
                                            <?php echo $student['total_applications']; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($student['accepted_count'] > 0): ?>
                                            <span class="badge-status status-success px-2 py-1">
                                                <?php echo $student['accepted_count']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="mahasiswa_detail.php?id=<?php echo $student['id']; ?>" class="btn btn-soft-outline detail-btn text-nowrap">
                                            <i class="bi bi-eye me-1"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>