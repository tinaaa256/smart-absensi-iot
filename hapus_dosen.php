<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];


/* hapus akun dosen */
$query = mysqli_query($conn, "
DELETE FROM users
WHERE id='$id'
AND role='dosen'
");


if ($query) {

    echo "
    <script>
    alert('Dosen berhasil dihapus');
    location='dosen.php';
    </script>";
} else {

    echo "
    <script>
    alert('Gagal menghapus dosen');
    location='dosen.php';
    </script>";
}
