<?php
session_start();
require_once '../../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../../index.php"); exit; }

$pesan = ""; $tipe = "";

// 1. Ambil Data Pendukung
$matkul = $pdo->query("SELECT * FROM mata_kuliah ORDER BY nama_mk ASC")->fetchAll();
$dosen = $pdo->query("SELECT id, nama_lengkap FROM users WHERE role='dosen' ORDER BY nama_lengkap ASC")->fetchAll();
$kelas = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC")->fetchAll();
$semester_aktif = $pdo->query("SELECT * FROM semester WHERE is_active=1 LIMIT 1")->fetch();

// 2. Simpan Jadwal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_jadwal'])) {
    if (!$semester_aktif) {
        $pesan = "Gagal! Belum ada semester yang aktif."; $tipe = "danger";
    } else {
        $stmt = $pdo->prepare("INSERT INTO jadwal (mata_kuliah_id, dosen_id, kelas_id, semester_id, hari, jam_mulai, jam_selesai) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$_POST['mk_id'], $_POST['dosen_id'], $_POST['kelas_id'], $semester_aktif['id'], $_POST['hari'], $_POST['jam_mulai'], $_POST['jam_selesai']]);
        $pesan = "Jadwal berhasil diterbitkan!"; $tipe = "success";
    }
}

// 3. Ambil List Jadwal
$query = "SELECT j.*, mk.nama_mk, u.nama_lengkap as dosen, k.nama_kelas 
          FROM jadwal j 
          JOIN mata_kuliah mk ON j.mata_kuliah_id = mk.id 
          JOIN users u ON j.dosen_id = u.id 
          JOIN kelas k ON j.kelas_id = k.id 
          WHERE j.semester_id = ? 
          ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jam_mulai ASC";
$stmt_list = $pdo->prepare($query);
$stmt_list->execute([$semester_aktif['id'] ?? 0]);
$jadwal = $stmt_list->fetchAll();

include '../../layouts/header.php';
include '../../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <h1 class="h2 fw-bold">Jadwal Kuliah</h1>
    <span class="badge bg-info text-dark p-2">Semester: <?= $semester_aktif['nama_semester'] ?? 'TIDAK ADA' ?></span>
</div>

<?php if($pesan): ?>
    <div class="alert alert-<?= $tipe ?> shadow-sm"><?= $pesan ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
    <h5 class="fw-bold mb-4"><i class="bi bi-plus-circle me-2"></i>Tambah Jadwal Baru</h5>
    <form method="POST" class="row g-3">
        <div class="col-md-4">
            <label class="form-label small fw-bold">Mata Kuliah</label>
            <select name="mk_id" class="form-select" required>
                <?php foreach($matkul as $m) echo "<option value='{$m['id']}'>{$m['nama_mk']}</option>"; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-bold">Dosen Pengampu</label>
            <select name="dosen_id" class="form-select" required>
                <?php foreach($dosen as $d) echo "<option value='{$d['id']}'>{$d['nama_lengkap']}</option>"; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-bold">Kelas</label>
            <select name="kelas_id" class="form-select" required>
                <?php foreach($kelas as $k) echo "<option value='{$k['id']}'>{$k['nama_kelas']}</option>"; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">Hari</label>
            <select name="hari" class="form-select">
                <option>Senin</option><option>Selasa</option><option>Rabu</option><option>Kamis</option><option>Jumat</option><option>Sabtu</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">Jam Mulai</label>
            <input type="time" name="jam_mulai" class="form-control" required>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">Jam Selesai</label>
            <input type="time" name="jam_selesai" class="form-control" required>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" name="tambah_jadwal" class="btn btn-success w-100">Terbitkan Jadwal</button>
        </div>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold">Jadwal Aktif</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Hari / Waktu</th><th>Mata Kuliah</th><th>Dosen</th><th>Kelas</th></tr>
            </thead>
            <tbody>
                <?php foreach($jadwal as $j): ?>
                <tr>
                    <td><strong><?= $j['hari'] ?></strong><br><small class="text-muted"><?= $j['jam_mulai'] ?> - <?= $j['jam_selesai'] ?></small></td>
                    <td><?= $j['nama_mk'] ?></td>
                    <td><?= $j['dosen'] ?></td>
                    <td><span class="badge bg-secondary"><?= $j['nama_kelas'] ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../../layouts/footer.php'; ?>