<?php
session_start();
require_once '../../config/db.php';

// Proteksi halaman: pastikan user sudah login dan rolenya adalah mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../../index.php");
    exit;
}

$mhs_id = $_SESSION['user_id'];

// 1. Ambil Semester Aktif
$stmt_smt = $pdo->query("SELECT * FROM semester WHERE is_active = 1 LIMIT 1");
$semester_aktif = $stmt_smt->fetch();
$smt_id = $semester_aktif ? $semester_aktif['id'] : 0;

// 2. Ambil Jadwal Kuliah di Semester Aktif
$q_jadwal = "SELECT j.*, mk.nama_mk, mk.sks, k.nama_kelas, u.nama_lengkap AS nama_dosen 
             FROM jadwal j 
             JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id 
             JOIN kelas k ON j.kelas_id = k.id 
             JOIN users u ON j.dosen_id = u.id
             WHERE j.semester_id = ? 
             ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), jam_mulai ASC";
$stmt_jadwal = $pdo->prepare($q_jadwal);
$stmt_jadwal->execute([$smt_id]);
$jadwal_list = $stmt_jadwal->fetchAll();

// 3. Ambil Nilai Mahasiswa yang sedang login
$q_nilai = "SELECT n.*, mk.nama_mk, mk.sks, k.nama_kelas 
            FROM nilai n
            JOIN jadwal j ON n.jadwal_id = j.id
            JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id
            JOIN kelas k ON j.kelas_id = k.id
            WHERE n.mahasiswa_id = ?";
$stmt_nilai = $pdo->prepare($q_nilai);
$stmt_nilai->execute([$mhs_id]);
$nilai_list = $stmt_nilai->fetchAll();

// Import Header dan Sidebar Modular
include '../../layouts/header.php';
include '../../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard Mahasiswa</h1>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm bg-white p-4" style="border-left: 5px solid #764ba2 !important;">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 bg-light p-3 rounded-circle text-primary">
                    <i class="bi bi-mortarboard fs-1"></i>
                </div>
                <div class="ms-4">
                    <h4 class="fw-bold mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>!</h4>
                    <p class="text-muted mb-0">Semester Aktif: <span class="badge bg-primary"><?= $semester_aktif ? $semester_aktif['nama_semester'] : 'Belum ditentukan' ?></span></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2 text-info"></i>Jadwal Kuliah Semester Ini</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Hari & Waktu</th>
                                <th>Mata Kuliah</th>
                                <th>Dosen</th>
                                <th>Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($jadwal_list) > 0): ?>
                                <?php foreach($jadwal_list as $j): ?>
                                <tr>
                                    <td>
                                        <span class="d-block fw-bold"><?= $j['hari'] ?></span>
                                        <small class="text-muted"><?= date('H:i', strtotime($j['jam_mulai'])) ?> - <?= date('H:i', strtotime($j['jam_selesai'])) ?></small>
                                    </td>
                                    <td>
                                        <span class="d-block fw-bold"><?= $j['nama_mk'] ?></span>
                                        <small class="text-muted"><?= $j['sks'] ?> SKS</small>
                                    </td>
                                    <td><?= $j['nama_dosen'] ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $j['nama_kelas'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">Belum ada jadwal kuliah yang tersedia.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-journal-check me-2 text-success"></i>Nilai Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (count($nilai_list) > 0): ?>
                    <?php 
                    // Ambil 5 nilai terakhir saja untuk ringkasan
                    $limit_nilai = array_slice($nilai_list, 0, 5);
                    foreach($limit_nilai as $n): 
                    ?>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <div>
                            <h6 class="mb-0 fw-bold"><?= $n['nama_mk'] ?></h6>
                            <small class="text-muted"><?= $n['nama_kelas'] ?></small>
                        </div>
                        <div class="text-end">
                            <span class="badge <?= ($n['indeks_huruf'] == 'A' || $n['indeks_huruf'] == 'B') ? 'bg-success' : 'bg-warning text-dark' ?> rounded-pill fs-6">
                                <?= $n['indeks_huruf'] ?>
                            </span>
                            <small class="d-block text-muted mt-1"><?= $n['nilai_akhir'] ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <div class="d-grid mt-3">
                        <a href="transkrip.php" class="btn btn-outline-primary btn-sm">Lihat Transkrip Lengkap</a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-info-circle fs-2 text-muted d-block mb-2"></i>
                        <p class="text-muted mb-0">Belum ada nilai yang diinput oleh dosen.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
// Import Footer Modular
include '../../layouts/footer.php'; 
?>