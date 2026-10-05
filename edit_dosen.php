<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];

if (isset($_POST['update'])) {

    $username = $_POST['username'];
    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    mysqli_query($conn, "
    UPDATE users SET
    username='$username',
    nama='$nama',
    email='$email',
    password='$password'
    WHERE id='$id'
    ");

    echo "<script>
    alert('Data dosen berhasil diubah');
    location='dosen.php';
    </script>";
}

$data = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT * FROM users
WHERE id='$id'
"));
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Edit Dosen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5">

        <div class="card p-4">

            <h4>Edit Dosen</h4>

            <form method="POST">

                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="<?= $data['username']; ?>" required>
                </div>

                <div class="mb-3">
                    <label>Nama</label>
                    <input type="text" name="nama" class="form-control" value="<?= $data['nama']; ?>" required>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?= $data['email']; ?>" required>
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="text" name="password" class="form-control" value="<?= $data['password']; ?>" required>
                </div>

                <button class="btn btn-primary" name="update">
                    Update
                </button>

                <a href="dosen.php" class="btn btn-secondary">
                    Kembali
                </a>

            </form>

        </div>

    </div>

</body>

</html>