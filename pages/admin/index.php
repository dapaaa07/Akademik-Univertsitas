<?php
session_start();
require_once '../../config/db.php';

// Proteksi halaman: Pastikan user login dan adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// 1. Ambil Statistik Data
$mhs_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'mahasiswa'")->fetchColumn();
$dosen_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'dosen'")->fetchColumn();
$mk_count = $pdo->query("SELECT COUNT(*) FROM mata_kuliah")->fetchColumn();
$semester_aktif = $pdo->query("SELECT nama_semester FROM semester WHERE is_active = 1 LIMIT 1")->fetchColumn();

include '../../layouts/header.php';
include '../../layouts/sidebar.php';
?>

<div class="main-content-inner">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <div>
            <h1 class="h2 fw-bold text-dark">Dashboard Admin</h1>
            <p class="text-muted mb-0">Selamat datang kembali di pusat kendali akademik.</p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="badge bg-white text-dark border p-2 shadow-sm">
                <i class="bi bi-clock-history text-primary me-2"></i>
                Periode Aktif: <strong><?= $semester_aktif ?: 'Belum Diatur' ?></strong>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-4 me-3">
                        <i class="bi bi-people-fill text-primary fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Mahasiswa</h6>
                        <h3 class="fw-bold mb-0"><?= $mhs_count ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-4 me-3">
                        <i class="bi bi-person-badge text-success fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Dosen</h6>
                        <h3 class="fw-bold mb-0"><?= $dosen_count ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded-4 me-3">
                        <i class="bi bi-journals text-info fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Mata Kuliah</h6>
                        <h3 class="fw-bold mb-0"><?= $mk_count ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <h5 class="fw-bold mb-3"><i class="bi bi-grid-fill me-2"></i>Akses Manajemen Cepat</h5>
        </div>
        
        <div class="col-md-3 mb-4 text-center">
            <a href="kelola_user.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm p-4 h-100 quick-access-card">
                    <i class="bi bi-person-gear fs-1 text-primary mb-3"></i>
                    <h6 class="fw-bold text-dark mb-1">Kelola Pengguna</h6>
                    <small class="text-muted">Edit Role & User</small>
                </div>
            </a>
        </div>
        
        <div class="col-md-3 mb-4 text-center">
            <a href="kelola_akademik.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm p-4 h-100 quick-access-card">
                    <i class="bi bi-book-half fs-1 text-success mb-3"></i>
                    <h6 class="fw-bold text-dark mb-1">Data Akademik</h6>
                    <small class="text-muted">Matkul & Semester</small>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-4 text-center">
            <a href="kelola_jadwal.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm p-4 h-100 quick-access-card">
                    <i class="bi bi-calendar-check fs-1 text-info mb-3"></i>
                    <h6 class="fw-bold text-dark mb-1">Jadwal Kuliah</h6>
                    <small class="text-muted">Atur Jam & Kelas</small>
                </div>
            </a>
        </div>

        <div class="col-md-3 mb-4 text-center">
            <a href="kelola_kelas.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm p-4 h-100 quick-access-card">
                    <i class="bi bi-door-open fs-1 text-danger mb-3"></i>
                    <h6 class="fw-bold text-dark mb-1">Kelola Kelas</h6>
                    <small class="text-muted">Manajemen Ruang</small>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    .quick-access-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border-radius: 15px;
    }
    .quick-access-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        background: #f8f9fa;
    }
    .main-content-inner {
        animation: fadeIn 0.5s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<?php include '../../layouts/footer.php'; ?>