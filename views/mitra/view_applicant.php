<?php
session_start();

include __DIR__ . '/../../core/middleware.php';
include __DIR__ . '/../../config/database.php';

onlyMitra();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: applications.php');
    exit;
}

$lamaran_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get application details
$query = "SELECT l.id, l.tanggal_apply, l.status, l.is_recommended,
                 p.id as peluang_id, p.judul, p.deskripsi, p.tipe, p.lokasi, p.kuota, p.min_ipk, p.min_semester, p.fakultas as required_fakultas, p.deadline,
                 m.nim, m.nama, m.fakultas as applicant_fakultas, m.prodi, m.ipk, m.semester, m.user_id as mahasiswa_user_id,
                 u.email
          FROM lamaran l
          JOIN peluang p ON l.peluang_id = p.id
          JOIN mahasiswa m ON l.mahasiswa_id = m.id
          JOIN users u ON m.user_id = u.id
          WHERE l.id = ? AND p.mitra_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ii', $lamaran_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'Lamaran tidak ditemukan.';
    header('Location: applications.php');
    exit;
}

$application = mysqli_fetch_assoc($result);

// Get documents
$query = "SELECT * FROM dokumen WHERE lamaran_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $lamaran_id);
mysqli_stmt_execute($stmt);
$documents = mysqli_stmt_get_result($stmt);

// Format Status untuk Badge
$status_class = 'status-warning';
if ($application['status'] == 'accepted') $status_class = 'status-success';
if ($application['status'] == 'rejected') $status_class = 'status-danger';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Lamaran - <?php echo htmlspecialchars($application['nama']); ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/layout.css">
    <link rel="stylesheet" href="../../assets/css/components.css">
    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <link rel="stylesheet" href="../../assets/css/mitra.css">
    
    <style>
        /* Sticky Layout for Applicant Detail */
        .applicant-detail-container {
            display: flex;
            gap: 1.5rem;
            position: relative;
            min-height: calc(100vh - 200px);
        }
        
        .applicant-sidebar {
            flex: 0 0 380px;
            position: sticky;
            top: 2rem;
            height: fit-content;
            max-height: calc(100vh - 4rem);
            overflow-y: auto;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* Internet Explorer 10+ */
        }
        
        .applicant-sidebar::-webkit-scrollbar {
            display: none; /* Safari and Chrome */
        }
        
        .applicant-content {
            flex: 1;
            min-width: 0; /* Prevent flex item from overflowing */
        }
        
        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .applicant-sidebar {
                flex: 0 0 340px;
            }
        }
        
        @media (max-width: 992px) {
            .applicant-detail-container {
                flex-direction: column;
            }
            
            .applicant-sidebar {
                flex: 1;
                position: static;
                max-height: none;
                overflow-y: visible;
            }
            
            .applicant-content {
                flex: 1;
            }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="page-title">Detail Lamaran</h1>
                <div class="page-subtitle mt-2 d-flex align-items-center">
                    <i class="bi bi-person-check me-2" style="color: var(--icon-muted); font-size: 1.1rem;"></i>
                    <span class="text-body"><?php echo htmlspecialchars($application['nama']); ?></span>
                </div>
            </div>
            <a href="applicants.php?id=<?php echo $application['peluang_id']; ?>" class="btn btn-soft-outline px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="applicant-detail-container">
            <div class="applicant-sidebar sticky-sidebar">
                <div class="mitra-profile-card mb-4">
                    <div class="mitra-profile-header text-center pt-4">
                        <div class="mitra-avatar mb-3 mx-auto" style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php 
                            $name_parts = explode(' ', trim($application['nama']));
                            $initials = strtoupper(substr($name_parts[0], 0, 1));
                            if(isset($name_parts[1])) {
                                $initials .= strtoupper(substr($name_parts[1], 0, 1));
                            }
                            echo $initials; 
                            ?>
                        </div>
                        <h5 class="mitra-name mb-1"><?php echo htmlspecialchars($application['nama']); ?></h5>
                        <p class="mitra-email mb-3"><?php echo htmlspecialchars($application['prodi']); ?></p>
                        
                        <span class="mitra-badge <?php echo $status_class; ?> mb-3">
                            <?php if($application['status'] == 'accepted') echo '<i class="bi bi-check-circle-fill me-1"></i>'; ?>
                            <?php if($application['status'] == 'rejected') echo '<i class="bi bi-x-circle-fill me-1"></i>'; ?>
                            <?php if($application['status'] == 'pending') echo '<i class="bi bi-clock-fill me-1"></i>'; ?>
                            <?php echo ucfirst($application['status']); ?>
                        </span>
                        
                        <?php if ($application['is_recommended']): ?>
                            <div class="mb-3">
                                <span class="mitra-badge border">
                                    <i class="bi bi-star-fill me-1"></i> Direkomendasikan DPA
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-muted small">
                            <i class="bi bi-clock me-1"></i> Melamar: <?php echo date('d M Y, H:i', strtotime($application['tanggal_apply'])); ?>
                        </div>
                    </div>
                    <div class="mitra-profile-body">
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">NIM</span>
                                <span class="info-value"><?php echo htmlspecialchars($application['nim']); ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Email</span>
                                <span class="info-value"><?php echo htmlspecialchars($application['email']); ?></span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="info-content">
                                <span class="info-label">Fakultas</span>
                                <span class="info-value"><?php echo htmlspecialchars($application['applicant_fakultas']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mitra-edit-card">
                    <div class="mitra-edit-header">
                        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Tindakan Evaluasi</h5>
                    </div>
                    <div class="mitra-edit-body">
                        <div class="d-grid gap-2">
                            <?php if ($application['status'] == 'pending'): ?>
                                <button type="button" class="btn btn-navy" onclick="acceptApplication(<?php echo $lamaran_id; ?>)">
                                    <i class="bi bi-check-circle me-1"></i> Terima Lamaran
                                </button>
                                <button type="button" class="btn btn-soft-danger" onclick="rejectApplication(<?php echo $lamaran_id; ?>)">
                                    <i class="bi bi-x-circle me-1"></i> Tolak Lamaran
                                </button>
                            <?php elseif ($application['status'] == 'accepted'): ?>
                                <button type="button" class="btn btn-soft-danger" onclick="rejectApplication(<?php echo $lamaran_id; ?>)">
                                    <i class="bi bi-x-circle me-1"></i> Batalkan Penerimaan
                                </button>
                            <?php elseif ($application['status'] == 'rejected'): ?>
                                <button type="button" class="btn btn-navy" onclick="acceptApplication(<?php echo $lamaran_id; ?>)">
                                    <i class="bi bi-check-circle me-1"></i> Pertimbangkan Kembali
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="applicant-content scrollable-content">
                
                <div class="mitra-edit-card mb-4">
                    <div class="mitra-edit-header">
                        <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Persyaratan vs Data Mahasiswa</h5>
                    </div>
                    <div class="mitra-edit-body bg-light">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded h-100">
                                    <h6 class="text-muted small text-uppercase mb-3">Indeks Prestasi Kumulatif (IPK)</h6>
                                    <div class="mb-2"><strong>Syarat:</strong> ≥ <?php echo $application['min_ipk']; ?></div>
                                    <div class="d-flex align-items-center">
                                        <strong class="me-2">Mahasiswa:</strong> 
                                        <span class="fs-5 fw-bold <?php echo ($application['ipk'] >= $application['min_ipk']) ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $application['ipk']; ?>
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <?php if ($application['ipk'] < $application['min_ipk']): ?>
                                            <span class="mitra-badge status-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Tidak memenuhi</span>
                                        <?php else: ?>
                                            <span class="mitra-badge status-success"><i class="bi bi-check-circle-fill me-1"></i> Memenuhi</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded h-100">
                                    <h6 class="text-muted small text-uppercase mb-3">Semester Minimal</h6>
                                    <div class="mb-2"><strong>Syarat:</strong> ≥ <?php echo $application['min_semester']; ?></div>
                                    <div class="d-flex align-items-center">
                                        <strong class="me-2">Mahasiswa:</strong> 
                                        <span class="fs-5 fw-bold <?php echo ($application['semester'] >= $application['min_semester']) ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $application['semester']; ?>
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <?php if ($application['semester'] < $application['min_semester']): ?>
                                            <span class="mitra-badge status-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Tidak memenuhi</span>
                                        <?php else: ?>
                                            <span class="mitra-badge status-success"><i class="bi bi-check-circle-fill me-1"></i> Memenuhi</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded h-100">
                                    <h6 class="text-muted small text-uppercase mb-3">Kriteria Fakultas</h6>
                                    <div class="mb-2"><strong>Syarat:</strong> <?php echo !empty($application['required_fakultas']) ? htmlspecialchars($application['required_fakultas']) : 'Semua Fakultas'; ?></div>
                                    <div class="d-flex flex-column">
                                        <strong class="mb-1">Mahasiswa:</strong> 
                                        <span class="<?php echo (empty($application['required_fakultas']) || $application['required_fakultas'] == $application['applicant_fakultas']) ? 'text-success fw-medium' : 'text-danger fw-medium'; ?>">
                                            <?php echo htmlspecialchars($application['applicant_fakultas']); ?>
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <?php if (!empty($application['required_fakultas']) && $application['required_fakultas'] != $application['applicant_fakultas']): ?>
                                            <span class="mitra-badge status-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Tidak memenuhi</span>
                                        <?php else: ?>
                                            <span class="mitra-badge status-success"><i class="bi bi-check-circle-fill me-1"></i> Memenuhi</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded h-100">
                                    <h6 class="text-muted small text-uppercase mb-3">Program Studi</h6>
                                    <div class="mb-2 text-muted"><em>Tidak ada batasan spesifik prodi</em></div>
                                    <div class="mt-3">
                                        <strong>Mahasiswa:</strong><br>
                                        <span class="text-dark fw-medium"><?php echo htmlspecialchars($application['prodi']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mitra-edit-card mb-4">
                    <div class="mitra-edit-header">
                        <h5 class="mb-0"><i class="bi bi-briefcase me-2"></i>Informasi Peluang Didaftar</h5>
                    </div>
                    <div class="mitra-edit-body">
                        <h5 class="mb-3"><?php echo htmlspecialchars($application['judul']); ?> <span class="mitra-badge border ms-2" style="font-size: 0.8rem;"><?php echo ucfirst($application['tipe']); ?></span></h5>
                        
                        <div class="p-3 bg-light rounded mb-3">
                            <p class="mb-0 small text-secondary" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($application['deskripsi'])); ?></p>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="bi bi-geo-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <span class="info-label">Lokasi</span>
                                        <span class="info-value"><?php echo htmlspecialchars($application['lokasi']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="info-content">
                                        <span class="info-label">Kuota</span>
                                        <span class="info-value"><?php echo $application['kuota']; ?> Orang</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="info-item">
                                    <div class="info-icon">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>
                                    <div class="info-content">
                                        <span class="info-label">Tenggat Waktu</span>
                                        <span class="info-value"><?php echo date('d F Y', strtotime($application['deadline'])); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mitra-edit-card mb-4">
                    <div class="mitra-edit-header">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Dokumen Pelamar</h5>
                    </div>
                    <div class="mitra-edit-body">
                        <?php if (mysqli_num_rows($documents) > 0): ?>
                            <div class="row g-3">
                                <?php while ($doc = mysqli_fetch_assoc($documents)): ?>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div class="info-icon me-3">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($doc['jenis']); ?></h6>
                                                    <small class="text-muted">Dokumen Upload</small>
                                                </div>
                                            </div>
                                            <a href="../../uploads/<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-navy">
                                                <i class="bi bi-download"></i> Unduh
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="mitra-empty-state text-center py-4">
                                <i class="bi bi-inbox empty-icon mb-2"></i>
                                <p class="text-muted mb-0">Tidak ada dokumen yang dilampirkan oleh pelamar.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function acceptApplication(lamaranId) {
    if (confirm('Apakah Anda yakin ingin menerima lamaran ini?')) {
        fetch('../../controllers/mitra/manage_application_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=accept&lamaran_id=' + lamaranId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

function rejectApplication(lamaranId) {
    if (confirm('Apakah Anda yakin ingin menolak lamaran ini?')) {
        fetch('../../controllers/mitra/manage_application_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=reject&lamaran_id=' + lamaranId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>

</body>
</html>