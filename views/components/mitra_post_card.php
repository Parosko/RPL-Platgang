<?php
$current_date = date('Y-m-d H:i:s');
$status = ($post['deadline'] >= $current_date) ? 'Open' : 'Closed';

// Override status if manually closed
if ($post['is_closed']) {
    $status = 'Closed';
}
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
                <?php if ($post['is_closed'] && $post['close_reason'] == 'manual'): ?>
                    <br><small>(Ditutup Manual)</small>
                <?php endif; ?>
            </span>
        </div>

        <p class="mt-2 mb-2">
            <?php echo htmlspecialchars($post['deskripsi']); ?>
        </p>

        <div class="d-flex gap-3 mb-3">
            <small>Dibuat: <?php echo $post['created_at']; ?></small>
            <small>Deadline: <?php echo $post['deadline']; ?></small>
            <?php if (isset($post['applicant_count'])): ?>
                <small class="badge bg-info">
                    <?php echo $post['applicant_count']; ?> Pendaftar
                </small>
            <?php endif; ?>
        </div>

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <?php if ($role == 'mitra'): ?>
                    <a href="<?= BASE_URL ?>/views/mitra/applicants.php?id=<?php echo $post['id']; ?>" 
                       class="btn btn-primary btn-sm">
                        Lihat Pendaftar (<?php echo $post['applicant_count'] ?? 0; ?>)
                    </a>
                    
                    <!-- Close Post Button -->
                    <?php if (!$post['is_closed']): ?>
                        <button type="button" class="btn btn-warning btn-sm ms-2" 
                            onclick="closePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['judul']); ?>')">
                            Tutup Posting
                        </button>
                    <?php else: ?>
                        <span class="badge bg-danger ms-2">Postingan Ditutup</span>
                    <?php endif; ?>

                    <!-- Delete Post Button -->
                    <button type="button" class="btn btn-danger btn-sm ms-2" 
                        onclick="deletePost(<?php echo $post['id']; ?>, '<?php echo htmlspecialchars($post['judul']); ?>')">
                        Hapus Posting
                    </button>
                <?php endif; ?>
            </div>

            <a href="<?= BASE_URL ?>/views/posts/detail.php?id=<?php echo $post['id']; ?>" 
               class="btn btn-outline-dark btn-sm">
                Lihat Detail
            </a>

        </div>

    </div>
</div>

<script>
function closePost(postId, postTitle) {
    if (confirm(`Apakah Anda yakin ingin menutup postingan "${postTitle}"? Semua lamaran yang belum diterima akan ditolak secara otomatis.`)) {
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
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error);
        });
    }
}

function deletePost(postId, postTitle) {
    if (confirm(`Apakah Anda yakin ingin menghapus postingan "${postTitle}" secara permanen? Tindakan ini tidak dapat dibatalkan dan semua data terkait akan dihapus.`)) {
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
            alert('Terjadi kesalahan: ' + error);
        });
    }
}
</script>
