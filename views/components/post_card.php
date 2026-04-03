<?php
$current_date = date('Y-m-d H:i:s');
$is_manually_closed = !empty($post['closed_at']);
$is_deadline_passed = $post['deadline'] < $current_date;
$is_post_open = !$is_manually_closed && !$is_deadline_passed;
$status = $is_post_open ? 'Open' : 'Closed';
?>

<div class="card mb-3">
    <div class="card-body">

        <div class="d-flex justify-content-between">
            <div>
                <h5 class="mb-1"><?php echo htmlspecialchars($post['judul']); ?></h5>
                <small class="text-muted">
                    <?php echo htmlspecialchars($post['nama_mitra'] ?? 'Mitra'); ?>
                </small>
            </div>

            <span class="badge <?php echo ($status == 'Open') ? 'bg-success' : 'bg-secondary'; ?>">
                <?php echo $status; ?>
            </span>
        </div>

        <p class="mt-2 mb-2">
            <?php echo htmlspecialchars($post['deskripsi']); ?>
        </p>

        <div class="d-flex gap-3 mb-3">
            <small>Dibuat: <?php echo $post['created_at']; ?></small>
            <small>Deadline: <?php echo $post['deadline']; ?></small>
            <?php if ($role == 'mitra' && isset($post['applicant_count']) && !isset($hide_applicant_counter)): ?>
                <small class="badge bg-info">
                    <?php echo $post['applicant_count']; ?> Pendaftar
                </small>
            <?php endif; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <?php if ($role == 'mahasiswa' && $is_post_open): ?>
<a href="<?= BASE_URL ?>/views/mahasiswa/apply.php?id=<?php echo $post['id']; ?>" 
                       class="btn btn-primary btn-sm"
                       onclick="return confirm('Apakah Anda yakin ingin mendaftar untuk peluang ini?')">
                        Daftar
                    </a>

                <?php elseif ($role == 'dpa' && $is_post_open): ?>
                    <a href="<?= BASE_URL ?>/views/dpa/recommend_post.php?id=<?php echo $post['id']; ?>" 
                       class="btn btn-warning btn-sm">
                        Rekomendasikan
                    </a>

                <?php elseif ($role == 'admin'): ?>
                    <button class="btn btn-danger btn-sm" 
                            onclick="deactivatePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['judul'])); ?>')">
                        Nonaktifkan
                    </button>

                <?php elseif ($role == 'mitra'): ?>
                    <a href="<?= BASE_URL ?>/views/mitra/applicants.php?id=<?php echo $post['id']; ?>" 
                       class="btn btn-primary btn-sm">
                        Lihat Pendaftar (<?php echo $post['applicant_count'] ?? 0; ?>)
                    </a>
                <?php endif; ?>

            </div>

            <a href="<?= BASE_URL ?>/views/posts/detail.php?id=<?php echo $post['id']; ?>" 
               class="btn btn-outline-dark btn-sm">
                Lihat Detail
            </a>

        </div>

    </div>
</div>