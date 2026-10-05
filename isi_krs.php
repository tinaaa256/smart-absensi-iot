<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

/* =====================================================
   USER LOGIN
===================================================== */

$user_id = (int)($_SESSION['id'] ?? 0);

$qUser = mysqli_query($conn, "
    SELECT mahasiswa_id
    FROM users
    WHERE id='$user_id'
    LIMIT 1
");

if (!$qUser) {
    die("Error users: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($qUser);

$mahasiswa_id = (int)($user['mahasiswa_id'] ?? 0);

if ($mahasiswa_id <= 0) {
    die("Data mahasiswa tidak ditemukan.");
}


/* =====================================================
   DATA MAHASISWA
===================================================== */

$qMhs = mysqli_query($conn, "
    SELECT
        id_mahasiswa,
        nim,
        nama,
        semester,
        kelas,
        prodi
    FROM mahasiswa
    WHERE id_mahasiswa='$mahasiswa_id'
    LIMIT 1
");

if (!$qMhs) {
    die("Error mahasiswa: " . mysqli_error($conn));
}

$mhs = mysqli_fetch_assoc($qMhs);

if (!$mhs) {
    die("Data mahasiswa tidak ditemukan.");
}

$namaMhs = $mhs['nama'] ?? 'Mahasiswa';

$semesterMahasiswa = (int)($mhs['semester'] ?? 1);


/* =====================================================
   FILTER SEMESTER
===================================================== */

$semesterFilter = isset($_GET['semester'])
    ? (int)$_GET['semester']
    : $semesterMahasiswa;

if ($semesterFilter < 1 || $semesterFilter > 8) {
    $semesterFilter = $semesterMahasiswa;
}


/* =====================================================
   SIMPAN KRS
===================================================== */

if (isset($_POST['simpan'])) {

    $semesterSimpan = (int)($_POST['semester_filter'] ?? 0);

    if ($semesterSimpan < 1 || $semesterSimpan > 8) {
        $semesterSimpan = $semesterFilter;
    }

    if (
        !isset($_POST['matkul']) ||
        !is_array($_POST['matkul']) ||
        count($_POST['matkul']) == 0
    ) {

        echo "<script>
            alert('Silakan pilih minimal satu mata kuliah.');
            window.location='isi_krs.php?kelola=1&semester=$semesterSimpan';
        </script>";

        exit;
    }


    $berhasil = 0;
    $sudahAda = 0;
    $tidakValid = 0;


    foreach ($_POST['matkul'] as $matkul_id) {

        $matkul_id = (int)$matkul_id;

        if ($matkul_id <= 0) {
            continue;
        }


        /* =================================================
           KELAS YANG DIPILIH
        ================================================= */

        $kelasDipilih = '';

        if (
            isset($_POST['kelas'][$matkul_id]) &&
            is_string($_POST['kelas'][$matkul_id])
        ) {
            $kelasDipilih = trim($_POST['kelas'][$matkul_id]);
        }

        if ($kelasDipilih == '') {
            $tidakValid++;
            continue;
        }

        $kelasEsc = mysqli_real_escape_string(
            $conn,
            $kelasDipilih
        );


        /* =================================================
           CEK MATKUL + SEMESTER + KELAS ADA DI JADWAL
        ================================================= */

        $cekJadwal = mysqli_query($conn, "
            SELECT id
            FROM jadwal
            WHERE matkul_id='$matkul_id'
              AND semester='$semesterSimpan'
              AND kelas='$kelasEsc'
            LIMIT 1
        ");

        if (!$cekJadwal) {
            die("Error validasi jadwal: "
                . mysqli_error($conn));
        }

        if (mysqli_num_rows($cekJadwal) == 0) {
            $tidakValid++;
            continue;
        }


        /* =================================================
           CEK KRS
           
           PENTING:
           TIDAK ADA CEK SEMESTER.
           
           Artinya:
           Semester 1 ambil Matkul A
           Semester 2 Matkul A muncul lagi
           => TETAP SUDAH DIAMBIL
        ================================================= */

        $cekKrs = mysqli_query($conn, "
            SELECT id
            FROM krs
            WHERE mahasiswa_id='$mahasiswa_id'
              AND matkul_id='$matkul_id'
            LIMIT 1
        ");

        if (!$cekKrs) {
            die("Error cek KRS: "
                . mysqli_error($conn));
        }

        if (mysqli_num_rows($cekKrs) > 0) {

            $sudahAda++;
            continue;
        }


        /* =================================================
           INSERT KRS
        ================================================= */

        $insert = mysqli_query($conn, "
            INSERT INTO krs
            (
                mahasiswa_id,
                matkul_id,
                semester,
                kelas
            )
            VALUES
            (
                '$mahasiswa_id',
                '$matkul_id',
                '$semesterSimpan',
                '$kelasEsc'
            )
        ");

        if (!$insert) {
            die("Gagal menyimpan KRS: "
                . mysqli_error($conn));
        }

        $berhasil++;
    }


    /* =================================================
       PESAN
    ================================================= */

    if ($berhasil > 0) {

        $pesan = "$berhasil mata kuliah berhasil disimpan.";

        if ($sudahAda > 0) {
            $pesan .= " $sudahAda mata kuliah sudah pernah diambil.";
        }

        if ($tidakValid > 0) {
            $pesan .= " $tidakValid pilihan tidak valid.";
        }
    } elseif ($sudahAda > 0) {

        $pesan =
            "Mata kuliah yang dipilih sudah pernah diambil.";
    } else {

        $pesan =
            "Tidak ada KRS yang berhasil disimpan.";
    }


    echo "<script>
        alert(" . json_encode($pesan) . ");
        window.location='isi_krs.php';
    </script>";

    exit;
}


/* =====================================================
   HAPUS KRS
===================================================== */

if (isset($_GET['hapus'])) {

    $id = (int)$_GET['hapus'];

    if ($id > 0) {

        $hapus = mysqli_query($conn, "
            DELETE FROM krs
            WHERE id='$id'
              AND mahasiswa_id='$mahasiswa_id'
            LIMIT 1
        ");

        if (!$hapus) {
            die("Gagal menghapus KRS: "
                . mysqli_error($conn));
        }
    }

    header("Location: isi_krs.php");
    exit;
}


/* =====================================================
   AMBIL SEMUA KRS
===================================================== */

$krs = mysqli_query($conn, "
    SELECT
        k.id,
        k.matkul_id,
        k.semester,
        k.kelas,
        m.kode,
        m.nama_matkul,
        m.sks
    FROM krs k

    INNER JOIN matkul m
        ON m.id = k.matkul_id

    WHERE k.mahasiswa_id='$mahasiswa_id'

    ORDER BY
        k.semester ASC,
        m.nama_matkul ASC
");

if (!$krs) {
    die("Error KRS: " . mysqli_error($conn));
}


/* =====================================================
   MATKUL YANG SUDAH PERNAH DIAMBIL
   TIDAK PEDULI SEMESTER
===================================================== */

$krsSudahAda = [];

while ($row = mysqli_fetch_assoc($krs)) {

    $krsSudahAda[(int)$row['matkul_id']] = true;
}


/* =====================================================
   QUERY KRS ULANG UNTUK TABEL
===================================================== */

$krs = mysqli_query($conn, "
    SELECT
        k.id,
        k.matkul_id,
        k.semester,
        k.kelas,
        m.kode,
        m.nama_matkul,
        m.sks
    FROM krs k

    INNER JOIN matkul m
        ON m.id = k.matkul_id

    WHERE k.mahasiswa_id='$mahasiswa_id'

    ORDER BY
        k.semester ASC,
        m.nama_matkul ASC
");

if (!$krs) {
    die("Error KRS: " . mysqli_error($conn));
}


/* =====================================================
   TOTAL MATKUL
===================================================== */

$qTotal = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM krs
    WHERE mahasiswa_id='$mahasiswa_id'
");

$dTotal = mysqli_fetch_assoc($qTotal);

$totalKrs = (int)($dTotal['total'] ?? 0);


/* =====================================================
   TOTAL SKS
===================================================== */

$qSks = mysqli_query($conn, "
    SELECT COALESCE(SUM(m.sks),0) AS total_sks
    FROM krs k

    INNER JOIN matkul m
        ON m.id = k.matkul_id

    WHERE k.mahasiswa_id='$mahasiswa_id'
");

$dSks = mysqli_fetch_assoc($qSks);

$totalSks = (int)($dSks['total_sks'] ?? 0);


/* =====================================================
   DATA MATKUL BERDASARKAN SEMESTER
===================================================== */

$matkulData = [];

$qMatkul = mysqli_query($conn, "
    SELECT
        m.id,
        m.kode,
        m.nama_matkul,
        m.sks,
        j.semester,

        GROUP_CONCAT(
            DISTINCT j.kelas
            ORDER BY j.kelas ASC
            SEPARATOR ','
        ) AS daftar_kelas

    FROM jadwal j

    INNER JOIN matkul m
        ON m.id = j.matkul_id

    WHERE j.semester='$semesterFilter'

    GROUP BY
        m.id,
        m.kode,
        m.nama_matkul,
        m.sks,
        j.semester

    ORDER BY m.nama_matkul ASC
");

if (!$qMatkul) {
    die("Error mata kuliah: "
        . mysqli_error($conn));
}

while ($row = mysqli_fetch_assoc($qMatkul)) {
    $matkulData[] = $row;
}


/* =====================================================
   MODE KELOLA
===================================================== */

$kelola =
    isset($_GET['kelola']) &&
    $_GET['kelola'] == '1';

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>KRS Mahasiswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f1f5f9;
            font-family: Arial, Helvetica, sans-serif;
            color: #1e293b;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: #0d6efd;
            padding: 22px 18px;
            z-index: 1000;
        }

        .sidebar h4 {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            gap: 9px;
            color: white;
            text-decoration: none;
            padding: 13px 15px;
            margin-bottom: 8px;
            border-radius: 8px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255, 255, 255, .2);
        }

        .main {
            margin-left: 250px;
            padding: 25px;
            min-height: 100vh;
        }

        .box {
            background: white;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
        }

        .header-title {
            font-size: 24px;
            font-weight: bold;
        }

        .header-subtitle {
            color: #64748b;
        }

        .krs-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .summary-icon {
            width: 58px;
            height: 58px;
            border-radius: 12px;
            background: #eff6ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .summary-number {
            font-size: 27px;
            font-weight: bold;
        }

        .summary-text {
            color: #64748b;
            font-size: 14px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: #0d6efd;
            color: white;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .checkbox-pilih {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .kelas-wrapper {
            position: relative;
            display: inline-block;
            min-width: 55px;
        }

        .kelas-pilihan {
            display: block;
            width: 42px;
            text-align: center;
            padding: 4px 6px;
            border-radius: 6px;
            background: #0d6efd;
            color: white;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 2px;
        }

        .kelas-pilihan:hover {
            background: #0b5ed7;
        }

        .kelas-lain {
            display: none;
        }

        .kelas-lain.show {
            display: block;
        }

        .kelas-option {
            display: block;
            width: 42px;
            text-align: center;
            padding: 4px 6px;
            border-radius: 6px;
            background: #e2e8f0;
            color: #334155;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 2px;
        }

        .kelas-option:hover {
            background: #cbd5e1;
        }

        .kelas-option.selected {
            background: #0d6efd;
            color: white;
        }

        .empty {
            text-align: center;
            padding: 40px 20px;
            color: #64748b;
        }

        .empty i {
            font-size: 45px;
            color: #94a3b8;
        }

        .menu-btn {
            display: none;
        }

        @media(max-width:768px) {

            .sidebar {
                left: -270px;
                transition: .3s;
                box-shadow: 3px 0 15px rgba(0, 0, 0, .2);
            }

            .sidebar.active {
                left: 0;
            }

            .menu-btn {
                display: flex;
                position: fixed;
                top: 15px;
                left: 15px;
                width: 45px;
                height: 45px;
                background: #0d6efd;
                color: white;
                align-items: center;
                justify-content: center;
                border-radius: 9px;
                z-index: 1100;
                font-size: 23px;
            }

            .main {
                margin-left: 0;
                padding: 75px 12px 20px;
            }

            .krs-summary {
                align-items: flex-start;
                flex-direction: column;
            }

            .header-title {
                font-size: 20px;
            }
        }
    </style>

</head>

<body>


    <div class="menu-btn"
        onclick="toggleMenu()">

        <i class="bi bi-list"></i>

    </div>


    <div class="sidebar"
        id="sidebar">

        <h4>

            <i class="bi bi-mortarboard-fill"></i>

            Mahasiswa

        </h4>

        <a href="dashboard_mahasiswa.php">

            <i class="bi bi-speedometer2"></i>

            Dashboard

        </a>

        <a href="isi_krs.php"
            class="active">

            <i class="bi bi-journal-check"></i>

            KRS

        </a>

        <a href="jadwal_mahasiswa.php">

            <i class="bi bi-calendar-event"></i>

            Jadwal

        </a>

        <a href="profil_mahasiswa.php">

            <i class="bi bi-person-circle"></i>

            Profil

        </a>

        <a href="logout.php"
            onclick="return confirm('Yakin ingin logout?')">

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

    </div>


    <div class="main">


        <div class="box">

            <div class="header-title">

                📚 Kartu Rencana Studi

            </div>

            <div class="header-subtitle mt-1">

                Halo,
                <strong>
                    <?= htmlspecialchars($namaMhs) ?>
                </strong>
                👋

            </div>

        </div>


        <div class="box">

            <div class="krs-summary">

                <div class="d-flex align-items-center gap-3">

                    <div class="summary-icon">

                        <i class="bi bi-journal-check"></i>

                    </div>

                    <div>

                        <div class="fw-bold">
                            KRS Saya
                        </div>

                        <div>

                            <span class="summary-number">
                                <?= $totalKrs ?>
                            </span>

                            <span class="summary-text">
                                Mata Kuliah
                            </span>

                            <span class="mx-2 text-muted">
                                •
                            </span>

                            <strong>
                                <?= $totalSks ?>
                            </strong>

                            <span class="summary-text">
                                SKS
                            </span>

                        </div>

                    </div>

                </div>


                <a
                    href="isi_krs.php?kelola=1&semester=<?= $semesterFilter ?>"
                    class="btn btn-primary">

                    <i class="bi bi-plus-circle"></i>

                    Kelola KRS

                </a>

            </div>

        </div>


        <?php if ($kelola) { ?>


            <div class="box">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h5>

                            <i class="bi bi-pencil-square text-primary"></i>

                            Kelola KRS

                        </h5>

                        <small class="text-muted">

                            Mata kuliah yang pernah diambil tetap dianggap sudah diambil,
                            walaupun berbeda semester.

                        </small>

                    </div>

                    <a href="isi_krs.php"
                        class="btn btn-outline-secondary btn-sm">

                        <i class="bi bi-x-lg"></i>

                        Tutup

                    </a>

                </div>


                <form method="GET"
                    action="isi_krs.php"
                    class="mb-4">

                    <input
                        type="hidden"
                        name="kelola"
                        value="1">

                    <label class="fw-semibold mb-2">

                        Semester

                    </label>

                    <select
                        name="semester"
                        class="form-select"
                        onchange="this.form.submit()">

                        <?php for ($i = 1; $i <= 8; $i++) { ?>

                            <option
                                value="<?= $i ?>"
                                <?= $semesterFilter == $i ? 'selected' : '' ?>>

                                Semester <?= $i ?>

                            </option>

                        <?php } ?>

                    </select>

                </form>


                <form method="POST"
                    id="formKrs">

                    <input
                        type="hidden"
                        name="semester_filter"
                        value="<?= $semesterFilter ?>">


                    <div class="table-responsive">

                        <table class="table table-bordered table-hover">

                            <thead>

                                <tr>

                                    <th class="text-center">
                                        Pilih
                                    </th>

                                    <th>
                                        Kode
                                    </th>

                                    <th>
                                        Mata Kuliah
                                    </th>

                                    <th class="text-center">
                                        SKS
                                    </th>

                                    <th class="text-center">
                                        Kelas
                                    </th>

                                </tr>

                            </thead>

                            <tbody>


                                <?php if (count($matkulData) > 0) { ?>


                                    <?php foreach ($matkulData as $m) { ?>

                                        <?php

                                        $matkulId = (int)$m['id'];

                                        /*
                     * PENTING:
                     * Tidak peduli semester.
                     */
                                        $sudahAda =
                                            isset($krsSudahAda[$matkulId]);


                                        $kelasList = [];

                                        if (!empty($m['daftar_kelas'])) {

                                            foreach (
                                                explode(',', $m['daftar_kelas'])
                                                as $kelas
                                            ) {

                                                $kelas = trim($kelas);

                                                if ($kelas !== '') {
                                                    $kelasList[] = $kelas;
                                                }
                                            }
                                        }

                                        $kelasList = array_values(
                                            array_unique($kelasList)
                                        );

                                        ?>


                                        <tr>


                                            <td class="text-center">

                                                <?php if ($sudahAda) { ?>

                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input checkbox-pilih"
                                                        checked
                                                        disabled>

                                                <?php } else { ?>

                                                    <input
                                                        type="checkbox"
                                                        class="form-check-input checkbox-pilih matkul-check"
                                                        name="matkul[]"
                                                        value="<?= $matkulId ?>">

                                                <?php } ?>

                                            </td>


                                            <td>

                                                <strong>
                                                    <?= htmlspecialchars($m['kode']) ?>
                                                </strong>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $m['nama_matkul']
                                                ) ?>

                                                <?php if ($sudahAda) { ?>

                                                    <br>

                                                    <small class="text-success fw-semibold">

                                                        <i class="bi bi-check-circle"></i>

                                                        Sudah pernah diambil

                                                    </small>

                                                <?php } ?>

                                            </td>


                                            <td class="text-center">

                                                <?= (int)$m['sks'] ?>

                                            </td>


                                            <td class="text-center">


                                                <?php if (!$sudahAda && count($kelasList) > 0) { ?>


                                                    <div
                                                        class="kelas-wrapper"
                                                        data-matkul="<?= $matkulId ?>">


                                                        <?php
                                                        $kelasPertama = $kelasList[0];
                                                        ?>


                                                        <input
                                                            type="hidden"
                                                            name="kelas[<?= $matkulId ?>]"
                                                            value="<?= htmlspecialchars($kelasPertama) ?>"
                                                            class="kelas-input">


                                                        <span
                                                            class="kelas-pilihan"
                                                            onclick="pilihKelas(
                                    <?= $matkulId ?>,
                                    '<?= htmlspecialchars(
                                                        $kelasPertama,
                                                        ENT_QUOTES
                                                    ) ?>'
                                )">

                                                            <?= htmlspecialchars(
                                                                $kelasPertama
                                                            ) ?>

                                                        </span>


                                                        <?php if (count($kelasList) > 1) { ?>

                                                            <div class="kelas-lain">

                                                                <?php
                                                                for (
                                                                    $x = 1;
                                                                    $x < count($kelasList);
                                                                    $x++
                                                                ) {

                                                                    $kelas = $kelasList[$x];
                                                                ?>

                                                                    <span
                                                                        class="kelas-option"
                                                                        onclick="pilihKelas(
                                                <?= $matkulId ?>,
                                                '<?= htmlspecialchars(
                                                                        $kelas,
                                                                        ENT_QUOTES
                                                                    ) ?>'
                                            )">

                                                                        <?= htmlspecialchars($kelas) ?>

                                                                    </span>

                                                                <?php } ?>

                                                            </div>

                                                        <?php } ?>

                                                    </div>


                                                <?php } elseif ($sudahAda) { ?>


                                                    <span class="badge bg-success">

                                                        Sudah diambil

                                                    </span>


                                                <?php } else { ?>


                                                    <span class="text-muted">
                                                        -
                                                    </span>


                                                <?php } ?>


                                            </td>

                                        </tr>


                                    <?php } ?>


                                <?php } else { ?>


                                    <tr>

                                        <td colspan="5">

                                            <div class="empty">

                                                <i class="bi bi-journal-x"></i>

                                                <p class="mt-2">

                                                    Belum ada mata kuliah.

                                                </p>

                                                <small>

                                                    Belum ada jadwal
                                                    Semester <?= $semesterFilter ?>.

                                                </small>

                                            </div>

                                        </td>

                                    </tr>


                                <?php } ?>


                            </tbody>

                        </table>

                    </div>


                    <?php if (count($matkulData) > 0) { ?>

                        <button
                            type="submit"
                            name="simpan"
                            class="btn btn-primary w-100 mt-3">

                            <i class="bi bi-check-circle"></i>

                            Simpan KRS

                        </button>

                    <?php } ?>


                </form>

            </div>


        <?php } ?>


        <div class="box">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h5 class="mb-1">

                        <i class="bi bi-journal-check text-primary"></i>

                        KRS Saya

                    </h5>

                    <small class="text-muted">

                        Semua mata kuliah yang pernah diambil.

                    </small>

                </div>


                <a
                    href="cetak_krs.php"
                    target="_blank"
                    class="btn btn-dark btn-sm">

                    <i class="bi bi-printer"></i>

                    Cetak

                </a>

            </div>


            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead>

                        <tr>

                            <th class="text-center">
                                No
                            </th>

                            <th>
                                Kode
                            </th>

                            <th>
                                Mata Kuliah
                            </th>

                            <th class="text-center">
                                SKS
                            </th>

                            <th class="text-center">
                                Semester
                            </th>

                            <th class="text-center">
                                Kelas
                            </th>

                            <th class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>

                    <tbody>


                        <?php

                        $no = 1;

                        if (mysqli_num_rows($krs) > 0) {

                            while ($d = mysqli_fetch_assoc($krs)) {

                        ?>


                                <tr>

                                    <td class="text-center">

                                        <?= $no++ ?>

                                    </td>


                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $d['kode']
                                            ) ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $d['nama_matkul']
                                        ) ?>

                                    </td>


                                    <td class="text-center">

                                        <?= (int)$d['sks'] ?>

                                    </td>


                                    <td class="text-center">

                                        <span class="badge bg-secondary">

                                            Semester
                                            <?= (int)$d['semester'] ?>

                                        </span>

                                    </td>


                                    <td class="text-center">

                                        <span class="badge bg-primary">

                                            <?= htmlspecialchars(
                                                $d['kelas'] ?: '-'
                                            ) ?>

                                        </span>

                                    </td>


                                    <td class="text-center">

                                        <a
                                            href="isi_krs.php?hapus=<?= (int)$d['id'] ?>"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm(
                            'Yakin ingin menghapus mata kuliah ini dari KRS?'
                        );">

                                            <i class="bi bi-trash"></i>

                                        </a>

                                    </td>

                                </tr>


                            <?php

                            }
                        } else {

                            ?>


                            <tr>

                                <td colspan="7">

                                    <div class="empty">

                                        <i class="bi bi-journal-x"></i>

                                        <p class="mt-2 mb-0">

                                            Belum ada mata kuliah
                                            yang diambil.

                                        </p>

                                    </div>

                                </td>

                            </tr>


                        <?php } ?>


                    </tbody>

                </table>

            </div>

        </div>


    </div>


    <script>
        function toggleMenu() {

            document
                .getElementById("sidebar")
                .classList.toggle("active");

        }


        function pilihKelas(matkulId, kelas) {

            const wrapper = document.querySelector(
                '.kelas-wrapper[data-matkul="' + matkulId + '"]'
            );

            if (!wrapper) {
                return;
            }

            const input =
                wrapper.querySelector(".kelas-input");

            const utama =
                wrapper.querySelector(".kelas-pilihan");

            const lainnya =
                wrapper.querySelector(".kelas-lain");


            input.value = kelas;

            utama.innerText = kelas;


            if (lainnya) {

                lainnya.classList.toggle("show");

            }


            wrapper
                .querySelectorAll(".kelas-option")
                .forEach(function(el) {

                    if (el.innerText.trim() === kelas) {

                        el.classList.add("selected");

                    } else {

                        el.classList.remove("selected");

                    }

                });

        }


        const form =
            document.getElementById("formKrs");


        if (form) {

            form.addEventListener(
                "submit",
                function(e) {

                    const checked =
                        document.querySelectorAll(
                            ".matkul-check:checked"
                        );

                    if (checked.length === 0) {

                        e.preventDefault();

                        alert(
                            "Silakan pilih minimal satu mata kuliah."
                        );

                        return;

                    }


                    let valid = true;


                    checked.forEach(function(cb) {

                        const id = cb.value;

                        const input =
                            document.querySelector(
                                'input[name="kelas[' + id + ']"]'
                            );

                        if (!input || !input.value) {

                            valid = false;

                        }

                    });


                    if (!valid) {

                        e.preventDefault();

                        alert(
                            "Kelas mata kuliah belum dipilih."
                        );

                    }

                }
            );

        }
    </script>

</body>

</html>