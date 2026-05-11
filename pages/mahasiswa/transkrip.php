<?php
session_start();
require_once '../../config/db.php';

// Proteksi halaman Mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../../index.php");
    exit;
}

$mhs_id = $_SESSION['user_id'];

// Ambil data nilai, mata kuliah, dan semester terkait
$query = "SELECT n.*, mk.nama_mk, mk.kode_mk, mk.sks, s.nama_semester 
          FROM nilai n
          JOIN jadwal j ON n.jadwal_id = j.id
          JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id
          JOIN semester s ON j.semester_id = s.id
          WHERE n.mahasiswa_id = ?
          ORDER BY s.id ASC, mk.nama_mk ASC";

$stmt = $pdo->prepare($query);
$stmt->execute([$mhs_id]);
$transkrip = $stmt->fetchAll();

// Logika Perhitungan IPK & SKS
$total_sks = 0;
$total_poin = 0;

$bobot = [
    'A' => 4,
    'B' => 3,
    'C' => 2,
    'D' => 1,
    'E' => 0
];

foreach ($transkrip as $t) {
    $total_sks += $t['sks'];
    $total_poin += ($bobot[$t['indeks_huruf']] * $t['sks']);
}

$ipk = ($total_sks > 0) ? round($total_poin / $total_sks, 2) : 0;

include '../../layouts/header.php';
include '../../layouts/sidebar.php';
?>

<div class="main-content-inner animation-fade-in">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <div>
            <h1 class="h2 fw-bold text-dark">Transkrip Nilai</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Transkrip</li>
                </ol>
            </nav>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button onclick="window.print()" class="btn btn-outline-primary shadow-sm">
                <i class="bi bi-printer me-2"></i>Cetak Transkrip
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total SKS Diambil</h6>
                <h2 class="fw-bold text-primary mb-0"><?= $total_sks ?></h2>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center">
                <h6 class="text-muted mb-1 small fw-bold text-uppercase">IP Kumulatif (IPK)</h6>
                <h2 class="fw-bold text-success mb-0"><?= number_format($ipk, 2) ?></h2>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>Daftar Nilai Keseluruhan</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Semester</th>
                            <th>Kode & Mata Kuliah</th>
                            <th class="text-center">SKS</th>
                            <th class="text-center">Nilai Akhir</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">Poin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($transkrip as $t): 
                            $poin_matkul = $bobot[$t['indeks_huruf']] * $t['sks'];
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><span class="small fw-bold text-muted"><?= $t['nama_semester'] ?></span></td>
                            <td>
                                <strong class="d-block text-dark"><?= $t['nama_mk'] ?></strong>
                                <small class="text-muted"><?= $t['kode_mk'] ?></small>
                            </td>
                            <td class="text-center"><?= $t['sks'] ?></td>
                            <td class="text-center"><?= $t['nilai_akhir'] ?></td>
                            <td class="text-center">
                                <?php 
                                    $badge_class = 'bg-secondary';
                                    if ($t['indeks_huruf'] == 'A') $badge_class = 'bg-success';
                                    elseif ($t['indeks_huruf'] == 'B') $badge_class = 'bg-primary';
                                    elseif ($t['indeks_huruf'] == 'C') $badge_class = 'bg-warning text-dark';
                                    elseif ($t['indeks_huruf'] == 'D') $badge_class = 'bg-danger';
                                ?>
                                <span class="badge <?= $badge_class ?> rounded-pill px-3"><?= $t['indeks_huruf'] ?></span>
                            </td>
                            <td class="text-center fw-bold"><?= $poin_matkul ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (count($transkrip) == 0): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                                    Belum ada data nilai yang tersedia.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <?php if (count($transkrip) > 0): ?>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="3" class="text-end">TOTAL :</td>
                            <td class="text-center"><?= $total_sks ?></td>
                            <td colspan="2"></td>
                            <td class="text-center text-primary"><?= $total_poin ?></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .sidebar, .btn-toolbar, .breadcrumb, .navbar { display: none !important; }
        .main-content { width: 100% !important; margin: 0 !important; padding: 0 !important; }
        .card { border: 1px solid #ddd !important; shadow: none !important; }
    }
    .animation-fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<?php include '../../layouts/footer.php'; ?>