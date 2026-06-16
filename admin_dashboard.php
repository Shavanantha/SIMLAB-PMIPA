<?php
session_start();
include 'config/koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// 1. Ambil Statistik Ringkasan
$jml_alat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM alat"))['total'];
$jml_pinjam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman"))['total'];
$jml_user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='mahasiswa'"))['total'];
$perlu_acc = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status='menunggu'"))['total'];

// 2. Ambil Data untuk Grafik (Distribusi Alat per Prodi)
$label_prodi = [];
$data_prodi  = [];
$query_grafik = mysqli_query($koneksi, "SELECT prodi_pemilik, COUNT(*) as jumlah FROM alat GROUP BY prodi_pemilik");
while($row = mysqli_fetch_assoc($query_grafik)){
    $label_prodi[] = $row['prodi_pemilik'];
    $data_prodi[]  = $row['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SIMLAB PMIPA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root { --primary: #556B2F; --accent: #6b8e23; --bg: #f8f9fa; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg); width: 100%; overflow-x: hidden; }
        
        /* --- SIDEBAR MASTER LAPTOP --- */
        .sidebar { width: 270px; height: 100vh; background: var(--primary); color: white; position: fixed; padding: 40px 20px; box-sizing: border-box; z-index: 1000; }
        .sidebar a { display: flex; align-items: center; gap: 15px; color: rgba(255,255,255,0.6); text-decoration: none; padding: 15px; border-radius: 15px; margin-bottom: 10px; transition: 0.3s; font-weight: 600; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; }

        /* --- CONTENT AREA --- */
        .content { margin-left: 270px; flex: 1; padding: 50px; box-sizing: border-box; width: calc(100% - 270px); }
        .header-welcome { margin-bottom: 40px; }
        .header-welcome h1 { color: var(--primary); margin: 0; font-weight: 800; }

        /* Grid Statistik */
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card-stat { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 20px; }
        .card-stat i { font-size: 1.8rem; color: var(--primary); background: #f1f3ee; padding: 15px; border-radius: 15px; }
        .card-stat h3 { margin: 0; font-size: 1.5rem; }
        .card-stat p { margin: 0; color: #aaa; font-size: 0.75rem; text-transform: uppercase; }

        /* Area Grafik */
        .chart-container { background: white; padding: 30px; border-radius: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.03); max-width: 500px; width: 100%; box-sizing: border-box; }
        
        /* 📱 REVISI LAYOUT ADMIN DASHBOARD KETIKA DI-INSPECT MODE HP */
        @media screen and (max-width: 768px) {
            body {
                flex-direction: column !important; /* Susun komponen dari atas ke bawah */
            }

            /* Transformasi struktur bodi sidebar menempel kiri jadi bar menu atas */
            .sidebar {
                width: 100% !important;
                height: auto !important;
                position: relative !important; /* Copot kuncian status fixed */
                padding: 20px !important;
                box-sizing: border-box;
                border-right: none !important;
                border-bottom: 1px solid #eee;
                display: flex !important;
                flex-direction: column !important;
                align-items: center;
            }

            .sidebar div {
                margin-bottom: 20px !important;
            }
            .sidebar div img {
                width: 45px !important; /* Perkecil skala gambar logo PMIPA */
            }
            .sidebar h2 {
                font-size: 1rem !important;
                margin-top: 5px !important;
            }

            /* Atur jajaran link tombol administrator berbaris horizontal kesamping */
            .sidebar-nav-links {
                display: flex !important;
                flex-wrap: wrap;
                justify-content: center;
                gap: 8px;
                width: 100%;
            }

            .sidebar a {
                padding: 8px 12px !important;
                font-size: 12px !important;
                margin-bottom: 0 !important;
                flex: 1;
                min-width: 105px;
                justify-content: center;
            }

            .sidebar hr {
                display: none !important; /* Singkirkan garis pemisah */
            }

            /* 🌟 KUNCI UTAMA: Lebarkan porsi konten utama ke ukuran penuh */
            .content {
                margin-left: 0 !important; /* Hapus jeda margin 270px laptop */
                padding: 25px 15px !important;
                width: 100% !important;
                box-sizing: border-box;
            }

            .header-welcome {
                text-align: center;
                margin-bottom: 25px;
            }
            .header-welcome h1 {
                font-size: 1.6rem !important;
            }
            .header-welcome p {
                font-size: 0.9rem !important;
            }

            /* Kotak ringkasan kuantitas roboh vertikal satu kolom ke bawah */
            .grid-stats {
                grid-template-columns: 1fr !important;
                gap: 15px !important;
                margin-bottom: 30px;
            }
            .card-stat {
                padding: 20px !important;
                border-radius: 15px !important;
                gap: 15px !important;
            }
            .card-stat i {
                padding: 12px !important;
                font-size: 1.4rem !important;
            }
            .card-stat h3 {
                font-size: 1.3rem !important;
            }

            /* Sesuaikan ukuran box Chart penampang donat agar pas di layar HP */
            .chart-container {
                max-width: 100% !important;
                padding: 20px !important;
                border-radius: 20px !important;
                margin: 0 auto;
            }
            .chart-container h4 {
                font-size: 1rem !important;
                text-align: center;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div style="text-align:center; margin-bottom: 50px;">
            <img src="assets/img/logo/logo_unila.png" width="70">
            <img src="assets/img/logo/logo_simlabnew.png?v=1" width="70" alt="Logo SIMLAB">
            <h2 style="font-size: 1.2rem; margin-top:10px;">LAB PMIPA</h2>
        </div>
        <div class="sidebar-nav-links">
            <a href="admin_dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_peminjaman.php"><i class="fas fa-clipboard-check"></i> Peminjaman</a>
            <a href="admin_alat.php"><i class="fas fa-microscope"></i> Inventaris Alat</a>
            <a href="logout.php" style="color: #ffb8b8;"><i class="fas fa-power-off"></i> Keluar</a>
        </div>
    </div>

    <div class="content">
        <div class="header-welcome" data-aos="fade-down">
            <h1>Halo, Admin <?php echo explode(' ', $_SESSION['nama'])[0]; ?>! 👋</h1>
            <p style="color: #888;">Berikut adalah ringkasan sistem laboratorium hari ini.</p>
        </div>

        <div class="grid-stats">
            <div class="card-stat" data-aos="zoom-in">
                <i class="fas fa-boxes"></i>
                <div><h3><?php echo $jml_alat; ?></h3><p>Total Alat</p></div>
            </div>
            <div class="card-stat" data-aos="zoom-in" data-aos-delay="100">
                <i class="fas fa-users"></i>
                <div><h3><?php echo $jml_user; ?></h3><p>Mahasiswa</p></div>
            </div>
            <div class="card-stat" data-aos="zoom-in" data-aos-delay="200">
                <i class="fas fa-exchange-alt"></i>
                <div><h3><?php echo $jml_pinjam; ?></h3><p>Total Pinjam</p></div>
            </div>
            <div class="card-stat" data-aos="zoom-in" data-aos-delay="300" style="border: 1px solid #f39c12;">
                <i class="fas fa-exclamation-circle" style="color: #f39c12;"></i>
                <div><h3 style="color: #f39c12;"><?php echo $perlu_acc; ?></h3><p>Butuh ACC</p></div>
            </div>
        </div>

        <div class="chart-container" data-aos="fade-up">
            <h4 style="margin-top:0; color:var(--primary);">Distribusi Alat per Prodi</h4>
            <canvas id="myChart"></canvas>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        // Inisialisasi Grafik Chart.js bawaan sistem
        const ctx = document.getElementById('myChart');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($label_prodi); ?>,
                datasets: [{
                    label: 'Jumlah Alat',
                    data: <?php echo json_encode($data_prodi); ?>,
                    backgroundColor: ['#556B2F', '#6b8e23', '#a2ad91', '#2d3e15'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>