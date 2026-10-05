<?php
include "config.php";
session_start();

/* =====================================================
   CEK LOGIN
===================================================== */

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

/* =====================================================
   HANYA ADMIN
===================================================== */

if ($_SESSION['role'] != 'admin') {
    header("Location: dashboard_mahasiswa.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

$username = $_SESSION['username'] ?? 'Admin';


/* =====================================================
   STATISTIK MAHASISWA
===================================================== */

$q_mhs = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM mahasiswa
");

$jml_mhs = 0;

if ($q_mhs) {
    $d_mhs = mysqli_fetch_assoc($q_mhs);
    $jml_mhs = (int)($d_mhs['total'] ?? 0);
}


/* =====================================================
   STATISTIK DOSEN
===================================================== */

$q_dosen = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM users
    WHERE role = 'dosen'
");

$jml_dosen = 0;

if ($q_dosen) {
    $d_dosen = mysqli_fetch_assoc($q_dosen);
    $jml_dosen = (int)($d_dosen['total'] ?? 0);
}


/* =====================================================
   STATISTIK MATA KULIAH
===================================================== */

$q_matkul = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM matkul
");

$jml_matkul = 0;

if ($q_matkul) {
    $d_matkul = mysqli_fetch_assoc($q_matkul);
    $jml_matkul = (int)($d_matkul['total'] ?? 0);
}


/* =====================================================
   STATISTIK JADWAL
===================================================== */

$q_jadwal = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM jadwal
");

$jml_jadwal = 0;

if ($q_jadwal) {
    $d_jadwal = mysqli_fetch_assoc($q_jadwal);
    $jml_jadwal = (int)($d_jadwal['total'] ?? 0);
}


/* =====================================================
   TOTAL ABSENSI HARI INI
===================================================== */

$q_absen = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM absensi
    WHERE tanggal = CURDATE()
");

$jml_absen = 0;

if ($q_absen) {
    $d_absen = mysqli_fetch_assoc($q_absen);
    $jml_absen = (int)($d_absen['total'] ?? 0);
}


/* =====================================================
   GRAFIK KEHADIRAN HARI INI
===================================================== */

$grafik = [
    'Hadir' => 0,
    'Izin'  => 0,
    'Sakit' => 0,
    'Alpha' => 0
];

$q_grafik = mysqli_query($conn, "
    SELECT
        status,
        COUNT(*) AS jumlah
    FROM absensi
    WHERE tanggal = CURDATE()
    GROUP BY status
");

if ($q_grafik) {

    while ($row = mysqli_fetch_assoc($q_grafik)) {

        $status = strtolower(
            trim($row['status'] ?? '')
        );

        $jumlah = (int)($row['jumlah'] ?? 0);

        if ($status == 'hadir') {
            $grafik['Hadir'] = $jumlah;
        } elseif ($status == 'izin') {
            $grafik['Izin'] = $jumlah;
        } elseif ($status == 'sakit') {
            $grafik['Sakit'] = $jumlah;
        } elseif ($status == 'alpha') {
            $grafik['Alpha'] = $jumlah;
        }
    }
}


/* =====================================================
   PERSENTASE GRAFIK
===================================================== */

$total_grafik = array_sum($grafik);

if ($total_grafik > 0) {

    $persen_hadir = round(
        ($grafik['Hadir'] / $total_grafik) * 100
    );

    $persen_izin = round(
        ($grafik['Izin'] / $total_grafik) * 100
    );

    $persen_sakit = round(
        ($grafik['Sakit'] / $total_grafik) * 100
    );

    $persen_alpha = round(
        ($grafik['Alpha'] / $total_grafik) * 100
    );
} else {

    $persen_hadir = 0;
    $persen_izin = 0;
    $persen_sakit = 0;
    $persen_alpha = 0;
}

?>


<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Dashboard Admin - Smart Absensi
    </title>


    <!-- BOOTSTRAP -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- BOOTSTRAP ICON -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
        rel="stylesheet">


    <style>
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

        }


        /* =====================================================
           SIDEBAR
        ===================================================== */

        .sidebar {

            width: 250px;

            height: 100vh;

            background: #0d6efd;

            position: fixed;

            top: 0;

            left: 0;

            padding-top: 20px;

            overflow-y: auto;

        }


        .sidebar h4 {

            color: white;

            text-align: center;

            margin-bottom: 30px;

            font-weight: bold;

        }


        .sidebar a {

            display: block;

            color: white;

            padding: 14px 22px;

            text-decoration: none;

            transition: .2s;

        }


        .sidebar a:hover {

            background:
                rgba(255, 255, 255, .15);

            padding-left: 28px;

        }


        .sidebar a i {

            margin-right: 10px;

        }


        /* =====================================================
           MAIN
        ===================================================== */

        .main {

            margin-left: 250px;

            padding: 25px;

        }


        /* =====================================================
           TOPBAR
        ===================================================== */

        .topbar {

            background: white;

            padding: 15px 20px;

            border-radius: 12px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, .05);

            margin-bottom: 25px;

        }


        .topbar h5 {

            font-weight: bold;

        }


        .welcome {

            font-size: 14px;

            color: #666;

        }


        #jam {

            font-size: 13px;

            color: #0d6efd;

            font-weight: 600;

            margin-top: 4px;

        }


        /* =====================================================
           STATISTIK
        ===================================================== */

        .stat-card {

            background: white;

            border: none;

            border-radius: 14px;

            padding: 20px;

            min-height: 135px;

            box-shadow:
                0 2px 12px rgba(0, 0, 0, .05);

            position: relative;

            overflow: hidden;

        }


        .stat-card p {

            color: #777;

            font-size: 14px;

            margin-bottom: 7px;

        }


        .stat-card h2 {

            font-size: 30px;

            font-weight: bold;

            margin: 0;

        }


        .stat-card small {

            color: #888;

        }


        .stat-icon {

            position: absolute;

            right: 20px;

            top: 38px;

            font-size: 38px;

        }


        /* =====================================================
           CONTENT CARD
        ===================================================== */

        .content-card {

            background: white;

            border-radius: 14px;

            box-shadow:
                0 2px 12px rgba(0, 0, 0, .05);

            padding: 18px;

            height: 100%;

        }


        .content-title {

            font-size: 16px;

            font-weight: bold;

            margin-bottom: 15px;

        }


        /* =====================================================
           TABLE
        ===================================================== */

        .table {

            font-size: 13px;

            vertical-align: middle;

        }


        .table thead th {

            background: #f8fafc;

            white-space: nowrap;

        }


        .table tbody td {

            white-space: nowrap;

        }


        /* =====================================================
           BADGE
        ===================================================== */

        .badge-hadir {

            background: #198754;

        }


        .badge-izin {

            background: #ffc107;

            color: #000;

        }


        .badge-sakit {

            background: #0dcaf0;

            color: #000;

        }


        .badge-alpha {

            background: #dc3545;

        }


        /* =====================================================
           GRAFIK
        ===================================================== */

        .chart-area {

            height: 245px;

            display: flex;

            align-items: flex-end;

            justify-content: space-around;

            padding: 15px 10px 10px;

            border: 1px solid #eee;

            border-radius: 10px;

            background: white;

        }


        .bar-wrapper {

            width: 55px;

            height: 200px;

            display: flex;

            flex-direction: column;

            justify-content: flex-end;

            align-items: center;

        }


        .bar {

            width: 32px;

            background: #0d6efd;

            border-radius:
                5px 5px 0 0;

            min-height: 3px;

            transition: height .4s;

        }


        .bar-label {

            margin-top: 7px;

            font-size: 12px;

            color: #555;

        }


        .bar-value {

            font-size: 11px;

            color: #777;

            margin-bottom: 3px;

        }


        /* =====================================================
           QUICK MENU
        ===================================================== */

        .quick-menu {

            background: white;

            border: 1px solid #eee;

            border-radius: 10px;

            padding: 16px;

            height: 100%;

            text-decoration: none;

            color: #333;

            display: block;

            transition: .2s;

        }


        .quick-menu:hover {

            transform: translateY(-3px);

            box-shadow:
                0 4px 12px rgba(0, 0, 0, .08);

            color: #0d6efd;

        }


        .quick-menu i {

            font-size: 27px;

            display: block;

            margin-bottom: 8px;

            color: #0d6efd;

        }


        .quick-menu span {

            font-size: 13px;

            font-weight: 600;

        }


        /* =====================================================
           REALTIME
        ===================================================== */

        .realtime-dot {

            display: inline-block;

            width: 9px;

            height: 9px;

            background: #198754;

            border-radius: 50%;

            margin-right: 6px;

            animation: pulse 1.5s infinite;

        }


        @keyframes pulse {

            0% {
                opacity: 1;
            }

            50% {
                opacity: .35;
            }

            100% {
                opacity: 1;
            }

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media(max-width: 900px) {

            .sidebar {

                width: 210px;

            }


            .main {

                margin-left: 210px;

            }

        }


        @media(max-width: 700px) {

            .sidebar {

                position: relative;

                width: 100%;

                height: auto;

            }


            .main {

                margin-left: 0;

                padding: 15px;

            }

        }
    </style>

</head>


<body>


    <!-- =====================================================
     SIDEBAR
===================================================== -->

    <div class="sidebar">

        <h4>
            Smart Absensi
        </h4>


        <a href="dashboard_admin.php">

            <i class="bi bi-speedometer2"></i>

            Dashboard

        </a>


        <a href="mahasiswa.php">

            <i class="bi bi-people-fill"></i>

            Mahasiswa

        </a>


        <a href="matkul.php">

            <i class="bi bi-book-fill"></i>

            Mata Kuliah

        </a>


        <a href="jadwal.php">

            <i class="bi bi-calendar-event"></i>

            Jadwal Kuliah

        </a>


        <a href="krs.php">

            <i class="bi bi-journal-check"></i>

            KRS

        </a>


        <a href="fingerprint_queue.php">

            <i class="bi bi-fingerprint"></i>

            Fingerprint

        </a>


        <a href="absensi.php">

            <i class="bi bi-clipboard-check"></i>

            Absensi

        </a>


        <a href="ruangan.php">

            <i class="bi bi-building"></i>

            Ruangan

        </a>


        <a href="dosen.php">

            <i class="bi bi-person-badge-fill"></i>

            Dosen

        </a>


        <a href="laporan.php">

            <i class="bi bi-file-earmark-text"></i>

            Laporan

        </a>


        <a href="logout.php">

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

    </div>



    <!-- =====================================================
     MAIN
===================================================== -->

    <div class="main">


        <!-- =================================================
         TOPBAR
    ================================================= -->

        <div class="
        topbar
        d-flex
        justify-content-between
        align-items-center
    ">

            <div>

                <h5 class="mb-1">
                    Dashboard Admin
                </h5>


                <div class="welcome">

                    Selamat datang,

                    <b>
                        <?= htmlspecialchars($username); ?>
                    </b>

                </div>


                <div id="jam"></div>

            </div>


            <div class="
            d-flex
            align-items-center
            gap-2
        ">

                <i class="
                bi bi-person-circle
                fs-2
                text-primary
            "></i>


                <div>

                    <b>
                        <?= htmlspecialchars($username); ?>
                    </b>


                    <small class="
                    d-block
                    text-muted
                ">
                        Administrator
                    </small>

                </div>

            </div>

        </div>



        <!-- =================================================
         STATISTIK
    ================================================= -->

        <div class="row g-4 mb-4">


            <!-- MAHASISWA -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <p>
                        Mahasiswa
                    </p>


                    <h2>
                        <?= $jml_mhs; ?>
                    </h2>


                    <small>
                        Total Mahasiswa
                    </small>


                    <i class="
                    bi bi-people-fill
                    stat-icon
                    text-primary
                "></i>

                </div>

            </div>



            <!-- DOSEN -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <p>
                        Dosen
                    </p>


                    <h2>
                        <?= $jml_dosen; ?>
                    </h2>


                    <small>
                        Total Dosen
                    </small>


                    <i class="
                    bi bi-person-badge-fill
                    stat-icon
                    text-success
                "></i>

                </div>

            </div>



            <!-- MATA KULIAH -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <p>
                        Mata Kuliah
                    </p>


                    <h2>
                        <?= $jml_matkul; ?>
                    </h2>


                    <small>
                        Total Mata Kuliah
                    </small>


                    <i class="
                    bi bi-book-fill
                    stat-icon
                    text-warning
                "></i>

                </div>

            </div>



            <!-- JADWAL -->

            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <p>
                        Kelas / Jadwal
                    </p>


                    <h2>
                        <?= $jml_jadwal; ?>
                    </h2>


                    <small>
                        Total Jadwal
                    </small>


                    <i class="
                    bi bi-calendar-event
                    stat-icon
                    text-primary
                "></i>

                </div>

            </div>

        </div>



        <!-- =================================================
         ABSENSI + GRAFIK
    ================================================= -->

        <div class="row g-4 mb-4">


            <!-- =================================================
             ABSENSI HARI INI
        ================================================= -->

            <div class="col-lg-8">

                <div class="content-card">


                    <div class="
                    d-flex
                    justify-content-between
                    align-items-center
                    mb-3
                ">

                        <div class="content-title mb-0">

                            Absensi Hari Ini

                        </div>


                        <div class="
                        small
                        text-success
                        fw-semibold
                    ">

                            <span class="realtime-dot"></span>

                            Realtime

                        </div>

                    </div>


                    <div class="table-responsive">

                        <table class="
                        table
                        table-hover
                        table-bordered
                    ">

                            <thead>

                                <tr>

                                    <th>No</th>

                                    <th>NIM</th>

                                    <th>Nama</th>

                                    <th>Mata Kuliah</th>

                                    <th>Pertemuan</th>

                                    <th>Status</th>

                                    <th>Tanggal</th>

                                    <th>Jam Masuk</th>

                                    <th>Jam Selesai</th>

                                </tr>

                            </thead>


                            <tbody id="live-absensi">

                                <tr>

                                    <td
                                        colspan="10"
                                        class="text-center text-muted py-4">

                                        Memuat data absensi...

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>



            <!-- =================================================
             GRAFIK
        ================================================= -->

            <div class="col-lg-4">

                <div class="content-card">


                    <div class="content-title">

                        Grafik Kehadiran Hari Ini

                    </div>


                    <div class="chart-area">


                        <!-- HADIR -->

                        <div class="bar-wrapper">

                            <div class="bar-value">
                                <?= $persen_hadir; ?>%
                            </div>


                            <div
                                class="bar"
                                style="
                                height:
                                <?= max(
                                    3,
                                    $persen_hadir * 1.6
                                ); ?>px;
                            "></div>


                            <div class="bar-label">
                                Hadir
                            </div>

                        </div>



                        <!-- IZIN -->

                        <div class="bar-wrapper">

                            <div class="bar-value">
                                <?= $persen_izin; ?>%
                            </div>


                            <div
                                class="bar"
                                style="
                                height:
                                <?= max(
                                    3,
                                    $persen_izin * 1.6
                                ); ?>px;
                            "></div>


                            <div class="bar-label">
                                Izin
                            </div>

                        </div>



                        <!-- SAKIT -->

                        <div class="bar-wrapper">

                            <div class="bar-value">
                                <?= $persen_sakit; ?>%
                            </div>


                            <div
                                class="bar"
                                style="
                                height:
                                <?= max(
                                    3,
                                    $persen_sakit * 1.6
                                ); ?>px;
                            "></div>


                            <div class="bar-label">
                                Sakit
                            </div>

                        </div>



                        <!-- ALPHA -->

                        <div class="bar-wrapper">

                            <div class="bar-value">
                                <?= $persen_alpha; ?>%
                            </div>


                            <div
                                class="bar"
                                style="
                                height:
                                <?= max(
                                    3,
                                    $persen_alpha * 1.6
                                ); ?>px;
                            "></div>


                            <div class="bar-label">
                                Alpha
                            </div>

                        </div>


                    </div>


                    <div class="text-center mt-3">

                        <strong>
                            <?= $persen_hadir; ?>%
                        </strong>


                        <small class="text-muted">
                            rata-rata kehadiran
                        </small>

                    </div>


                    <div class="text-center mt-2">

                        <small class="text-muted">

                            Total absensi hari ini:

                            <b>
                                <?= $jml_absen; ?>
                            </b>

                        </small>

                    </div>

                </div>

            </div>

        </div>



        <!-- =================================================
         MENU CEPAT
    ================================================= -->

        <div class="content-card mb-4">


            <div class="content-title">

                Menu Cepat

            </div>


            <div class="row g-3">


                <!-- MAHASISWA -->

                <div class="col-xl col-md-4 col-6">

                    <a
                        href="mahasiswa.php"
                        class="quick-menu">

                        <i class="
                        bi bi-person-plus-fill
                    "></i>


                        <span>
                            Tambah Mahasiswa
                        </span>

                    </a>

                </div>



                <!-- DOSEN -->

                <div class="col-xl col-md-4 col-6">

                    <a
                        href="dosen.php"
                        class="quick-menu">

                        <i class="
                        bi bi-person-badge
                    "></i>


                        <span>
                            Tambah Dosen
                        </span>

                    </a>

                </div>



                <!-- MATA KULIAH -->

                <div class="col-xl col-md-4 col-6">

                    <a
                        href="matkul.php"
                        class="quick-menu">

                        <i class="
                        bi bi-book
                    "></i>


                        <span>
                            Tambah Mata Kuliah
                        </span>

                    </a>

                </div>



                <!-- JADWAL -->

                <div class="col-xl col-md-4 col-6">

                    <a
                        href="jadwal.php"
                        class="quick-menu">

                        <i class="
                        bi bi-calendar-plus
                    "></i>


                        <span>
                            Tambah Jadwal
                        </span>

                    </a>

                </div>



                <!-- REKAP -->

                <div class="col-xl col-md-4 col-6">

                    <a
                        href="laporan.php"
                        class="quick-menu">

                        <i class="
                        bi bi-file-earmark-bar-graph
                    "></i>


                        <span>
                            Rekap Absensi
                        </span>

                    </a>

                </div>


            </div>

        </div>


    </div>



    <!-- =====================================================
     JAVASCRIPT JAM
===================================================== -->

    <script>
        function updateJam() {

            let now = new Date();


            let hari = [

                "Minggu",
                "Senin",
                "Selasa",
                "Rabu",
                "Kamis",
                "Jumat",
                "Sabtu"

            ];


            let bulan = [

                "Januari",
                "Februari",
                "Maret",
                "April",
                "Mei",
                "Juni",
                "Juli",
                "Agustus",
                "September",
                "Oktober",
                "November",
                "Desember"

            ];


            let h =
                hari[now.getDay()];


            let tgl =
                now.getDate();


            let bln =
                bulan[now.getMonth()];


            let thn =
                now.getFullYear();


            let jam =
                String(
                    now.getHours()
                ).padStart(2, '0');


            let menit =
                String(
                    now.getMinutes()
                ).padStart(2, '0');


            let detik =
                String(
                    now.getSeconds()
                ).padStart(2, '0');


            document
                .getElementById("jam")
                .innerHTML =
                `${h}, ${tgl} ${bln} ${thn} | ${jam}:${menit}:${detik}`;

        }


        setInterval(
            updateJam,
            1000
        );


        updateJam();


        /* =====================================================
           ABSENSI REALTIME
        ===================================================== */

        function loadAbsensi() {

            fetch(
                    "realtime_absensi.php?ts=" +
                    Date.now()
                )

                .then(response => {

                    if (!response.ok) {

                        throw new Error(
                            "Gagal mengambil data absensi"
                        );

                    }

                    return response.text();

                })

                .then(data => {

                    document
                        .getElementById("live-absensi")
                        .innerHTML = data;

                })

                .catch(error => {

                    console.error(
                        "Realtime absensi:",
                        error
                    );

                });

        }


        /* Update setiap 2 detik */

        setInterval(
            loadAbsensi,
            2000
        );


        /* Jalankan saat halaman dibuka */

        loadAbsensi();
    </script>


</body>

</html>