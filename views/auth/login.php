<?php
$registered = isset($_GET['registered']);
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/auth.css">
</head>

<body>

<div class="container d-flex justify-content-center align-items-center auth-container">

    <div class="card shadow-soft auth-card">

        <h4 class="text-center auth-title">Welcome Back</h4>
        <p class="text-center auth-subtitle">Masuk untuk melanjutkan</p>

        <?php if ($registered): ?>
            <div class="alert alert-success">
                Akun berhasil dibuat. Silakan login.
            </div>
        <?php endif; ?>

        <?php if ($error === 'not_verified'): ?>
            <div class="alert alert-warning">
                Akun Anda belum diverifikasi oleh admin.
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger">
                Email atau password salah.
            </div>
        <?php endif; ?>

        <form action="../../controllers/auth/login_process.php" method="POST">

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3 position-relative">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control pe-5" id="passwordInput" required>

                <span onclick="togglePassword()" 
                      id="toggleText"
                      style="position:absolute; right:12px; top:38px; cursor:pointer; font-size:13px; color:#555;">
                    Show
                </span>
            </div>

            <button class="btn btn-dark w-100">Login</button>

        </form>

        <div class="register-link">
            Belum punya akun? <a href="register.php">Register</a>
        </div>

    </div>

</div>

<script>
function togglePassword() {
    const input = document.getElementById("passwordInput");
    const toggle = document.getElementById("toggleText");

    if (input.type === "password") {
        input.type = "text";
        toggle.innerText = "Hide";
    } else {
        input.type = "password";
        toggle.innerText = "Show";
    }
}
</script>

</body>
</html>