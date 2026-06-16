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
    $id_pinjam   = "TRX-" . date('YmdHis') . "-" . rand(100, 999);

    // 🌟 2. GENERATE TOKEN REVERSE QR-VERIFICATION
    $qr_data     = $id_pinjam . "|" . $npm . "|" . $id_alat;
    
    // Menggunakan API QR open-source
    $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qr_data);

    // 🌟 3. KUERI INSERTS YANG SUDAH SINKRON DENGAN STRUKTUR BARU
    $insert = mysqli_query($koneksi, "INSERT INTO peminjaman (id_pinjam, npm, id_alat, tgl_pinjam, tgl_kembali, keperluan, status, qr_code) 
            VALUES ('$id_pinjam', '$npm', '$id_alat', '$tgl_pinjam', '$tgl_kembali', '$keperluan', '$status', '$qr_code_url')");

    if ($insert) {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman - SIMLAB PMIPA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --earthy-green: #556B2F; 
            --soft-krem: #fdfaf1; 
            --text-dark: #333333;
        }
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--soft-krem); 
            margin: 0; 
            display: flex; 
            width: 100%;
            overflow-x: hidden;
        }
        
        /* --- SIDEBAR MASTER LAYOUT (DESKTOP LAPTOP) --- */
        .sidebar { 
            width: 260px; 
            height: 100vh; 
            background: white; 
            border-right: 1px solid #eee; 
            position: fixed; 
            padding: 30px 20px; 
            box-sizing: border-box; 
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }

        .logo-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 40px;
            padding-bottom: 15px;
            border-bottom: 2px solid #fdfaf1;
        }
        .logo-header img.unila { 
            width: 55px; 
            height: auto; 
        }
        .logo-header img.simlab { 
            width: 50px; 
            height: auto; 
        }

        .sidebar-menu {
            flex: 1;
        }
        .sidebar a { 
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px; 
            color: #4a4a4a; 
            text-decoration: none; 
            border-radius: 10px; 
            margin-bottom: 8px; 
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .sidebar a i {
            width: 20px;
            text-align: center;
        }
        .sidebar a:hover {
            background: #f5f5f5;
            color: var(--earthy-green);
        }
        .sidebar a.active { 
            background: var(--earthy-green); 
            color: white; 
            box-shadow: 0 4px 10px rgba(85, 107, 47, 0.3);
        }

        /* --- MAIN CONTENT AREA --- */
        .main { 
            margin-left: 260px; 
            flex: 1; 
            padding: 40px; 
            display: flex;
            justify-content: center;
            align-items: flex-start;
            box-sizing: border-box;
            width: calc(100% - 260px);
        }
        .form-box { 
            background: white; 
            padding: 35px; 
            border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.04); 
            width: 100%;
            max-width: 600px; 
            box-sizing: border-box;
        }
        .form-box h2 {
            margin-top: 0;
            color: var(--text-dark);
        }
        .form-group { 
            margin-bottom: 22px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600; 
            color: var(--earthy-green); 
            font-size: 14px;
        }
        .form-group input, .form-group textarea { 
            width: 100%; 
            padding: 12px 15px; 
            border-radius: 10px; 
            border: 1px solid #ddd; 
            box-sizing: border-box; 
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--earthy-green);
        }
        
        .alat-info { 
            background: #f9f9f9; 
            padding: 15px 18px; 
            border-radius: 10px; 
            margin-bottom: 25px; 
            border-left: 5px solid var(--earthy-green); 
            font-size: 15px;
            color: #444;
            word-wrap: break-word;
        }
        .btn-submit { 
            background: var(--earthy-green); 
            color: white; 
            border: none; 
            padding: 15px; 
            border-radius: 10px; 
            cursor: pointer; 
            font-weight: bold; 
            font-size: 15px;
            width: 100%; 
            letter-spacing: 0.5px;
            transition: background 0.2s;
        }
        .btn-submit:hover { 
            background: #415224; 
        }

        /* 📱 REVISI STRUKTUR NAV SIDEBAR MENJADI TOPBAR KETIKA DI LAYAR HP */
        @media screen and (max-width: 768px) {
            body {
                flex-direction: column !important; /* Paksa tumpukan susunan dari atas ke bawah */
            }

            /* Transformasi sidebar menjadi bar horizontal atas */
            .sidebar {
                width: 100% !important;
                height: auto !important;
                position: relative !important; /* Lepas pengekangan status fixed */
                padding: 15px 20px !important;
                box-sizing: border-box;
                border-right: none !important;
                border-bottom: 1px solid #eee;
            }

            .logo-header {
                margin-bottom: 15px !important;
                padding-bottom: 8px !important;
            }
            .logo-header img.unila { width: 45px; }
            .logo-header img.simlab { width: 40px; }

            /* Susun barisan menu navigasi sejajar horizontal kesamping di HP */
            .sidebar-menu {
                display: flex !important;
                justify-content: center !important;
                gap: 5px !important;
                width: 100%;
            }
            .sidebar a {
                padding: 8px 10px !important;
                font-size: 0.8rem !important;
                margin-bottom: 0 !important;
                flex: 1;
                justify-content: center;
                gap: 6px;
            }

            .sidebar-footer, hr {
                display: none !important; /* Sembunyikan pembatas dan logout atas demi kerapian layout */
            }

            /* Lebarkan kontainer area form utama agar mengikuti ruang HP */
            .main {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 25px 15px !important;
            }
            .form-box {
                padding: 25px 20px !important;
                border-radius: 15px !important;
            }
            .form-box h2 {
                font-size: 1.4rem !important;
            }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo-header">
            <img src="assets/img/logo/logo_unila.png" class="unila" alt="Logo Unila">
            <img src="assets/img/logo/logo_simlabnew.png?v=1" class="simlab" alt="Logo SIMLAB">
        </div>
        
        <div class="sidebar-menu">
            <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
            <a href="katalog.php" class="active"><i class="fas fa-microscope"></i> Katalog Alat</a>
            <a href="panduan.php"><i class="fas fa-book-open"></i> Panduan Pinjam</a>
        </div>

        <div class="sidebar-footer">
            <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 15px;">
            <a href="logout.php" style="color: #d9534f; margin-bottom: 0;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="form-box">
            <h2>Pengajuan Peminjaman</h2>
            <p style="color: #888; margin-bottom: 25px;">Lengkapi data di bawah ini untuk meminjam alat.</p>
            
            <div class="alat-info">
                <strong>Alat yang dipilih:</strong><br>
                <span style="color: var(--earthy-green); font-weight: 600;">
                    <?php echo str_replace('_', ' ', $data_alat['nama_alat']); ?>
                </span> 
                (<?php echo $data_alat['prodi_pemilik']; ?>)
            </div>

            <form action="" method="POST">
                <div class="form-group">
                    <label><i class="far fa-calendar-alt"></i> Tanggal Pinjam</label>
                    <input type="date" name="tgl_pinjam" required>
                </div>
                <div class="form-group">
                    <label><i class="far fa-calendar-check"></i> Estimasi Pengembalian</label>
                    <input type="date" name="tgl_kembali" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-pen-nib"></i> Keperluan / Alasan Pinjam</label>
                    <textarea name="keperluan" rows="4" placeholder="Contoh: Praktikum Mata Kuliah Mikrobiologi" required></textarea>
                </div>
                <button type="submit" name="ajukan" class="btn-submit">KIRIM PERMOHONAN</button>
            </form>
        </div>
    </div>

</body>
</html>