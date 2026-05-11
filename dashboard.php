<?php
session_start();
require 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

include 'layouts/header.php';
include 'layouts/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard Overview</h1>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white p-3">
            <h5>Status User</h5>
            <h3><?= ucfirst($_SESSION['role']) ?></h3>
        </div>
    </div>
    
    <?php if($_SESSION['role'] == 'mahasiswa'): ?>
    <div class="col-md-12">
        <div class="card p-4">
            <h5>Informasi Akademik</h5>
            <p>Selamat datang di sistem informasi akademik. Silakan cek menu jadwal untuk melihat jadwal kuliah Anda.</p>
        </div>
    </div>
    <?php endif; ?>

    </div>

<?php include 'layouts/footer.php'; ?>