<?php
session_start();
include 'config/koneksi.php'; // Menggunakan file koneksi.php asli milikmu

// Proteksi: Kalau belum login, kembalikan ke login.php
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Tangkap ID Transaksi dari URL browser
if (!isset($_GET['id'])) {
    echo "<script>alert('Akses tidak sah!'); window.location='katalog.php';</script>";
    exit;
}

$id_pinjam = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data peminjaman gabung dengan detail alat berdasarkan id_pinjam
$sql = "SELECT peminjaman.*, alat.nama_alat, alat.prodi_pemilik 
        FROM peminjaman 
        JOIN alat ON peminjaman.id_alat = alat.id_alat 
        WHERE peminjaman.id_pinjam = '$id_pinjam'";

$query = mysqli_query($koneksi, $sql);
$data  = mysqli_fetch_assoc($query);

// Jika data transaksi tidak ditemukan di database
if (!$data) {
    echo "<script>alert('Data transaksi tidak ditemukan!'); window.location='katalog.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Sukses - SIMLAB PMIPA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root {
            --earthy-green: #556B2F;
            --soft-krem: #fdfaf1;
            --dark-text: #4a4a4a;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--soft-krem);
            color: var(--dark-text);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            text-align: center;
            max-width: 450px;
            width: 90%;
            margin: 20px;
        }
        .icon-success {
            color: var(--earthy-green);
            font-size: 4rem;
            margin-bottom: 15px;
        }
        h2 {
            color: var(--earthy-green);
            margin-bottom: 5px;
        }
        .trx-id {
            background: #f0f4eb;
            color: var(--earthy-green);
            display: inline-block;
            padding: 5px 15px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 20px;
            border: 1px dashed var(--earthy-green);
        }
        .qr-box {
            background: #fdfdfd;
            border: 2px solid #eee;
            padding: 15px;
            border-radius: 15px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .qr-box img {
            width: 200px;
            height: 200px;
            display: block;
        }
        .info-detail {
            text-align: left;
            background: #fafafa;
            padding: 15px;
            border-radius: 12px;
            font-size: 0.9rem;
            margin-bottom: 25px;
            border: 1px solid #eee;
        }
        .info-detail table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-detail td {
            padding: 6px 0;
        }
        .info-detail td.label {
            color: #888;
            width: 40%;
        }
        .btn-kembali {
            display: block;
            background: var(--earthy-green);
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-kembali:hover {
            background: #6b8e23;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(85, 107, 47, 0.3);
        }
    </style>
</head>
<body>

<div class="container" data-aos="zoom-in" data-aos-duration="800">
    <i class="fas fa-check-circle icon-success"></i>
    <h2>Pengajuan Berhasil!</h2>
    <p style="color:#888; margin-top:0; font-size:0.9rem;">Sistem berhasil mengunci token digital Anda.</p>
    
    <div class="trx-id"><?php echo $data['id_pinjam']; ?></div>
    
    <br>
    <div class="qr-box">
        <img src="<?php echo $data['qr_code']; ?>" alt="QR Token Peminjaman">
    </div>
    
    <p style="font-size: 0.85rem; color: #e67e22; font-weight: 600; margin-bottom: 20px;">
        <i class="fas fa-exclamation-triangle"></i> Tunjukkan QR Code di atas kepada Laboran untuk penyerahan alat praktikum.
    </p>

    <div class="info-detail">
        <table>
            <tr>
                <td class="label">Nama Alat</td>
                <td><strong><?php echo str_replace('_', ' ', $data['nama_alat']); ?></strong></td>
            </tr>
            <tr>
                <td class="label">Prodi Pemilik</td>
                <td><span style="color:var(--earthy-green); font-weight:600;"><?php echo $data['prodi_pemilik']; ?></span></td>
            </tr>
            <tr>
                <td class="label">Tgl Pinjam</td>
                <td><?php echo date('d M Y', strtotime($data['tgl_pinjam'])); ?></td>
            </tr>
            <tr>
                <td class="label">Tgl Kembali</td>
                <td><?php echo date('d M Y', strtotime($data['tgl_kembali'])); ?></td>
            </tr>
        </table>
    </div>

    <a href="index.php" class="btn-kembali"><i class="fas fa-home"></i> Kembali ke Beranda</a>
</div>

<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init();
</script>
</body>
</html>