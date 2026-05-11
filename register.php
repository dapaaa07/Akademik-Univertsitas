<?php
session_start();
require_once 'config/db.php';

// Jika user sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $username     = $_POST['username'];
    $password     = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if (!empty($nama_lengkap) && !empty($username) && !empty($password)) {
        if ($password !== $confirm_pass) {
            $error = "Konfirmasi password tidak cocok!";
        } else {
            // Cek apakah username sudah ada
            $stmt_cek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt_cek->execute([$username]);
            
            if ($stmt_cek->rowCount() > 0) {
                $error = "Username sudah digunakan, cari yang lain!";
            } else {
                // Simpan ke database (Default role: mahasiswa)
                // Catatan: Gunakan password_hash() untuk keamanan di sistem asli
                $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, username, password, role) VALUES (?, ?, ?, 'mahasiswa')");
                
                if ($stmt->execute([$nama_lengkap, $username, $password])) {
                    $success = "Registrasi berhasil! Silakan login.";
                } else {
                    $error = "Terjadi kesalahan saat mendaftar.";
                }
            }
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
    <title>Registrasi - SIAKAD PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .register-card {
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
            border: none;
            border-radius: 1.2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            background: #ffffff;
        }
        .brand-logo {
            color: #764ba2;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .btn-register {
            background: #764ba2;
            border: none;
            padding: 0.7rem;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-register:hover {
            background: #5a397e;
            transform: translateY(-2px);
        }
        .form-control {
            padding: 0.7rem;
            border-radius: 0.5rem;
        }
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="brand-logo">
        <i class="bi bi-person-plus-fill fs-1"></i>
        <h4 class="mt-2 fw-bold">Buat Akun Baru</h4>
        <p class="text-muted small">Daftar sebagai mahasiswa untuk akses layanan akademik</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success py-2 small text-center" role="alert">
            <?= $success ?> <br>
            <a href="index.php" class="fw-bold text-decoration-none">Klik untuk Login</a>
        </div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="mb-3">
            <label class="form-label text-muted small fw-bold">Nama Lengkap</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                <input type="text" name="nama_lengkap" class="form-control bg-light" placeholder="Nama Lengkap" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label text-muted small fw-bold">Username</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-at"></i></span>
                <input type="text" name="username" class="form-control bg-light" placeholder="Pilih username" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted small fw-bold">Password</label>
                <input type="password" name="password" class="form-control bg-light" placeholder="Password" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label text-muted small fw-bold">Konfirmasi</label>
                <input type="password" name="confirm_password" class="form-control bg-light" placeholder="Ulangi" required>
            </div>
        </div>

        <button type="submit" class="btn btn-register btn-primary w-100 shadow-sm mt-3">
            Daftar Sekarang <i class="bi bi-check2-circle ms-1"></i>
        </button>
    </form>

    <div class="login-link">
        <span class="text-muted">Sudah memiliki akun?</span> 
        <a href="index.php" class="text-decoration-none fw-bold" style="color: #764ba2;">Masuk Disini</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>