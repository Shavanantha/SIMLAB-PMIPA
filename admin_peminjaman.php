<?php
session_start();
include 'config/koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Akses Ditolak!'); window.location='login.php';</script>";
    exit;
}

// Ambil Statistik untuk Card di Atas
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM peminjaman");
$d_total = mysqli_fetch_assoc($q_total);

$q_pending = mysqli_query($koneksi, "SELECT COUNT(*) as jml FROM peminjaman WHERE status = 'menunggu'");
$d_pending = mysqli_fetch_assoc($q_pending);

// Kueri Lengkap (Ambil data peminjaman + Nama Alat + Foto Alat + Nama Mahasiswa)
$sql = "SELECT peminjaman.*, alat.nama_alat, alat.prodi_pemilik, alat.foto_alat, users.nama 
        FROM peminjaman 
        JOIN alat ON peminjaman.id_alat = alat.id_alat 
        JOIN users ON peminjaman.npm = users.nim_id 
        ORDER BY CASE WHEN peminjaman.status = 'menunggu' THEN 1 ELSE 2 END, peminjaman.id_pinjam DESC";
$query = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Pengelola - SIMLAB PMIPA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        :root { --primary: #556B2F; --accent: #6b8e23; --bg-gray: #f8f9fa; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: var(--bg-gray); }
        
        /* Sidebar */
        .sidebar { width: 270px; height: 100vh; background: var(--primary); color: white; position: fixed; padding: 40px 20px; box-sizing: border-box; }
        .sidebar a { display: flex; align-items: center; gap: 15px; color: rgba(255,255,255,0.6); text-decoration: none; padding: 15px; border-radius: 15px; margin-bottom: 10px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.1); color: white; }

        .content { margin-left: 270px; flex: 1; padding: 50px; }
        
        /* Card Statistik di Atas */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: white; padding: 30px; border-radius: 25px; display: flex; align-items: center; gap: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        .stat-card i { font-size: 2rem; color: var(--primary); background: #f1f3ee; padding: 20px; border-radius: 20px; }
        .stat-card h3 { margin: 0; font-size: 1.8rem; }
        .stat-card p { margin: 0; color: #aaa; font-size: 0.8rem; text-transform: uppercase; }

        /* Desain Grid Dua Kolom untuk Gateway Validasi */
        .gateway-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
        .gateway-card { background: white; padding: 30px; border-radius: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }

        /* Kustomisasi Wajib untuk Tombol Unggah File Gambar bawaan Library HTML5-QRCode */
        #reader-file, #reader-kamera { 
            border: 2px dashed var(--primary) !important; 
            border-radius: 15px !important; 
            padding: 15px !important; 
            background: #fafafa !important;
        }
        #reader-file button, #reader-kamera button {
            background: var(--primary) !important;
            color: white !important;
            border: none !important;
            padding: 10px 18px !important;
            border-radius: 10px !important;
            font-weight: bold !important;
            cursor: pointer !important;
            font-size: 0.85rem !important;
            margin: 8px 0 !important;
            transition: 0.3s;
        }
        #reader-file button:hover, #reader-kamera button:hover { background: var(--accent) !important; }
        #reader-file a, #reader-kamera a { color: var(--primary) !important; font-weight: bold !important; }

        /* Tabel */
        .table-card { background: white; border-radius: 30px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.03); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; color: #bbb; font-size: 0.75rem; text-transform: uppercase; border-bottom: 2px solid #f8f9fa; }
        td { padding: 20px; border-bottom: 1px solid #fcfcfc; font-size: 0.9rem; }
        
        .img-mini { width: 55px; height: 55px; border-radius: 12px; object-fit: cover; margin-right: 15px; box-shadow: 0 5px 10px rgba(0,0,0,0.05); }
        .alat-box { display: flex; align-items: center; }

        .badge { padding: 6px 15px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .status-menunggu { background: #fff9e6; color: #f39c12; }
        .status-disetujui { background: #ebf9f1; color: #2ecc71; }
        .status-ditolak { background: #fdf2f2; color: #e74c3c; }
        .status-selesai { background: #f0f7ff; color: #3498db; }

        .btn { padding: 10px 15px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.75rem; transition: 0.3s; display: inline-block; }
        .btn-acc { background: #2ecc71; color: white; margin-right: 5px; }
        .btn-tolak { background: #e74c3c; color: white; }
        .btn-kembali { background: #3498db; color: white; }
        .btn:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div style="text-align:center; margin-bottom:50px;">
            <img src="assets/img/logo/logo_unila.png" width="70">
            <img src="assets/img/logo/logo_simlabnew.png?v=1" width="70" alt="Logo SIMLAB">
            <h2 style="font-size:1.2rem; margin-top:10px;">LAB PMIPA</h2>
        </div>
        <a href="admin_dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="admin_peminjaman.php" class="active"><i class="fas fa-clipboard-check"></i> Peminjaman</a>
        <a href="admin_alat.php"><i class="fas fa-microscope"></i> Inventaris Alat</a>
        <hr style="border: 0.5px solid rgba(255,255,255,0.1); margin: 30px 0;">
        <a href="logout.php" style="color: #ffb8b8;"><i class="fas fa-power-off"></i> Keluar</a>
    </div>

    <div class="content">
        <h1 style="color:var(--primary); font-weight:800; margin-bottom:40px;">Kelola Permohonan</h1>
        
        <div class="gateway-grid">
            
            <div class="gateway-card" data-aos="fade-right">
                <h3 style="color: var(--primary); margin-top:0;"><i class="fas fa-camera"></i> Metode 1: Live Scan Kamera</h3>
                <p style="color: #888; font-size: 0.8rem; margin-top: -5px; margin-bottom: 20px;">Nyalakan sensor kamera laptop untuk mendeteksi langsung QR Code.</p>
                
                <div id="reader-kamera"></div>
            </div>

            <div class="gateway-card" data-aos="fade-left">
                <h3 style="color: var(--primary); margin-top:0;"><i class="fas fa-file-import"></i> Metode 2: Upload Gambar / Manual</h3>
                <p style="color: #888; font-size: 0.8rem; margin-top: -5px; margin-bottom: 20px;">Pilih berkas screenshot QR Code mahasiswa atau input manual kodenya.</p>
                
                <div id="reader-file"></div>

                <form action="proses_validasi_qr.php" method="POST" style="display: flex; gap: 10px; margin-top: 25px;">
                    <input type="text" name="id_pinjam" placeholder="Atau ketik manual ID Transaksi..." required 
                        style="flex: 1; padding: 12px 15px; border-radius: 10px; border: 1px solid #ddd; outline: none; font-weight: bold; color: var(--primary); font-size: 0.85rem;">
                    <button type="submit" name="validasi" style="background: var(--primary); color: white; border: none; padding: 0 20px; border-radius: 10px; font-weight: bold; cursor: pointer; font-size: 0.85rem;">VALIDASI</button>
                </form>
            </div>

        </div>

        <form action="proses_validasi_qr.php" method="POST" id="form-scan-otomatis">
            <input type="hidden" name="id_pinjam" id="id_pinjam_hidden">
            <button type="submit" name="validasi" id="btn_submit_otomatis" style="display: none;"></button>
        </form>

        <div class="stats-grid">
            <div class="stat-card" data-aos="fade-up">
                <i class="fas fa-folder-open"></i>
                <div>
                    <h3><?php echo $d_total['jml']; ?></h3>
                    <p>Total Masuk</p>
                </div>
            </div>
            <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                <i class="fas fa-hourglass-half" style="color: #f39c12;"></i>
                <div>
                    <h3 style="color: #f39c12;"><?php echo $d_pending['jml']; ?></h3>
                    <p>Menunggu ACC</p>
                </div>
            </div>
        </div>

        <div class="table-card" data-aos="fade-up">
            <table>
                <thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Alat & Prodi Pemilik</th>
                        <th>Tgl Pinjam</th>
                        <th>Status</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($data = mysqli_fetch_array($query)) { 
                        $status_low = strtolower($data['status']);
                    ?>
                    <tr>
                        <td>
                            <strong style="display:block;"><?php echo $data['nama']; ?></strong>
                            <small style="color:#aaa;"><?php echo $data['npm']; ?></small>
                        </td>
                        <td>
                            <div class="alat-box">
                                <img src="assets/img/alat/<?php echo $data['foto_alat']; ?>" onerror="this.src='https://via.placeholder.com/55'" class="img-mini">
                                <div>
                                    <strong style="display:block;"><?php echo str_replace('_', ' ', $data['nama_alat']); ?></strong>
                                    <small style="color:var(--accent);"><?php echo $data['prodi_pemilik']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?php echo date('d M Y', strtotime($data['tgl_pinjam'])); ?></td>
                        <td><span class="badge status-<?php echo $status_low; ?>"><?php echo $status_low; ?></span></td>
                        <td>
                            <?php if ($status_low == 'menunggu'): ?>
                                <a href="proses_aksi.php?id=<?php echo $data['id_pinjam']; ?>&status=disetujui" class="btn btn-acc" onclick="return confirm('Setujui peminjaman?')">ACC</a>
                                <a href="proses_aksi.php?id=<?php echo $data['id_pinjam']; ?>&status=ditolak" class="btn btn-tolak" onclick="return confirm('Tolak permohonan?')">TOLAK</a>
                            <?php elseif ($status_low == 'disetujui'): ?>
                                <a href="proses_kembali.php?id=<?php echo $data['id_pinjam']; ?>" class="btn btn-kembali" onclick="return confirm('Apakah alat sudah dikembalikan?')">KEMBALIKAN</a>
                            <?php else: ?>
                                <span style="color:#2ecc71; font-weight:800; font-size:0.75rem;">SELESAI</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 800 });</script>
    
    <script src="https://unpkg.com/html5-qrcode"></script>
    
    <script>
        // Router Pemroses Utama Token Sirkulasi
  // Router Pemroses Utama Token Sirkulasi
// Router Pemroses Utama Token Sirkulasi
        function kirimDataValidasi(decodedContent) {
            let idTransaksi = decodedContent;
            if (decodedContent.includes('|')) {
                let dataPecah = decodedContent.split('|');
                idTransaksi = dataPecah[0]; // Ambil Kunci ID TRX saja
            }

            // Masukkan secara paksa ke hidden input
            document.getElementById('id_pinjam_hidden').value = idTransaksi.trim();
            
            // Alert konfirmasi penangkap token
            alert('Sistem Berhasil Menangkap Token: ' + idTransaksi.trim());
            
            // KUNCI REVISI JAVASCRIPT: Memicu aksi klik fisik pada tombol submit otomatis agar sinkron ke backend
            document.getElementById('btn_submit_otomatis').click();
        }

        // --- SENSOR 1: LIVE SCAN KAMERA ---
        function onCameraScanSuccess(decodedText, decodedResult) {
            kirimDataValidasi(decodedText);
        }
        let html5QrcodeCamera = new Html5QrcodeScanner("reader-kamera", { fps: 10, qrbox: 250 });
        html5QrcodeCamera.render(onCameraScanSuccess, function(error){});

        // --- SENSOR 2: BACA SCREENSHOT FILE GAMBAR ---
        // KUNCI REVISI: Menggunakan elemen input bawaan Html5QrcodeScanner agar UI pencari berkas tampil sempurna tanpa crash
        let html5QrcodeFile = new Html5QrcodeScanner("reader-file", { fps: 10, qrbox: 250 });
        html5QrcodeFile.render(function(decodedText, decodedResult) {
            kirimDataValidasi(decodedText);
        }, function(error){});
    </script>
</body>
</html>