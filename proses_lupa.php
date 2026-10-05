<?php
include "config.php";
session_start();

if (isset($_POST['email'])) {

    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // cek email terdaftar
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($cek) > 0) {

        $otp = rand(100000, 999999);

        // simpan otp ke database
        mysqli_query($conn, "UPDATE users SET otp='$otp' WHERE email='$email'");

        $_SESSION['email_reset'] = $email;

        echo "<script>
                alert('Kode OTP Anda: $otp');
                window.location='verifikasi_otp.php';
              </script>";
    } else {

        echo "<script>
                alert('Email tidak ditemukan!');
                window.location='lupa_password.php';
              </script>";
    }
} else {
    header("Location: lupa_password.php");
}
