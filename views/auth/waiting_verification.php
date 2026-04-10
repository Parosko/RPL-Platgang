<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menunggu Verifikasi | Sistem Peluang</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="../../assets/css/design-system.css">
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/auth.css">
</head>

<body style="background-color: var(--bg-canvas); font-family: var(--font-primary);">

<div class="container d-flex justify-content-center align-items-center auth-container">

    <div class="auth-card shadow-card" style="background-color: var(--bg-surface); border: 1px solid var(--border-base); border-radius: var(--radius-lg);">

        <div class="text-center mb-4">
            <div class="auth-logo mb-3">
                <i class="bi bi-clock-history" style="font-size: 3rem; color: var(--warning);"></i>
            </div>
            <h4 class="auth-title" style="color: var(--text-headline); font-weight: 600; margin-bottom: 8px;">Menunggu Verifikasi</h4>
            <p class="auth-subtitle" style="color: var(--text-subtle); font-size: 0.95rem; margin-bottom: 0;">Akun Anda sedang dalam proses verifikasi admin</p>
        </div>

        <div class="alert alert-warning d-flex align-items-center gap-2" style="background-color: var(--warning-bg-hover); border: 1px solid var(--warning-border); color: var(--warning); border-radius: var(--radius-base);">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <strong>Pendaftaran Berhasil!</strong><br>
                Akun kamu telah dibuat, namun masih menunggu verifikasi dari admin sebelum dapat digunakan.
            </div>
        </div>

        <div class="contact-info mb-4">
            <h6 class="mb-3" style="color: var(--text-body); font-weight: 500;">Hubungi Admin untuk Verifikasi:</h6>
            
            <div class="contact-item mb-3">
                <a href="mailto:Admin@stf.univ.id" 
                   class="contact-link d-flex align-items-center gap-3 p-3 text-decoration-none"
                   style="background-color: var(--bg-subtle); border: 1px solid var(--border-base); border-radius: var(--radius-base); transition: var(--transition-base);">
                    <div class="contact-icon" style="width: 40px; height: 40px; background-color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-envelope-fill" style="color: white; font-size: 1.1rem;"></i>
                    </div>
                    <div class="contact-details text-start">
                        <div class="contact-label" style="color: var(--text-subtle); font-size: 0.85rem; margin-bottom: 2px;">Email</div>
                        <div class="contact-value" style="color: var(--text-body); font-weight: 500;">Admin@stf.univ.id</div>
                    </div>
                    <i class="bi bi-arrow-right-circle ms-auto" style="color: var(--icon-muted);"></i>
                </a>
            </div>

            <div class="contact-item">
                <a href="https://wa.me/6282396676390?text=Halo%20Admin,%20saya%20mau%20bertanya%20tentang%20verifikasi%20akun%20Simpadu" 
                   target="_blank"
                   class="contact-link d-flex align-items-center gap-3 p-3 text-decoration-none"
                   style="background-color: var(--bg-subtle); border: 1px solid var(--border-base); border-radius: var(--radius-base); transition: var(--transition-base);">
                    <div class="contact-icon" style="width: 40px; height: 40px; background-color: #25D366; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-whatsapp" style="color: white; font-size: 1.1rem;"></i>
                    </div>
                    <div class="contact-details text-start">
                        <div class="contact-label" style="color: var(--text-subtle); font-size: 0.85rem; margin-bottom: 2px;">WhatsApp</div>
                        <div class="contact-value" style="color: var(--text-body); font-weight: 500;">082396676390</div>
                    </div>
                    <i class="bi bi-arrow-right-circle ms-auto" style="color: var(--icon-muted);"></i>
                </a>
            </div>
        </div>

        <div class="register-link text-center mt-4" style="color: var(--text-subtle); font-size: 0.9rem;">
            <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Login
            </a>
        </div>

    </div>

</div>

<style>
.contact-link:hover {
    background-color: var(--bg-hover-light) !important;
    border-color: var(--border-normal) !important;
    transform: translateY(-1px);
    box-shadow: var(--shadow-subtle);
}

.contact-link:hover .contact-icon {
    transform: scale(1.05);
    transition: var(--transition-base);
}

.contact-link:hover i.bi-arrow-right-circle {
    color: var(--primary) !important;
}
</style>

</body>
</html>