<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

/* AMBIL USER */
$user_id = $_SESSION['id'];

$get = mysqli_query($conn, "
    SELECT mahasiswa_id 
    FROM users 
    WHERE id='$user_id'
");

$data = mysqli_fetch_assoc($get);
$mahasiswa_id = $data['mahasiswa_id'];

/* NAMA */
$getNama = mysqli_query($conn, "
    SELECT nama 
    FROM mahasiswa 
    WHERE id_mahasiswa='$mahasiswa_id'
");

$nama = mysqli_fetch_assoc($getNama)['nama'] ?? 'Mahasiswa';

/* DATA KRS */
$krs = mysqli_query($conn, "
SELECT k.*, m.kode, m.nama_matkul, m.sks
FROM krs k
JOIN matkul m ON k.matkul_id = m.id
WHERE k.mahasiswa_id='$mahasiswa_id'
ORDER BY k.id DESC
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Cetak KRS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial;
            padding: 20px;
        }

        h3 {
            text-align: center;
        }

        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>

<body>

    <h3>KARTU RENCANA STUDI</h3>
    <p class="text-center">Nama: <b><?= $nama ?></b></p>

    <table class="table table-bordered">
        <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Mata Kuliah</th>
            <th>SKS</th>
            <th>Semester</th>
            <th>Kelas</th>
        </tr>

        <?php $no = 1;
        while ($d = mysqli_fetch_assoc($krs)) { ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $d['kode'] ?></td>
                <td><?= $d['nama_matkul'] ?></td>
                <td><?= $d['sks'] ?></td>
                <td><?= $d['semester'] ?></td>
                <td><?= $d['kelas'] ?></td>
            </tr>
        <?php } ?>
    </table>

    <button onclick="window.print()" class="btn btn-success">
        Print / Cetak
    </button>

</body>

</html>