<?php
session_start();
include 'config/koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Logika Hapus Alat
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM alat WHERE id_alat='$id'");
    echo "<script>alert('Alat berhasil dihapus!'); window.location='admin_alat.php';</script>";
}

// Logika Eksekusi Proses Tambah Alat Baru (Form Integrasi ENUM)
if (isset($_POST['proses_tambah'])) {
    $nama_alat     = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $prodi_pemilik = mysqli_real_escape_string($koneksi, $_POST['prodi_pemilik']);
    $stok          = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $deskripsi     = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    
    // Default handler untuk foto
// Default handler untuk foto
    $foto_alat = "default.png";
    if ($_FILES['foto_alat']['name'] != "") {
        $foto_alat = time() . "_" . $_FILES['foto_alat']['name'];
        // KUNCI REVISI: Menghapus kata 'uploaded_' yang ganda agar menjadi fungsi legal PHP
        move_uploaded_file($_FILES['foto_alat']['tmp_name'], "assets/img/alat/" . $foto_alat);
    }

    $insert = "INSERT INTO alat (nama_alat, prodi_pemilik, stok, deskripsi, foto_alat) VALUES ('$nama_alat', '$prodi_pemilik', '$stok', '$deskripsi', '$foto_alat')";
    if (mysqli_query($koneksi, $insert)) {
        echo "<script>alert('Data Alat Baru Berhasil Disimpan!'); window.location='admin_alat.php';</script>";
    }
}

// Logika Eksekusi Proses Edit/Update Alat
if (isset($_POST['proses_update'])) {
    $id_alat       = mysqli_real_escape_string($koneksi, $_POST['id_alat']);
    $nama_alat     = mysqli_real_escape_string($koneksi, $_POST['nama_alat']);
    $prodi_pemilik = mysqli_real_escape_string($koneksi, $_POST['prodi_pemilik']);
    $stok          = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $deskripsi     = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    
    // Periksa apakah admin mengganti foto fisik
    if ($_FILES['foto_alat']['name'] != "") {
        $foto_alat = time() . "_" . $_FILES['foto_alat']['name'];
        move_uploaded_file($_FILES['foto_alat']['tmp_name'], "assets/img/alat/" . $foto_alat);
        $update = "UPDATE alat SET nama_alat='$nama_alat', prodi_pemilik='$prodi_pemilik', stok='$stok', deskripsi='$deskripsi', foto_alat='$foto_alat' WHERE id_alat='$id_alat'";
    } else {
        $update = "UPDATE alat SET nama_alat='$nama_alat', prodi_pemilik='$prodi_pemilik', stok='$stok', deskripsi='$deskripsi' WHERE id_alat='$id_alat'";
    }

    if (mysqli_query($koneksi, $update)) {
        echo "<script>alert('Data Alat Berhasil Diperbarui!'); window.location='admin_alat.php';</script>";
    }
}

// Ambil Data Inventaris Terbaru
$query = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY id_alat DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Inventaris - SIMLAB PMIPA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root { --primary: #556B2F; --accent: #6b8e23; --bg-gray: #f8f9fa; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg-gray); }
        
        .sidebar { width: 270px; height: 100vh; background: var(--primary); color: white; position: fixed; padding: 40px 20px; box-sizing: border-box; }
        .sidebar a { display: flex; align-items: center; gap: 15px; color: rgba(255,255,255,0.6); text-decoration: none; padding: 15px; border-radius: 15px; margin-bottom: 10px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; }

        .content { margin-left: 270px; flex: 1; padding: 50px; }
        .header-box { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        
        /* Tombol Pemicu Modal */
        .btn-tambah { background: var(--primary); color: white; border: none; padding: 12px 25px; border-radius: 12px; font-weight: bold; transition: 0.3s; display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem; }
        .btn-tambah:hover { background: var(--accent); transform: translateY(-3px); box-shadow: 0 10px 20px rgba(85,107,47,0.2); }

        .table-card { background: white; border-radius: 30px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.03); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #bbb; font-size: 0.75rem; text-transform: uppercase; border-bottom: 2px solid #f8f9fa; }
        td { padding: 20px; border-bottom: 1px solid #fcfcfc; font-size: 0.9rem; }

        .img-preview { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; }
        .btn-edit { color: #f39c12; text-decoration: none; margin-right: 15px; font-weight: bold; background: none; border: none; cursor: pointer; font-family: inherit; font-size: 0.9rem; }
        .btn-hapus { color: #e74c3c; text-decoration: none; font-weight: bold; }

        /* --- STYLES MODAL ENGINE --- */
        .modal-overlay { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center; }
        .modal-box { background: white; padding: 35px; border-radius: 25px; width: 100%; max-width: 500px; box-shadow: 0 15px 50px rgba(0,0,0,0.15); animation: slideDown 0.3s ease; }
        @keyframes slideDown { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 6px; color: #333; font-size: 0.85rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 10px; box-sizing: border-box; outline: none; font-family: inherit; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--primary); }
        
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 25px; }
        .btn-cancel { background: #f1f3f0; color: #666; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: bold; }
        .btn-save { background: var(--primary); color: white; border: none; padding: 10px 25px; border-radius: 10px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div style="text-align:center; margin-bottom: 50px;">
            <img src="assets/img/logo/logo_unila.png" width="70">
            <img src="assets/img/logo/logo_simlabnew.png?v=1" width="70" alt="Logo SIMLAB">
            <h2 style="font-size: 1.2rem; margin-top:10px;">LAB PMIPA</h2>
        </div>
        <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="admin_peminjaman.php"><i class="fas fa-clipboard-check"></i> Peminjaman</a>
        <a href="admin_alat.php" class="active"><i class="fas fa-microscope"></i> Inventaris Alat</a>
        <hr style="border: 0.5px solid rgba(255,255,255,0.1); margin: 30px 0;">
        <a href="logout.php" style="color: #ffb8b8;"><i class="fas fa-power-off"></i> Keluar</a>
    </div>

    <div class="content">
        <div class="header-box">
            <h1 style="color: var(--primary); font-weight: 800;">Manajemen Alat</h1>
            <button class="btn-tambah" onclick="openModalTambah()"><i class="fas fa-plus"></i> Tambah Alat Baru</button>
        </div>

        <div class="table-card" data-aos="fade-up">
            <table>
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama Alat / Paket Bundle</th>
                        <th>Prodi Pemilik</th>
                        <th>Sisa Stok</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_array($query)) { ?>
                    <tr>
                        <td><img src="assets/img/alat/<?php echo $row['foto_alat']; ?>" onerror="this.src='https://via.placeholder.com/100'" class="img-preview"></td>
                        <td><strong><?php echo str_replace('_', ' ', $row['nama_alat']); ?></strong></td>
                        <td><span style="color: var(--primary); font-weight: 600;"><?php echo $row['prodi_pemilik']; ?></span></td>
                        <td>
                            <?php if($row['stok'] > 0) { ?>
                                <b style="color: #2e7d32;"><?php echo $row['stok']; ?> Unit</b>
                            <?php } else { ?>
                                <b style="color: #c62828;">Habis</b>
                            <?php } ?>
                        </td>
                        <td><small style="color: #888;"><?php echo substr($row['deskripsi'], 0, 50); ?>...</small></td>
                        <td>
                            <button class="btn-edit" onclick="openModalEdit('<?php echo $row['id_alat']; ?>', '<?php echo $row['nama_alat']; ?>', '<?php echo $row['prodi_pemilik']; ?>', '<?php echo $row['stok']; ?>', '<?php echo $row['deskripsi']; ?>')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <a href="admin_alat.php?hapus=<?php echo $row['id_alat']; ?>" class="btn-hapus" onclick="return confirm('Hapus alat ini?')"><i class="fas fa-trash"></i> Hapus</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-tambah" class="modal-overlay">
        <div class="modal-box">
            <h3 style="color: var(--primary); margin-top:0;"><i class="fas fa-plus-circle"></i> Tambah Inventaris Baru</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama Alat / Paket Bundle</label>
                    <input type="text" name="nama_alat" required placeholder="Contoh: Bundle_Biologi_Dasar">
                </div>
                <div class="form-group">
                    <label>Prodi Pemilik (Klaster PMIPA)</label>
                    <select name="prodi_pemilik" required>
                        <option value="">-- Pilih Prodi --</option>
                        <option value="PTI">Pendidikan Teknologi Informasi (PTI)</option>
                        <option value="PMTK">Pendidikan Matematika (PMTK)</option>
                        <option value="PBIO">Pendidikan Biologi (PBIO)</option>
                        <option value="PFIS">Pendidikan Fisika (PFIS)</option>
                        <option value="PKIM">Pendidikan Kimia (PKIM)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jumlah Stok Awal</label>
                    <input type="number" name="stok" min="0" required placeholder="0">
                </div>
                <div class="form-group">
                    <label>Deskripsi / Komponen Bundle</label>
                    <textarea name="deskripsi" rows="3" placeholder="Tulis rincian spesifikasi atau isi bundle..."></textarea>
                </div>
                <div class="form-group">
                    <label>Foto Fisik Alat</label>
                    <input type="file" name="foto_alat">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalTambah()">Batal</button>
                    <button type="submit" name="proses_tambah" class="btn-save">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-edit" class="modal-overlay">
        <div class="modal-box">
            <h3 style="color: #f39c12; margin-top:0;"><i class="fas fa-edit"></i> Edit Informasi Inventaris</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_alat" id="edit_id_alat">
                
                <div class="form-group">
                    <label>Nama Alat / Paket Bundle</label>
                    <input type="text" name="nama_alat" id="edit_nama_alat" required>
                </div>
                <div class="form-group">
                    <label>Prodi Pemilik (Klaster PMIPA)</label>
                    <select name="prodi_pemilik" id="edit_prodi_pemilik" required>
                        <option value="PTI">Pendidikan Teknologi Informasi (PTI)</option>
                        <option value="PMTK">Pendidikan Matematika (PMTK)</option>
                        <option value="PBIO">Pendidikan Biologi (PBIO)</option>
                        <option value="PFIS">Pendidikan Fisika (PFIS)</option>
                        <option value="PKIM">Pendidikan Kimia (PKIM)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Sisa Stok di Rak</label>
                    <input type="number" name="stok" id="edit_stok" min="0" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi / Komponen Bundle</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Ubah Foto Fisik <small style="color:#aaa;">(Biarkan kosong jika tidak diganti)</small></label>
                    <input type="file" name="foto_alat">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModalEdit()">Batal</button>
                    <button type="submit" name="proses_update" class="btn-save" style="background:#f39c12;">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, once: true });

        // Fungsi Modal Tambah
        function openModalTambah() { document.getElementById('modal-tambah').style.display = 'flex'; }
        function closeModalTambah() { document.getElementById('modal-tambah').style.display = 'none'; }

        // Fungsi Modal Edit Otomatis Membawa & Mengisi Data Lama Ke Kolom Input
        function openModalEdit(id, nama, prodi, stok, deskripsi) {
            document.getElementById('edit_id_alat').value = id;
            document.getElementById('edit_nama_alat').value = nama;
            document.getElementById('edit_prodi_pemilik').value = prodi;
            document.getElementById('edit_stok').value = stok;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('modal-edit').style.display = 'flex';
        }
        function closeModalEdit() { document.getElementById('modal-edit').style.display = 'none'; }
    </script>
</body>
</html>