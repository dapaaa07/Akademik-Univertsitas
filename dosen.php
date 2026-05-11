<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'dosen') {
    header("Location: index.php");
    exit;
}

// Proses Input Nilai
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_nilai'])) {
    $mhs_id = $_POST['mahasiswa_id'];
    $matkul = $_POST['mata_kuliah'];
    $tugas = $_POST['nilai_tugas'];
    $uts = $_POST['nilai_uts'];
    $uas = $_POST['nilai_uas'];

    // Rumus Perhitungan Otomatis
    $nilai_akhir = ($tugas * 0.3) + ($uts * 0.3) + ($uas * 0.4);

    // Penentuan Grade
    if ($nilai_akhir >= 80) $grade = 'A';
    elseif ($nilai_akhir >= 70) $grade = 'B';
    elseif ($nilai_akhir >= 60) $grade = 'C';
    elseif ($nilai_akhir >= 50) $grade = 'D';
    else $grade = 'E';

    $stmt = $pdo->prepare("INSERT INTO nilai (mahasiswa_id, mata_kuliah, nilai_tugas, nilai_uts, nilai_uas, nilai_akhir, indeks_huruf) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$mhs_id, $matkul, $tugas, $uts, $uas, $nilai_akhir, $grade]);
    $pesan = "Nilai berhasil disimpan!";
}

// Ambil daftar mahasiswa untuk dropdown
$stmt_mhs = $pdo->query("SELECT id, nama_lengkap FROM users WHERE role = 'mahasiswa'");
$mahasiswa_list = $stmt_mhs->fetchAll();

// Ambil semua data nilai
$stmt_nilai = $pdo->query("SELECT n.*, u.nama_lengkap FROM nilai n JOIN users u ON n.mahasiswa_id = u.id");
$nilai_list = $stmt_nilai->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Dosen</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .form-box { background: #f9f9f9; padding: 15px; border: 1px solid #ccc; width: 400px; margin-bottom: 20px;}
        input, select, button { width: 100%; margin-bottom: 10px; padding: 8px; box-sizing: border-box;}
    </style>
</head>
<body>
    <h2>Selamat Datang, Dosen <?= htmlspecialchars($_SESSION['nama_lengkap']) ?> | <a href="logout.php">Logout</a></h2>
    
    <div class="form-box">
        <h3>Input Nilai Mahasiswa</h3>
        <?php if(isset($pesan)) echo "<p style='color:green;'>$pesan</p>"; ?>
        <form method="POST">
            <select name="mahasiswa_id" required>
                <option value="">Pilih Mahasiswa</option>
                <?php foreach($mahasiswa_list as $mhs): ?>
                    <option value="<?= $mhs['id'] ?>"><?= htmlspecialchars($mhs['nama_lengkap']) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="mata_kuliah" placeholder="Mata Kuliah" required>
            <input type="number" name="nilai_tugas" placeholder="Nilai Tugas (0-100)" min="0" max="100" required>
            <input type="number" name="nilai_uts" placeholder="Nilai UTS (0-100)" min="0" max="100" required>
            <input type="number" name="nilai_uas" placeholder="Nilai UAS (0-100)" min="0" max="100" required>
            <button type="submit" name="simpan_nilai" style="background-color: #28a745; color: white; border: none;">Simpan Nilai</button>
        </form>
    </div>

    <h3>Daftar Nilai Seluruh Mahasiswa</h3>
    <table>
        <tr>
            <th>Nama Mahasiswa</th>
            <th>Mata Kuliah</th>
            <th>Tugas (30%)</th>
            <th>UTS (30%)</th>
            <th>UAS (40%)</th>
            <th>Nilai Akhir</th>
            <th>Grade</th>
        </tr>
        <?php foreach($nilai_list as $n): ?>
        <tr>
            <td><?= htmlspecialchars($n['nama_lengkap']) ?></td>
            <td><?= htmlspecialchars($n['mata_kuliah']) ?></td>
            <td><?= $n['nilai_tugas'] ?></td>
            <td><?= $n['nilai_uts'] ?></td>
            <td><?= $n['nilai_uas'] ?></td>
            <td><strong><?= $n['nilai_akhir'] ?></strong></td>
            <td><strong><?= $n['indeks_huruf'] ?></strong></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>