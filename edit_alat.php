<?php
session_start();
include 'config/koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil data lama alat yang mau diedit
$id = $_GET['id'];
$query_lama = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat='$id'");
$data_lama = mysqli_fetch_assoc($query_lama);

// Logika Update Data
if (isset($_POST['update'])) {
    $nama_alat     = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $prodi_pemilik = mysqli_real_escape_string($koneksi, $_POST['prodi_pemilik']);
    $deskripsi     = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    
    $foto_name = $_FILES['foto']['name'];
    $foto_tmp  = $_FILES['foto']['tmp_name'];

    if (!empty($foto_name)) {
        // Jika upload foto baru, hapus foto lama dulu biar nggak menuhin storage
        if (file_exists("assets/img/alat/" . $data_lama['foto_alat'])) {
            unlink("assets/img/alat/" . $data_lama['foto_alat']);
        }
        
        $ekstensi  = pathinfo($foto_name, PATHINFO_EXTENSION);
        $foto_baru = time() . "_" . $nama_alat . "." . $ekstensi;
        move_uploaded_file($foto_tmp, "assets/img/alat/" . $foto_baru);
        
        $query_update = "UPDATE alat SET 
                         nama_alat='$nama_alat', 
                         prodi_pemilik='$prodi_pemilik', 
                         deskripsi='$deskripsi', 
                         foto_alat='$foto_baru' 
                         WHERE id_alat='$id'";
    } else {
        // Jika tidak upload foto baru, pakai nama foto yang lama
        $query_update = "UPDATE alat SET 
                         nama_alat='$nama_alat', 
                         prodi_pemilik='$prodi_pemilik', 
                         deskripsi='$deskripsi' 
                         WHERE id_alat='$id'";
    }

    if (mysqli_query($koneksi, $query_update)) {
        echo "<script>alert('Data alat berhasil diupdate!'); window.location='admin_alat.php';</script>";
    } else {
        echo "<script>alert('Gagal update data!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Alat - SIMLAB PMIPA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --primary: #556B2F; --bg: #fdfaf1; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); margin: 0; padding: 50px 0; display: flex; justify-content: center; }
        .form-card { background: white; width: 90%; max-width: 600px; padding: 40px; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.05); }
        h2 { color: var(--primary); font-weight: 800; margin-bottom: 30px; text-align: center; }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        .input-group input, .input-group select, .input-group textarea { width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #eee; outline: none; box-sizing: border-box; }
        .btn-update { width: 100%; padding: 15px; background: var(--primary); color: white; border: none; border-radius: 15px; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn-update:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .preview-old { margin-bottom: 10px; border-radius: 10px; width: 100px; height: 100px; object-fit: cover; border: 2px solid var(--primary); }
    </style>
</head>
<body>

    <div class="form-card" data-aos="fade-up">
        <h2>Edit Data Inventaris</h2>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label>Nama Alat</label>
                <input type="text" name="nama_alat" value="<?php echo $data_lama['nama_alat']; ?>" required>
            </div>
            
            <div class="input-group">
                <label>Prodi Pemilik</label>
                <select name="prodi_pemilik" required>
                    <option value="Biologi" <?php if($data_lama['prodi_pemilik'] == 'Biologi') echo 'selected'; ?>>Pendidikan Biologi</option>
                    <option value="Kimia" <?php if($data_lama['prodi_pemilik'] == 'Kimia') echo 'selected'; ?>>Pendidikan Kimia</option>
                    <option value="Fisika" <?php if($data_lama['prodi_pemilik'] == 'Fisika') echo 'selected'; ?>>Pendidikan Fisika</option>
                    <option value="PTI" <?php if($data_lama['prodi_pemilik'] == 'PTI') echo 'selected'; ?>>Pendidikan Teknologi Informasi</option>
                </select>
            </div>
            
            <div class="input-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="4" required><?php echo $data_lama['deskripsi']; ?></textarea>
            </div>
            
            <div class="input-group">
                <label>Foto Saat Ini</label><br>
                <img src="assets/img/alat/<?php echo $data_lama['foto_alat']; ?>" class="preview-old"><br>
                <label style="font-size: 0.8rem; color: #888;">Ganti foto (Kosongkan jika tidak ingin ganti):</label>
                <input type="file" name="foto" accept="image/*">
            </div>
            
            <button type="submit" name="update" class="btn-update">SIMPAN PERUBAHAN</button>
            <a href="admin_alat.php" style="display:block; text-align:center; margin-top:20px; color:#aaa; text-decoration:none;">Batal</a>
        </form>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 800 });</script>
</body>
</html>