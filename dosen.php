<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['simpan'])) {

    $username = $_POST['username'];
    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    mysqli_query($conn, "
    INSERT INTO users(username,nama,email,password,role)
    VALUES(
    '$username',
    '$nama',
    '$email',
    '$password',
    'dosen')
    ");

    echo "<script>
    alert('Dosen berhasil ditambahkan');
    location='dosen.php';
    </script>";
}

$data = mysqli_query($conn, "
SELECT *
FROM users
WHERE role='dosen'
ORDER BY nama ASC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Data Dosen</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background: #f1f5f9;
            font-family: Arial;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background: #0d6efd;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
        }

        .sidebar h4 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            padding: 14px 22px;
            color: white;
            text-decoration: none;
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, .15);
            padding-left: 28px;
        }

        .main {
            margin-left: 250px;
            padding: 25px;
        }

        .topbar {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            margin-bottom: 20px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
        }
    </style>

</head>

<body>

    <div class="sidebar">

        <h4>Smart Absensi</h4>

        <a href="dashboard_admin.php"><i class="bi bi-speedometer2"></i> Dashboard</a>

        <a href="mahasiswa.php"><i class="bi bi-people-fill"></i> Mahasiswa</a>

        <a href="matkul.php"><i class="bi bi-book-fill"></i> Mata Kuliah</a>

        <a href="jadwal.php"><i class="bi bi-calendar-event"></i> Jadwal Kuliah</a>

        <a href="krs.php"><i class="bi bi-journal-check"></i> KRS</a>

        <a href="fingerprint_queue.php"><i class="bi bi-fingerprint"></i> Fingerprint</a>

        <a href="absensi.php"><i class="bi bi-clipboard-check"></i> Absensi</a>

        <a href="ruangan.php"><i class="bi bi-building"></i> Ruangan</a>

        <a href="dosen.php"><i class="bi bi-person-workspace"></i> Dosen</a>

        <a href="laporan.php"><i class="bi bi-file-earmark-text"></i> Laporan</a>

        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>

    </div>

    <div class="main">

        <div class="topbar">

            <h4>Data Dosen</h4>

        </div>

        <div class="card p-3">

            <h5>Tambah Dosen</h5>

            <form method="POST">

                <div class="row">

                    <div class="col-md-3">

                        <input
                            type="text"
                            name="username"
                            class="form-control"
                            placeholder="Username"
                            required>

                    </div>

                    <div class="col-md-3">

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            placeholder="Nama Dosen"
                            required>

                    </div>

                    <div class="col-md-3">

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="Email"
                            required>

                    </div>

                    <div class="col-md-2">

                        <input
                            type="text"
                            name="password"
                            class="form-control"
                            placeholder="Password"
                            required>

                    </div>

                    <div class="col-md-1">

                        <button
                            class="btn btn-primary w-100"
                            name="simpan">

                            Simpan

                        </button>

                    </div>

                </div>

            </form>

            <hr>
            <h5 class="mb-3">Daftar Dosen</h5>

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead class="table-primary">

                        <tr>

                            <th width="60">No</th>
                            <th>Username</th>
                            <th>Nama Dosen</th>
                            <th>Email</th>
                            <th width="170">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        $no = 1;

                        while ($d = mysqli_fetch_assoc($data)) {

                        ?>

                            <tr>

                                <td><?= $no++; ?></td>

                                <td><?= $d['username']; ?></td>

                                <td><?= $d['nama']; ?></td>

                                <td><?= $d['email']; ?></td>

                                <td>

                                    <a
                                        href="edit_dosen.php?id=<?= $d['id']; ?>"
                                        class="btn btn-warning btn-sm">

                                        <i class="bi bi-pencil-square"></i>
                                        Edit

                                    </a>

                                    <a
                                        href="hapus_dosen.php?id=<?= $d['id']; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus dosen ini?')">

                                        <i class="bi bi-trash"></i>
                                        Hapus

                                    </a>

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>
</body>

</html>