<?php
session_start();
include 'config/koneksi.php'; // Koneksi asli kamu

// Proteksi Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Tangkap ID Transaksi dari POST form scan atau GET
$id_pinjam = "";
if (isset($_POST['id_pinjam'])) {
    $id_pinjam = mysqli_real_escape_string($koneksi, $_POST['id_pinjam']);
} elseif (isset($_GET['id'])) {
    $id_pinjam = mysqli_real_escape_string($koneksi, $_GET['id']);
}

if (empty($id_pinjam)) {
    echo "<script>alert('ID Transaksi Kosong!'); window.location='admin_peminjaman.php';</script>";
    exit;
}

// Ambil Data Transaksi Lengkap + Foto Alat untuk diverifikasi di layar
$sql = "SELECT peminjaman.*, alat.nama_alat, alat.prodi_pemilik, alat.foto_alat, users.nama 
        FROM peminjaman 
        JOIN alat ON peminjaman.id_alat = alat.id_alat 
        JOIN users ON peminjaman.npm = users.nim_id 
        WHERE peminjaman.id_pinjam = '$id_pinjam'";

$query = mysqli_query($koneksi, $sql);
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Gagal Validasi: ID Transaksi tidak terdaftar!'); window.location='admin_peminjaman.php';</script>";
    exit;
}

$status_sekarang = strtolower($data['status']);

// Logika ketika Admin menyetujui verifikasi di layar (Mengklik tombol "VALIDASI SESUAI")
if (isset($_POST['konfirmasi_setuju'])) {
    if ($status_sekarang == 'menunggu') {
        mysqli_query($koneksi, "UPDATE peminjaman SET status = 'disetujui' WHERE id_pinjam = '$id_pinjam'");
    } elseif ($status_sekarang == 'disetujui') {
        // Jika statusnya sedang dipinjam, berarti ini proses pengembalian, arahkan ke form kembali
        header("Location: proses_kembali.php?id=" . $id_pinjam);
        exit;
    }
    echo "<script>alert('Sirkulasi Berhasil Divalidasi secara Digital!'); window.location='admin_peminjaman.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lembar Kendali Digital - SIMLAB PMIPA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --primary: #556B2F; 
            --bg: #f8f9fa; 
            --accent-orange: #e67e22;
        }
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--bg); 
            margin: 0; 
            padding: 40px 20px; 
            display: flex; 
            justify-content: center; 
            align-items: center;
            min-height: 90vh;
        }
        .struk-card { 
            background: white; 
            width: 100%; 
            max-width: 460px; 
            padding: 35px; 
            border-radius: 25px; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.06); 
            border: 1px solid #eee; 
            text-align: center;
        }
        .struk-header { 
            border-bottom: 2px dashed #ddd; 
            padding-bottom: 20px; 
            margin-bottom: 20px; 
        }
        .struk-header h2 { 
            color: var(--primary); 
            margin: 10px 0 0; 
            font-weight: 800; 
            font-size: 1.5rem;
        }
        
        /* Gaya Visual Foto Alat Baru */
        .foto-container {
            margin: 15px auto;
            width: 130px;
            height: 130px;
            border-radius: 50px; /* Bentuk lingkaran modern squirrel */
            overflow: hidden;
            border: 3px solid #f0f0f0;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            background: #fff;
        }
        .foto-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .badge-status { 
            background: #fff3cd; 
            color: #856404; 
            padding: 6px 15px; 
            border-radius: 50px; 
            font-size: 0.75rem; 
            font-weight: 800; 
            display: inline-block; 
            text-transform: uppercase; 
            margin-top: 10px; 
        }
        .status-disetujui {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .info-box {
            text-align: left;
            margin-top: 10px;
        }
        .info-row { 
            display: flex; 
            justify-content: space-between; 
            padding: 12px 0; 
            border-bottom: 1px solid #fcfcfc; 
            font-size: 0.95rem; 
        }
        .info-label { color: #999; }
        .info-value { font-weight: 600; color: #333; text-align: right; }
        
        .btn-box { 
            display: flex; 
            gap: 15px; 
            margin-top: 30px; 
        }
        .btn-action { 
            flex: 1; 
            border: none; 
            padding: 14px; 
            border-radius: 12px; 
            font-weight: bold; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 10px; 
            font-size: 0.95rem; 
            transition: 0.3s; 
            text-decoration: none; 
        }
        .btn-confirm { 
            background: var(--primary); 
            color: white; 
        }
        .btn-confirm:hover { 
            background: #6b8e23; 
            transform: translateY(-2px); 
        }
        .btn-cancel { 
            background: #fdf2f2; 
            color: #ec5b5b; 
            border: 1px solid #f5c6c6;
        }
        .btn-cancel:hover { 
            background: #fde8e8; 
        }
    </style>
</head>
<body>

    <div class="struk-card">
        <div class="struk-header">
            <img src="assets/img/logo/logo_unila.png" width="50" alt="Logo Unila">
            <img src="assets/img/logo/logo_simlabnew.png?v=1" width="50" alt="Logo SIMLAB">
            <h2>LEMBAR KENDALI LAB</h2>
            <p style="color:#888; font-size:0.8rem; margin: 5px 0 0;">Verifikasi Berkas & Kondisi Fisik Alat</p>
            <span class="badge-status <?php echo ($status_sekarang == 'disetujui') ? 'status-disetujui' : ''; ?>">
                STATUS: <?php echo $data['status']; ?>
            </span>
        </div>

        <div class="foto-container">
            <img src="assets/img/alat/<?php echo $data['foto_alat']; ?>" onerror="this.src='https://via.placeholder.com/150'" alt="Foto Alat">
        </div>

        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Nama Alat</span>
                <span class="info-value" style="color: var(--primary); font-size: 1.05rem;"><?php echo str_replace('_', ' ', $data['nama_alat']); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">ID Peminjaman</span>
                <span class="info-value">#<?php echo $data['id_pinjam']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Nama Praktikan</span>
                <span class="info-value"><?php echo $data['nama']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">NPM Mahasiswa</span>
                <span class="info-value"><?php echo $data['npm']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Asal Laboratorium</span>
                <span class="info-value">PMIPA - <?php echo $data['prodi_pemilik']; ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal Ambil</span>
                <span class="info-value"><?php echo date('d M Y', strtotime($data['tgl_pinjam'])); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Batas Pengembalian</span>
                <span class="info-value" style="color: var(--accent-orange);"><?php echo date('d M Y', strtotime($data['tgl_kembali'])); ?></span>
            </div>
        </div>

        <form action="" method="POST" class="btn-box">
            <input type="hidden" name="id_pinjam" value="<?php echo $id_pinjam; ?>">
            <a href="admin_peminjaman.php" class="btn-action btn-cancel">
                <i class="fas fa-times"></i> BATALKAN
            </a>
            <button type="submit" name="konfirmasi_setuju" class="btn-action btn-confirm">
                <i class="fas fa-check-double"></i> VALIDASI SESUAI
            </button>
        </form>
    </div>

</body>
</html>