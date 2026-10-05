<?php
include "config.php";
session_start();

$username = $_POST['username'];
$password = $_POST['password'];

$q = mysqli_query($conn, "
SELECT * FROM users
WHERE username='$username'
AND password='$password'
");

$data = mysqli_fetch_assoc($q);

if ($data) {

    $_SESSION['login'] = true;
    $_SESSION['id'] = $data['id'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['nama'] = $data['nama'];
    $_SESSION['role'] = $data['role'];

    $role = strtolower(trim($data['role']));

    if ($role == 'admin') {

        header("Location: dashboard_admin.php");
    } elseif ($role == 'dosen') {

        header("Location: dashboard_dosen.php");
    } elseif ($role == 'mahasiswa') {

        header("Location: dashboard_mahasiswa.php");
    } else {

        echo "Role tidak dikenali: " . $data['role'];
    }

    exit;
} else {
    echo "Login gagal";
}
