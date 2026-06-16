<?php
session_start();
include 'config/koneksi.php';

// Proteksi: Kalau belum login, tendang balik ke login.php
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$npm_saya = $_SESSION['npm'];

// Hitung statistik kecil buat dashboard mahasiswa
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM peminjaman WHERE npm = '$npm_saya'");
$d_total = mysqli_fetch_assoc($q_total);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Saya - SIMLAB PMIPA</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #556B2F;
            --accent: #6b8e23;
            --soft-krem: #fdfaf1;
            --dark: #4a4a4a;
            --blue-kembali: #3498db;
        }

        body { 
            font-family: 'Segoe UI', sans-serif; 
            margin: 0; background-color: var(--soft-krem); 
            color: var(--dark); 
            overflow-x: hidden;
            width: 100%;
        }

        /* --- NAVBAR MASTER FIXED (SINKRON 100% DENGAN INDEX/KATALOG) --- */
        nav {
            position: fixed; top: 0; width: 100%; height: 85px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 8%; box-sizing: border-box; transition: 0.4s ease; z-index: 99999;
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        
        .navbar-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--primary); font-weight: 800; }
        
        .nav-links { display: flex; gap: 30px; list-style: none; align-items: center; margin: 0; padding: 0; }
        .nav-links a { text-decoration: none; color: var(--dark); font-weight: 600; font-size: 0.95rem; transition: 0.3s; }
        .nav-links a:hover, .nav-links a.active { color: var(--primary); }

        /* Sembunyikan tombol hamburger di mode laptop */
        .menu-toggle { display: none; }

        /* HEADER & CONTAINER */
        .header-history {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/img/lab_bg.jpg');
            background-size: cover; background-position: center;
            height: 250px; display: flex; flex-direction: column;
            justify-content: center; align-items: center; color: white; text-align: center;
        }

        .container { padding: 0 8% 60px; margin-top: -50px; width: 100%; box-sizing: border-box; }
        
        .summary-wrapper {
            display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap;
        }
        .summary-mini {
            background: white; padding: 20px; border-radius: 20px;
            flex: 1; min-width: 200px; display: flex; align-items: center; gap: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        .summary-mini i { font-size: 1.5rem; color: var(--primary); background: var(--soft-krem); padding: 15px; border-radius: 15px; }

        /* TABLE STYLE */
        .history-card {
            background: white; border-radius: 30px; padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05); border: 1px solid #eee;
            box-sizing: border-box;
        }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 20px; color: var(--primary); font-size: 0.8rem; text-transform: uppercase; border-bottom: 2px solid var(--soft-krem); }
        td { padding: 20px; border-bottom: 1px solid #f9f9f9; }

        .alat-img { width: 60px; height: 60px; border-radius: 12px; object-fit: cover; }

        /* BADGE STATUS */
        .badge { padding: 8px 16px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; text-transform: uppercase; }
        .st-menunggu { background: #fff8e1; color: #f39c12; }
        .st-disetujui { background: #e8f8f5; color: #27ae60; }
        .st-ditolak { background: #fdeded; color: #e74c3c; }
        .st-kembali { background: #e3f2fd; color: var(--blue-kembali); border: 1px solid #c7d2fe; }

        .btn-detail { color: var(--primary); font-weight: bold; text-decoration: none; font-size: 0.8rem; border: 2px solid var(--primary); padding: 8px 18px; border-radius: 10px; transition: 0.3s; }
        .btn-detail:hover { background: var(--primary); color: white; }

        footer { 
            background: #1a1a1a; 
            color: #ccc; 
            padding: 80px 10% 40px; 
            border-top: 5px solid var(--primary);
            margin-top: 50px;
        }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 60px; text-align: left; }
        .footer-info h4 { color: white; margin-bottom: 25px; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; }
        .footer-info p { line-height: 1.8; font-size: 0.95rem; margin: 5px 0; }
        .footer-bottom { 
            text-align: center; margin-top: 60px; padding-top: 30px; 
            border-top: 1px solid #333; font-size: 0.85rem; opacity: 0.6; 
        }

        /* 📱 JINAKKAN NAVIGATION DROPDOWN & RESPONSIVE TABLE DI LAYAR HP */
        @media screen and (max-width: 768px) {
            nav {
                padding: 0 5% !important;
                height: 80px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                background: var(--primary) !important; /* Lock warna dasar hijau di HP */
            }

            .menu-toggle {
                display: block !important;
                color: white !important;
                font-size: 1.5rem;
                cursor: pointer;
                z-index: 100000;
            }

            .navbar-brand span {
                font-size: 1.1rem !important;
                color: white !important;
            }

            /* Daftar link menu bertransformasi menjadi dropdown vertikal mobile */
            .nav-links {
                position: absolute !important;
                top: 80px;
                left: -100%; /* Sembunyi awal di luar layar kiri */
                width: 100% !important;
                background: rgba(85, 107, 47, 0.98) !important;
                flex-direction: column !important;
                gap: 0 !important;
                padding: 15px 0 !important;
                transition: 0.4s ease-in-out;
                box-shadow: 0 10px 15px rgba(0,0,0,0.1);
            }

            .nav-links li {
                width: 100%;
                text-align: center;
            }

            .nav-links a {
                display: block;
                padding: 15px 0 !important;
                font-size: 1rem !important;
                color: white !important;
                border-bottom: 1px solid rgba(255,255,255,0.08);
            }

            /* Pemicu geser masuk layar dari JavaScript */
            .nav-links.active {
                left: 0 !important;
            }

            .nav-links a[href="logout.php"] {
                color: #ff8784 !important;
                background: transparent !important;
                box-shadow: none !important;
            }

            /* --- RESIZE KONTEN UTAMA DI HP --- */
            .container { padding: 0 15px 40px !important; }
            .summary-mini { padding: 15px; gap: 10px; }
            .summary-mini i { padding: 10px; font-size: 1.2rem; }
            .history-card { padding: 20px 15px !important; border-radius: 20px; }

            /* 🌟 KUNCI EMAS: Wrapper pembungkus tabel agar bisa di-scroll horizontal tanpa jebol */
            .table-responsive-wrapper {
                width: 100% !important;
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch; /* Momentum scrolling halus di smartphone */
                margin-top: 10px;
            }
            
            table {
                min-width: 650px !important; /* Paksa lebar minimal tabel agar isi kolom tidak berdesakan */
            }
            
            th, td { padding: 12px 10px !important; font-size: 0.85rem !important; }
            .alat-img { width: 45px; height: 45px; border-radius: 8px; }
            .btn-detail { padding: 6px 12px !important; font-size: 0.75rem !important; }
        }
    </style>
</head>
<body>

    <nav id="navbar">
        <a href="index.php" class="navbar-brand">
            <img src="assets/img/logo/logo_unila.png" width="40">
            <img src="assets/img/logo/logo_simlabnew.png" width="42" alt="Logo SIMLAB">
            <span style="margin-left: 5px;">SIMLAB PMIPA</span>
        </a>
        
        <div class="menu-toggle" id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>

        <ul class="nav-links" id="nav-list">
            <li><a href="index.php">Beranda</a></li>
            <li><a href="katalog.php">Katalog Alat</a></li>
            <li><a href="panduan.php">Panduan</a></li>
            <li><a href="riwayat.php" class="active">Riwayat Saya</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <header class="header-history">
        <h1 data-aos="zoom-in">Aktivitas Peminjaman</h1>
        <p data-aos="fade-up">Pantau status alat yang kamu pinjam di sini</p>
    </header>

    <div class="container">
        <div class="summary-wrapper" data-aos="fade-up">
            <div class="summary-mini">
                <i class="fas fa-clipboard-check"></i>
                <div>
                    <span style="display:block; font-size:1.2rem; font-weight:800; color:var(--primary);"><?php echo $d_total['jml']; ?></span>
                    <small>Total Pengajuan</small>
                </div>
            </div>
            <div class="summary-mini">
                <i class="fas fa-user"></i>
                <div>
                    <span style="display:block; font-size:1rem; font-weight:700;"><?php echo $_SESSION['nama']; ?></span>
                    <small><?php echo $npm_saya; ?></small>
                </div>
            </div>
        </div>

        <div class="history-card" data-aos="fade-up">
            <div class="table-responsive-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Alat</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT peminjaman.*, alat.nama_alat, alat.foto_alat 
                                FROM peminjaman 
                                JOIN alat ON peminjaman.id_alat = alat.id_alat 
                                WHERE peminjaman.npm = '$npm_saya' 
                                ORDER BY id_pinjam DESC";
                        $query = mysqli_query($koneksi, $sql);
                        
                        while ($data = mysqli_fetch_array($query)) {
                            $st = trim(strtolower($data['status']));
                            
                            // Penentuan Class & Ikon status sirkulasi database
                            if ($st == 'menunggu') { $c = "st-menunggu"; $i = "fa-clock"; $t = "MENUNGGU"; }
                            elseif ($st == 'disetujui') { $c = "st-disetujui"; $i = "fa-check-circle"; $t = "DISETUJUI"; }
                            elseif ($st == 'kembali') { $c = "st-kembali"; $i = "fa-check-double"; $t = "DIKEMBALIKAN"; }
                            elseif ($st == 'ditolak') { $c = "st-ditolak"; $i = "fa-times-circle"; $t = "DITOLAK"; }
                            else { $c = "st-unknown"; $i = "fa-question-circle"; $t = "PENDING"; }
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:15px;">
                                    <img src="assets/img/alat/<?php echo $data['foto_alat']; ?>" onerror="this.src='https://via.placeholder.com/60'" class="alat-img">
                                    <strong><?php echo str_replace('_', ' ', $data['nama_alat']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo date('d M Y', strtotime($data['tgl_pinjam'])); ?></td>
                            <td>
                                <span class="badge <?php echo $c; ?>">
                                    <i class="fas <?php echo $i; ?>"></i> <?php echo $t; ?>
                                </span>
                            </td>
                            <td>
                                <a href="detail_riwayat.php?id=<?php echo $data['id_pinjam']; ?>" class="btn-detail">DETAIL</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 1000, once: true });

        // 🌟 JAVASCRIPT BUKA-TUTUP DROPDOWN MOBILE MENU
        const mobileMenu = document.getElementById('mobile-menu');
        const navList = document.getElementById('nav-list');

        mobileMenu.addEventListener('click', () => {
            navList.classList.toggle('active');
            
            // Animasi icon bars ke silang (X)
            const icon = mobileMenu.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });
    </script>
</body>
</html>