<!-- reset_password.php -->
<?php
include "config.php";
session_start();

if (isset($_POST['otp'])) {

    $otp   = $_POST['otp'];
    $email = $_SESSION['email_reset'];

    $cek = mysqli_query($conn, "SELECT * FROM users 
                               WHERE email='$email' 
                               AND otp='$otp'");

    if (mysqli_num_rows($cek) == 0) {
        echo "<script>alert('OTP Salah');window.location='verifikasi_otp.php';</script>";
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
    <title>Reset Password</title>
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
        <h5 class="text-center mb-3">Reset Password</h5>

        <form method="POST">
            <input type="password" name="password_baru" class="form-control mb-3" placeholder="Password Baru" required>

            <button name="ubah" class="btn btn-success w-100">Ubah Password</button>
        </form>

    </div>

</body>

</html>

<?php
if (isset($_POST['ubah'])) {

    $pass  = $_POST['password_baru'];
    $email = $_SESSION['email_reset'];

    mysqli_query($conn, "UPDATE users 
                        SET password='$pass', otp='' 
                        WHERE email='$email'");

    session_destroy();

    echo "<script>alert('Password berhasil diubah');window.location='login.php';</script>";
}
?>