<?php
session_start();
include 'config/koneksi.php';

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
        }

        /* NAVBAR ATAS (Sama dengan Panduan & Index) */
        .navbar {
            background: white;
            padding: 15px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--primary); font-weight: 800; }
        .navbar-menu { display: flex; gap: 30px; align-items: center; }
        .navbar-menu a { text-decoration: none; color: var(--dark); font-weight: 600; font-size: 0.95rem; transition: 0.3s; }
        .navbar-menu a:hover, .navbar-menu a.active { color: var(--primary); }

        /* HEADER & CONTAINER */
        .header-history {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/img/lab_bg.jpg');
            background-size: cover; background-position: center;
            height: 250px; display: flex; flex-direction: column;
            justify-content: center; align-items: center; color: white; text-align: center;
        }

        .container { padding: 0 8% 60px; margin-top: -50px; }
        
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
    </style>
</head>
<body>

    <!-- NAVBAR ATAS -->
    <nav class="navbar">
        <a href="index.php" class="navbar-brand">
            <img src="assets/img/logo/logo_unila.png" width="40">
            <img src="assets/img/logo/logo_simlabnew.png" width="42" alt="Logo SIMLAB">
            <span style="margin-left: 5px;">SIMLAB PMIPA</span>
        </a>
        <div class="navbar-menu">
            <a href="index.php">Beranda</a>
            <a href="katalog.php">Katalog Alat</a>
            <a href="panduan.php">Panduan</a>
            <a href="riwayat.php" class="active">Riwayat Saya</a>
            <a href="logout.php" style="color: #e74c3c;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
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
                        
                        // Penentuan Class & Ikon
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
    <script>AOS.init();</script>
</body>
</html>