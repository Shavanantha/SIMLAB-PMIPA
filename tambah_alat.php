<?php
session_start();
include 'config/koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Logika Simpan Data
if (isset($_POST['simpan'])) {
    $nama_alat     = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $prodi_pemilik = mysqli_real_escape_string($koneksi, $_POST['prodi_pemilik']);
    $deskripsi     = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    
    // Logika Upload Foto
    $foto_name = $_FILES['foto']['name'];
    $foto_tmp  = $_FILES['foto']['tmp_name'];
    $ekstensi  = pathinfo($foto_name, PATHINFO_EXTENSION);
    $foto_baru = time() . "_" . $nama_alat . "." . $ekstensi;
    $path      = "assets/img/alat/" . $foto_baru;

    if (move_uploaded_file($foto_tmp, $path)) {
        $query = "INSERT INTO alat (nama_alat, prodi_pemilik, deskripsi, foto_alat) 
                  VALUES ('$nama_alat', '$prodi_pemilik', '$deskripsi', '$foto_baru')";
        
        if (mysqli_query($koneksi, $query)) {
            echo "<script>alert('Alat berhasil ditambahkan!'); window.location='admin_alat.php';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan ke database!');</script>";
        }
    } else {
        echo "<script>alert('Gagal upload foto!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Alat - SIMLAB PMIPA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root { 
            --primary: #556B2F; 
            --accent: #6b8e23;
            --bg: #fdfaf1; 
            --text-dark: #333333;
        }
        
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: var(--bg); 
            margin: 0; 
            display: flex; 
            width: 100%;
            overflow-x: hidden;
        }
        
        /* --- SIDEBAR MASTER LAYOUT ADMIN (DESKTOP LAPTOP) --- */
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
        .logo-header img.unila { width: 55px; height: auto; }
        .logo-header img.simlab { width: 50px; height: auto; }

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
        .sidebar a i { width: 20px; text-align: center; }
        .sidebar a:hover { background: #f5f5f5; color: var(--primary); }
        .sidebar a.active { 
            background: var(--primary); 
            color: white; 
            box-shadow: 0 4px 10px rgba(85, 107, 47, 0.3);
        }

        /* --- KONTEN UTAMA SISI KANAN --- */
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

        .form-card {
            background: white; 
            width: 90%; 
            max-width: 600px;
            padding: 40px; 
            border-radius: 30px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.03);
            box-sizing: border-box;
        }
        h2 { color: var(--primary); font-weight: 800; margin-top: 0; margin-bottom: 30px; text-align: center; }
        
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 0.9rem; }
        .input-group input, .input-group select, .input-group textarea {
            width: 100%; padding: 12px 15px; border-radius: 12px;
            border: 1px solid #eee; outline: none; transition: 0.3s;
            box-sizing: border-box; font-family: inherit; font-size: 14px;
        }
        .input-group input:focus, .input-group select:focus, .input-group textarea:focus { 
            border-color: var(--primary); 
            box-shadow: 0 0 10px rgba(85,107,47,0.1); 
        }
        
        .btn-simpan {
            width: 100%; padding: 15px; background: var(--primary);
            color: white; border: none; border-radius: 15px;
            font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 1rem;
            letter-spacing: 0.5px;
        }
        .btn-simpan:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(85,107,47,0.2); }
        
        .btn-batal {
            display: block; text-align: center; margin-top: 20px;
            color: #aaa; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: 0.2s;
        }
        .btn-batal:hover { color: #d9534f; }

        /* 📱 JINAKKAN LAYOUT SIDEBAR & FORM INPUT ADMIN DI LAYAR HP */
        @media screen and (max-width: 768px) {
            body {
                flex-direction: column !important; /* Susun vertikal atas-bawah */
            }

            /* Transformasi komponen sidebar admin menjadi topbar horizontal */
            .sidebar {
                width: 100% !important;
                height: auto !important;
                position: relative !important; /* Lepas posisi fixed */
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

            /* Tata letak jajaran menu kelola internal admin di HP */
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
                display: none !important; /* Sembunyikan garis pemisah dan tombol logout atas demi space HP */
            }

            /* Lebarkan porsi isian box form utama */
            .main {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 25px 15px !important;
            }
            .form-card {
                padding: 25px 20px !important;
                border-radius: 20px !important;
            }
            h2 {
                font-size: 1.4rem !important;
                margin-bottom: 20px;
            }
            .input-group label {
                font-size: 0.8rem;
            }
            .input-group input, .input-group select, .input-group textarea {
                padding: 10px 12px !important;
                font-size: 0.85rem !important;
            }
            .btn-simpan {
                padding: 12px !important;
                font-size: 0.95rem !important;
            }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo-header">
            <img src="assets/img/logo/logo_unila.png" class="unila" alt="Logo Unila">
            <img src="assets/img/logo/logo_simlabnew.png" class="simlab" alt="Logo SIMLAB">
        </div>
        
        <div class="sidebar-menu">
            <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="admin_alat.php" class="active"><i class="fas fa-boxes"></i> Kelola Alat</a>
            <a href="admin_peminjaman.php"><i class="fas fa-exchange-alt"></i> Transaksi</a>
        </div>

        <div class="sidebar-footer">
            <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 15px;">
            <a href="logout.php" style="color: #d9534f; margin-bottom: 0;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="main">
        <div class="form-card" data-aos="zoom-in">
            <h2>Tambah Inventaris Baru</h2>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="input-group">
                    <label>Nama Alat Laboratorium</label>
                    <input type="text" name="nama_alat" placeholder="Contoh: Mikroskop Binokuler" required>
                </div>
                
                <div class="input-group">
                    <label>Prodi Pemilik Alat</label>
                    <select name="prodi_pemilik" required>
                        <option value="">-- Pilih Prodi --</option>
                        <option value="Biologi">Pendidikan Biologi</option>
                        <option value="Kimia">Pendidikan Kimia</option>
                        <option value="Fisika">Pendidikan Fisika</option>
                        <option value="PTI">Pendidikan Teknologi Informasi</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <label>Deskripsi Singkat</label>
                    <textarea name="deskripsi" rows="4" placeholder="Jelaskan spesifikasi atau kondisi alat..." required></textarea>
                </div>
                
                <div class="input-group">
                    <label>Foto Alat</label>
                    <input type="file" name="foto" accept="image/*" required>
                </div>
                
                <button type="submit" name="simpan" class="btn-simpan">SIMPAN ALAT</button>
                <a href="admin_alat.php" class="btn-batal">Batal & Kembali</a>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>