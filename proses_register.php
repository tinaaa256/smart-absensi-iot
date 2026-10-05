<?php
include "config.php";
session_start();

$username = $_POST['username'];
$email    = $_POST['email'];
$password = $_POST['password'];

$nim      = $_POST['nim'];
$nama     = $_POST['nama'];
$semester = $_POST['semester'];
$pa       = $_POST['pa'];
$prodi    = $_POST['prodi'];


// CEK USERNAME / EMAIL
$cek = mysqli_query($conn, "
SELECT * FROM users 
WHERE username='$username'
OR email='$email'
");

if (mysqli_num_rows($cek) > 0) {

   echo "<script>
    alert('Username atau Email sudah digunakan');
    window.location='register.php';
    </script>";

   exit;
}


// SIMPAN DATA MAHASISWA
$queryMhs = mysqli_query($conn, "
INSERT INTO mahasiswa
(nim,nama,password,semester,pa,prodi,finger_id)
VALUES
('$nim','$nama','$password','$semester','$pa','$prodi',NULL)
");


if (!$queryMhs) {
   die("Data mahasiswa gagal: " . mysqli_error($conn));
}


// AMBIL ID MAHASISWA BARU
$id_mahasiswa = mysqli_insert_id($conn);


// SIMPAN DATA USER
$queryUser = mysqli_query($conn, "
INSERT INTO users
(username,nama,email,password,role,otp,mahasiswa_id)
VALUES
('$username','$nama','$email','$password','mahasiswa','','$id_mahasiswa')
");


if (!$queryUser) {
   die("Data users gagal: " . mysqli_error($conn));
}


echo "<script>
alert('Register berhasil');
window.location='login.php';
</script>";
