<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3">
    <div class="position-sticky">
        <h4 class="text-center mb-4 text-primary">SIAKAD PRO</h4>
        <div class="text-center mb-4">
            <small class="text-muted d-block">Halo,</small>
            <strong><?= $_SESSION['nama_lengkap'] ?></strong>
            <span class="badge bg-info d-block mt-1"><?= ucfirst($_SESSION['role']) ?></span>
        </div>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            
            <?php if($_SESSION['role'] == 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="admin_users.php"><i class="bi bi-people me-2"></i> Kelola User</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_akademik.php"><i class="bi bi-book me-2"></i> Data Akademik</a>
            </li>
            <?php endif; ?>

            <?php if($_SESSION['role'] == 'dosen'): ?>
            <li class="nav-item">
                <a class="nav-link" href="dosen_nilai.php"><i class="bi bi-pencil-square me-2"></i> Input Nilai</a>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link" href="jadwal.php"><i class="bi bi-calendar3 me-2"></i> Jadwal Kuliah</a>
            </li>
        </ul>
        <hr>
        <a href="logout.php" class="btn btn-danger btn-sm w-100">
            <i class="bi bi-box-arrow-right"></i> Keluar
        </a>
    </div>
</nav>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content"></main>