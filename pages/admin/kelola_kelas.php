<?php
session_start();
require_once '../../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: ../../index.php"); exit; }

$pesan = ""; $tipe = "";

// Tambah Kelas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas) VALUES (?)");
    if ($stmt->execute([$_POST['nama_kelas']])) {
        $pesan = "Kelas berhasil ditambahkan!"; $tipe = "success";
    }
}

// Hapus Kelas
if (isset($_GET['del'])) {
    $stmt = $pdo->prepare("DELETE FROM kelas WHERE id = ?");
    $stmt->execute([$_GET['del']]);
    header("Location: kelola_kelas.php");
}

$kelas = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC")->fetchAll();
include '../../layouts/header.php';
include '../../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2 fw-bold">Kelola Kelas</h1>
</div>

<?php if($pesan): ?>
    <div class="alert alert-<?= $tipe ?> shadow-sm"><?= $pesan ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h5 class="fw-bold mb-3">Tambah Kelas Baru</h5>
            <form method="POST">
                <input type="text" name="nama_kelas" class="form-control mb-3" placeholder="Contoh: IF-22-A" required>
                <button type="submit" name="tambah" class="btn btn-primary w-100">Simpan Kelas</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr><th>No</th><th>Nama Kelas</th><th class="text-center">Aksi</th></tr>
                </thead>
                <tbody>
                    <?php $no=1; foreach($kelas as $k): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td class="fw-bold"><?= $k['nama_kelas'] ?></td>
                        <td class="text-center">
                            <a href="?del=<?= $k['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kelas?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../../layouts/footer.php'; ?>