<?php
$current_date = date('Y-m-d H:i:s');
$is_manually_closed = !empty($post['closed_at']);
$is_deadline_passed = $post['deadline'] < $current_date;
$is_post_open = !$is_manually_closed && !$is_deadline_passed;
$status = $is_post_open ? 'Open' : 'Closed';
?>

<div class="card mb-4 post-card">
    <div class="card-body p-4">

        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="post-title mb-1"><?php echo htmlspecialchars($post['judul']); ?></h5>
                <div class="post-subtitle d-flex align-items-center">
                    <i class="bi bi-building me-2"></i>
                    <?php echo htmlspecialchars($post['nama_mitra'] ?? 'Mitra'); ?>
                </div>
            </div>
            
            <span class="badge badge-status <?php echo ($status == 'Open') ? 'status-open' : 'status-closed'; ?>">
                <?php if ($status == 'Open'): ?>
                    <i class="bi bi-circle-fill status-icon-pulse"></i> <?php echo $status; ?>
                <?php else: ?>
                    <i class="bi bi-lock-fill status-icon"></i> <?php echo $status; ?>
                <?php endif; ?>
            </span>
        </div>

        <p class="post-desc mt-2 mb-4">
            <?php echo htmlspecialchars($post['deskripsi']); ?>
        </p>

        <div class="d-flex flex-wrap gap-4 mb-4 pb-3 border-bottom post-meta-container">
            <div class="post-meta">
                <i class="bi bi-calendar3"></i>
                <span>Dibuat: <?php echo date('d M Y', strtotime($post['created_at'])); ?></span>
            </div>
            <div class="post-meta">
                <i class="bi bi-clock-history"></i>
                <span>Deadline: <?php echo date('d M Y', strtotime($post['deadline'])); ?></span>
            </div>
            <?php if ($role == 'mitra' && isset($post['applicant_count']) && !isset($hide_applicant_counter)): ?>
                <div class="post-meta meta-highlight badge">
                    <i class="bi bi-people-fill me-1"></i>
                    <?php echo $post['applicant_count']; ?> Pendaftar
                </div>
            <?php endif; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2">
                <?php if ($role == 'mahasiswa' && $is_post_open): ?>
                    <a href="<?= BASE_URL ?>/views/mahasiswa/apply.php?id=<?php echo $post['id']; ?>" 
                       class="btn btn-navy btn-sm d-flex align-items-center gap-2"
                       onclick="return confirm('Apakah Anda yakin ingin mendaftar untuk peluang ini?')">
                        <i class="bi bi-send"></i> Daftar
                    </a>

                <?php elseif ($role == 'dpa' && $is_post_open): ?>
                    <a href="<?= BASE_URL ?>/views/dpa/recommend_post.php?id=<?php echo $post['id']; ?>" 
                       class="btn btn-soft-warning btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-star"></i> Rekomendasikan
                    </a>

                <?php elseif ($role == 'admin'): ?>
                    <button class="btn btn-soft-danger btn-sm d-flex align-items-center gap-2" 
                            onclick="deactivatePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['judul'])); ?>')">
                        <i class="bi bi-slash-circle"></i> Nonaktifkan
                    </button>

                <?php elseif ($role == 'mitra'): ?>
                    <a href="<?= BASE_URL ?>/views/mitra/applicants.php?id=<?php echo $post['id']; ?>" 
                       class="btn btn-navy btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-person-lines-fill"></i> Lihat Pendaftar (<?php echo $post['applicant_count'] ?? 0; ?>)
                    </a>
                <?php endif; ?>
            </div>

            <a href="<?= BASE_URL ?>/views/posts/detail.php?id=<?php echo $post['id']; ?>" 
               class="btn btn-soft-outline btn-sm d-flex align-items-center gap-2">
                Detail <i class="bi bi-arrow-right-short fs-5 lh-1"></i>
            </a>
        </div>

    </div>
</div>