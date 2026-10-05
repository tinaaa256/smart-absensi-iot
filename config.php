<?php
// koneksi database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "smart_absensi";

$conn = mysqli_connect($host, $user, $pass, $db);

// cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// set timezone biar jam absensi sesuai Indonesia
date_default_timezone_set("Asia/Jakarta");
