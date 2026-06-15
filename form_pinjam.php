<?php
session_start();
include 'config/koneksi.php';

// Proteksi: Kalau belum login, tendang balik ke login.php
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Ambil ID alat dari URL
$id_alat = $_GET['id'];
$query_alat = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat = '$id_alat'");
$data_alat = mysqli_fetch_assoc($query_alat);

// Jika tombol Ajukan diklik
if (isset($_POST['ajukan'])) {
    $npm         = $_SESSION['npm']; // Mengambil NPM dari session login asli Shava
    $tgl_pinjam  = $_POST['tgl_pinjam'];
    $tgl_kembali = $_POST['tgl_kembali'];
    $keperluan   = mysqli_real_escape_string($koneksi, $_POST['keperluan']);
    $status      = "Menunggu"; // Status awal data masuk

    // 🌟 1. LOGIKA AUTOGENERATE ID TRANSAKSI UNIK (OTOMATIS BERBASIS WAKTU)
    // Menghasilkan kode acak anti-duplikat, Contoh hasil: TRX-20260521-742
    $id_pinjam   = "TRX-" . date('YmdHis') . "-" . rand(100, 999);

    // 🌟 2. GENERATE TOKEN REVERSE QR-VERIFICATION
    // Teks di dalam QR Code berisi gabungan ID Pinjam, NPM Mahasiswa, dan ID Alat
    $qr_data     = $id_pinjam . "|" . $npm . "|" . $id_alat;
    
    // Menggunakan API QR open-source untuk mengubah string teks menjadi gambar QR secara otomatis
    $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qr_data);

    // 🌟 3. KUERI INSERTS YANG SUDAH SINKRON DENGAN STRUKTUR BARU (MENDUKUNG VARCHAR & QR)
    $insert = mysqli_query($koneksi, "INSERT INTO peminjaman (id_pinjam, npm, id_alat, tgl_pinjam, tgl_kembali, keperluan, status, qr_code) 
            VALUES ('$id_pinjam', '$npm', '$id_alat', '$tgl_pinjam', '$tgl_kembali', '$keperluan', '$status', '$qr_code_url')");

    if ($insert) {
        // Jika sukses disimpan, langsung arahkan mahasiswa ke halaman sukses booking bawa data ID Pinjamnya
        echo "<script>alert('Permohonan berhasil dikirim! Silakan tunjukkan QR Code Anda ke Laboran.'); window.location='sukses_booking.php?id=" . $id_pinjam . "';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan transaksi: " . mysqli_error($koneksi) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Peminjaman - SIMLAB PMIPA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --earthy-green: #556B2F; --soft-krem: #fdfaf1; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--soft-krem); margin: 0; display: flex; }
        
        .sidebar { width: 260px; height: 100vh; background: white; border-right: 1px solid #eee; position: fixed; padding: 50px 20px; box-sizing: border-box; }
        .sidebar a { display: block; padding: 12px 15px; color: #4a4a4a; text-decoration: none; border-radius: 10px; margin-bottom: 8px; }
        .sidebar a.active { background: var(--earthy-green); color: white; }

        .main { margin-left: 260px; flex: 1; padding: 40px; }
        .form-box { background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: bold; color: var(--earthy-green); }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ddd; box-sizing: border-box; }
        
        .alat-info { background: #f9f9f9; padding: 15px; border-radius: 10px; margin-bottom: 25px; border-left: 5px solid var(--earthy-green); }
        .btn-submit { background: var(--earthy-green); color: white; border: none; padding: 15px 25px; border-radius: 10px; cursor: pointer; font-weight: bold; width: 100%; }
        .btn-submit:hover { background: #6b8e23; }
    </style>
</head>
<body>

    <div class="sidebar">
        <img src="assets/img/logo/logo_unila.png" alt="Logo Unila" style="width: 80px; display: block; margin: 0 auto 20px;">
        <img src="assets/img/logo/logo_simlabnew.png?v=1" width="70" alt="Logo SIMLAB">
        <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
        <a href="katalog.php" class="active"><i class="fas fa-microscope"></i> Katalog Alat</a>
        <a href="panduan.php"><i class="fas fa-book-open"></i> Panduan Pinjam</a>
        <hr style="border: 0.5px solid #eee; margin: 20px 0;">
        <a href="logout.php" style="color: #d9534f;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main">
        <div class="form-box">
            <h2>Pengajuan Peminjaman</h2>
            <p style="color: #888;">Lengkapi data di bawah ini untuk meminjam alat.</p>
            
            <div class="alat-info">
                <strong>Alat yang dipilih:</strong><br>
                <?php echo str_replace('_', ' ', $data_alat['nama_alat']); ?> (<?php echo $data_alat['prodi_pemilik']; ?>)
            </div>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Tanggal Pinjam</label>
                    <input type="date" name="tgl_pinjam" required>
                </div>
                <div class="form-group">
                    <label>Estimasi Pengembalian</label>
                    <input type="date" name="tgl_kembali" required>
                </div>
                <div class="form-group">
                    <label>Keperluan / Alasan Pinjam</label>
                    <textarea name="keperluan" rows="4" placeholder="Contoh: Praktikum Mata Kuliah Mikrobiologi" required></textarea>
                </div>
                <button type="submit" name="ajukan" class="btn-submit">KIRIM PERMOHONAN</button>
            </form>
        </div>
    </div>

</body>
</html>