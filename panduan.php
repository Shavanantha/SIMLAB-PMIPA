<?php
session_start(); 
include 'config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panduan Peminjaman - SIMLAB PMIPA</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --earthy-green: #556B2F;
            --soft-krem: #fdfaf1;
            --dark-text: #4a4a4a;
            --accent-green: #6b8e23;
        }

        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            background-color: var(--soft-krem); 
            color: var(--dark-text); 
        }

        /* NAVBAR ATAS (NAVBAR HORIZONTAL) */
        .navbar {
            background: white;
            padding: 15px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand { 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            text-decoration: none; 
            color: var(--earthy-green); 
            font-weight: 800; 
            font-size: 1.2rem;
        }
        .navbar-menu { 
            display: flex; 
            align-items: center;
            gap: 35px; 
        }
        .navbar-menu a { 
            text-decoration: none; 
            color: var(--dark-text); 
            font-weight: 600; 
            font-size: 0.95rem; 
            transition: 0.3s; 
        }
        .navbar-menu a:hover, .navbar-menu a.active { 
            color: var(--earthy-green); 
        }
        .btn-login { 
            background: var(--earthy-green); 
            color: white !important; 
            padding: 10px 25px; 
            border-radius: 50px; 
            box-shadow: 0 4px 10px rgba(85, 107, 47, 0.3);
        }

        /* MAIN CONTENT */
        .container { 
            padding: 60px 10% 100px; 
            max-width: 1100px;
            margin: auto;
        }
        
        .content-box { 
            background: white; 
            padding: 50px; 
            border-radius: 30px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.03); 
            border: 1px solid #f1f1f1;
        }

        .header-panduan { 
            text-align: center; 
            margin-bottom: 60px; 
            border-bottom: 2px solid var(--soft-krem);
            padding-bottom: 30px;
        }
        .header-panduan h2 { 
            color: var(--earthy-green); 
            font-size: 2.2rem; 
            margin: 0;
            font-weight: 800;
        }
        .header-panduan p { 
            color: #888; 
            margin-top: 15px; 
            font-size: 1.1rem;
        }

        /* TIMELINE STYLE */
        .timeline { position: relative; padding-left: 20px; }
        .timeline::before {
            content: '';
            position: absolute;
            left: 37px;
            top: 5px;
            width: 3px;
            height: calc(100% - 50px);
            background: #f0f0f0;
            z-index: 1;
        }

        .step { position: relative; display: flex; align-items: flex-start; margin-bottom: 50px; z-index: 2; }
        .step-number { 
            background: var(--earthy-green); 
            color: white; 
            width: 36px; 
            height: 36px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold; 
            margin-right: 30px; 
            flex-shrink: 0; 
            box-shadow: 0 4px 12px rgba(85, 107, 47, 0.2);
            border: 4px solid white;
        }

        .step-content { 
            background: #fcfcfc; 
            padding: 25px 35px; 
            border-radius: 20px; 
            width: 100%;
            transition: 0.4s;
            border: 1px solid #eee;
        }
        .step:hover .step-content { 
            border-color: var(--earthy-green); 
            transform: translateX(15px); 
            background: white; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
        }
        
        .step-content h3 { 
            margin: 0 0 12px 0; 
            color: var(--earthy-green); 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            font-size: 1.2rem; 
        }
        .step-content p { 
            margin: 0; 
            line-height: 1.8; 
            color: #666; 
            font-size: 1rem; 
        }

        .alert-info { 
            margin-top: 40px; 
            background: #e8f4fd; 
            color: #2980b9; 
            padding: 20px 30px; 
            border-radius: 15px; 
            font-size: 1rem; 
            display: flex; 
            align-items: center; 
            gap: 15px;
            border-left: 5px solid #2980b9;
        }

        footer { 
            background: #1a1a1a; 
            color: #ccc; 
            padding: 80px 10% 40px; 
            border-top: 5px solid var(--earthy-green);
            margin-top: 50px;
        }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 60px; text-align: left; }
        .footer-info h4 { color: white; margin-bottom: 25px; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; }
        .footer-info p { line-height: 1.8; font-size: 0.95rem; margin: 5px 0; }
        .footer-bottom { 
            text-align: center; margin-top: 60px; padding-top: 30px; 
            border-top: 1px solid #333; font-size: 0.85rem; opacity: 0.6; 
        }

        @media (max-width: 768px) {
            .navbar { padding: 15px 5%; }
            .navbar-menu { display: none; } /* Mobile menu simplified for demo */
            .container { padding: 40px 5%; }
        }
    </style>
</head>
<body>

    <!-- NAVBAR ATAS -->
        <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <img src="assets/img/logo/logo_unila.png" width="42" alt="Logo Unila">
            <img src="assets/img/logo/logo_simlabnew.png" width="42" alt="Logo SIMLAB">
            <span style="margin-left: 5px;">SIMLAB PMIPA</span>
        </a>
        <div class="navbar-menu">
            <a href="index.php">Beranda</a>
            <a href="katalog.php">Katalog Alat</a>
            <a href="panduan.php" class="active">Panduan</a>
            <?php if(isset($_SESSION['login'])): ?>
                <a href="riwayat.php">Riwayat Saya</a>
                <a href="logout.php" style="color: #d9534f;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="content-box" data-aos="fade-up">
            <div class="header-panduan">
                <h2>Prosedur Peminjaman Alat</h2>
                <p>Halo <strong><?php echo isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Mahasiswa Unila'; ?></strong>, ikuti alur peminjaman berikut:</p>
            </div>

<div class="timeline">
                
                <div class="step" data-aos="fade-left" data-aos-delay="100">
                    <div class="step-number">1</div>
                    <div class="step-content" style="border-left: 5px solid #d9534f; background: #fff9f9;">
                        <h3 style="color: #d9534f;"><i class="fas fa-id-card-clip"></i> Registrasi Akun Perdana</h3>
                        <p>Bagi mahasiswa/praktikan baru yang NPM-nya <b>belum terdaftar</b> di sistem, silakan datang langsung ke meja petugas <b>Laboran di Gedung L FKIP Unila</b> dengan membawa KTM aktif untuk didaftarkan akunnya secara resmi.</p>
                    </div>
                </div>

                <div class="step" data-aos="fade-left" data-aos-delay="200">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3><i class="fas fa-search"></i> Cari Alat Praktikum</h3>
                        <p>Telusuri menu <b>Katalog Alat</b> untuk melihat inventaris sarana praktikum eksakta yang tersedia di Laboratorium PMIPA. Pastikan status alat menunjukkan kuantitas stok memadai.</p>
                    </div>
                </div>

                <div class="step" data-aos="fade-left" data-aos-delay="300">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3><i class="fas fa-sign-in-alt"></i> Login Validasi Sesi</h3>
                        <p>Klik tombol <b>Login</b> di pojok kanan atas. Masukkan kredensial akun berupa <b>NPM</b> dan <b>Password</b> yang telah diaktivasi oleh laboran sebelumnya.</p>
                    </div>
                </div>

                <div class="step" data-aos="fade-left" data-aos-delay="400">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3><i class="fas fa-file-pen"></i> Isi Formulir Pinjam</h3>
                        <p>Lengkapi rincian formulir data pengajuan seperti tanggal penggunaan, estimasi pengembalian (maksimal 3 hari kerja), serta klaster rumpun prodi praktikan.</p>
                    </div>
                </div>

                <div class="step" data-aos="fade-left" data-aos-delay="500">
                    <div class="step-number">5</div>
                    <div class="step-content">
                        <h3><i class="fas fa-qrcode"></i> Klaim Bukti Token</h3>
                        <p>Tunggu verifikasi admin. Jika status pengajuan berubah menjadi <b>Disetujui</b>, bawa screenshot lembar ringkasan berkas atau token <b>QR Code</b> Anda ke laboratorium untuk pencocokan fisik barang.</p>
                    </div>
                </div>
            </div>

            <div class="alert-info" style="background: #f1f7e9; color: var(--earthy-green); border-left: 5px solid var(--earthy-green);">
                <i class="fas fa-circle-info" style="color: var(--accent-green);"></i>
                <span>Status pengesahan sirkulasi dan sisa batas waktu kembali dapat Anda pantau secara <i>real-time</i> melalui menu <strong>Riwayat Saya</strong> setelah login.</span>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-grid">
            <div class="footer-info">
                <h4><i class="fas fa-map-marker-alt"></i> Lokasi Laboratorium</h4>
                <p>Gedung L FKIP Universitas Lampung, Lantai 2 dan 3</p>
                <p>Jl. Prof. Dr. Sumantri Brojonegoro No. 1</p>
                <p>Bandar Lampung, 35145</p>
            </div>
            <div class="footer-info">
                <h4><i class="fas fa-envelope"></i> Hubungi Kami</h4>
                <p>Email: labpmipa@fkip.unila.ac.id</p>
                <p>WhatsApp: +62 812-3456-7890 (Admin Lab)</p>
                <p>Instagram: @simlabpmipa_unila</p>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; 2026 SIMLAB - PMIPA FKIP Universitas Lampung
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>

</body>
</html>