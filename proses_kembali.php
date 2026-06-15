<?php
session_start();
include 'config/koneksi.php';

// Proteksi Admin agar tidak bisa diakses sembarang orang
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    // Ambil ID dengan aman
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // UPDATE: Gunakan kata 'kembali' sesuai struktur Enum database kamu
    $query = mysqli_query($koneksi, "UPDATE peminjaman SET status='kembali' WHERE id_pinjam='$id'");
    
    if ($query) {
        echo "<script>alert('Alat telah dikembalikan! Status diperbarui menjadi KEMBALI.'); window.location='admin_peminjaman.php';</script>";
    } else {
        echo "<script>alert('Gagal memproses data: " . mysqli_error($koneksi) . "'); window.location='admin_peminjaman.php';</script>";
    }
} else {
    header("Location: admin_peminjaman.php");
}
?>