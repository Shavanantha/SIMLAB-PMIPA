<?php
$host = "sql309.infinityfree.com";
$user = "if0_42178768";
$pass = "mijAkQgunWRE";
$db   = "if0_42178768_simlab";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>