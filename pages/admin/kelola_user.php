<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

$pesan = "";
$tipe = "";

// 1. PROSES TAMBAH USER
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_user'])) {
    $nama = $_POST['nama_lengkap'];
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $role = $_POST['role'];
    $kelas = !empty($_POST['kelas_id']) ? $_POST['kelas_id'] : null;

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role, kelas_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user, $pass, $nama, $role, $kelas]);
        $pesan = "User berhasil didaftarkan!";
        $tipe = "success";
    } catch (PDOException $e) {
        $pesan = "Gagal: Username sudah ada.";
        $tipe = "danger";
    }
}

// 2. PROSES UPDATE IDENTITY (ROLE & KELAS)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_identitas'])) {
    $uid = $_POST['user_id'];
    $role = $_POST['role'];
    $kelas = !empty($_POST['kelas_id']) ? $_POST['kelas_id'] : null;

    try {
        $stmt = $pdo->prepare("UPDATE users SET role = ?, kelas_id = ? WHERE id = ?");
        $stmt->execute([$role, $kelas, $uid]);
        $pesan = "Identitas user berhasil diperbarui!";
        $tipe = "success";
    } catch (PDOException $e) {
        $pesan = "Gagal memperbarui identitas.";
        $tipe = "danger";
    }
}

// Ambil Data User & Kelas
$users = $pdo->query("SELECT u.*, k.nama_kelas FROM users u LEFT JOIN kelas k ON u.kelas_id = k.id ORDER BY u.role ASC")->fetchAll();
$kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC")->fetchAll();

include '../../layouts/header.php';
include '../../layouts/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <h1 class="h2 fw-bold">Kelola Identitas User</h1>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="bi bi-person-plus me-2"></i>Tambah User
    </button>
</div>

<?php if ($pesan): ?>
    <div class="alert alert-<?= $tipe ?> shadow-sm"><?= $pesan ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Nama & Username</th>
                <th>Role</th>
                <th>Identitas Kelas</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <strong><?= $u['nama_lengkap'] ?></strong><br>
                        <small class="text-muted">@<?= $u['username'] ?></small>
                    </td>
                    <td>
                        <span class="badge <?= $u['role'] == 'dosen' ? 'bg-success' : ($u['role'] == 'admin' ? 'bg-danger' : 'bg-primary') ?>">
                            <?= strtoupper($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u['role'] == 'mahasiswa'): ?>
                            <i class="bi bi-mortarboard me-1"></i> Kelas: <?= $u['nama_kelas'] ?: '<span class="text-danger small">Belum diatur</span>' ?>
                        <?php elseif ($u['role'] == 'dosen'): ?>
                            <i class="bi bi-person-badge me-1"></i> Wali Kelas: <?= $u['nama_kelas'] ?: '<span class="text-muted small">-</span>' ?>
                        <?php else: ?>
                            <span class="text-muted small">Akses Sistem</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editModal<?= $u['id'] ?>">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </td>
                </tr>

                <div class="modal fade" id="editModal<?= $u['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <div class="modal-header">
                                <h5 class="fw-bold">Update Identitas: <?= $u['nama_lengkap'] ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Role User</label>
                                        <select name="role" class="form-select" required>
                                            <option value="mahasiswa" <?= $u['role'] == 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                                            <option value="dosen" <?= $u['role'] == 'dosen' ? 'selected' : '' ?>>Dosen</option>
                                            <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Pilih Kelas (Wali/Identitas)</label>
                                        <select name="kelas_id" class="form-select">
                                            <option value="">-- Tanpa Kelas --</option>
                                            <?php foreach ($kelas_list as $k): ?>
                                                <option value="<?= $k['id'] ?>" <?= $u['kelas_id'] == $k['id'] ? 'selected' : '' ?>><?= $k['nama_kelas'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="update_identitas" class="btn btn-primary w-100">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="fw-bold">Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body p-4">
                    <input type="text" name="nama_lengkap" class="form-control mb-3" placeholder="Nama Lengkap" required>
                    <input type="text" name="username" class="form-control mb-3" placeholder="Username" required>
                    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
                    <select name="role" class="form-select mb-3" required>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="dosen">Dosen</option>
                        <option value="admin">Admin</option>
                    </select>
                    <select name="kelas_id" class="form-select">
                        <option value="">-- Pilih Kelas (Opsional) --</option>
                        <?php foreach ($kelas_list as $k): ?>
                            <option value="<?= $k['id'] ?>"><?= $k['nama_kelas'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="tambah_user" class="btn btn-primary w-100">Daftarkan User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../layouts/footer.php'; ?>