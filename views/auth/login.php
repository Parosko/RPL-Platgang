<?php
$registered = isset($_GET['registered']);
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Peluang</title>

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
                <i class="bi bi-mortarboard-fill" style="font-size: 3rem; color: var(--primary);"></i>
            </div>
            <h4 class="auth-title" style="color: var(--text-headline); font-weight: 600; margin-bottom: 8px;">Selamat Datang</h4>
            <p class="auth-subtitle" style="color: var(--text-subtle); font-size: 0.95rem; margin-bottom: 0;">Masuk ke akun Anda untuk melanjutkan</p>
        </div>

        <?php if ($registered): ?>
            <div class="alert alert-success d-flex align-items-center gap-2" style="background-color: var(--success-light); border: 1px solid var(--success-border); color: var(--success-text); border-radius: var(--radius-base);">
                <i class="bi bi-check-circle-fill"></i>
                <div>Akun berhasil dibuat. Silakan login.</div>
            </div>
        <?php endif; ?>

        <?php if ($error === 'not_verified'): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2" style="background-color: var(--warning-bg-hover); border: 1px solid var(--warning-border); color: var(--warning); border-radius: var(--radius-base);">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>Akun Anda belum diverifikasi oleh admin.</div>
            </div>
        <?php elseif ($error === 'deactivated'): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2" style="background-color: var(--danger-bg-hover); border: 1px solid var(--danger-border-hover); color: var(--danger-text); border-radius: var(--radius-base);">
                <i class="bi bi-x-circle-fill"></i>
                <div>Akun Anda telah dinonaktifkan. Hubungi administrator jika ada pertanyaan.</div>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2" style="background-color: var(--danger-bg-hover); border: 1px solid var(--danger-border-hover); color: var(--danger-text); border-radius: var(--radius-base);">
                <i class="bi bi-x-circle-fill"></i>
                <div>Email atau password salah.</div>
            </div>
        <?php endif; ?>

        <form action="../../controllers/auth/login_process.php" method="POST">

            <div class="mb-3">
                <label class="form-label custom-label" style="color: var(--text-body); font-weight: 500; font-size: 0.9rem;">Email</label>
                <div class="input-group">
                    <span class="input-group-text" style="background-color: var(--bg-subtle); border: 1px solid var(--border-base); border-right: none; color: var(--icon-muted);">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" name="email" class="form-control custom-input" style="border-left: none; border-color: var(--border-base);" required placeholder="nama@email.com">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label custom-label" style="color: var(--text-body); font-weight: 500; font-size: 0.9rem;">Password</label>
                <div class="input-group">
                    <span class="input-group-text" style="background-color: var(--bg-subtle); border: 1px solid var(--border-base); border-right: none; color: var(--icon-muted);">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" name="password" class="form-control custom-input" style="border-left: none; border-color: var(--border-base);" id="passwordInput" required placeholder="Masukkan password">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()" id="toggleBtn" style="background-color: var(--bg-subtle); border: 1px solid var(--border-base); border-left: none; color: var(--icon-muted);">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100" style="background-color: var(--primary); border-color: var(--primary); padding: 0.75rem; font-weight: 500; border-radius: var(--radius-base);">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </button>

        </form>

        <div class="register-link text-center mt-4" style="color: var(--text-subtle); font-size: 0.9rem;">
            Belum punya akun? <a href="register.php" style="color: var(--primary); text-decoration: none; font-weight: 500;">Daftar Sekarang</a>
        </div>

    </div>

</div>

<script>
function togglePassword() {
    const input = document.getElementById("passwordInput");
    const toggleIcon = document.getElementById("toggleIcon");

    if (input.type === "password") {
        input.type = "text";
        toggleIcon.classList.remove('bi-eye');
        toggleIcon.classList.add('bi-eye-slash');
    } else {
        input.type = "password";
        toggleIcon.classList.remove('bi-eye-slash');
        toggleIcon.classList.add('bi-eye');
    }
}
</script>

</body>
</html>