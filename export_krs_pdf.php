<?php
include "config.php";

$id = $_GET['id'] ?? '';
$s  = $_GET['s'] ?? '';

$id = mysqli_real_escape_string($conn, $id);
$s  = mysqli_real_escape_string($conn, $s);

$mhs = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT nama
    FROM mahasiswa
    WHERE id_mahasiswa='$id'
"));

$data = mysqli_query($conn, "
    SELECT
        m.kode,
        m.nama_matkul,
        m.sks,

        GROUP_CONCAT(
            DISTINCT k.kelas
            ORDER BY k.kelas
            SEPARATOR ', '
        ) AS kelas

    FROM krs k

    JOIN matkul m
        ON m.id = k.matkul_id

    WHERE k.mahasiswa_id='$id'
      AND k.semester='$s'

    GROUP BY
        k.matkul_id,
        m.kode,
        m.nama_matkul,
        m.sks

    ORDER BY m.kode
");
?>

<!DOCTYPE html>
<html>

<head>

    <title>Cetak KRS</title>

    <style>
        body {
            font-family: Arial;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td,
        th {
            border: 1px solid #000;
            padding: 8px;
        }

        th {
            text-align: center;
        }
    </style>

</head>

<body onload="window.print()">

    <h3 style="text-align:center">
        KRS MAHASISWA
    </h3>

    <p>
        Nama:
        <?= htmlspecialchars($mhs['nama']) ?>
    </p>

    <p>
        Semester:
        <?= htmlspecialchars($s) ?>
    </p>

    <table>

        <tr>
            <th>Kode</th>
            <th>Matkul</th>
            <th>SKS</th>
            <th>Kelas</th>
        </tr>

        <?php while ($d = mysqli_fetch_assoc($data)) { ?>

            <tr>

                <td>
                    <?= htmlspecialchars($d['kode']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($d['nama_matkul']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($d['sks']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($d['kelas']) ?>
                </td>

            </tr>

        <?php } ?>

    </table>

</body>

</html>