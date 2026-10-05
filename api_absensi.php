<?php

include "config.php";

date_default_timezone_set('Asia/Jakarta');
header('Content-Type: text/plain');


/* =========================================================
   1. TERIMA FINGERPRINT
   ========================================================= */

$finger_id = intval($_POST['finger_id'] ?? 0);

if ($finger_id <= 0) {
    exit("ERROR|EMPTY_FINGER");
}


/* =========================================================
   2. CARI MAHASISWA BERDASARKAN FINGERPRINT
   ========================================================= */

$mhs = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT
        id_mahasiswa,
        nim,
        nama,
        finger_id
    FROM mahasiswa
    WHERE finger_id = '$finger_id'
    LIMIT 1
"));

if (!$mhs) {
    exit("ERROR|MAHASISWA_NOT_FOUND");
}

$mahasiswa_id = $mhs['id_mahasiswa'];

$tanggal = date("Y-m-d");
$jam     = date("H:i:s");


/* =========================================================
   3. KONVERSI HARI
   ========================================================= */

$hari_eng = date("l");

$hari_map = [
    "Monday"    => "Senin",
    "Tuesday"   => "Selasa",
    "Wednesday" => "Rabu",
    "Thursday"  => "Kamis",
    "Friday"    => "Jumat",
    "Saturday"  => "Sabtu",
    "Sunday"    => "Minggu"
];

$hari = $hari_map[$hari_eng];


/* =========================================================
   4. CARI JADWAL BERDASARKAN KRS
   =========================================================

   PENTING:

   Semester mahasiswa TIDAK digunakan.

   Yang digunakan:
   - mahasiswa_id
   - krs.matkul_id
   - krs.kelas
   - krs.semester
   - jadwal.hari
   - jadwal.jam_masuk
   - jadwal.jam_selesai

   Contoh:

   Mahasiswa semester 4
   KRS mengambil MK semester 6

   Maka tetap bisa absen.
   ========================================================= */

$queryJadwal = mysqli_query($conn, "
    SELECT
        j.id AS jadwal_id,
        j.hari,
        j.jam_masuk,
        j.jam_selesai,
        j.matkul_id,
        j.dosen_id,
        j.ruangan_id,
        j.kelas,
        j.semester,

        mk.kode,
        mk.nama_matkul,
        mk.sks,

        k.semester AS semester_krs,
        k.kelas AS kelas_krs

    FROM krs k

    INNER JOIN jadwal j
        ON j.matkul_id = k.matkul_id
        AND j.kelas = k.kelas
        AND j.semester = k.semester

    INNER JOIN matkul mk
        ON mk.id = j.matkul_id

    WHERE k.mahasiswa_id = '$mahasiswa_id'

    AND j.hari = '$hari'

    AND '$jam' BETWEEN j.jam_masuk AND j.jam_selesai

    LIMIT 1
");


if (!$queryJadwal) {
    exit("ERROR|DATABASE_ERROR");
}

$jadwal = mysqli_fetch_assoc($queryJadwal);

if (!$jadwal) {
    exit("ERROR|NO_SCHEDULE");
}


/* =========================================================
   5. DATA JADWAL AKTIF
   ========================================================= */

$jadwal_id = $jadwal['jadwal_id'];

$matkul_id = $jadwal['matkul_id'];

$kelas = $jadwal['kelas_krs'];

/*
 * SEMESTER DIAMBIL DARI KRS
 *
 * BUKAN:
 * mahasiswa.semester
 */
$semester = $jadwal['semester_krs'];

$nama_matkul = $jadwal['nama_matkul'];


/* =========================================================
   6. TENTUKAN PERTEMUAN
   =========================================================

   Karena tabel pertemuan_aktif belum diberikan,
   sistem mencari nomor pertemuan berdasarkan data
   pertemuan yang sudah tersimpan.

   Jika belum ada pertemuan untuk mata kuliah + kelas +
   semester + tanggal hari ini, maka menggunakan
   nomor pertemuan berikutnya.
   ========================================================= */

$queryPertemuanTerakhir = mysqli_query($conn, "
    SELECT MAX(pertemuan) AS pertemuan_terakhir
    FROM pertemuan

    WHERE matkul_id = '$matkul_id'
    AND kelas = '$kelas'
    AND semester = '$semester'
");

if (!$queryPertemuanTerakhir) {
    exit("ERROR|PERTEMUAN_DATABASE_ERROR");
}

$dataPertemuan = mysqli_fetch_assoc($queryPertemuanTerakhir);

$pertemuanTerakhir = intval(
    $dataPertemuan['pertemuan_terakhir'] ?? 0
);


/* =========================================================
   7. CEK PERTEMUAN HARI INI
   ========================================================= */

$queryPertemuanHariIni = mysqli_query($conn, "
    SELECT
        id,
        pertemuan
    FROM pertemuan

    WHERE matkul_id = '$matkul_id'
    AND kelas = '$kelas'
    AND semester = '$semester'
    AND tanggal = '$tanggal'

    LIMIT 1
");

if (!$queryPertemuanHariIni) {
    exit("ERROR|PERTEMUAN_DATABASE_ERROR");
}

$pertemuanHariIni = mysqli_fetch_assoc($queryPertemuanHariIni);


/*
 * Jika sudah ada pertemuan hari ini,
 * gunakan nomor tersebut.
 */
if ($pertemuanHariIni) {

    $pertemuan = intval($pertemuanHariIni['pertemuan']);
} else {

    /*
     * Jika belum ada, buat nomor pertemuan berikutnya.
     */
    $pertemuan = $pertemuanTerakhir + 1;

    if ($pertemuan <= 0) {
        $pertemuan = 1;
    }

    mysqli_query($conn, "
        INSERT INTO pertemuan (
            matkul_id,
            kelas,
            semester,
            pertemuan,
            tanggal
        )
        VALUES (
            '$matkul_id',
            '$kelas',
            '$semester',
            '$pertemuan',
            '$tanggal'
        )
    ");
}


/* =========================================================
   8. CEK ABSENSI MAHASISWA HARI INI
   ========================================================= */

$queryCekAbsensi = mysqli_query($conn, "
    SELECT
        id,
        mahasiswa_id,
        matkul_id,
        semester,
        pertemuan,
        tanggal,
        jam_masuk,
        jam_selesai,
        status

    FROM absensi

    WHERE mahasiswa_id = '$mahasiswa_id'

    AND matkul_id = '$matkul_id'

    AND semester = '$semester'

    AND pertemuan = '$pertemuan'

    AND tanggal = '$tanggal'

    LIMIT 1
");

if (!$queryCekAbsensi) {
    exit("ERROR|ABSENSI_DATABASE_ERROR");
}

$cek = mysqli_fetch_assoc($queryCekAbsensi);


/* =========================================================
   9. TENTUKAN STATUS HADIR / TELAT
   ========================================================= */

$waktuMasukJadwal = strtotime($tanggal . " " . $jadwal['jam_masuk']);

$waktuFingerprint = strtotime($tanggal . " " . $jam);

$selisihMenit = max(
    0,
    ($waktuFingerprint - $waktuMasukJadwal) / 60
);

$status = ($selisihMenit > 20)
    ? "TELAT"
    : "HADIR";


/* =========================================================
   10. ABSENSI MASUK
   ========================================================= */

if (!$cek) {

    $nim = mysqli_real_escape_string(
        $conn,
        $mhs['nim']
    );

    $nama = mysqli_real_escape_string(
        $conn,
        $mhs['nama']
    );

    $nama_matkul_db = mysqli_real_escape_string(
        $conn,
        $nama_matkul
    );

    $queryInsert = mysqli_query($conn, "
        INSERT INTO absensi (
            mahasiswa_id,
            nim,
            nama,
            matkul_id,
            nama_matkul,
            semester,
            pertemuan,
            status,
            tanggal,
            finger_id,
            jam_masuk,
            jam_selesai
        )
        VALUES (
            '$mahasiswa_id',
            '$nim',
            '$nama',
            '$matkul_id',
            '$nama_matkul_db',
            '$semester',
            '$pertemuan',
            '$status',
            '$tanggal',
            '$finger_id',
            '$jam',
            NULL
        )
    ");

    if (!$queryInsert) {
        exit("ERROR|ABSENSI_GAGAL");
    }

    exit("SUCCESS|$status");
}


/* =========================================================
   11. CHECK OUT / JAM SELESAI
   ========================================================= */

if (empty($cek['jam_selesai'])) {

    $queryUpdate = mysqli_query($conn, "
        UPDATE absensi

        SET jam_selesai = '$jam'

        WHERE id = '{$cek['id']}'
    ");

    if (!$queryUpdate) {
        exit("ERROR|CHECKOUT_GAGAL");
    }

    exit("SUCCESS|SELESAI");
}


/* =========================================================
   12. SUDAH SELESAI ABSENSI
   ========================================================= */

exit("ERROR|SUDAH_ABSEN");

?>

Hasil akhirnya

Misalnya:

Mahasiswa:
Andi
Semester = 4

KRS:

Pemrograman Web
Semester KRS = 6
Kelas = 01

Jadwal:

Pemrograman Web
Semester = 6
Kelas = 01
08:00 - 09:40

Andi fingerprint jam 08:10:

Fingerprint
↓
Andi
↓
KRS Andi
↓
Pemrograman Web
Semester KRS = 6
↓
Jadwal semester 6 kelas 01
↓
COCOK ✅
↓
Absensi tersimpan
semester = 6
pertemuan = 1
status = HADIR

Jadi semester Andi yang 4 tidak menghalangi dia untuk absen di mata kuliah semester 6.

Catatan: versi ini menentukan nomor pertemuan dari tabel "pertemuan" karena struktur "pertemuan_aktif" belum kamu berikan.