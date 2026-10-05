<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'dosen') {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

$nama = $_SESSION['nama'];


/* =====================================================
   HARI INI
===================================================== */

$hari_arr = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu'
];

$hari_ini = $hari_arr[date('l')];
$tanggal_hari_ini = date('Y-m-d');


/* =====================================================
   JADWAL MENGAJAR DOSEN
===================================================== */

$nama_safe = mysqli_real_escape_string($conn, $nama);

$jadwal = mysqli_query($conn, "
SELECT
    j.*,
    m.kode,
    m.nama_matkul,
    m.sks,
    r.nama_ruang
FROM jadwal j
JOIN matkul m
    ON m.id = j.matkul_id
LEFT JOIN ruangan r
    ON r.id = j.ruangan_id
WHERE m.dosen = '$nama_safe'
ORDER BY
    FIELD(
        j.hari,
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
        'Minggu'
    ),
    j.jam_masuk
");

if (!$jadwal) {
    die("Error jadwal: " . mysqli_error($conn));
}


/* =====================================================
   ABSENSI HARI INI
   HANYA MATA KULIAH DOSEN LOGIN
===================================================== */

$absensi_hari_ini = mysqli_query($conn, "
SELECT
    a.id,
    a.nim,
    a.nama,
    a.nama_matkul,
    a.semester,
    a.pertemuan,
    a.status,
    a.tanggal,
    a.finger_id,
    a.jam_masuk,
    a.jam_selesai,

    m.kode,
    m.nama_matkul AS nama_matkul_master

FROM absensi a

JOIN matkul m
    ON m.id = a.matkul_id

WHERE m.dosen = '$nama_safe'
AND a.tanggal = '$tanggal_hari_ini'

ORDER BY
    a.jam_masuk DESC,
    a.nama ASC
");

if (!$absensi_hari_ini) {
    die("Error absensi: " . mysqli_error($conn));
}


/* =====================================================
   TOTAL ABSENSI HARI INI
===================================================== */

$q_total_absensi = mysqli_query($conn, "
SELECT COUNT(*) AS total
FROM absensi a
JOIN matkul m
    ON m.id = a.matkul_id
WHERE m.dosen = '$nama_safe'
AND a.tanggal = '$tanggal_hari_ini'
");

$total_absensi = mysqli_fetch_assoc($q_total_absensi)['total'] ?? 0;

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>Jadwal Dosen - Smart Absensi</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
        rel="stylesheet">


    <style>
        body {
            background: #f1f5f9;
            font-family: Arial, sans-serif;
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

            transition: .3s;

            z-index: 1000;

        }


        .sidebar h4 {

            color: white;

            text-align: center;

            margin-bottom: 30px;

            font-weight: bold;

        }


        .sidebar a {

            display: block;

            padding: 14px 22px;

            color: white;

            text-decoration: none;

            transition: .2s;

        }


        .sidebar a:hover {

            background:
                rgba(255, 255, 255, .2);

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
           CARD
        ===================================================== */

        .card-box {

            background: white;

            border-radius: 14px;

            padding: 20px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, .05);

            margin-bottom: 20px;

        }


        /* =====================================================
           JUDUL
        ===================================================== */

        .page-title {

            font-weight: bold;

            margin-bottom: 5px;

        }


        .page-subtitle {

            color: #777;

            font-size: 14px;

        }


        /* =====================================================
           TABLE
        ===================================================== */

        .table {

            vertical-align: middle;

            font-size: 13px;

        }


        .table thead th {

            background: #0d6efd;

            color: white;

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

            color: white;

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

            color: white;

        }


        /* =====================================================
           INFO
        ===================================================== */

        .info-card {

            border-radius: 12px;

            background: #f8fafc;

            padding: 15px;

            border: 1px solid #eee;

        }


        .info-number {

            font-size: 25px;

            font-weight: bold;

            color: #0d6efd;

        }


        /* =====================================================
           MENU MOBILE
        ===================================================== */

        .menu-btn {

            display: none;

            position: fixed;

            top: 15px;

            left: 15px;

            width: 45px;

            height: 45px;

            background: #0d6efd;

            color: white;

            border-radius: 8px;

            align-items: center;

            justify-content: center;

            font-size: 25px;

            z-index: 1100;

        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media(max-width:768px) {

            .menu-btn {

                display: flex;

            }


            .sidebar {

                left: -250px;

                padding-top: 70px;

            }


            .sidebar.active {

                left: 0;

            }


            .main {

                margin-left: 0;

                padding:
                    70px 15px 15px;

            }


            .card-box {

                padding: 15px;

            }


            .table {

                font-size: 12px;

                white-space: nowrap;

            }

        }
    </style>

</head>


<body>


    <!-- =====================================================
         MENU MOBILE
    ===================================================== -->

    <div
        class="menu-btn"
        onclick="toggleMenu()">

        ☰

    </div>


    <!-- =====================================================
         SIDEBAR
    ===================================================== -->

    <div class="sidebar">

        <h4>
            Smart Absensi
        </h4>


        <a href="dashboard_dosen.php">

            <i class="bi bi-speedometer2"></i>

            Dashboard

        </a>


        <a href="jadwal_dosen.php">

            <i class="bi bi-calendar-event"></i>

            Jadwal Mengajar

        </a>


        <a href="rekap_absensi.php">

            <i class="bi bi-clipboard-check"></i>

            Rekap Absensi

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


        <!-- =====================================================
             JADWAL MENGAJAR
        ===================================================== -->

        <div class="card-box">

            <h4 class="page-title">

                Jadwal Mengajar

            </h4>


            <p class="page-subtitle">

                Jadwal mata kuliah yang Anda ampu

            </p>


            <div class="table-responsive">

                <table
                    class="table table-bordered table-striped mt-3">

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Hari</th>

                            <th>Kode</th>

                            <th>Mata Kuliah</th>

                            <th>SKS</th>

                            <th>Kelas</th>

                            <th>Semester</th>

                            <th>Jam</th>

                            <th>Ruangan</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php

                        $no = 1;

                        if (mysqli_num_rows($jadwal) > 0) {

                            while (
                                $j = mysqli_fetch_assoc($jadwal)
                            ) {

                        ?>

                                <tr>

                                    <td>
                                        <?= $no++ ?>
                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $j['hari']
                                        ) ?>
                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $j['kode']
                                        ) ?>
                                    </td>


                                    <td>

                                        <b>
                                            <?= htmlspecialchars(
                                                $j['nama_matkul']
                                            ) ?>
                                        </b>

                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $j['sks']
                                        ) ?>
                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $j['kelas']
                                        ) ?>
                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $j['semester']
                                        ) ?>
                                    </td>


                                    <td>

                                        <?= date(
                                            'H:i',
                                            strtotime(
                                                $j['jam_masuk']
                                            )
                                        ) ?>

                                        -

                                        <?= date(
                                            'H:i',
                                            strtotime(
                                                $j['jam_selesai']
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $j['nama_ruang']
                                                ?? '-'
                                        ) ?>

                                    </td>

                                </tr>

                            <?php

                            }
                        } else {

                            ?>

                            <tr>

                                <td
                                    colspan="9"
                                    class="text-center text-danger">

                                    Belum ada jadwal mengajar.

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>


        <!-- =====================================================
             ABSENSI HARI INI
        ===================================================== -->

        <div class="card-box">

            <div
                class="d-flex
                       justify-content-between
                       align-items-center
                       flex-wrap
                       gap-2
                       mb-3">

                <div>

                    <h4 class="page-title">

                        Absensi Hari Ini

                    </h4>

                    <p class="page-subtitle mb-0">

                        <?= $hari_ini ?>,
                        <?= date('d-m-Y') ?>

                        — Absensi mata kuliah yang Anda ampu

                    </p>

                </div>


                <div class="info-card text-center">

                    <small class="text-muted">
                        Total Absensi
                    </small>

                    <div class="info-number">

                        <?= $total_absensi ?>

                    </div>

                </div>

            </div>


            <div class="table-responsive">

                <table
                    class="table table-bordered table-hover">

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>NIM</th>

                            <th>Nama</th>

                            <th>Mata Kuliah</th>

                            <th>Semester</th>

                            <th>Pertemuan</th>

                            <th>Status</th>

                            <th>Finger ID</th>

                            <th>Jam Masuk</th>

                            <th>Jam Selesai</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php

                        $no_absen = 1;

                        if (
                            mysqli_num_rows(
                                $absensi_hari_ini
                            ) > 0
                        ) {

                            while (
                                $a =
                                mysqli_fetch_assoc(
                                    $absensi_hari_ini
                                )
                            ) {

                                $status =
                                    strtoupper(
                                        trim(
                                            $a['status']
                                        )
                                    );

                        ?>

                                <tr>

                                    <td>
                                        <?= $no_absen++ ?>
                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $a['nim']
                                        ) ?>
                                    </td>


                                    <td>

                                        <b>
                                            <?= htmlspecialchars(
                                                $a['nama']
                                            ) ?>
                                        </b>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $a['nama_matkul_master']
                                                ?? $a['nama_matkul']
                                        ) ?>

                                        <br>

                                        <small
                                            class="text-muted">

                                            <?= htmlspecialchars(
                                                $a['kode']
                                            ) ?>

                                        </small>

                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $a['semester']
                                        ) ?>
                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $a['pertemuan']
                                        ) ?>
                                    </td>


                                    <td>

                                        <?php

                                        if (
                                            $status ==
                                            'HADIR'
                                        ) {

                                            echo '
                                            <span class="
                                                badge
                                                badge-hadir
                                            ">
                                                HADIR
                                            </span>';
                                        } elseif (
                                            $status ==
                                            'IZIN'
                                        ) {

                                            echo '
                                            <span class="
                                                badge
                                                badge-izin
                                            ">
                                                IZIN
                                            </span>';
                                        } elseif (
                                            $status ==
                                            'SAKIT'
                                        ) {

                                            echo '
                                            <span class="
                                                badge
                                                badge-sakit
                                            ">
                                                SAKIT
                                            </span>';
                                        } elseif (
                                            $status ==
                                            'ALPHA'
                                        ) {

                                            echo '
                                            <span class="
                                                badge
                                                badge-alpha
                                            ">
                                                ALPHA
                                            </span>';
                                        } else {

                                            echo '
                                            <span class="
                                                badge
                                                bg-secondary
                                            ">
                                                '
                                                .
                                                htmlspecialchars(
                                                    $status
                                                )
                                                .
                                                '
                                            </span>';
                                        }

                                        ?>

                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $a['finger_id']
                                                ?? '-'
                                        ) ?>
                                    </td>


                                    <td>

                                        <?= !empty($a['jam_masuk'])
                                            ? date(
                                                'H:i:s',
                                                strtotime(
                                                    $a['jam_masuk']
                                                )
                                            )
                                            : '-'
                                        ?>

                                    </td>


                                    <td>

                                        <?= !empty($a['jam_selesai'])
                                            ? date(
                                                'H:i:s',
                                                strtotime(
                                                    $a['jam_selesai']
                                                )
                                            )
                                            : '-'
                                        ?>

                                    </td>

                                </tr>

                            <?php

                            }
                        } else {

                            ?>

                            <tr>

                                <td
                                    colspan="10"
                                    class="
                                        text-center
                                        text-muted
                                        py-4
                                    ">

                                    <i class="
                                        bi
                                        bi-calendar-x
                                        fs-3
                                    "></i>

                                    <br>

                                    Belum ada absensi hari ini
                                    untuk mata kuliah yang Anda ampu.

                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>


    </div>


    <!-- =====================================================
         JAVASCRIPT
    ===================================================== -->

    <script>
        function toggleMenu() {

            document
                .querySelector(".sidebar")
                .classList
                .toggle("active");

        }


        document
            .querySelectorAll(".sidebar a")
            .forEach(function(link) {

                link.addEventListener(
                    "click",
                    function() {

                        document
                            .querySelector(".sidebar")
                            .classList
                            .remove("active");

                    }
                );

            });
    </script>


</body>

</html>