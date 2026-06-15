<?php
session_start();
include 'config/koneksi.php';

// Menangkap input pencarian dan filter prodi
$search = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : "";
$prodi_filter = isset($_GET['prodi']) ? mysqli_real_escape_string($koneksi, $_GET['prodi']) : "";

// Menyusun Query Dinamis Berdasarkan Filter
$conditions = [];

if (!empty($search)) {
    $conditions[] = "(nama_alat LIKE '%$search%' OR deskripsi LIKE '%$search%')";
}

if (!empty($prodi_filter)) {
    $conditions[] = "prodi_pemilik = '$prodi_filter'";
}

// Gabungkan kondisi jika ada
if (count($conditions) > 0) {
    $sql = "SELECT * FROM alat WHERE " . implode(' AND ', $conditions) . " ORDER BY id_alat DESC";
} else {
    $sql = "SELECT * FROM alat ORDER BY id_alat DESC";
}

$query = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Alat - SIMLAB PMIPA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <style>
        /* CSS bawaan kamu tetap di sini ya... */
        :root { --primary: #556B2F; --accent: #6b8e23; --soft-krem: #fdfaf1; --dark: #2d2d2d; }
        body { font-family: 'Poppins', sans-serif; margin: 0; background-color: var(--soft-krem); color: var(--dark); overflow-x: hidden; }
        .navbar { position: fixed; top: 0; width: 100%; height: 85px; display: flex; align-items: center; justify-content: space-between; padding: 0 8%; box-sizing: border-box; z-index: 1000; transition: 0.4s; }
        .navbar.sticky { background: white; height: 75px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .navbar-brand { display: flex; align-items: center; gap: 15px; text-decoration: none; color: white; transition: 0.3s; }
        .navbar.sticky .navbar-brand { color: var(--primary); }
        .navbar-brand span { font-weight: 800; font-size: 1.3rem; letter-spacing: 1px; }
        .navbar-menu { display: flex; gap: 35px; list-style: none; align-items: center; margin: 0; padding: 0; }
        .navbar-menu a { text-decoration: none; color: white; font-weight: 600; font-size: 0.95rem; transition: 0.3s; }
        .navbar.sticky .navbar-menu a { color: var(--dark); }
        .navbar-menu a:hover, .navbar-menu a.active { color: var(--accent) !important; }
        .navbar.sticky .navbar-menu a.active { color: var(--primary) !important; }
        .btn-login-nav { background: var(--accent); color: white !important; padding: 10px 25px; border-radius: 50px; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .header-section { height: 450px; background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/img/lab_bg.jpg'); background-size: cover; background-position: center; background-attachment: fixed; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white; }
        .header-section h1 { font-size: 3.5rem; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
        .header-section p { font-size: 1.2rem; opacity: 0.8; margin-top: 15px; max-width: 700px; line-height: 1.6; }
        .search-wrapper { margin-top: -45px; display: flex; flex-direction: column; align-items: center; padding: 0 20px; gap: 15px; }
        .search-bar { background: white; width: 100%; max-width: 850px; padding: 15px 35px; border-radius: 100px; display: flex; align-items: center; box-shadow: 0 15px 40px rgba(0,0,0,0.1); border: 1px solid #eee; box-sizing: border-box; }
        .search-bar input { flex: 1; border: none; outline: none; font-size: 1rem; font-family: inherit; }
        .search-bar button { background: var(--primary); color: white; border: none; padding: 12px 35px; border-radius: 50px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .search-bar button:hover { background: var(--accent); transform: scale(1.05); }
        .content-container { padding: 40px 8% 120px; }
        .grid-alat { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 40px; }
        .card-alat { background: white; border-radius: 30px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.03); border: 1px solid #f0f0f0; transition: 0.5s; display: flex; flex-direction: column; }
        .card-alat:hover { transform: translateY(-15px); box-shadow: 0 20px 45px rgba(0,0,0,0.08); border-color: var(--primary); }
        .img-box { position: relative; height: 250px; overflow: hidden; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; transition: 0.6s; }
        .card-alat:hover .img-box img { transform: scale(1.1); }
        .badge-status { position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.95); padding: 6px 18px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; color: var(--primary); z-index: 2; }
        .card-info { padding: 25px 35px 35px; flex-grow: 1; display: flex; flex-direction: column; }
        .card-info h3 { margin: 0 0 10px; color: var(--dark); font-size: 1.5rem; font-weight: 700; }
        .card-info p { font-size: 0.95rem; color: #777; line-height: 1.7; margin-bottom: 25px; }
        .stok-wrapper { padding: 0 35px; margin-top: 25px; font-size: 0.85rem; }
        .btn-action { margin-top: auto; background: var(--primary); color: white; text-align: center; text-decoration: none; padding: 16px; border-radius: 20px; font-weight: 800; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; border: none; font-size: 0.95rem; }
        .btn-action:hover { background: var(--accent); box-shadow: 0 10px 20px rgba(107, 142, 35, 0.3); cursor: pointer; }
        .btn-disabled { margin-top: auto; background: #ddd !important; color: #999 !important; text-align: center; padding: 16px; border-radius: 20px; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 10px; border: none; font-size: 0.95rem; cursor: not-allowed; box-shadow: none !important; }
        footer { background: #1a1a1a; color: #ccc; padding: 80px 10% 40px; border-top: 5px solid var(--primary); margin-top: 50px; }
        .footer-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 60px; text-align: left; }
        .footer-info h4 { color: white; margin-bottom: 25px; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; }
        .footer-info p { line-height: 1.8; font-size: 0.95rem; margin: 5px 0; }
        .footer-bottom { text-align: center; margin-top: 60px; padding-top: 30px; border-top: 1px solid #333; font-size: 0.85rem; opacity: 0.6; }

        /* 🌟 CSS BARU: Tombol Filter Prodi PMIPA */
        .prodi-filter-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-bottom: 20px; width: 100%; max-width: 850px; }
        .prodi-badge-btn { text-decoration: none; padding: 8px 20px; border-radius: 50px; font-size: 0.85rem; font-weight: bold; background: white; color: var(--primary); border: 2px solid var(--primary); transition: 0.3s; }
        .prodi-badge-btn.active, .prodi-badge-btn:hover { background: var(--primary); color: white; }
    </style>
</head>
<body>

    <nav class="navbar" id="navbar">
        <a href="index.php" class="navbar-brand">
            <img src="assets/img/logo/logo_unila.png" width="45">
            <img src="assets/img/logo/logo_simlabnew.png" width="45" alt="Logo SIMLAB">
            <span style="margin-left: 5px;">SIMLAB PMIPA</span>
        </a>
        <div class="navbar-menu">
            <a href="index.php">Beranda</a>
            <a href="katalog.php" class="active">Katalog Alat</a>
            <a href="panduan.php">Panduan</a>
            <?php if(isset($_SESSION['login'])): ?>
                <a href="riwayat.php">Riwayat Saya</a>
                <a href="logout.php" style="color: #d9534f;"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-login-nav">Login</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="header-section">
        <h1 data-aos="zoom-out">Katalog Inventaris</h1>
        <p data-aos="fade-up">Eksplorasi berbagai perangkat laboratorium untuk mendukung kegiatan riset dan praktikum anda.</p>
    </header>

    <div class="search-wrapper">
        <form action="" method="GET" class="search-bar" data-aos="fade-up">
            <i class="fas fa-search" style="color: #ccc; margin-right: 15px;"></i>
            <input type="text" name="cari" placeholder="Cari nama alat..." value="<?php echo htmlspecialchars($search); ?>">
            <?php if(!empty($prodi_filter)): ?>
                <input type="hidden" name="prodi" value="<?php echo htmlspecialchars($prodi_filter); ?>">
            <?php endif; ?>
            <button type="submit">Cari Alat</button>
        </form>

        <div class="prodi-filter-container" data-aos="fade-up">
            <a href="katalog.php?cari=<?php echo urlencode($search); ?>" class="prodi-badge-btn <?php echo empty($prodi_filter) ? 'active' : ''; ?>">Semua PMIPA</a>
            <a href="katalog.php?prodi=PTI&cari=<?php echo urlencode($search); ?>" class="prodi-badge-btn <?php echo $prodi_filter == 'PTI' ? 'active' : ''; ?>">PTI</a>
            <a href="katalog.php?prodi=PMTK&cari=<?php echo urlencode($search); ?>" class="prodi-badge-btn <?php echo $prodi_filter == 'PMTK' ? 'active' : ''; ?>">Matematika</a>
            <a href="katalog.php?prodi=PBIO&cari=<?php echo urlencode($search); ?>" class="prodi-badge-btn <?php echo $prodi_filter == 'PBIO' ? 'active' : ''; ?>">Biologi</a>
            <a href="katalog.php?prodi=PFIS&cari=<?php echo urlencode($search); ?>" class="prodi-badge-btn <?php echo $prodi_filter == 'PFIS' ? 'active' : ''; ?>">Fisika</a>
            <a href="katalog.php?prodi=PKIM&cari=<?php echo urlencode($search); ?>" class="prodi-badge-btn <?php echo $prodi_filter == 'PKIM' ? 'active' : ''; ?>">Kimia</a>
        </div>
    </div>

    <main class="content-container">
        <div class="grid-alat">
            <?php 
            if (mysqli_num_rows($query) > 0) {
                while ($row = mysqli_fetch_assoc($query)) { 
                    $deskripsi = !empty($row['deskripsi']) ? $row['deskripsi'] : "Instrumen laboratorium esensial Pendidikan MIPA Universitas Lampung.";
            ?>
                <div class="card-alat" data-aos="fade-up">
                    <div class="img-box">
                        <?php if ($row['stok'] > 0) { ?>
                            <span class="badge-status" style="color: var(--primary);">Tersedia</span>
                        <?php } else { ?>
                            <span class="badge-status" style="color: #c62828; background: #ffebee;">Habis</span>
                        <?php } ?>
                        <img src="assets/img/alat/<?php echo $row['foto_alat']; ?>" onerror="this.src='https://via.placeholder.com/400x300?text=SIMLAB+PMIPA'">
                    </div>

                    <div class="stok-wrapper">
                        <?php if ($row['stok'] > 0) { ?>
                            <span style="background: #e8f5e9; color: #2e7d32; padding: 6px 14px; border-radius: 50px; font-weight: bold; display: inline-block;">
                                <i class="fas fa-check-circle"></i> Tersedia: <?php echo $row['stok']; ?> Unit
                            </span>
                        <?php } else { ?>
                            <span style="background: #ffebee; color: #c62828; padding: 6px 14px; border-radius: 50px; font-weight: bold; display: inline-block;">
                                <i class="fas fa-times-circle"></i> Stok Habis di Rak Lab
                            </span>
                        <?php } ?>
                    </div>
                    
                    <div class="card-info">
                        <h3><?php echo str_replace('_', ' ', $row['nama_alat']); ?></h3>
                        <p><?php echo (strlen($deskripsi) > 100) ? substr($deskripsi, 0, 100) . '...' : $deskripsi; ?></p>
                        
                        <?php if ($row['stok'] > 0) { ?>
                            <a href="form_pinjam.php?id=<?php echo $row['id_alat']; ?>" class="btn-action">
                                <i class="fas fa-calendar-check"></i> Ajukan Pinjam
                            </a>
                        <?php } else { ?>
                            <button disabled class="btn-disabled">
                                <i class="fas fa-ban"></i> Tidak Dapat Dipinjam
                            </button>
                        <?php } ?>
                    </div>
                </div>
            <?php 
                } 
            } else { ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 100px 0;">
                    <i class="fas fa-search" style="font-size: 4rem; color: #ddd; margin-bottom: 20px;"></i>
                    <h2 style="color: #ccc;">Alat "<?php echo htmlspecialchars($search); ?>" Tidak Ditemukan</h2>
                </div>
            <?php } ?>
        </div>
    </main>

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

        // LOGIKA STICKY NAVBAR
        const navbar = document.getElementById("navbar");
        window.onscroll = () => {
            if (window.scrollY > 100) {
                navbar.classList.add("sticky");
            } else {
                navbar.classList.remove("sticky");
            }
        };
    </script>
</body>
</html>