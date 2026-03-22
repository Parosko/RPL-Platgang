<?php
// Hitung status otomatis
$current_date = date('Y-m-d');
$status = ($post['deadline'] >= $current_date) ? 'Open' : 'Closed';
?>

<div class="card mb-3">
    <div class="card-body">

        <div class="d-flex justify-content-between">
            <div>
                <h5 class="mb-1"><?php echo htmlspecialchars($post['title']); ?></h5>
                <small class="text-muted"><?php echo htmlspecialchars($post['company']); ?></small>
            </div>

            <span class="badge <?php echo ($status == 'Open') ? 'bg-success' : 'bg-secondary'; ?>">
                <?php echo $status; ?>
            </span>
        </div>

        <p class="mt-2 mb-2">
            <?php echo htmlspecialchars($post['description']); ?>
        </p>

        <div class="d-flex gap-3 mb-3">
            <small>📅 Dibuat: <?php echo $post['created_at']; ?></small>
            <small>⏳ Deadline: <?php echo $post['deadline']; ?></small>
        </div>

        <div class="d-flex justify-content-between align-items-center">

            <div>
                <?php if ($role == 'mahasiswa' && $status == 'Open'): ?>
                    <button class="btn btn-primary btn-sm">Apply</button>

                <?php elseif ($role == 'dpa' && $status == 'Open'): ?>
                    <button class="btn btn-warning btn-sm">Rekomendasikan</button>

                <?php elseif ($role == 'admin'): ?>
                    <button class="btn btn-danger btn-sm">Hapus</button>
                <?php endif; ?>
            </div>

            <a href="#" class="btn btn-outline-dark btn-sm">
                Lihat Detail
            </a>

        </div>

    </div>
</div>