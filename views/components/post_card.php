<?php
$current_date = date('Y-m-d H:i:s');
$status = ($post['deadline'] >= $current_date) ? 'Open' : 'Closed';
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
        </div>

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <?php if ($role == 'mahasiswa' && $status == 'Open'): ?>
<a href="<?= BASE_URL ?>/controllers/mahasiswa/apply_process.php?id=<?php echo $post['id']; ?>" 
                       class="btn btn-primary btn-sm"
                       onclick="return confirm('Apakah Anda yakin ingin mendaftar untuk peluang ini?')">
                        Daftar
                    </a>

                <?php elseif ($role == 'dpa' && $status == 'Open'): ?>
                    <button class="btn btn-warning btn-sm">Rekomendasikan</button>

                <?php elseif ($role == 'admin'): ?>
                    <button class="btn btn-danger btn-sm">Hapus</button>

                <?php elseif ($role == 'mitra'): ?>
                    <a href="../mitra/applicants.php?id=<?php echo $post['id']; ?>" 
                       class="btn btn-primary btn-sm">
                        Lihat Pendaftar
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