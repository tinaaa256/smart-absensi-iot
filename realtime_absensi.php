<?php

include "config.php";

date_default_timezone_set('Asia/Jakarta');


/* =========================================================
   AMBIL DATA ABSENSI HARI INI
   ========================================================= */

$q = mysqli_query($conn, "

    SELECT
        a.id,
        a.nim,
        a.nama,
        m.nama_matkul,
        a.pertemuan,
        a.status,
        a.tanggal,
        a.jam_masuk,
        a.jam_selesai

    FROM absensi a

    LEFT JOIN matkul m
        ON m.id = a.matkul_id

    WHERE a.tanggal = CURDATE()

    ORDER BY a.id DESC

    LIMIT 50

");


/* =========================================================
   TAMPILKAN DATA
   ========================================================= */

$no = 1;


if ($q && mysqli_num_rows($q) > 0) {

    while ($row = mysqli_fetch_assoc($q)) {

        $status = htmlspecialchars(
            $row['status'] ?? ''
        );

        $nim = htmlspecialchars(
            $row['nim'] ?? ''
        );

        $nama = htmlspecialchars(
            $row['nama'] ?? ''
        );

        $nama_matkul = htmlspecialchars(
            $row['nama_matkul'] ?? ''
        );

        $pertemuan = htmlspecialchars(
            $row['pertemuan'] ?? ''
        );

        $tanggal = htmlspecialchars(
            $row['tanggal'] ?? ''
        );

        $jam_masuk = htmlspecialchars(
            $row['jam_masuk'] ?? ''
        );

        $jam_selesai = htmlspecialchars(
            $row['jam_selesai'] ?? ''
        );


        echo "
        <tr>

            <td>{$no}</td>

            <td>{$nim}</td>

            <td>{$nama}</td>

            <td>{$nama_matkul}</td>

            <td>{$pertemuan}</td>

            <td>
                <span class='badge bg-success'>
                    {$status}
                </span>
            </td>

            <td>{$tanggal}</td>

            <td>{$jam_masuk}</td>

            <td>
                " . ($jam_selesai ?: '-') . "
            </td>

        </tr>
        ";

        $no++;
    }
} else {

    echo "
    <tr>
        <td colspan='9' class='text-center text-muted'>
            Belum ada mahasiswa yang absen hari ini
        </td>
    </tr>
    ";
}
