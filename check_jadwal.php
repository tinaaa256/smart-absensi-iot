<?php

include "config.php";

date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json; charset=utf-8');


/* ===============================
   KONVERSI HARI
================================ */

$hariInggris = date('l');

$hariMap = [
    "Monday"    => "Senin",
    "Tuesday"   => "Selasa",
    "Wednesday" => "Rabu",
    "Thursday"  => "Kamis",
    "Friday"    => "Jumat",
    "Saturday"  => "Sabtu",
    "Sunday"    => "Minggu"
];

$hari = $hariMap[$hariInggris] ?? '';

$jam = date('H:i:s');


if ($hari == '') {

    echo json_encode([
        "status" => "ERROR",
        "message" => "HARI_TIDAK_VALID"
    ]);

    exit;
}


/* ===============================
   CARI JADWAL AKTIF
================================ */

$sql = "

    SELECT
        j.id AS jadwal_id,
        j.matkul_id,
        j.hari,
        j.jam_masuk,
        j.jam_selesai,
        j.kelas,
        j.semester,

        m.kode,
        m.nama_matkul,
        m.sks

    FROM jadwal j

    INNER JOIN matkul m
        ON m.id = j.matkul_id

    WHERE j.hari = '$hari'

      AND '$jam' >= j.jam_masuk

      AND '$jam' <= j.jam_selesai

    ORDER BY j.jam_masuk ASC

    LIMIT 1

";


$q = mysqli_query($conn, $sql);


if (!$q) {

    echo json_encode([
        "status" => "ERROR",
        "message" => "QUERY_ERROR",
        "detail" => mysqli_error($conn)
    ]);

    exit;
}


/* ===============================
   TIDAK ADA JADWAL
================================ */

if (mysqli_num_rows($q) == 0) {

    echo json_encode([
        "status" => "LOCK",
        "hari" => $hari,
        "jam" => $jam
    ]);

    exit;
}


/* ===============================
   JADWAL AKTIF
================================ */

$d = mysqli_fetch_assoc($q);


echo json_encode([

    "status" => "ACTIVE",

    "jadwal_id" => (int)$d['jadwal_id'],

    "matkul_id" => (int)$d['matkul_id'],

    "kode" => $d['kode'],

    "nama_matkul" => $d['nama_matkul'],

    "sks" => (int)$d['sks'],

    "hari" => $d['hari'],

    "jam_masuk" => $d['jam_masuk'],

    "jam_selesai" => $d['jam_selesai'],

    "kelas" => $d['kelas'],

    "semester" => (int)$d['semester']

]);
