<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: riwayat.php");
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// Perbaikan Query: Menggunakan 'nama' sesuai struktur tabel kamu
// Dan menggunakan 'nim_id' atau 'npm' sesuai kolom join yang benar
$sql = "SELECT peminjaman.*, alat.nama_alat, alat.foto_alat, alat.prodi_pemilik, users.nama 
        FROM peminjaman 
        JOIN alat ON peminjaman.id_alat = alat.id_alat 
        JOIN users ON peminjaman.npm = users.nim_id 
        WHERE peminjaman.id_pinjam = '$id'";

$query = mysqli_query($koneksi, $sql);
$d = mysqli_fetch_assoc($query);

if (!$d) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='riwayat.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peminjaman - SIMLAB PMIPA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #fdfaf1; margin: 0; padding: 50px; }
        .card-detail { max-width: 600px; background: white; margin: auto; border-radius: 30px; padding: 40px; box-shadow: 0 20px 50px rgba(0,0,0,0.05); }
        .back-link { text-decoration: none; color: #556B2F; font-weight: bold; display: inline-block; margin-bottom: 20px; }
        .status-badge { padding: 10px 20px; border-radius: 50px; font-weight: 800; font-size: 0.8rem; display: inline-block; margin-top: 10px; text-transform: uppercase; }
        .st-kembali { background: #e3f2fd; color: #3498db; }
        .st-disetujui { background: #e8f8f5; color: #27ae60; }
        .st-menunggu { background: #fff8e1; color: #f39c12; }
        .st-ditolak { background: #fdeded; color: #e74c3c; }
        .info-row { display: flex; justify-content: space-between; padding: 15px 0; border-bottom: 1px solid #f9f9f9; }
        .info-label { color: #aaa; font-size: 0.9rem; }
        .info-value { font-weight: 600; color: #2d2d2d; }
    </style>
</head>
<body>

    <div class="card-detail">
        <a href="riwayat.php" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Riwayat</a>
        
        <div style="text-align: center; margin-bottom: 30px;">
            <img src="assets/img/alat/<?php echo $d['foto_alat']; ?>" style="width: 150px; height: 150px; border-radius: 20px; object-fit: cover; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
            <h2 style="margin: 15px 0 5px;"><?php echo str_replace('_', ' ', $d['nama_alat']); ?></h2>
            <span class="status-badge st-<?php echo strtolower($d['status']); ?>">
                <i class="fas fa-circle" style="font-size: 0.5rem; vertical-align: middle;"></i> <?php echo $d['status']; ?>
            </span>
        </div>

        <div class="info-row">
            <span class="info-label">Peminjam</span>
            <span class="info-value"><?php echo $d['nama']; ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">ID Pinjam</span>
            <span class="info-value">#ID-<?php echo $d['id_pinjam']; ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Pinjam</span>
            <span class="info-value"><?php echo date('d F Y', strtotime($d['tgl_pinjam'])); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Keperluan</span>
            <span class="info-value"><?php echo $d['keperluan']; ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Prodi Pemilik</span>
            <span class="info-value"><?php echo $d['prodi_pemilik']; ?></span>
        </div>
    </div>

</body>
</html>