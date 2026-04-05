<?php
$current_date = date('Y-m-d H:i:s');
$status = ($post['deadline'] >= $current_date) ? 'Open' : 'Closed';

// Override status if manually closed
if ($post['is_closed']) {
    $status = 'Closed';
}
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

            <div class="d-flex flex-column align-items-end">
                <span class="badge badge-status <?php echo ($status == 'Open') ? 'status-open' : 'status-closed'; ?>">
                    <?php if ($status == 'Open'): ?>
                        <i class="bi bi-circle-fill status-icon-pulse"></i> <?php echo $status; ?>
                    <?php else: ?>
                        <i class="bi bi-lock-fill status-icon"></i> <?php echo $status; ?>
                    <?php endif; ?>
                </span>
                
                <?php if ($post['is_closed'] && isset($post['close_reason']) && $post['close_reason'] == 'manual'): ?>
                    <small class="text-muted mt-1" style="font-size: 0.7rem; font-weight: 500;">(Ditutup Manual)</small>
                <?php endif; ?>
            </div>
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
            <?php if (isset($post['applicant_count'])): ?>
                <div class="post-meta meta-highlight badge">
                    <i class="bi bi-people-fill me-1"></i>
                    <?php echo $post['applicant_count']; ?> Pendaftar
                </div>
            <?php endif; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            
            <div class="d-flex flex-wrap gap-2">
                <?php if ($role == 'mitra'): ?>
                    
                    <a href="<?= BASE_URL ?>/views/mitra/applicants.php?id=<?php echo $post['id']; ?>" 
                       class="btn btn-navy btn-sm d-flex align-items-center gap-2">
                        <i class="bi bi-person-lines-fill"></i> Lihat Pendaftar (<?php echo $post['applicant_count'] ?? 0; ?>)
                    </a>
                    
                    <?php if (!$post['is_closed']): ?>
                        <button type="button" class="btn btn-soft-warning btn-sm d-flex align-items-center gap-2" 
                            onclick="closePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['judul'])); ?>')">
                            <i class="bi bi-pause-circle"></i> Tutup Posting
                        </button>
                    <?php else: ?>
                        <span class="badge status-closed d-flex align-items-center gap-1" style="padding: 0.4em 0.8em;">
                            <i class="bi bi-shield-lock"></i> Sesi Ditutup
                        </span>
                    <?php endif; ?>

                    <button type="button" class="btn btn-soft-danger btn-sm d-flex align-items-center gap-2" 
                        onclick="deletePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars(addslashes($post['judul'])); ?>')">
                        <i class="bi bi-trash3"></i> Hapus
                    </button>
                    
                <?php endif; ?>
            </div>

            <a href="<?= BASE_URL ?>/views/posts/detail.php?id=<?php echo $post['id']; ?>" 
               class="btn btn-soft-outline btn-sm d-flex align-items-center gap-2">
                Detail <i class="bi bi-arrow-right-short fs-5 lh-1"></i>
            </a>

        </div>

    </div>
</div>

<script>
function closePost(postId, postTitle) {
    if (confirm(`Apakah Anda yakin ingin menutup postingan "${postTitle}"?\nSemua lamaran yang belum diproses akan diabaikan secara otomatis.`)) {
        fetch('<?= BASE_URL ?>/controllers/mitra/close_post_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'peluang_id=' + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Notifikasi bisa diganti dengan Toast UI agar lebih elegan jika Anda punya library-nya
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan sistem: ' + error);
        });
    }
}

function deletePost(postId, postTitle) {
    if (confirm(`Peringatan: Apakah Anda yakin ingin menghapus "${postTitle}" secara permanen?\n\nTindakan ini bersifat destruktif dan semua data terkait (termasuk pendaftar) akan terhapus.`)) {
        fetch('<?= BASE_URL ?>/controllers/mitra/delete_post_process.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'peluang_id=' + postId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan sistem: ' + error);
        });
    }
}
</script>