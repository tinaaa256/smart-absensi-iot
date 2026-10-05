<!-- verifikasi_otp.php -->
<?php
include "config.php";
session_start();

if (isset($_POST['email'])) {

    $email = $_POST['email'];

    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($cek) > 0) {

        $otp = rand(100000, 999999);

        mysqli_query($conn, "UPDATE users SET otp='$otp' WHERE email='$email'");

        $_SESSION['email_reset'] = $email;

        echo "<script>alert('Kode OTP Anda: $otp');</script>";
    } else {
        echo "<script>alert('Email tidak ditemukan');window.location='lupa_password.php';</script>";
        exit;
    }
} else {
    if (!isset($_SESSION['email_reset'])) {
        header("Location:lupa_password.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Verifikasi OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f1f5f9;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .box {
            width: 340px;
            background: #fff;
            padding: 25px;
            border-radius: 14px;
        }
    </style>
</head>

<body>

    <div class="box shadow">
        <h5 class="text-center mb-3">Verifikasi OTP</h5>

        <form action="reset_password.php" method="POST">
            <input type="text" name="otp" class="form-control mb-3" placeholder="Masukkan OTP" required>

            <button class="btn btn-primary w-100">Verifikasi</button>
        </form>

    </div>

</body>

</html>