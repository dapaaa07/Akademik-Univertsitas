<?php
session_start();
require_once 'config/db.php';

// Jika user sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        // Query untuk mencari user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Verifikasi login (Gunakan password_verify jika menggunakan hash)
        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Harap isi semua kolom!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIAKAD PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            background: #ffffff;
        }
        .brand-logo {
            font-size: 2.5rem;
            color: #764ba2;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .btn-primary {
            background: #764ba2;
            border: none;
            padding: 0.8rem;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #5a397e;
        }
        .form-control {
            padding: 0.8rem;
            border-radius: 0.5rem;
        }
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand-logo">
        <i class="bi bi-mortarboard-fill"></i>
        <h4 class="mt-2 fw-bold">SIAKAD PRO</h4>
        <p class="fs-6 text-muted fw-normal">Sistem Informasi Akademik Terpadu</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label text-muted">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-person"></i>
                </span>
                <input type="text" name="username" class="form-control bg-light border-start-0" id="username" placeholder="Masukkan username" required>
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label text-muted">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                    <i class="bi bi-lock"></i>
                </span>
                <input type="password" name="password" class="form-control bg-light border-start-0" id="password" placeholder="Masukkan password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 shadow-sm">
            Masuk Sekarang <i class="bi bi-arrow-right ms-2"></i>
        </button>
    </form>

    <div class="register-link">
        <span class="text-muted">Belum punya akun?</span> 
        <a href="register.php" class="text-decoration-none fw-bold" style="color: #764ba2;">Daftar Disini</a>
    </div>
    
    <div class="mt-4 text-center">
        <small class="text-muted">&copy; <?= date('Y') ?> Akademik Pro v2.0</small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>