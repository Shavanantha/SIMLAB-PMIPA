<?php
session_start(); // Wajib paling atas
include 'config/koneksi.php';

// Statistik asli dari database kamu
$q_alat = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM alat");
$d_alat = mysqli_fetch_assoc($q_alat);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMLAB PMIPA - Digital Portal</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --earthy-green: #556B2F;
            --soft-krem: #fdfaf1;
            --dark-text: #4a4a4a;
            --accent-green: #6b8e23;
            --danger: #d9534f;
        }

        body, html { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; padding: 0;
            scroll-behavior: smooth;
            background-color: white;
            color: var(--dark-text);
        }

        /* --- NAVBAR REVISI (UKURAN SINKRON 85px/75px) --- */
        nav {
            position: fixed; top: 0; width: 100%; height: 85px; /* Standar tinggi awal */
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 8%; box-sizing: border-box; transition: 0.4s ease; z-index: 99999;
        }
        nav.sticky { 
            background: white; height: 75px; /* Standar tinggi scroll */
            box-shadow: 0 5px 25px rgba(0,0,0,0.1); 
        }
        
        .logo-box { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .logo-box span { font-weight: 800; font-size: 1.4rem; color: white; letter-spacing: 1px; transition: 0.3s; }
        nav.sticky .logo-box span { color: var(--earthy-green); }

        .nav-links { display: flex; gap: 30px; list-style: none; align-items: center; margin: 0; }
        .nav-links a { text-decoration: none; color: white; font-weight: 600; font-size: 0.95rem; transition: 0.3s; }
        nav.sticky .nav-links a { color: var(--dark-text); }
        .nav-links a:hover, .nav-links a.active { color: var(--accent-green) !important; }
        nav.sticky .nav-links a.active { color: var(--earthy-green) !important; }

        /* STANDAR TOMBOL LOGOUT (MERAH KONSISTEN) */
        .btn-nav-logout { 
            background: var(--danger); color: white !important; 
            padding: 10px 25px; border-radius: 50px; 
            font-weight: 700; transition: 0.3s;
            box-shadow: 0 4px 10px rgba(217, 83, 79, 0.3);
            font-size: 0.9rem;
        }
        .btn-nav-logout:hover { background: #c9302c; transform: scale(1.05); }
        .btn-nav-login { background: var(--accent-green); color: white !important; padding: 10px 25px; border-radius: 50px; font-size: 0.9rem; }

        /* --- HERO SECTION --- */
        .hero { 
            height: 100vh; 
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/img/lab_bg.jpg'); 
            background-size: cover; background-position: center; background-attachment: fixed;
            display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white;
        }
        .hero h1 { font-size: 3.8rem; font-weight: 800; margin: 0; text-shadow: 2px 2px 10px rgba(0,0,0,0.3); }
        .hero p { font-size: 1.3rem; margin: 20px 0 40px; opacity: 0.9; max-width: 700px; line-height: 1.6; }
        .btn-check { 
            background: var(--accent-green); color: white; padding: 18px 40px; border-radius: 50px; 
            text-decoration: none; font-weight: bold; transition: 0.4s; border: 2px solid var(--accent-green);
        }
        .btn-check:hover { background: transparent; border-color: white; transform: scale(1.05); }

        /* --- SECTION STATS --- */
        .section { padding: 100px 10%; position: relative; }
        .section-title { text-align: center; margin-bottom: 70px; }
        .section-title h2 { color: var(--earthy-green); font-size: 2.5rem; margin-bottom: 15px; font-weight: 800; }
        .section-title p { color: #888; font-size: 1.1rem; }

        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 35px; }
        .stat-card { 
            background: var(--soft-krem); padding: 50px 30px; border-radius: 30px; 
            text-align: center; transition: 0.5s; border: 1px solid transparent;
        }
        .stat-card:hover { transform: translateY(-15px); background: white; box-shadow: 0 20px 40px rgba(0,0,0,0.06); border-color: var(--earthy-green); }
        .stat-card i { font-size: 3.5rem; color: var(--earthy-green); margin-bottom: 20px; display: block; }
        .stat-card .number { font-size: 3.5rem; font-weight: 800; color: #333; margin: 10px 0; }

        /* --- INFO SECTION --- */
        .info-container { display: grid; grid-template-columns: 1.6fr 1fr; gap: 40px; margin-top: 40px; }
        .info-card { background: white; padding: 45px; border-radius: 25px; border: 1px solid #eee; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
        .info-card h3 { color: var(--earthy-green); font-size: 1.5rem; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; }
        .info-card ul { padding-left: 20px; color: #666; line-height: 2.2; }

        /* --- FAQ --- */
        .faq-wrapper { max-width: 900px; margin: 0 auto; }
        .faq-item { background: #fafafa; margin-bottom: 15px; border-radius: 15px; transition: 0.3s; border: 1px solid #eee; }
        .faq-header { padding: 25px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: 600; color: var(--dark-text); }
        .faq-body { padding: 0 25px; max-height: 0; overflow: hidden; transition: 0.3s ease; color: #777; line-height: 1.7; }
        .faq-item.active { background: white; box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-color: var(--accent-green); }
        .faq-item.active .faq-body { padding-bottom: 25px; max-height: 300px; }

        #backToTop {
            position: fixed; bottom: 35px; right: 35px; width: 55px; height: 55px;
            background: var(--earthy-green); color: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; opacity: 0; visibility: hidden; transition: 0.4s; z-index: 2000;
        }
        #backToTop.show { opacity: 1; visibility: visible; }

        footer { background: #1a1a1a; color: #ccc; padding: 80px 10% 40px; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 60px; }
    </style>
</head>
<body>

    <div id="backToTop"><i class="fas fa-arrow-up"></i></div>

    <nav id="navbar">
        <a href="index.php" class="logo-box">
            <img src="assets/img/logo/logo_unila.png" width="45" alt="Unila">
            <img src="assets/img/logo/logo_simlabnew.png" width="45" alt="Logo SIMLAB">
            <span style="margin-left: 5px;">SIMLAB PMIPA</span>
        </a>
        <ul class="nav-links">
            <li><a href="index.php" class="active">Beranda</a></li>
            <li><a href="katalog.php">Katalog Alat</a></li>
            <li><a href="panduan.php">Panduan</a></li>
            <?php if(isset($_SESSION['login'])): ?>
                <!-- LINK SUDAH DIPASTIKAN KE riwayat.php -->
                <li><a href="riwayat.php">Riwayat Saya</a></li>
                <li><a href="logout.php" class="btn-nav-logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="login.php" class="btn-nav-login">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <section class="hero">
        <div data-aos="fade-up">
            <?php if(isset($_SESSION['login'])): ?>
                <h1>Hai, <?php echo $_SESSION['nama']; ?>! 👋</h1>
            <?php else: ?>
                <h1>Digital Laboratorium PMIPA</h1>
            <?php endif; ?>
            <p>Sistem Peminjaman Alat Laboratorium Terintegrasi Pendidikan MIPA <br>Fakultas Keguruan dan Ilmu Pendidikan, Universitas Lampung</p>
            <a href="katalog.php" class="btn-check">Cek Ketersediaan Alat <i class="fas fa-chevron-right" style="margin-left:10px;"></i></a>
        </div>
    </section>

    <section class="section">
        <div class="section-title" data-aos="fade-up">
            <h2>Inventaris & Status</h2>
            <p>Data terbaru ketersediaan alat di Laboratorium Pendidikan MIPA</p>
        </div>
        <div class="grid-stats">
            <div class="stat-card" data-aos="zoom-in">
                <i class="fas fa-microscope"></i>
                <h3>Total Alat Tersedia</h3>
                <div class="number"><?php echo $d_alat['total']; ?></div>
                <p>Item Terdaftar</p>
            </div>
            <div class="stat-card" data-aos="zoom-in" data-aos-delay="100">
                <i class="fas fa-flask"></i>
                <h3>Kategori Prodi</h3>
                <div class="number">5</div>
                <p>Biologi, Fisika, Kimia, Matematika, Teknologi Informasi</p>
            </div>
            <div class="stat-card" data-aos="zoom-in" data-aos-delay="200">
                <i class="fas fa-check-circle" style="color: #2ecc71;"></i>
                <h3>Status Server</h3>
                <div class="number" style="color: #2ecc71; font-size: 2.5rem;">ONLINE</div>
                <p>Sistem Aktif 24 Jam</p>
            </div>
        </div>
    </section>

    <section class="section" style="background: #fafafa;">
        <div class="info-container">
            <div class="info-card" data-aos="fade-right">
                <h3><i class="fas fa-bullhorn"></i> Pengumuman Terbaru</h3>
                <p>Informasi penting seputar penggunaan fasilitas laboratorium:</p>
                <ul>
                    <li>Batas maksimal peminjaman alat adalah 3 hari kerja.</li>
                    <li>Wajib menyerahkan KTM asli saat pengambilan alat di Lab.</li>
                    <li>Segala bentuk kerusakan alat menjadi tanggung jawab penuh peminjam.</li>
                    <li>Pastikan alat dikembalikan dalam keadaan bersih dan rapi.</li>
                </ul>
            </div>
            <div class="info-card" data-aos="fade-left" style="border-top: 6px solid #f39c12;">
                <h3><i class="fas fa-clock"></i> Jam Layanan Lab</h3>
                <table style="width: 100%; border-collapse: collapse; color: #666;">
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px 0;">Senin - Kamis</td>
                        <td style="text-align: right; font-weight: bold;">08:00 - 15:30</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 15px 0;">Jumat</td>
                        <td style="text-align: right; font-weight: bold;">08:00 - 16:00</td>
                    </tr>
                    <tr>
                        <td style="padding: 15px 0; color: #d9534f; font-weight: bold;">Sabtu - Minggu</td>
                        <td style="text-align: right; color: #d9534f; font-weight: bold;">TUTUP</td>
                    </tr>
                </table>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-title" data-aos="fade-up">
            <h2>Pertanyaan Mahasiswa</h2>
            <p>Hal-hal yang sering ditanyakan mengenai peminjaman alat</p>
        </div>
        <div class="faq-wrapper">
            <div class="faq-item" data-aos="fade-up">
                <div class="faq-header">Bagaimana jika saya terlambat mengembalikan alat? <i class="fas fa-plus"></i></div>
                <div class="faq-body">Keterlambatan akan dikenakan sanksi berupa pembekuan akun pinjam sementara sesuai kebijakan laboran.</div>
            </div>
            <div class="faq-item" data-aos="fade-up">
                <div class="faq-header">Apakah bisa meminjam alat untuk keperluan lomba/kegiatan luar? <i class="fas fa-plus"></i></div>
                <div class="faq-body">Bisa, selama melampirkan surat tugas/izin resmi dari pimpinan fakultas atau prodi.</div>
            </div>
        </div>
    </section>

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
        AOS.init({ duration: 1000, once: true });
        const navbar = document.getElementById("navbar");
        const btt = document.getElementById("backToTop");

        window.onscroll = () => {
            if (window.scrollY > 150) {
                navbar.classList.add("sticky");
                btt.classList.add("show");
            } else {
                navbar.classList.remove("sticky");
                btt.classList.remove("show");
            }
        };

        btt.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });

        document.querySelectorAll('.faq-header').forEach(header => {
            header.onclick = () => {
                const item = header.parentElement;
                item.classList.toggle('active');
                const icon = header.querySelector('i');
                icon.classList.toggle('fa-plus');
                icon.classList.toggle('fa-minus');
            };
        });
    </script>
</body>
</html>