<?php
include "config.php";

header('Content-Type: application/json');

$semester = $_GET['semester'] ?? '';
$kelas    = $_GET['kelas'] ?? '';

$label = [];
$data  = [];

$q = mysqli_query($conn, "
    SELECT 
        mk.id,
        mk.nama_matkul,
        COUNT(a.id) AS hadir
    FROM matkul mk
    LEFT JOIN absensi a 
        ON a.matkul_id = mk.id
        AND a.jam_selesai IS NOT NULL
    LEFT JOIN krs k
        ON k.matkul_id = mk.id
    WHERE ('$semester'='' OR k.semester='$semester')
    AND ('$kelas'='' OR k.kelas='$kelas')
    GROUP BY mk.id
    ORDER BY mk.nama_matkul ASC
");

while ($r = mysqli_fetch_assoc($q)) {

    $total = 12;
    $persen = ($total > 0) ? ($r['hadir'] / $total) * 100 : 0;

    $label[] = substr($r['nama_matkul'], 0, 12);
    $data[]  = round($persen, 1);
}

echo json_encode([
    "label" => $label,
    "data" => $data
]);
