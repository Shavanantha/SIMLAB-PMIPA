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
        :root { --primary: #556B2F; --bg: #fdfaf1; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 50px 0; display: flex; justify-content: center; }
        
        .form-card {
            background: white; width: 90%; max-width: 600px;
            padding: 40px; border-radius: 30px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
        }
        h2 { color: var(--primary); font-weight: 800; margin-bottom: 30px; text-align: center; }
        
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 0.9rem; }
        .input-group input, .input-group select, .input-group textarea {
            width: 100%; padding: 12px 15px; border-radius: 12px;
            border: 1px solid #eee; outline: none; transition: 0.3s;
            box-sizing: border-box; font-family: inherit;
        }
        .input-group input:focus { border-color: var(--primary); box-shadow: 0 0 10px rgba(85,107,47,0.1); }
        
        .btn-simpan {
            width: 100%; padding: 15px; background: var(--primary);
            color: white; border: none; border-radius: 15px;
            font-weight: bold; cursor: pointer; transition: 0.3s; font-size: 1rem;
        }
        .btn-simpan:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        
        .btn-batal {
            display: block; text-align: center; margin-top: 20px;
            color: #aaa; text-decoration: none; font-size: 0.9rem;
        }
    </style>
</head>
<body>

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

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 800 });</script>
</body>
</html>