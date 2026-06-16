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
        :root {
            --primary: #556B2F;
            --soft-krem: #fdfaf1;
            --dark: #2d2d2d;
        }

        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--soft-krem); 
            margin: 0; 
            padding: 50px 20px; /* Diubah agar punya ruang napas di HP */
            box-sizing: border-box;
            width: 100%;
            overflow-x: hidden;
        }

        .card-detail { 
            width: 100%;
            max-width: 600px; 
            background: white; 
            margin: auto; 
            border-radius: 30px; 
            padding: 40px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.03); 
            box-sizing: border-box;
        }

        .back-link { 
            text-decoration: none; 
            color: var(--primary); 
            font-weight: bold; 
            display: inline-block; 
            margin-bottom: 25px; 
            transition: 0.2s;
        }
        .back-link:hover {
            transform: translateX(-5px);
        }

        .status-badge { 
            padding: 8px 18px; 
            border-radius: 50px; 
            font-weight: 800; 
            font-size: 0.75rem; 
            display: inline-block; 
            margin-top: 10px; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        .st-menunggu { background: #fff8e1; color: #f39c12; }
        .st-dipinjam { background: #e3f2fd; color: #3498db; } /* Sinkron status sedang dipinjam */
        .st-selesai { background: #e8f8f5; color: #27ae60; } /* Sinkron status selesai kembali */
        .st-ditolak { background: #fdeded; color: #e74c3c; }

        .info-row { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 15px 0; 
            border-bottom: 1px solid #fcfcfc; 
        }
        .info-label { color: #999; font-size: 0.9rem; font-weight: 500; }
        .info-value { font-weight: 600; color: var(--dark); font-size: 0.95rem; text-align: right; }

        /* 🌟 QR CODE WRAPPER CONTAINER */
        .qr-section {
            background: #fafafa;
            padding: 20px;
            border-radius: 20px;
            margin: 25px 0;
            text-align: center;
            border: 2px dashed #eee;
        }

        /* 📱 MEDIA QUERY PENYELAMAT LAYAR MOBILE HP */
        @media screen and (max-width: 768px) {
            body {
                padding: 20px 10px; /* Perkecil bodi wrapper luar di HP */
            }
            .card-detail {
                padding: 25px 20px; /* Ciutkan padding dalam kartu biar pas di ruang HP */
                border-radius: 20px;
            }
            .info-row {
                flex-direction: column; /* Ubah struktur horizontal jadi baris vertikal ke bawah */
                align-items: flex-start;
                gap: 5px;
                padding: 12px 0;
            }
            .info-value {
                text-align: left; /* Teks nilai rata kiri di HP biar rapi */
                font-size: 0.9rem;
            }
            .info-label {
                font-size: 0.8rem;
            }
            h2 {
                font-size: 1.4rem !important;
            }
            .qr-section img {
                width: 160px !important; /* Sesuaikan skala gambar QR agar manis di HP */
                height: 160px !important;
            }
        }
    </style>
</head>
<body>

    <div class="card-detail">
        <a href="riwayat.php" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Riwayat</a>
        
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="assets/img/alat/<?php echo $d['foto_alat']; ?>" onerror="this.src='https://via.placeholder.com/150?text=SIMLAB'" style="width: 130px; height: 150px; border-radius: 20px; object-fit: cover; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
            <h2 style="margin: 15px 0 5px; color: var(--dark);"><?php echo str_replace('_', ' ', $d['nama_alat']); ?></h2>
            
            <span class="status-badge st-<?php echo strtolower($d['status']); ?>">
                <i class="fas fa-circle" style="font-size: 0.4rem; vertical-align: middle; margin-right: 5px;"></i> <?php echo $d['status']; ?>
            </span>
        </div>

        <div class="qr-section">
            <p style="margin: 0 0 12px; font-size: 0.8rem; color: #888; font-weight: 600; letter-spacing: 0.5px;">VERIFICATION QR CODE TOKEN</p>
            <img src="<?php echo $d['qr_code']; ?>" alt="QR Transaksi" style="width: 180px; height: 180px; display: block; margin: 0 auto;">
            <p style="margin: 12px 0 0; font-size: 0.85rem; font-weight: 700; color: var(--primary);">#ID-<?php echo $d['id_pinjam']; ?></p>
        </div>

        <div class="info-row">
            <span class="info-label">Peminjam</span>
            <span class="info-value"><?php echo $d['nama']; ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Pinjam</span>
            <span class="info-value"><?php echo date('d F Y', strtotime($d['tgl_pinjam'])); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">Estimasi Kembali</span>
            <span class="info-value"><?php echo date('d F Y', strtotime($d['tgl_kembali'])); ?></span>
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