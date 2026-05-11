<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3 sidebar-sticky">
        
        <div class="user-profile text-center mb-3 p-2">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['nama_lengkap']) ?>&background=random&color=fff" class="rounded-circle mb-2" width="60" alt="Avatar">
            <h6 class="text-white mb-0 small fw-bold"><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></h6>
            <span class="badge bg-primary mt-1" style="font-size: 10px;"><?= strtoupper($_SESSION['role']) ?></span>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>" href="index.php">
                    <i class="bi bi-house-door me-2"></i> Dashboard
                </a>
            </li>
            
            <?php if ($_SESSION['role'] == 'admin'): ?>
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase small">
                <span>Manajemen Data</span>
            </h6>
            <li class="nav-item">
                <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'kelola_user.php') ? 'active' : '' ?>" href="kelola_user.php">
                    <i class="bi bi-people me-2"></i> Kelola User
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="kelola_akademik.php">
                    <i class="bi bi-journal-text me-2"></i> Akademik
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <div class="px-3 mt-5">
            <a href="../../logout.php" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Logout?')">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-4">