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
            overflow-x: hidden;
            width: 100%;
        }

        /* --- NAVBAR MASTER (SINKRON 100% DENGAN INDEX/KATALOG) --- */
        nav {
            position: fixed; 
            top: 0; 
            width: 100%; 
            height: 85px;
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            padding: 0 8%; 
            box-sizing: border-box; 
            transition: 0.4s ease; 
            z-index: 99999;
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
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
        .navbar-brand span { font-weight: 800; }

        .nav-links { display: flex; gap: 35px; list-style: none; align-items: center; margin: 0; padding: 0; }
        .nav-links a { text-decoration: none; color: var(--dark-text); font-weight: 600; font-size: 0.95rem; transition: 0.3s; }
        .nav-links a:hover, .nav-links a.active { color: var(--earthy-green) !important; }
        
        .btn-login { 
            background: var(--earthy-green); 
            color: white !important; 
            padding: 10px 25px; 
            border-radius: 50px; 
            box-shadow: 0 4px 10px rgba(85, 107, 47, 0.3);
            font-size: 0.9rem;
            text-decoration: none;
            font-weight: 600;
        }

        /* Sembunyikan tombol hamburger secara default di laptop */
        .menu-toggle { display: none; }

        /* --- MAIN CONTENT LAYOUT --- */
        .container { 
            padding: 140px 8% 100px; /* Dikunci agar tidak amblas tertutup navbar fixed laptop */
            max-width: 1100px;
            margin: auto;
            box-sizing: border-box;
            width: 100%;
        }
        
        .content-box { 
            background: white; 
            padding: 50px; 
            border-radius: 30px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.03); 
            border: 1px solid #f1f1f1;
            box-sizing: border-box;
            width: 100%;
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

        /* --- TIMELINE STYLE --- */
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
            box-sizing: border-box;
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
            box-sizing: border-box;
        }

        /* --- FOOTER --- */
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

        /* 📱 JINAKKAN TAMPILAN RESPONSIVE TOTAL KHUSUS DI HP */
        @media screen and (max-width: 768px) {
            nav {
                padding: 0 5% !important;
                height: 80px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                background: var(--earthy-green) !important; /* Kunci warna hijau PMIPA di HP */
            }

            /* Perbaikan posisi tombol garis tiga agar pas di dalam ruang kanan layar HP */
            .menu-toggle {
                display: block !important;
                color: white !important;
                font-size: 1.5rem;
                cursor: pointer;
                z-index: 100000;
                margin-left: auto; /* Paksa nempel presisi di kanan bar */
            }

            .navbar-brand span {
                color: white !important;
                font-size: 1.1rem !important;
            }

            /* Menu drop-down meluncur ke bawah vertikal */
            .nav-links {
                position: absolute !important;
                top: 80px;
                left: -100%; /* Sembunyi aman di kiri luar layar */
                width: 100% !important;
                background: rgba(85, 107, 47, 0.98) !important;
                flex-direction: column !important;
                gap: 0 !important;
                padding: 15px 0 !important;
                transition: 0.4s ease-in-out;
                box-shadow: 0 10px 15px rgba(0,0,0,0.1);
            }

            .nav-links li { width: 100%; text-align: center; }
            .nav-links a { display: block; padding: 15px 0 !important; color: white !important; border-bottom: 1px solid rgba(255,255,255,0.08); }
            .nav-links.active { left: 0 !important; }

            .btn-login {
                border-radius: 0 !important;
                box-shadow: none !important;
                background: transparent !important;
                padding: 15px 0 !important;
                display: block;
                width: 100%;
            }
            .nav-links a[style*="color: #d9534f;"] { color: #ff8784 !important; }

            /* 🌟 PENYELAMAT: Tambah padding atas ekstra besar di HP agar judul PROSEDUR PEMINJAMAN ALAT muncul utuh */
            .container { 
                padding: 120px 15px 60px !important; 
                width: 100% !important;
                box-sizing: border-box;
            }
            .content-box { padding: 25px 15px !important; border-radius: 20px; width: 100% !important; box-sizing: border-box; }
            
            .header-panduan { margin-bottom: 35px; padding-bottom: 15px; }
            .header-panduan h2 { font-size: 1.4rem !important; font-weight: 800 !important; display: block !important; visibility: visible !important; }
            .header-panduan p { font-size: 0.9rem !important; }

            /* Skala ulang bodi timeline agar simetris pas di layar HP */
            .timeline { padding-left: 0px !important; }
            .timeline::before { left: 14px !important; } /* Geser as garis vertikal ke kiri bodi */
            
            .step { margin-bottom: 35px !important; gap: 0; }
            .step-number { 
                width: 28px !important; 
                height: 28px !important; 
                font-size: 0.85rem !important; 
                margin-right: 15px !important; 
                border: 2px solid white !important;
            }
            .step-content { padding: 15px 15px !important; border-radius: 15px; width: calc(100% - 43px) !important; box-sizing: border-box; }
            .step-content h3 { font-size: 1rem !important; gap: 8px !important; }
            .step-content p { font-size: 0.85rem !important; line-height: 1.6; }
            
            .step:hover .step-content { transform: none !important; } /* Matikan animasi geser kanan agar tidak memicu geser layar samping */
            .alert-info { padding: 15px !important; font-size: 0.85rem !important; gap: 10px !important; flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

    <nav id="navbar">
        <a href="index.php" class="navbar-brand">
            <img src="assets/img/logo/logo_unila.png" width="42" alt="Logo Unila">
            <img src="assets/img/logo/logo_simlabnew.png" width="42" alt="Logo SIMLAB">
            <span style="margin-left: 5px;">SIMLAB PMIPA</span>
        </a>
        
        <div class="menu-toggle" id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>

        <ul class="nav-links" id="nav-list">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="katalog.php">Katalog Alat</a></li>
            <li><a href="panduan.php" class="active">Panduan</a></li>
            <?php if(isset($_SESSION['login'])): ?>
                <li><a href="riwayat.php">Riwayat Saya</a></li>
                <li><a href="logout.php" style="color: #d9534f;"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="login.php" class="btn-login">Login</a></li>
            <?php endif; ?>
        </ul>
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

            <div class="alert-info">
                <i class="fas fa-circle-info" style="color: var(--accent-green);"></i>
                <span>Status pengesahan sirkulasi dan sisa batas waktu kembali dapat Anda pantau secara <i>real-time</i> melalui menu <strong>Riwayat Saya</strong> setelah login.</span>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-grid">
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
        </div>
        <div class="footer-bottom">
            &copy; 2026 SIMLAB - PMIPA FKIP Universitas Lampung
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });

        // JAVASCRIPT PICUAN DROPDOWN PANDUAN MOBILE HP
        const mobileMenu = document.getElementById('mobile-menu');
        const navList = document.getElementById('nav-list');

        mobileMenu.addEventListener('click', () => {
            navList.classList.toggle('active');
            
            const icon = mobileMenu.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
    </script>

</body>
</html>