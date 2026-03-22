<?php
$registered = isset($_GET['registered']);
$error = isset($_GET['error']) ? $_GET['error'] : null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

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

        <h4 class="text-center auth-title">Welcome Back</h4>
        <p class="text-center auth-subtitle">Masuk untuk melanjutkan</p>

        <!-- SUCCESS REGISTER -->
        <?php if ($registered): ?>
            <div class="alert alert-success">
                Akun berhasil dibuat, silakan login.
            </div>
        <?php endif; ?>

        <!-- ERROR LOGIN -->
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="../../controllers/auth/login_process.php" method="POST">

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-2">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-dark w-100">Login</button>

        </form>

        <div class="register-link">
            Belum punya akun? <a href="register.php">Register</a>
        </div>

    </div>

</div>

</body>
</html>