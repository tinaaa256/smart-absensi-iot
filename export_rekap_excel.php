<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'dosen') {
    exit("Akses ditolak.");
}

$nama = $_SESSION['nama'];

$matkul   = $_GET['matkul'] ?? '';
$kelas    = $_GET['kelas'] ?? '';
$semester = $_GET['semester'] ?? '';

$where = " WHERE mk.dosen='$nama' ";

if ($matkul != "") {
    $where .= " AND mk.id='$matkul'";
}

if ($kelas != "") {
    $where .= " AND k.kelas='$kelas'";
}

if ($semester != "") {
    $where .= " AND k.semester='$semester'";
}

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Rekap_Absensi_" . date("Ymd_His") . ".xls");

?>

<table border="1">

    <tr style="background:#0d6efd;color:white;">
        <th>No</th>
        <th>NIM</th>
        <th>Nama</th>
        <th>Mata Kuliah</th>
        <th>Kelas</th>
        <th>Semester</th>
        <th>Hadir</th>
        <th>Total Pertemuan</th>
        <th>Persentase</th>
        <th>Status</th>
    </tr>

    <?php

    $no = 1;

    $data = mysqli_query($conn, "
SELECT
m.id_mahasiswa,
m.nim,
m.nama,
k.kelas,
k.semester,
mk.id AS matkul_id,
mk.nama_matkul

FROM krs k

JOIN mahasiswa m
ON m.id_mahasiswa=k.mahasiswa_id

JOIN matkul mk
ON mk.id=k.matkul_id

$where

ORDER BY
mk.nama_matkul,
m.nama
");

    while ($d = mysqli_fetch_assoc($data)) {

        $idmhs = $d['id_mahasiswa'];
        $idmatkul = $d['matkul_id'];

        $qHadir = mysqli_query($conn, "
SELECT COUNT(*) AS hadir
FROM absensi
WHERE mahasiswa_id='$idmhs'
AND matkul_id='$idmatkul'
AND status='HADIR'
");

        $hadir = mysqli_fetch_assoc($qHadir)['hadir'];

        $qPertemuan = mysqli_query($conn, "
SELECT COUNT(*) AS total
FROM pertemuan
WHERE matkul_id='$idmatkul'
AND kelas='" . $d['kelas'] . "'
AND semester='" . $d['semester'] . "'
");

        $total = mysqli_fetch_assoc($qPertemuan)['total'];

        $persen = 0;

        if ($total > 0) {
            $persen = ($hadir / $total) * 100;
        }

        $status = ($persen >= 75)
            ? "Layak Ujian"
            : "Tidak Layak";

    ?>

        <tr>

            <td><?= $no++; ?></td>

            <td><?= $d['nim']; ?></td>

            <td><?= $d['nama']; ?></td>

            <td><?= $d['nama_matkul']; ?></td>

            <td><?= $d['kelas']; ?></td>

            <td><?= $d['semester']; ?></td>

            <td><?= $hadir; ?></td>

            <td><?= $total; ?></td>

            <td><?= number_format($persen, 2); ?>%</td>

            <td><?= $status; ?></td>

        </tr>

    <?php } ?>

</table>