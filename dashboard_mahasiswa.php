<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

/* =========================
   AMBIL MAHASISWA ID
========================= */

$user_id = $_SESSION['id'];

$get = mysqli_query($conn, "
    SELECT mahasiswa_id
    FROM users
    WHERE id='$user_id'
");

$data = mysqli_fetch_assoc($get);

$mahasiswa_id = $data['mahasiswa_id'] ?? 0;


/* =========================
   NAMA MAHASISWA
========================= */

$getNama = mysqli_query($conn, "
    SELECT nama
    FROM mahasiswa
    WHERE id_mahasiswa='$mahasiswa_id'
");

$dataNama = mysqli_fetch_assoc($getNama);

$nama = $dataNama['nama'] ?? 'Mahasiswa';


/* =========================
   HARI INDONESIA
========================= */

$hari_map = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu'
];

$hari = $hari_map[date('l')];


/* =========================
   JAM SEKARANG
========================= */

$jam_sekarang = date("H:i:s");


/* =====================================================
   JADWAL HARI INI
===================================================== */

$jadwal = mysqli_query($conn, "

SELECT
    j.*,
    m.nama_matkul,
    m.kode,
    r.nama_ruang,

    CASE

        WHEN '$jam_sekarang'
        BETWEEN j.jam_masuk AND j.jam_selesai
        THEN 'Sedang Berlangsung'

        WHEN '$jam_sekarang' < j.jam_masuk
        THEN 'Akan Datang'

        ELSE 'Selesai'

    END AS status_jadwal

FROM krs k

JOIN jadwal j
    ON j.matkul_id = k.matkul_id
    AND j.semester = k.semester
    AND j.kelas = k.kelas

JOIN matkul m
    ON m.id = j.matkul_id

LEFT JOIN ruangan r
    ON r.id = j.ruangan_id

WHERE k.mahasiswa_id = '$mahasiswa_id'
AND j.hari = '$hari'

ORDER BY j.jam_masuk ASC

");

if (!$jadwal) {
    die(mysqli_error($conn));
}


/* =====================================================
   KRS MAHASISWA
===================================================== */

$krs = mysqli_query($conn, "

SELECT
    k.*,
    m.kode,
    m.nama_matkul

FROM krs k

JOIN matkul m
    ON m.id = k.matkul_id

WHERE k.mahasiswa_id = '$mahasiswa_id'

ORDER BY m.nama_matkul ASC

");

if (!$krs) {
    die(mysqli_error($conn));
}


/* =====================================================
   TOTAL MATA KULIAH
===================================================== */

$q_total_matkul = mysqli_query($conn, "

SELECT COUNT(DISTINCT matkul_id) AS total

FROM krs

WHERE mahasiswa_id = '$mahasiswa_id'

");

$d_total_matkul = mysqli_fetch_assoc($q_total_matkul);

$total_matkul = (int)($d_total_matkul['total'] ?? 0);


/* =====================================================
   TOTAL KELAS
===================================================== */

$q_total_kelas = mysqli_query($conn, "

SELECT COUNT(DISTINCT kelas) AS total

FROM krs

WHERE mahasiswa_id = '$mahasiswa_id'

");

$d_total_kelas = mysqli_fetch_assoc($q_total_kelas);

$total_kelas = (int)($d_total_kelas['total'] ?? 0);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Dashboard Mahasiswa - Smart Absensi
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">


    <style>
        /* =================================================
           RESET
        ================================================= */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            background: #f1f5f9;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: #1e293b;

            overflow-x: hidden;

        }


        /* =================================================
           SIDEBAR
        ================================================= */

        .sidebar {

            position: fixed;

            top: 0;
            left: 0;

            width: 250px;
            height: 100vh;

            background: #0d6efd;

            padding: 22px 18px;

            overflow-y: auto;

            z-index: 1000;

            transition:
                left 0.3s ease;

        }


        .sidebar h4 {

            color: white;

            text-align: center;

            font-weight: bold;

            margin-bottom: 30px;

        }


        .sidebar a {

            display: flex;

            align-items: center;

            gap: 8px;

            color: white;

            text-decoration: none;

            padding: 13px 15px;

            margin-bottom: 8px;

            border-radius: 8px;

            transition: 0.25s;

        }


        .sidebar a:hover {

            background: rgba(255, 255, 255, 0.18);

            padding-left: 21px;

        }


        .sidebar a i {

            font-size: 18px;

        }


        /* =================================================
           MAIN
        ================================================= */

        .main {

            margin-left: 250px;

            width: calc(100% - 250px);

            min-height: 100vh;

            padding: 25px;

            transition:
                margin-left 0.3s ease,
                width 0.3s ease;

        }


        /* =================================================
           BOX
        ================================================= */

        .box {

            background: white;

            border-radius: 14px;

            padding: 20px;

            margin-bottom: 20px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, 0.07);

        }


        /* =================================================
           HEADER
        ================================================= */

        .welcome-box {

            min-height: 120px;

            display: flex;

            align-items: center;

        }


        .welcome-title {

            font-size: 24px;

            font-weight: bold;

            margin-bottom: 8px;

        }


        #jam {

            font-weight: bold;

            color: #0d6efd;

            font-size: 15px;

        }


        #motivasi {

            margin-top: 8px;

            color: #64748b;

            font-style: italic;

        }


        /* =================================================
           STAT CARD
        ================================================= */

        .stat-card {

            background: white;

            border-radius: 14px;

            padding: 20px;

            min-height: 145px;

            position: relative;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, 0.07);

            overflow: hidden;

        }


        .stat-card p {

            color: #64748b;

            font-size: 14px;

            margin-bottom: 8px;

        }


        .stat-card h2 {

            font-size: 30px;

            font-weight: bold;

            margin: 0;

        }


        .stat-card small {

            display: block;

            margin-top: 5px;

            color: #94a3b8;

        }


        .stat-icon {

            position: absolute;

            right: 20px;

            top: 35px;

            font-size: 42px;

            opacity: 0.9;

        }


        /* =================================================
           KRS
        ================================================= */

        .section-title {

            font-size: 18px;

            font-weight: bold;

            margin-bottom: 15px;

        }


        .btn-krs {

            background: #198754;

            border: none;

            color: white;

            padding: 8px 15px;

            border-radius: 7px;

            text-decoration: none;

            font-size: 13px;

        }


        .btn-krs:hover {

            background: #157347;

            color: white;

        }


        /* =================================================
           TABLE
        ================================================= */

        .table-responsive {

            width: 100%;

            overflow-x: auto;

            -webkit-overflow-scrolling: touch;

        }


        .table {

            margin-bottom: 0;

            min-width: 650px;

        }


        .table th {

            background: #0d6efd;

            color: white;

            white-space: nowrap;

            vertical-align: middle;

        }


        .table td {

            vertical-align: middle;

            white-space: nowrap;

        }


        .table-hover tbody tr:hover {

            background: #f8fafc;

        }


        /* =================================================
           JADWAL CARD
        ================================================= */

        .jadwal-card {

            border: 1px solid #e2e8f0;

            border-radius: 10px;

            padding: 15px;

            margin-bottom: 12px;

            background: white;

            transition: .2s;

        }


        .jadwal-card:hover {

            border-color: #0d6efd;

            transform: translateY(-2px);

        }


        .jadwal-nama {

            font-weight: bold;

            font-size: 14px;

            margin-bottom: 7px;

        }


        .jadwal-kode {

            color: #64748b;

            font-size: 12px;

            margin-bottom: 10px;

        }


        .jadwal-detail {

            color: #64748b;

            font-size: 12px;

            margin-top: 5px;

        }


        .jadwal-detail i {

            color: #0d6efd;

            margin-right: 5px;

        }


        /* =================================================
           INFORMASI
        ================================================= */

        .info-box {

            background: #fff;

            border-radius: 14px;

            padding: 20px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, 0.07);

        }


        .info-box p {

            color: #64748b;

            margin: 0;

            font-size: 14px;

        }


        /* =================================================
           MENU HP
        ================================================= */

        .menu-btn {

            display: none;

            position: fixed;

            top: 15px;

            left: 15px;

            width: 45px;

            height: 45px;

            background: #0d6efd;

            color: white;

            border-radius: 10px;

            align-items: center;

            justify-content: center;

            font-size: 22px;

            cursor: pointer;

            z-index: 1100;

            box-shadow:
                0 3px 10px rgba(0, 0, 0, 0.2);

        }


        /* =================================================
           OVERLAY HP
        ================================================= */

        .sidebar-overlay {

            display: none;

            position: fixed;

            inset: 0;

            background: rgba(0, 0, 0, 0.4);

            z-index: 900;

        }


        .sidebar-overlay.active {

            display: block;

        }


        /* =================================================
           LAPTOP
        ================================================= */

        @media (max-width: 1200px) {

            .main {

                padding: 20px;

            }

            .stat-card {

                padding: 17px;

            }

            .stat-card h2 {

                font-size: 27px;

            }

        }


        /* =================================================
           TABLET
        ================================================= */

        @media (max-width: 992px) {

            .sidebar {

                width: 220px;

            }


            .main {

                margin-left: 220px;

                width: calc(100% - 220px);

            }


            .stat-icon {

                font-size: 35px;

            }

        }


        /* =================================================
           HP
        ================================================= */

        @media (max-width: 768px) {

            .sidebar {

                left: -270px;

                width: 250px;

                box-shadow:
                    3px 0 15px rgba(0, 0, 0, 0.2);

            }


            .sidebar.active {

                left: 0;

            }


            .menu-btn {

                display: flex;

            }


            .main {

                margin-left: 0;

                width: 100%;

                padding:
                    75px 12px 20px;

            }


            .box {

                padding: 15px;

                border-radius: 12px;

            }


            .welcome-box {

                min-height: auto;

            }


            .welcome-title {

                font-size: 20px;

                line-height: 1.4;

            }


            #jam {

                font-size: 13px;

            }


            #motivasi {

                font-size: 13px;

            }


            .stat-card {

                min-height: 125px;

                padding: 16px;

            }


            .stat-card h2 {

                font-size: 26px;

            }


            .stat-card p {

                font-size: 13px;

            }


            .stat-card small {

                font-size: 11px;

                max-width: 70%;

            }


            .stat-icon {

                right: 15px;

                top: 32px;

                font-size: 32px;

            }


            .table {

                font-size: 12px;

            }


            .table th,
            .table td {

                padding: 9px 10px;

            }


            h5 {

                font-size: 17px;

            }

        }


        /* =================================================
           HP KECIL
        ================================================= */

        @media (max-width: 480px) {

            .main {

                padding:
                    70px 10px 15px;

            }


            .box {

                padding: 13px;

            }


            .welcome-title {

                font-size: 18px;

            }


            .stat-card {

                min-height: 115px;

            }


            .stat-card h2 {

                font-size: 24px;

            }


            .stat-icon {

                font-size: 28px;

                top: 30px;

            }

        }
    </style>

</head>


<body>


    <!-- =====================================================
     OVERLAY
===================================================== -->

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
        onclick="toggleMenu()">
    </div>


    <!-- =====================================================
     MENU HP
===================================================== -->

    <div
        class="menu-btn"
        onclick="toggleMenu()">

        <i class="bi bi-list"></i>

    </div>


    <!-- =====================================================
     SIDEBAR
===================================================== -->

    <div
        class="sidebar"
        id="sidebar">


        <h4>

            <i class="bi bi-mortarboard-fill"></i>

            Mahasiswa

        </h4>


        <a
            href="dashboard_mahasiswa.php">

            <i class="bi bi-speedometer2"></i>

            <span>
                Dashboard
            </span>

        </a>


        <a
            href="isi_krs.php">

            <i class="bi bi-journal-check"></i>

            <span>
                KRS
            </span>

        </a>


        <a
            href="jadwal_mahasiswa.php">

            <i class="bi bi-calendar-event"></i>

            <span>
                Jadwal
            </span>

        </a>


        <a
            href="profil_mahasiswa.php">

            <i class="bi bi-person-circle"></i>

            <span>
                Profil
            </span>

        </a>


        <a
            href="logout.php"
            onclick="return confirm('Yakin ingin logout?')">

            <i class="bi bi-box-arrow-right"></i>

            <span>
                Logout
            </span>

        </a>


    </div>


    <!-- =====================================================
     MAIN
===================================================== -->

    <div class="main">


        <!-- =================================================
         HEADER
    ================================================= -->

        <div class="box welcome-box">


            <div class="w-100">


                <div
                    class="d-flex justify-content-between
                align-items-center">


                    <div>


                        <div class="welcome-title">

                            Selamat Datang,
                            <?= htmlspecialchars($nama) ?>
                            👋

                        </div>


                        <div id="jam"></div>


                        <p class="text-muted mt-2 mb-0">

                            Semangat kuliah hari ini 💪

                        </p>


                        <div id="motivasi"></div>


                    </div>


                    <div class="d-none d-md-block">

                        <i
                            class="bi bi-person-circle text-primary"
                            style="font-size:55px;">
                        </i>

                    </div>


                </div>


            </div>


        </div>


        <!-- =================================================
         STATISTIK
         HANYA KRS DAN KELAS
    ================================================= -->

        <div class="row g-3 mb-4">


            <!-- TOTAL MATKUL -->

            <div class="col-xl-3 col-md-6 col-6">

                <div class="stat-card">


                    <p>

                        Total Mata Kuliah

                    </p>


                    <h2>

                        <?= $total_matkul ?>

                    </h2>


                    <small>

                        Mata kuliah yang diambil

                    </small>


                    <i
                        class="bi bi-book-fill
                    stat-icon text-primary">
                    </i>


                </div>

            </div>


            <!-- TOTAL KELAS -->

            <div class="col-xl-3 col-md-6 col-6">

                <div class="stat-card">


                    <p>

                        Total Kelas

                    </p>


                    <h2>

                        <?= $total_kelas ?>

                    </h2>


                    <small>

                        Kelas yang diikuti

                    </small>


                    <i
                        class="bi bi-people-fill
                    stat-icon text-success">
                    </i>


                </div>

            </div>


        </div>


        <!-- =================================================
         KRS DAN JADWAL
    ================================================= -->

        <div class="row g-3">


            <!-- =================================================
             KRS SAYA
        ================================================= -->

            <div class="col-lg-7">


                <div class="box">


                    <div
                        class="d-flex
                    justify-content-between
                    align-items-center
                    mb-3">


                        <h5 class="mb-0">

                            📚 KRS Saya

                        </h5>


                        <a
                            href="isi_krs.php"
                            class="btn-krs">

                            <i
                                class="bi bi-pencil-square">
                            </i>

                            Kelola KRS

                        </a>


                    </div>


                    <div class="table-responsive">


                        <table
                            class="table
                        table-bordered
                        table-hover
                        align-middle">


                            <thead
                                class="text-center">


                                <tr>

                                    <th>
                                        No
                                    </th>

                                    <th>
                                        Kode
                                    </th>

                                    <th>
                                        Mata Kuliah
                                    </th>

                                    <th>
                                        Kelas
                                    </th>

                                    <th>
                                        Semester
                                    </th>

                                </tr>


                            </thead>


                            <tbody>


                                <?php

                                $no_krs = 1;


                                if (
                                    $krs &&
                                    mysqli_num_rows($krs) > 0
                                ) {


                                    while (
                                        $row =
                                        mysqli_fetch_assoc($krs)
                                    ) {


                                ?>


                                        <tr>


                                            <td class="text-center">

                                                <?= $no_krs++ ?>

                                            </td>


                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $row['kode']
                                                    ) ?>

                                                </strong>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $row['nama_matkul']
                                                ) ?>

                                            </td>


                                            <td class="text-center">

                                                <?= htmlspecialchars(
                                                    $row['kelas']
                                                ) ?>

                                            </td>


                                            <td class="text-center">

                                                <?= htmlspecialchars(
                                                    $row['semester']
                                                ) ?>

                                            </td>


                                        </tr>


                                    <?php


                                    }
                                } else {


                                    ?>


                                    <tr>


                                        <td
                                            colspan="5"
                                            class="text-center
                                    text-muted
                                    py-4">


                                            <i
                                                class="bi
                                        bi-journal-x"
                                                style="
                                        font-size:35px;">
                                            </i>


                                            <div class="mt-2">

                                                Belum ada KRS.

                                            </div>


                                        </td>


                                    </tr>


                                <?php


                                }


                                ?>


                            </tbody>


                        </table>


                    </div>


                </div>


            </div>


            <!-- =================================================
             JADWAL HARI INI
        ================================================= -->

            <div class="col-lg-5">


                <div class="box">


                    <div
                        class="d-flex
                    justify-content-between
                    align-items-center
                    mb-3">


                        <h5 class="mb-0">

                            📅 Jadwal Hari Ini

                        </h5>


                    </div>


                    <p class="text-muted"
                        style="font-size:13px;">

                        <?= $hari ?>,
                        <?= date('d-m-Y') ?>

                    </p>


                    <?php


                    if (
                        $jadwal &&
                        mysqli_num_rows($jadwal) > 0
                    ) {


                        while (
                            $j =
                            mysqli_fetch_assoc($jadwal)
                        ) {


                    ?>


                            <div class="jadwal-card">


                                <div class="jadwal-nama">

                                    <?= htmlspecialchars(
                                        $j['nama_matkul']
                                    ) ?>

                                </div>


                                <div class="jadwal-kode">

                                    <?= htmlspecialchars(
                                        $j['kode']
                                    ) ?>

                                    • Kelas

                                    <?= htmlspecialchars(
                                        $j['kelas']
                                    ) ?>

                                </div>


                                <div class="jadwal-detail">

                                    <i
                                        class="bi bi-clock">
                                    </i>

                                    <?= date(
                                        "H:i",
                                        strtotime(
                                            $j['jam_masuk']
                                        )
                                    ) ?>

                                    -

                                    <?= date(
                                        "H:i",
                                        strtotime(
                                            $j['jam_selesai']
                                        )
                                    ) ?>

                                </div>


                                <div class="jadwal-detail">

                                    <i
                                        class="bi bi-geo-alt">
                                    </i>

                                    <?= htmlspecialchars(
                                        $j['nama_ruang'] ?? '-'
                                    ) ?>

                                </div>


                                <div class="mt-2">


                                    <?php


                                    if (
                                        $j['status_jadwal']
                                        ==
                                        "Sedang Berlangsung"
                                    ) {


                                        echo '

                                <span
                                    class="badge bg-success">

                                    Sedang Berlangsung

                                </span>

                            ';
                                    } elseif (
                                        $j['status_jadwal']
                                        ==
                                        "Akan Datang"
                                    ) {


                                        echo '

                                <span
                                    class="
                                    badge
                                    bg-warning
                                    text-dark">

                                    Akan Datang

                                </span>

                            ';
                                    } else {


                                        echo '

                                <span
                                    class="
                                    badge
                                    bg-secondary">

                                    Selesai

                                </span>

                            ';
                                    }


                                    ?>


                                </div>


                            </div>


                        <?php


                        }
                    } else {


                        ?>


                        <div
                            class="text-center
                        text-muted
                        py-4">


                            <i
                                class="
                            bi
                            bi-calendar-x"
                                style="
                            font-size:40px;">
                            </i>


                            <p class="mt-2">

                                Tidak ada jadwal
                                hari ini.

                            </p>


                        </div>


                    <?php


                    }


                    ?>


                    <div class="text-center mt-3">


                        <a
                            href="jadwal_mahasiswa.php"
                            class="
                        btn
                        btn-primary
                        btn-sm">


                            <i
                                class="bi
                            bi-calendar3">
                            </i>

                            Lihat Jadwal Lengkap

                        </a>


                    </div>


                </div>


            </div>


        </div>


        <!-- =================================================
         INFORMASI
    ================================================= -->

        <div class="info-box mt-3">


            <h5 class="mb-3">

                ℹ️ Informasi

            </h5>


            <p>

                Silakan gunakan menu
                <strong>KRS</strong>
                untuk melihat dan mengelola
                mata kuliah yang kamu ambil.

                Untuk melihat jadwal perkuliahan
                secara lengkap, pilih menu
                <strong>Jadwal</strong>.

            </p>


        </div>


    </div>


    <!-- =====================================================
     JAVASCRIPT JAM
===================================================== -->

    <script>
        function updateJam() {

            const now = new Date();

            const jam =
                document.getElementById("jam");

            if (!jam) return;


            jam.innerHTML =
                now.toLocaleString(
                    "id-ID", {
                        weekday: "long",
                        year: "numeric",
                        month: "long",
                        day: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                        second: "2-digit"
                    }
                );

        }


        updateJam();

        setInterval(
            updateJam,
            1000
        );
    </script>


    <!-- =====================================================
     MOTIVASI
===================================================== -->

    <script>
        const quotes = [

            "Belajar hari ini, sukses di masa depan 💪",

            "Konsistensi lebih penting daripada motivasi 🔥",

            "Sedikit demi sedikit lama-lama menjadi bukit 📚",

            "Jangan menyerah, kamu hampir sampai 🎯",

            "Ilmu adalah investasi terbaik ✨",

            "Kuliah adalah proses menuju masa depan 🧠"

        ];


        function gantiMotivasi() {

            const el =
                document.getElementById("motivasi");

            if (!el) return;


            const random =
                Math.floor(
                    Math.random() *
                    quotes.length
                );


            el.innerHTML =
                "✨ " +
                quotes[random];

        }


        gantiMotivasi();

        setInterval(
            gantiMotivasi,
            5000
        );
    </script>


    <!-- =====================================================
     SIDEBAR HP
===================================================== -->

    <script>
        function toggleMenu() {

            const sidebar =
                document.getElementById("sidebar");

            const overlay =
                document.getElementById(
                    "sidebarOverlay"
                );


            sidebar.classList.toggle("active");

            overlay.classList.toggle("active");

        }


        document
            .querySelectorAll(".sidebar a")
            .forEach(function(link) {

                link.addEventListener(
                    "click",
                    function() {

                        if (
                            window.innerWidth <= 768
                        ) {

                            document
                                .getElementById(
                                    "sidebar"
                                )
                                .classList
                                .remove("active");


                            document
                                .getElementById(
                                    "sidebarOverlay"
                                )
                                .classList
                                .remove("active");

                        }

                    }
                );

            });
    </script>


</body>

</html>