<?php
session_start();
require_once '../../config/db.php';

// Proteksi halaman: Pastikan user login dan adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

$pesan = "";
$tipe_pesan = "";

// 1. PROSES TAMBAH USER BARU
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_user'])) {
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password = $_POST['password']; // Di production, gunakan password_hash()
    $role = $_POST['role'];

    // Cek apakah username sudah ada
    $cek = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $cek->execute([$username]);

    if ($cek->rowCount() > 0) {
        $pesan = "Username sudah digunakan! Silakan pilih yang lain.";
        $tipe_pesan = "warning";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $password, $nama_lengkap, $role]);
            $pesan = "Pengguna baru berhasil ditambahkan!";
            $tipe_pesan = "success";
        } catch (PDOException $e) {
            $pesan = "Gagal menambah pengguna: " . $e->getMessage();
            $tipe_pesan = "danger";
        }
    }
}

// 2. PROSES UPDATE ROLE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_role'])) {
    $user_id_update = $_POST['user_id'];
    $role_baru = $_POST['role'];

    if ($user_id_update == $_SESSION['user_id'] && $role_baru != 'admin') {
        $pesan = "Anda tidak dapat menghapus hak akses Admin dari akun Anda sendiri!";
        $tipe_pesan = "danger";
    } else {
        try {
            $stmt_update = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt_update->execute([$role_baru, $user_id_update]);
            $pesan = "Hak akses pengguna berhasil diperbarui!";
            $tipe_pesan = "success";
        } catch (PDOException $e) {
            $pesan = "Terjadi kesalahan: " . $e->getMessage();
            $tipe_pesan = "danger";
        }
    }
}

// 3. PROSES HAPUS USER
if (isset($_GET['delete'])) {
    $user_id_delete = $_GET['delete'];

    if ($user_id_delete == $_SESSION['user_id']) {
        $pesan = "Anda tidak dapat menghapus akun Anda sendiri!";
        $tipe_pesan = "warning";
    } else {
        try {
            $stmt_delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt_delete->execute([$user_id_delete]);
            $pesan = "Pengguna berhasil dihapus dari sistem.";
            $tipe_pesan = "success";
        } catch (PDOException $e) {
            $pesan = "Gagal menghapus! User ini memiliki data (nilai/jadwal) yang tidak bisa dihapus sembarangan.";
            $tipe_pesan = "danger";
        }
    }
}

// Ambil semua data user dari database
$stmt_users = $pdo->query("SELECT id, username, nama_lengkap, role FROM users ORDER BY role ASC, nama_lengkap ASC");
$users = $stmt_users->fetchAll();

// Import Layouts
include '../../layouts/header.php';
include '../../layouts/sidebar.php';
?>

<div class="main-content-inner animation-fade-in">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <div>
            <h1 class="h2 fw-bold text-dark">Kelola Pengguna</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Manajemen User</li>
                </ol>
            </nav>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
                <i class="bi bi-person-plus-fill me-2"></i>Tambah User Baru
            </button>
        </div>
    </div>

    <?php if ($pesan): ?>
        <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show shadow-sm rounded-4" role="alert">
            <i class="bi <?= $tipe_pesan == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' ?> me-2"></i>
            <?= $pesan ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-people me-2"></i>Daftar Pengguna Sistem</h5>
            <span class="badge bg-light text-dark border px-3 py-2">Total: <?= count($users) ?> User</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="25%">Informasi Pengguna</th>
                            <th width="15%">Role Saat Ini</th>
                            <th width="35%">Manajemen Akses</th>
                            <th width="20%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($users as $u):
                        ?>
                            <tr>
                                <td class="text-center text-muted"><?= $no++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light p-2 rounded-circle me-3">
                                            <i class="bi bi-person-circle fs-4 text-secondary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($u['nama_lengkap']) ?></h6>
                                            <small class="text-muted">@<?= htmlspecialchars($u['username']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $badge_bg = 'bg-secondary';
                                    if ($u['role'] == 'admin') $badge_bg = 'bg-danger bg-opacity-10 text-danger border border-danger';
                                    elseif ($u['role'] == 'dosen') $badge_bg = 'bg-success bg-opacity-10 text-success border border-success';
                                    elseif ($u['role'] == 'mahasiswa') $badge_bg = 'bg-primary bg-opacity-10 text-primary border border-primary';
                                    ?>
                                    <span class="badge <?= $badge_bg ?> px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi <?= $u['role'] == 'admin' ? 'bi-shield-lock' : ($u['role'] == 'dosen' ? 'bi-person-video3' : 'bi-mortarboard') ?> me-1"></i>
                                        <?= strtoupper($u['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <form action="" method="POST" class="d-flex align-items-center gap-2">
                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                            <select name="role" class="form-select form-select-sm w-auto shadow-none" required>
                                                <option value="mahasiswa" <?= $u['role'] == 'mahasiswa' ? 'selected' : '' ?>>Mahasiswa</option>
                                                <option value="dosen" <?= $u['role'] == 'dosen' ? 'selected' : '' ?>>Dosen</option>
                                                <option value="admin" <?= $u['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                            <button type="submit" name="update_role" class="btn btn-sm btn-light border text-primary">
                                                Update
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small px-2 py-1 bg-light rounded"><i class="bi bi-shield-check text-success me-1"></i> Akun Anda (Super Admin)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Hapus User" onclick="return confirm('Yakin ingin menghapus user <?= $u['nama_lengkap'] ?>?');">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-light rounded-circle p-2 text-muted" disabled><i class="bi bi-trash"></i></button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (count($users) == 0): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>Tidak ada data pengguna.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahUser" tabindex="-1" aria-labelledby="modalTambahUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold" id="modalTambahUserLabel"><i class="bi bi-person-plus me-2"></i>Tambah User Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" placeholder="Masukkan nama lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Username Login</label>
                        <input type="text" name="username" class="form-control" placeholder="Pilih username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">Hak Akses (Role)</label>
                        <select name="role" class="form-select" required>
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="dosen">Dosen</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah_user" class="btn btn-primary px-4">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .animation-fade-in {
        animation: fadeIn 0.4s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>

<?php include '../../layouts/footer.php'; ?>