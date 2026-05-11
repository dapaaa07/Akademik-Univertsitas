<?php
session_start();
require_once '../../config/db.php';

// Proteksi halaman: pastikan user sudah login dan rolenya adalah dosen
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
    header("Location: ../../index.php");
    exit;
}

$dosen_id = $_SESSION['user_id'];
$pesan = "";
$tipe_pesan = "";

// 1. Ambil Semester Aktif
$stmt_smt = $pdo->query("SELECT * FROM semester WHERE is_active = 1 LIMIT 1");
$semester_aktif = $stmt_smt->fetch();
$smt_id = $semester_aktif ? $semester_aktif['id'] : 0;

// 2. Proses Simpan Nilai
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_nilai'])) {
    $mhs_id = $_POST['mahasiswa_id'];
    $jadwal_id = $_POST['jadwal_id'];
    $tugas = $_POST['nilai_tugas'];
    $uts = $_POST['nilai_uts'];
    $uas = $_POST['nilai_uas'];
    
    // Hitung Nilai Akhir Otomatis (30%, 30%, 40%)
    $nilai_akhir = ($tugas * 0.3) + ($uts * 0.3) + ($uas * 0.4);

    // Tentukan Indeks Huruf
    if ($nilai_akhir >= 80) $grade = 'A';
    elseif ($nilai_akhir >= 70) $grade = 'B';
    elseif ($nilai_akhir >= 60) $grade = 'C';
    elseif ($nilai_akhir >= 50) $grade = 'D';
    else $grade = 'E';

    try {
        $stmt_input = $pdo->prepare("INSERT INTO nilai (mahasiswa_id, jadwal_id, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir, indeks_huruf) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_input->execute([$mhs_id, $jadwal_id, $tugas, $uts, $uas, $nilai_akhir, $grade]);
        $pesan = "Berhasil! Nilai mahasiswa telah tercatat secara otomatis.";
        $tipe_pesan = "success";
    } catch(PDOException $e) {
        $pesan = "Gagal menyimpan nilai. Pastikan data belum pernah diinput sebelumnya.";
        $tipe_pesan = "danger";
    }
}

// 3. Ambil Jadwal Mengajar Dosen di Semester Aktif
$q_jadwal = "SELECT j.*, mk.nama_mk, mk.kode_mk, k.nama_kelas 
             FROM jadwal j 
             JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id 
             JOIN kelas k ON j.kelas_id = k.id 
             WHERE j.dosen_id = ? AND j.semester_id = ?
             ORDER BY j.hari, j.jam_mulai ASC";
$stmt_jadwal = $pdo->prepare($q_jadwal);
$stmt_jadwal->execute([$dosen_id, $smt_id]);
$jadwal_list = $stmt_jadwal->fetchAll();

// 4. Ambil Daftar Mahasiswa untuk Dropdown Input
$stmt_mhs = $pdo->query("SELECT id, nama_lengkap FROM users WHERE role = 'mahasiswa' ORDER BY nama_lengkap ASC");
$mahasiswa_list = $stmt_mhs->fetchAll();

// Import Header dan Sidebar Modular
include '../../layouts/header.php';
include '../../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Panel Dosen</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="badge bg-dark p-2"><i class="bi bi-calendar-check me-1"></i> Semester: <?= $semester_aktif ? $semester_aktif['nama_semester'] : 'Tidak Aktif' ?></span>
    </div>
</div>

<?php if ($pesan): ?>
    <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi <?= $tipe_pesan == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
        <?= $pesan ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-layout-text-sidebar-reverse me-2"></i>Jadwal Mengajar Anda</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Kode MK</th>
                                <th>Mata Kuliah</th>
                                <th>Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($jadwal_list) > 0): ?>
                                <?php foreach($jadwal_list as $j): ?>
                                <tr>
                                    <td class="fw-bold"><?= $j['hari'] ?></td>
                                    <td><?= date('H:i', strtotime($j['jam_mulai'])) ?> - <?= date('H:i', strtotime($j['jam_selesai'])) ?></td>
                                    <td><span class="badge bg-secondary"><?= $j['kode_mk'] ?></span></td>
                                    <td><?= $j['nama_mk'] ?></td>
                                    <td><span class="badge bg-info text-dark">Kelas <?= $j['nama_kelas'] ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">Anda tidak memiliki jadwal mengajar di semester ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-success"><i class="bi bi-plus-circle me-2"></i>Input Nilai Mahasiswa</h5>
            </div>
            <div class="card-body p-4">
                <form action="" method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Mata Kuliah & Kelas</label>
                            <select name="jadwal_id" class="form-select" required>
                                <option value="">-- Pilih Jadwal --</option>
                                <?php foreach($jadwal_list as $j): ?>
                                    <option value="<?= $j['id'] ?>"><?= $j['nama_mk'] ?> (<?= $j['nama_kelas'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Nama Mahasiswa</label>
                            <select name="mahasiswa_id" class="form-select" required>
                                <option value="">-- Pilih Mahasiswa --</option>
                                <?php foreach($mahasiswa_list as $m): ?>
                                    <option value="<?= $m['id'] ?>"><?= $m['nama_lengkap'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <input type="number" name="nilai_tugas" class="form-control" placeholder="Tugas" min="0" max="100" required>
                                <input type="number" name="nilai_uts" class="form-control" placeholder="UTS" min="0" max="100" required>
                                <input type="number" name="nilai_uas" class="form-control" placeholder="UAS" min="0" max="100" required>
                            </div>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="submit" name="simpan_nilai" class="btn btn-success px-4 shadow-sm">
                                <i class="bi bi-save me-2"></i>Hitung & Simpan Nilai
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
// Import Footer Modular
include '../../layouts/footer.php'; 
?>