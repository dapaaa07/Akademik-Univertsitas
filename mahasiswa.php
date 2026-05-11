<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: index.php");
    exit;
}

$mahasiswa_id = $_SESSION['user_id'];

// Ambil nilai khusus untuk mahasiswa yang sedang login
$stmt = $pdo->prepare("SELECT * FROM nilai WHERE mahasiswa_id = ?");
$stmt->execute([$mahasiswa_id]);
$nilai_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Mahasiswa</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h2>Selamat Datang, Mahasiswa <?= htmlspecialchars($_SESSION['nama_lengkap']) ?> | <a href="logout.php">Logout</a></h2>
    
    <h3>Transkrip Nilai Anda</h3>
    <table>
        <tr>
            <th>Mata Kuliah</th>
            <th>Nilai Tugas</th>
            <th>Nilai UTS</th>
            <th>Nilai UAS</th>
            <th>Nilai Akhir</th>
            <th>Grade</th>
        </tr>
        <?php if (count($nilai_list) > 0): ?>
            <?php foreach($nilai_list as $n): ?>
            <tr>
                <td><?= htmlspecialchars($n['mata_kuliah']) ?></td>
                <td><?= $n['nilai_tugas'] ?></td>
                <td><?= $n['nilai_uts'] ?></td>
                <td><?= $n['nilai_uas'] ?></td>
                <td><strong><?= $n['nilai_akhir'] ?></strong></td>
                <td><strong><?= $n['indeks_huruf'] ?></strong></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center;">Belum ada nilai yang diinput.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>