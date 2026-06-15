<?php
include 'config/koneksi.php';

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status_baru = strtolower($_GET['status']); // Pastikan simpan huruf kecil

    $update = mysqli_query($koneksi, "UPDATE peminjaman SET status = '$status_baru' WHERE id_pinjam = '$id'");

    if ($update) {
        echo "<script>alert('Berhasil diproses!'); window.location='admin_peminjaman.php';</script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>