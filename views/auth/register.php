<?php
// Tangkap pesan dari URL
$error = isset($_GET['error']) ? $_GET['error'] : null;
$success = isset($_GET['success']) ? true : false;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/auth.css">
</head>

<body>

<div class="container d-flex justify-content-center align-items-center auth-container">

    <div class="card shadow-soft auth-card">

        <h4 class="text-center auth-title">Create Account</h4>
        <p class="text-center auth-subtitle">Daftar untuk mulai menggunakan platform</p>

        <!-- ALERT ERROR -->
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- ALERT SUCCESS -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                Registrasi berhasil, silakan login.
            </div>
        <?php endif; ?>

        <form action="../../controllers/auth/register_process.php" method="POST">

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
        <div class="form-text">
            Gunakan email aktif yang dapat dihubungi.
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
        <div class="form-text">
            Minimal 6 karakter.
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select" required>
            <option value="">Pilih Role</option>
            <option value="mahasiswa">Mahasiswa</option>
            <option value="mitra">Mitra</option>
            <option value="dpa">DPA</option>
        </select>
        <div class="form-text">
            Mitra dan DPA memerlukan verifikasi admin.
        </div>
    </div>

    <button class="btn btn-dark w-100">Register</button>

</form>

        <div class="register-link">
            Sudah punya akun? <a href="login.php">Login</a>
        </div>

    </div>

</div>

<!-- JS -->
<script src="../../assets/js/auth.js"></script>

</body>
</html>