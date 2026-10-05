<?php
include "config.php";

$data = mysqli_query($conn, "
SELECT j.*, m.kode_matkul, m.nama_matkul, m.sks, m.dosen, r.nama_ruangan
FROM jadwal j
LEFT JOIN matkul m ON j.matkul_id=m.id
LEFT JOIN ruangan r ON j.ruangan_id=r.id
");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Cetak Jadwal</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
            padding: 8px;
        }

        h3 {
            text-align: center;
        }
    </style>
</head>

<body onload="window.print()">

    <h3>Data Jadwal Kuliah</h3>

    <table>
        <tr>
            <th>No</th>
            <th>Hari</th>
            <th>Jam</th>
            <th>Kode</th>
            <th>SKS</th>
            <th>Mata Kuliah</th>
            <th>Dosen</th>
            <th>Ruang</th>
        </tr>

        <?php $no = 1;
        while ($d = mysqli_fetch_assoc($data)) { ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $d['hari']; ?></td>
                <td><?= $d['jam_masuk']; ?> - <?= $d['jam_selesai']; ?></td>
                <td><?= $d['kode_matkul']; ?></td>
                <td><?= $d['sks']; ?></td>
                <td><?= $d['nama_matkul']; ?></td>
                <td><?= $d['dosen']; ?></td>
                <td><?= $d['nama_ruangan']; ?></td>
            </tr>
        <?php } ?>

    </table>

</body>

</html>