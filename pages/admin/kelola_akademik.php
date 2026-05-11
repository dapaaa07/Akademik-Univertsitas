<?php
session_start();
require_once '../../config/db.php';

// Proteksi halaman
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

$pesan = "";
$tipe_pesan = "";

// ==========================================
// 1. PROSES MANAJEMEN SEMESTER
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_semester'])) {
    $nama_semester = $_POST['nama_semester'];
    try {
        $stmt = $pdo->prepare("INSERT INTO semester (nama_semester, is_active) VALUES (?, 0)");
        $stmt->execute([$nama_semester]);
        $pesan = "Semester baru berhasil ditambahkan!";
        $tipe_pesan = "success";
    } catch (PDOException $e) {
        $pesan = "Gagal menambah semester.";
        $tipe_pesan = "danger";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_aktif'])) {
    $smt_id = $_POST['semester_id'];
    try {
        // Nonaktifkan semua semester dulu
        $pdo->query("UPDATE semester SET is_active = 0");
        // Aktifkan hanya yang dipilih
        $stmt = $pdo->prepare("UPDATE semester SET is_active = 1 WHERE id = ?");
        $stmt->execute([$smt_id]);
        $pesan = "Semester aktif berhasil diperbarui!";
        $tipe_pesan = "success";
    } catch (PDOException $e) {
        $pesan = "Gagal mengupdate status semester.";
        $tipe_pesan = "danger";
    }
}

if (isset($_GET['del_smt'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM semester WHERE id = ?");
        $stmt->execute([$_GET['del_smt']]);
        $pesan = "Semester berhasil dihapus.";
        $tipe_pesan = "success";
    } catch (PDOException $e) {
        $pesan = "Gagal menghapus! Semester ini mungkin sudah memiliki jadwal yang terikat.";
        $tipe_pesan = "danger";
    }
}

// ==========================================
// 2. PROSES MANAJEMEN MATA KULIAH
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_mk'])) {
    $kode_mk = strtoupper($_POST['kode_mk']);
    $nama_mk = $_POST['nama_mk'];
    $sks = $_POST['sks'];

    // Cek apakah kode MK sudah ada
    $cek = $pdo->prepare("SELECT id FROM mata_kuliah WHERE kode_mk = ?");
    $cek->execute([$kode_mk]);

    if ($cek->rowCount() > 0) {
        $pesan = "Kode Mata Kuliah <strong>$kode_mk</strong> sudah digunakan!";
        $tipe_pesan = "warning";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO mata_kuliah (kode_mk, nama_mk, sks) VALUES (?, ?, ?)");
            $stmt->execute([$kode_mk, $nama_mk, $sks]);
            $pesan = "Mata kuliah baru berhasil ditambahkan!";
            $tipe_pesan = "success";
        } catch (PDOException $e) {
            $pesan = "Gagal menambah mata kuliah.";
            $tipe_pesan = "danger";
        }
    }
}

if (isset($_GET['del_mk'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM mata_kuliah WHERE id = ?");
        $stmt->execute([$_GET['del_mk']]);
        $pesan = "Mata kuliah berhasil dihapus.";
        $tipe_pesan = "success";
    } catch (PDOException $e) {
        $pesan = "Gagal menghapus! Mata kuliah ini sedang digunakan di jadwal/nilai.";
        $tipe_pesan = "danger";
    }
}

// Ambil Data
$semesters = $pdo->query("SELECT * FROM semester ORDER BY id DESC")->fetchAll();
$mata_kuliah = $pdo->query("SELECT * FROM mata_kuliah ORDER BY kode_mk ASC")->fetchAll();

include '../../layouts/header.php';
include '../../layouts/sidebar.php';
?>

<div class="main-content-inner animation-fade-in">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
        <div>
            <h1 class="h2 fw-bold text-dark">Data Akademik</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Matkul & Semester</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if ($pesan): ?>
        <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show shadow-sm rounded-3" role="alert">
            <i class="bi <?= $tipe_pesan == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
            <?= $pesan ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                    <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-calendar3 me-2"></i>Periode Semester</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalSemester">
                        <i class="bi bi-plus"></i> Tambah
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="45%">Nama Semester</th>
                                    <th width="30%" class="text-center">Status</th>
                                    <th width="25%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($semesters as $s): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($s['nama_semester']) ?></td>
                                    <td class="text-center">
                                        <?php if ($s['is_active']): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3"><i class="bi bi-check-circle me-1"></i> Aktif</span>
                                        <?php else: ?>
                                            <form action="" method="POST" class="d-inline">
                                                <input type="hidden" name="semester_id" value="<?= $s['id'] ?>">
                                                <button type="submit" name="set_aktif" class="btn btn-sm btn-light border text-muted rounded-pill px-3" title="Jadikan Aktif">
                                                    Set Aktif
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="?del_smt=<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle p-2" onclick="return confirm('Hapus semester ini?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($semesters) == 0): ?>
                                    <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada data semester.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                    <h5 class="mb-0 fw-bold text-success"><i class="bi bi-journal-bookmark me-2"></i>Daftar Mata Kuliah</h5>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalMatkul">
                        <i class="bi bi-plus"></i> Tambah MK
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th width="20%">Kode</th>
                                    <th width="50%">Mata Kuliah</th>
                                    <th width="15%" class="text-center">SKS</th>
                                    <th width="15%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mata_kuliah as $mk): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($mk['kode_mk']) ?></span></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($mk['nama_mk']) ?></td>
                                    <td class="text-center fw-bold"><?= $mk['sks'] ?></td>
                                    <td class="text-center">
                                        <a href="?del_mk=<?= $mk['id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle p-2" onclick="return confirm('Hapus mata kuliah <?= $mk['nama_mk'] ?>?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (count($mata_kuliah) == 0): ?>
                                    <tr><td colspan="4" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Belum ada mata kuliah.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSemester" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-2"></i>Tambah Semester Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Nama Semester (Contoh: Ganjil 2026/2027)</label>
                        <input type="text" name="nama_semester" class="form-control" required placeholder="Masukkan nama semester...">
                    </div>
                    <div class="alert alert-info py-2 small mb-0"><i class="bi bi-info-circle me-1"></i> Semester baru secara default tidak akan langsung aktif. Anda perlu mengklik tombol <b>Set Aktif</b>.</div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_semester" class="btn btn-primary px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMatkul" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-journal-plus me-2"></i>Tambah Mata Kuliah</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Kode Mata Kuliah</label>
                        <input type="text" name="kode_mk" class="form-control" style="text-transform: uppercase;" required placeholder="Contoh: TIF101">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Nama Mata Kuliah</label>
                        <input type="text" name="nama_mk" class="form-control" required placeholder="Contoh: Algoritma & Pemrograman">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Jumlah SKS</label>
                        <input type="number" name="sks" class="form-control" min="1" max="6" required placeholder="1-6">
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_mk" class="btn btn-success px-4">Simpan Mata Kuliah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .animation-fade-in { animation: fadeIn 0.4s ease-in-out; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<?php include '../../layouts/footer.php'; ?>