<?php
session_start();
include 'config/koneksi.php';

// Jika sudah login, langsung alihkan ke dashboard masing-masing role
if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$error = false;

// Logika Autentikasi Form Login Dual-Aktor
if (isset($_POST['login_proses'])) {
    $username_input = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password_input = $_POST['password'];
    $role_input     = $_POST['role']; // 'mahasiswa' atau 'admin'

    // Query dinamis mendeteksi tipe aktor secara terpisah
    if ($role_input === 'admin') {
        // KUNCI REVISI: Mengubah 'username' menjadi 'nim_id' agar sesuai dengan struktur database asli kamu
        $query = mysqli_query($koneksi, "SELECT * FROM users WHERE nim_id='$username_input' AND role='admin'");
    } else {
        // Cari berdasarkan nim_id (NPM) untuk akun mahasiswa
        $query = mysqli_query($koneksi, "SELECT * FROM users WHERE nim_id='$username_input' AND role='mahasiswa'");
    }

    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);
        
        // Validasi kecocokan sandi (Mendukung hash, MD5, atau teks biasa sesuai data lamamu)
        if (password_verify($password_input, $row['password']) || md5($password_input) === $row['password'] || $password_input === $row['password']) {
            
            // Menyimpan Data Penting ke Session
            $_SESSION['login']   = true;
            $_SESSION['id_user'] = $row['id_user'] ?? $row['id'];
            $_SESSION['nama']    = $row['nama'];
            $_SESSION['npm']     = $row['nim_id'];
            $_SESSION['role']    = $row['role'];
            $_SESSION['prodi']   = $row['prodi'] ?? '';

            // Pengalihan halaman berbasis hak akses (Role-Based Access Control)
            if ($row['role'] === 'admin') {
                echo "<script>alert('Selamat Datang Admin, ".$row['nama']."!'); window.location='admin_peminjaman.php';</script>";
            } else {
                echo "<script>alert('Selamat Datang, ".$row['nama']."!'); window.location='index.php';</script>";
            }
            exit;
        }
    }
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMLAB PMIPA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --earthy-green: #556B2F;
            --soft-accent: #6b8e23;
            --dark-card: #1e2516; /* Gelap kehijauan */
            --bg-glass: rgba(30, 37, 22, 0.85);
            --text-light: #fdfaf1;
            --text-muted: #a2ad91;
        }

        * {
            margin: 0; padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(rgba(11, 15, 9, 0.8), rgba(11, 15, 9, 0.8)), url('assets/img/bg_gedung_fkip.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Container Utama Dual Panel Gabungan Elemen EduSPACE & SIMLAB */
        .login-container {
            display: flex;
            width: 950px;
            max-width: 100%;
            background: var(--bg-glass);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(107, 142, 35, 0.3);
            backdrop-filter: blur(12px);
        }

        /* Panel Kiri: Ilustrasi Estetik Bertema Hijau Lab */
        .login-illustration {
            flex: 1;
            background: linear-gradient(135deg, var(--earthy-green), #1b260e);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            position: relative;
            text-align: center;
        }

        .login-illustration::before {
            content: '';
            position: absolute;
            width: 180px; height: 180px;
            background: var(--soft-accent);
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.3;
            top: 15%;
        }

        .login-illustration img {
            width: 100px;
            max-width: 100%;
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));
            animation: float 5s ease-in-out infinite;
            z-index: 2;
        }

        .login-illustration h2 {
            font-size: 28px;
            font-weight: 800;
            margin-top: 25px;
            letter-spacing: 1px;
            color: white;
            text-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .login-illustration p {
            color: #d1dcd0;
            font-size: 0.85rem;
            margin-top: 10px;
            max-width: 300px;
            line-height: 1.6;
            opacity: 0.9;
        }

        /* Panel Kanan: Form Komponen Kendali Input */
        .login-box {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: var(--text-light);
        }

        .login-header h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
            color: white;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 13px;
            margin-bottom: 30px;
        }

        /* Tab Switcher Peran (Mahasiswa vs Admin) */
        .role-switcher {
            display: flex;
            background: rgba(11, 15, 9, 0.6);
            padding: 6px;
            border-radius: 14px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .role-option {
            flex: 1;
            text-align: center;
            padding: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s ease;
            color: var(--text-muted);
        }

        .role-option.active {
            background: var(--earthy-green);
            color: white;
            box-shadow: 0 4px 15px rgba(85, 107, 47, 0.4);
        }

        .input-group {
            margin-bottom: 22px;
        }

        .input-group label {
            display: block;
            font-size: 11px;
            color: var(--soft-accent);
            margin-bottom: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 15px;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 15px 14px 48px;
            background: rgba(11, 15, 9, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            font-size: 14px;
            transition: all 0.3s;
            outline: none;
        }

        .input-wrapper input:focus {
            border-color: var(--soft-accent);
            box-shadow: 0 0 10px rgba(107, 142, 35, 0.3);
        }

        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            margin-bottom: 30px;
        }

        .forgot-pass {
            color: var(--soft-accent);
            text-decoration: none;
            font-weight: 600;
            transition: 0.2s;
        }

        .forgot-pass:hover {
            color: white;
            text-decoration: underline;
        }

        /* Tombol Utama Hijau Kebanggaan SIMLAB */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--earthy-green), #3d4f22);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(85, 107, 47, 0.3);
            letter-spacing: 1px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(85, 107, 47, 0.5);
            background: linear-gradient(135deg, var(--soft-accent), var(--earthy-green));
        }

        .error-alert {
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid #e74c3c;
            color: #ff7675;
            padding: 12px 15px;
            border-radius: 12px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
        }
        .back-link a {
            color: #666;
            text-decoration: none;
            transition: 0.3s;
        }
        .back-link a:hover { color: var(--soft-accent); }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
            100% { transform: translateY(0px); }
        }

        /* Collapse Panel untuk Ponsel */
        @media (max-width: 768px) {
            .login-container { flex-direction: column; width: 100%; }
            .login-illustration { padding: 35px 20px; }
            .login-illustration img { width: 70px; }
            .login-box { padding: 35px 25px; }
        }
        
        /* 📱 PENYELAMAT HALAMAN LOGIN DI HP */
        @media screen and (max-width: 768px) {
            body {
                padding: 20px !important;
                box-sizing: border-box;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                min-height: 100vh;
            }
            /* Ubah layout login 2 kolom menjadi 1 kolom memanjang ke bawah */
            .card-panel-login { 
                grid-template-columns: 1fr !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box;
            }
            /* Sembunyikan area co-branding kiri di HP agar fokus langsung ke form isi NPM */
            .area-branding-kiri {
                display: none !important;
            }
            .area-form-kanan {
                padding: 30px 20px !important;
            }
        }
    </style>
</head>
<body>

    <div class="login-container" data-aos="fade-up" data-aos-duration="900">
        
        <div class="login-illustration" style="text-align: center;">
            <div style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-bottom: 15px;">
                <img src="assets/img/logo/logo_unila.png" width="55" alt="Logo Unila" style="height: auto;">
                <img src="assets/img/logo/logo_simlabnew.png?v=1" width="55" alt="Logo SIMLAB" style="height: auto;">
            </div>
            <span style="font-weight: bold; color: var(--primary); font-size: 1.3rem; display: block; margin-bottom: 10px;">SIMLAB PMIPA</span>
            <p>Sistem Informasi Manajemen Laboratorium Pendidikan MIPA FKIP Universitas Lampung</p>
        </div>

        <div class="login-box">
            <div class="login-header">
                <h3>Selamat Datang</h3>
                <p>Silakan memproses autentikasi sesuai dengan hak akses peran anda.</p>
            </div>

            <?php if ($error) : ?>
                <div class="error-alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Kredensial salah atau Peran Aktor yang anda pilih tidak sesuai!</span>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                
                <input type="hidden" name="role" id="role_value" value="mahasiswa">
                <div class="role-switcher">
                    <div class="role-option active" id="btn-mhs" onclick="gantiAktor('mahasiswa', 'NPM / ID Praktikan')">
                        <i class="fas fa-user-graduate"></i> Mahasiswa
                    </div>
                    <div class="role-option" id="btn-admin" onclick="gantiAktor('admin', 'Username / Email Admin')">
                        <i class="fas fa-user-shield"></i> Admin
                    </div>
                </div>

                <div class="input-group">
                    <label id="label-user">NPM / ID Praktikan</label>
                    <div class="input-wrapper">
                        <i class="fas fa-id-card" id="icon-user"></i>
                        <input type="text" name="username" placeholder="Ketik data identitas..." required autocomplete="off">
                    </div>
                </div>

                <div class="input-group">
                    <label>Kata Sandi Akun</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="login-options">
                    <label style="display:flex; align-items:center; gap:8px; cursor:pointer; color:var(--text-muted);">
                        <input type="checkbox" style="accent-color:var(--earthy-green);"> Ingat Sesi Saya
                    </label>
                    <a href="javascript:void(0)" onclick="alert('Mekanisme Pemulihan Akun: Silakan laporkan Nama dan NPM anda secara langsung ke meja petugas Laboran di Gedung L FKIP Unila untuk mereset kata sandi.')" class="forgot-pass">Lupa Password?</a>
                </div>

                <button type="submit" name="login_proses" class="btn-login">
                    MASUK KE SISTEM LAB <i class="fas fa-sign-in-alt" style="margin-left:6px;"></i>
                </button>
            </form>

            <div class="back-link">
                <a href="index.php"><i class="fas fa-arrow-left" style="font-size:0.75rem;"></i> Kembali ke Beranda Utama</a>
            </div>
        </div>

    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();

        // Fungsi Penata Kendali Peran Aktor
        function gantiAktor(peran, textLabel) {
            document.getElementById('role_value').value = peran;
            document.getElementById('label-user').innerText = textLabel;
            
            if (peran === 'admin') {
                document.getElementById('btn-admin').classList.add('active');
                document.getElementById('btn-mhs').classList.remove('active');
                document.getElementById('icon-user').className = "fas fa-user-shield";
            } else {
                document.getElementById('btn-mhs').classList.add('active');
                document.getElementById('btn-admin').classList.remove('active');
                document.getElementById('icon-user').className = "fas fa-id-card";
            }
        }
    </script>
</body>
</html>