<?php
include "config.php";
session_start();

/* =====================================================
   CEK LOGIN DOSEN
===================================================== */

if (
    !isset($_SESSION['login']) ||
    $_SESSION['role'] != 'dosen'
) {
    header("Location: login.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

$nama = $_SESSION['nama'] ?? $_SESSION['username'] ?? '';

$nama_esc = mysqli_real_escape_string($conn, $nama);


/* =====================================================
   HARI INI
===================================================== */

$hari = [
    'Sunday'    => 'Minggu',
    'Monday'    => 'Senin',
    'Tuesday'   => 'Selasa',
    'Wednesday' => 'Rabu',
    'Thursday'  => 'Kamis',
    'Friday'    => 'Jumat',
    'Saturday'  => 'Sabtu'
][date('l')];


/* =====================================================
   DAFTAR MATA KULIAH DOSEN
===================================================== */

$matkul_dosen = mysqli_query($conn, "
    SELECT 
        id,
        kode,
        nama_matkul,
        sks
    FROM matkul
    WHERE dosen = '$nama_esc'
    ORDER BY nama_matkul ASC
");

if (!$matkul_dosen) {
    die(mysqli_error($conn));
}


/* =====================================================
   STATISTIK TOTAL MATA KULIAH
===================================================== */

$q_total_matkul = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM matkul
    WHERE dosen = '$nama_esc'
");

$d_total_matkul = mysqli_fetch_assoc($q_total_matkul);

$total_matkul = $d_total_matkul['total'] ?? 0;


/* =====================================================
   TOTAL KELAS
===================================================== */

$q_total_kelas = mysqli_query($conn, "
    SELECT COUNT(DISTINCT CONCAT(
        j.matkul_id,
        '-',
        j.kelas,
        '-',
        j.semester
    )) AS total

    FROM jadwal j

    INNER JOIN matkul m
        ON m.id = j.matkul_id

    WHERE m.dosen = '$nama_esc'
");

$d_total_kelas = mysqli_fetch_assoc($q_total_kelas);

$total_kelas = $d_total_kelas['total'] ?? 0;


/* =====================================================
   TOTAL MAHASISWA
===================================================== */

$q_total_mhs = mysqli_query($conn, "
    SELECT COUNT(DISTINCT k.mahasiswa_id) AS total

    FROM krs k

    INNER JOIN matkul m
        ON m.id = k.matkul_id

    WHERE m.dosen = '$nama_esc'
");

$d_total_mhs = mysqli_fetch_assoc($q_total_mhs);

$total_mhs = $d_total_mhs['total'] ?? 0;


/* =====================================================
   RATA-RATA KEHADIRAN
===================================================== */

$q_kehadiran = mysqli_query($conn, "

    SELECT

        COUNT(DISTINCT k.mahasiswa_id) AS total_mhs,

        COUNT(DISTINCT
            CASE
                WHEN UPPER(a.status) = 'HADIR'
                THEN a.mahasiswa_id
            END
        ) AS total_hadir

    FROM krs k

    INNER JOIN matkul m
        ON m.id = k.matkul_id

    LEFT JOIN absensi a
        ON a.mahasiswa_id = k.mahasiswa_id
        AND a.matkul_id = k.matkul_id

    WHERE m.dosen = '$nama_esc'

");

if (!$q_kehadiran) {
    die(mysqli_error($conn));
}

$d_kehadiran = mysqli_fetch_assoc($q_kehadiran);

$total_mhs_kehadiran =
    (int)($d_kehadiran['total_mhs'] ?? 0);

$total_hadir =
    (int)($d_kehadiran['total_hadir'] ?? 0);

if ($total_mhs_kehadiran > 0) {

    $rata_kehadiran = round(
        ($total_hadir / $total_mhs_kehadiran) * 100
    );
} else {

    $rata_kehadiran = 0;
}


/* =====================================================
   JADWAL MENGAJAR HARI INI
===================================================== */

$q_jadwal_hari_ini = mysqli_query($conn, "

    SELECT

        j.id,
        j.hari,
        j.jam_masuk,
        j.jam_selesai,
        j.kelas,
        j.semester,

        m.kode,
        m.nama_matkul,

        r.kode_ruang,
        r.nama_ruang

    FROM jadwal j

    INNER JOIN matkul m
        ON m.id = j.matkul_id

    LEFT JOIN ruangan r
        ON r.id = j.ruangan_id

    WHERE m.dosen = '$nama_esc'

    AND j.hari = '" .
    mysqli_real_escape_string($conn, $hari) .
    "'

    ORDER BY j.jam_masuk ASC

");

if (!$q_jadwal_hari_ini) {
    die(mysqli_error($conn));
}


/* =====================================================
   REKAP KEHADIRAN PER MATA KULIAH
===================================================== */

$q_rekap = mysqli_query($conn, "

    SELECT

        m.id,
        m.kode,
        m.nama_matkul,

        COUNT(a.id) AS total_absensi,

        SUM(
            CASE
                WHEN UPPER(a.status) = 'HADIR'
                THEN 1
                ELSE 0
            END
        ) AS total_hadir

    FROM matkul m

    LEFT JOIN absensi a
        ON a.matkul_id = m.id

    WHERE m.dosen = '$nama_esc'

    GROUP BY
        m.id,
        m.kode,
        m.nama_matkul

    ORDER BY m.nama_matkul ASC

");

if (!$q_rekap) {
    die(mysqli_error($conn));
}


/* =====================================================
   FILTER ABSENSI
===================================================== */

$filter = '';

if (
    isset($_GET['matkul_id']) &&
    $_GET['matkul_id'] != ''
) {

    $matkul_id = (int)$_GET['matkul_id'];

    $filter = "
        AND a.matkul_id = '$matkul_id'
    ";
}


/* =====================================================
   ABSENSI HARI INI
   HANYA MATA KULIAH DOSEN YANG LOGIN
===================================================== */

$q_absensi = mysqli_query($conn, "

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

    INNER JOIN matkul m
        ON m.id = a.matkul_id

    WHERE m.dosen = '$nama_esc'

    AND DATE(a.tanggal) = CURDATE()

    $filter

    ORDER BY
        a.jam_masuk DESC

");

if (!$q_absensi) {
    die(mysqli_error($conn));
}

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        Dashboard Dosen - Smart Absensi
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">


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
            font-family: Arial, Helvetica, sans-serif;
        }


        /* SIDEBAR */

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

            padding-left: 28px;

        }

        .sidebar a i {

            margin-right: 10px;

        }


        /* MAIN */

        .main {

            margin-left: 250px;

            padding: 25px;

        }


        /* TOPBAR */

        .topbar {

            background: white;

            padding: 18px 20px;

            border-radius: 12px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, .05);

        }

        .topbar h5 {

            font-weight: bold;

            margin-bottom: 6px;

        }

        .welcome {

            color: #666;

            font-size: 14px;

        }

        #jam {

            color: #0d6efd;

            font-size: 13px;

            font-weight: 600;

            margin-top: 5px;

        }


        /* STAT CARD */

        .stat-card {

            background: white;

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

            margin: 0 0 4px;

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


        /* CONTENT */

        .content-card {

            background: white;

            border-radius: 14px;

            padding: 18px;

            box-shadow:
                0 2px 12px rgba(0, 0, 0, .05);

        }

        .content-title {

            font-size: 16px;

            font-weight: bold;

            margin-bottom: 15px;

        }


        /* TABLE */

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


        /* STATUS */

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


        /* INFO */

        .info-box {

            border: 1px solid #eee;

            border-radius: 10px;

            padding: 15px;

            background: #fff;

        }

        .info-box h6 {

            font-weight: bold;

            margin-bottom: 5px;

        }

        .info-box small {

            color: #777;

        }


        /* FILTER */

        .filter-box {

            background: #f8fafc;

            border-radius: 10px;

            padding: 12px;

        }


        /* MENU MOBILE */

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


        /* MOBILE */

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

            .table {

                font-size: 12px;

                white-space: nowrap;

            }

        }
    </style>

</head>


<body>


    <!-- MENU MOBILE -->

    <div
        class="menu-btn"
        onclick="toggleMenu()">

        ☰

    </div>


    <!-- SIDEBAR -->

    <div class="sidebar">

        <h4>
            Smart Absensi
        </h4>


        <a href="dashboard_dosen.php">

            <i class="bi bi-speedometer2"></i>

            Dashboard

        </a>


        <a href="jadwal_dosen.php">

            <i class="bi bi-calendar"></i>

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


    <!-- MAIN -->

    <div class="main">


        <!-- TOPBAR -->

        <div class="topbar mb-4">

            <div class="d-flex
        justify-content-between
        align-items-center">

                <div>

                    <h5>
                        Dashboard Dosen
                    </h5>

                    <div class="welcome">

                        Selamat datang,

                        <b>
                            <?= htmlspecialchars($nama); ?>
                        </b>

                    </div>

                    <div id="jam"></div>

                </div>


                <div class="text-end">

                    <i class="
                bi bi-person-circle
                fs-2
                text-primary
            "></i>

                    <div>

                        <small>
                            Dosen
                        </small>

                    </div>

                </div>

            </div>

        </div>


        <!-- STATISTIK -->

        <div class="row g-4 mb-4">


            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <p>
                        Mata Kuliah Diampu
                    </p>

                    <h2>
                        <?= $total_matkul; ?>
                    </h2>

                    <small>
                        Total Mata Kuliah
                    </small>

                    <i class="
                bi bi-book-fill
                stat-icon
                text-primary
            "></i>

                </div>

            </div>


            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <p>
                        Kelas
                    </p>

                    <h2>
                        <?= $total_kelas; ?>
                    </h2>

                    <small>
                        Kelas yang diampu
                    </small>

                    <i class="
                bi bi-people-fill
                stat-icon
                text-success
            "></i>

                </div>

            </div>


            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <p>
                        Total Mahasiswa
                    </p>

                    <h2>
                        <?= $total_mhs; ?>
                    </h2>

                    <small>
                        Mahasiswa terdaftar
                    </small>

                    <i class="
                bi bi-person-fill
                stat-icon
                text-warning
            "></i>

                </div>

            </div>


            <div class="col-xl-3 col-md-6">

                <div class="stat-card">

                    <p>
                        Rata-rata Kehadiran
                    </p>

                    <h2>
                        <?= $rata_kehadiran; ?>%
                    </h2>

                    <small>
                        Kehadiran mahasiswa
                    </small>

                    <i class="
                bi bi-bar-chart-fill
                stat-icon
                text-primary
            "></i>

                </div>

            </div>

        </div>


        <!-- JADWAL + REKAP -->

        <div class="row g-4 mb-4">


            <!-- JADWAL HARI INI -->

            <div class="col-lg-7">

                <div class="content-card">

                    <div class="content-title">

                        Jadwal Mengajar Hari Ini

                        <span class="text-primary">
                            (<?= $hari; ?>)
                        </span>

                    </div>


                    <div class="table-responsive">

                        <table class="table table-hover">

                            <thead>

                                <tr>

                                    <th>No</th>
                                    <th>Mata Kuliah</th>
                                    <th>Jam</th>
                                    <th>Kelas</th>
                                    <th>Ruangan</th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php

                                $no_jadwal = 1;

                                if (
                                    mysqli_num_rows(
                                        $q_jadwal_hari_ini
                                    ) > 0
                                ):

                                    while (
                                        $j =
                                        mysqli_fetch_assoc(
                                            $q_jadwal_hari_ini
                                        )
                                    ):

                                ?>

                                        <tr>

                                            <td>
                                                <?= $no_jadwal++; ?>
                                            </td>

                                            <td>

                                                <b>
                                                    <?= htmlspecialchars(
                                                        $j['nama_matkul']
                                                    ); ?>
                                                </b>

                                                <br>

                                                <small class="text-muted">

                                                    <?= htmlspecialchars(
                                                        $j['kode'] ?? ''
                                                    ); ?>

                                                </small>

                                            </td>

                                            <td>

                                                <?= date(
                                                    'H:i',
                                                    strtotime(
                                                        $j['jam_masuk']
                                                    )
                                                ); ?>

                                                -

                                                <?= date(
                                                    'H:i',
                                                    strtotime(
                                                        $j['jam_selesai']
                                                    )
                                                ); ?>

                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $j['kelas']
                                                ); ?>
                                            </td>

                                            <td>

                                                <?= htmlspecialchars(
                                                    $j['nama_ruang']
                                                        ?? $j['kode_ruang']
                                                        ?? '-'
                                                ); ?>

                                            </td>

                                        </tr>

                                    <?php

                                    endwhile;

                                else:

                                    ?>

                                    <tr>

                                        <td
                                            colspan="5"
                                            class="text-center text-muted py-4">

                                            Tidak ada jadwal
                                            mengajar hari ini.

                                        </td>

                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            <!-- REKAP -->

            <div class="col-lg-5">

                <div class="content-card">

                    <div class="content-title">

                        Rekap Kehadiran

                    </div>


                    <?php

                    if (
                        mysqli_num_rows($q_rekap) > 0
                    ):

                        while (
                            $r =
                            mysqli_fetch_assoc(
                                $q_rekap
                            )
                        ):

                            $total =
                                (int)$r['total_absensi'];

                            $hadir =
                                (int)$r['total_hadir'];

                            if ($total > 0) {

                                $persen = round(
                                    ($hadir / $total) * 100
                                );
                            } else {

                                $persen = 0;
                            }

                    ?>

                            <div class="info-box mb-3">

                                <h6>

                                    <?= htmlspecialchars(
                                        $r['nama_matkul']
                                    ); ?>

                                </h6>

                                <small>

                                    <?= htmlspecialchars(
                                        $r['kode']
                                    ); ?>

                                </small>


                                <div
                                    class="progress mt-2"
                                    style="height:8px;">

                                    <div
                                        class="progress-bar bg-primary"
                                        role="progressbar"
                                        style="width:
                            <?= $persen; ?>%;">

                                    </div>

                                </div>


                                <div class="
                    d-flex
                    justify-content-between
                    mt-2
                ">

                                    <small>

                                        Hadir:
                                        <?= $hadir; ?>

                                    </small>

                                    <small>

                                        <?= $persen; ?>%

                                    </small>

                                </div>

                            </div>

                        <?php

                        endwhile;

                    else:

                        ?>

                        <div class="
                text-center
                text-muted
                py-4
            ">

                            Belum ada data
                            kehadiran.

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </div>


        <!-- =====================================================
     ABSENSI HARI INI
===================================================== -->

        <div class="content-card mb-4">


            <div class="
        d-flex
        justify-content-between
        align-items-center
        mb-3
    ">

                <div class="content-title mb-0">

                    <i class="bi bi-fingerprint text-primary"></i>

                    Absensi Hari Ini

                </div>


                <a
                    href="rekap_absensi.php"
                    class="btn btn-sm btn-primary">

                    Lihat Rekap

                </a>

            </div>


            <!-- FILTER MATA KULIAH -->

            <div class="filter-box mb-3">

                <form
                    method="GET"
                    class="row g-2">

                    <div class="col-md-5">

                        <select
                            name="matkul_id"
                            class="form-select"
                            onchange="this.form.submit()">

                            <option value="">

                                Semua Mata Kuliah Saya

                            </option>


                            <?php

                            mysqli_data_seek(
                                $matkul_dosen,
                                0
                            );

                            while (
                                $m =
                                mysqli_fetch_assoc(
                                    $matkul_dosen
                                )
                            ):

                            ?>

                                <option
                                    value="<?= $m['id']; ?>"

                                    <?= (
                                        isset(
                                            $_GET['matkul_id']
                                        ) &&
                                        $_GET['matkul_id']
                                        == $m['id']
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>>

                                    <?= htmlspecialchars(
                                        $m['kode']
                                    ); ?>

                                    -

                                    <?= htmlspecialchars(
                                        $m['nama_matkul']
                                    ); ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                </form>

            </div>


            <div class="table-responsive">

                <table class="
            table
            table-bordered
            table-hover
        ">

                    <thead class="table-primary">

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

                            <th>Finger ID</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php

                        $no_absen = 1;

                        if (
                            mysqli_num_rows($q_absensi) > 0
                        ):

                            while (
                                $a =
                                mysqli_fetch_assoc(
                                    $q_absensi
                                )
                            ):

                        ?>

                                <tr>

                                    <td>
                                        <?= $no_absen++; ?>
                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $a['nim']
                                        ); ?>
                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $a['nama']
                                        ); ?>
                                    </td>


                                    <td>

                                        <b>
                                            <?= htmlspecialchars(
                                                $a['nama_matkul']
                                            ); ?>
                                        </b>

                                        <br>

                                        <small class="text-muted">

                                            <?= htmlspecialchars(
                                                $a['kode']
                                            ); ?>

                                        </small>

                                    </td>


                                    <td>
                                        <?= htmlspecialchars(
                                            $a['pertemuan']
                                        ); ?>
                                    </td>


                                    <td>

                                        <?php

                                        $status =
                                            strtoupper(
                                                trim(
                                                    $a['status']
                                                )
                                            );

                                        if (
                                            $status == 'HADIR'
                                        ) {

                                            echo '
                            <span class="
                                badge
                                badge-hadir
                            ">
                                HADIR
                            </span>';
                                        } elseif (
                                            $status == 'IZIN'
                                        ) {

                                            echo '
                            <span class="
                                badge
                                badge-izin
                            ">
                                IZIN
                            </span>';
                                        } elseif (
                                            $status == 'SAKIT'
                                        ) {

                                            echo '
                            <span class="
                                badge
                                badge-sakit
                            ">
                                SAKIT
                            </span>';
                                        } elseif (
                                            $status == 'ALPHA'
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
                                                '</span>';
                                        }

                                        ?>

                                    </td>


                                    <td>

                                        <?= date(
                                            'd-m-Y',
                                            strtotime(
                                                $a['tanggal']
                                            )
                                        ); ?>

                                    </td>


                                    <td>

                                        <?= !empty($a['jam_masuk'])

                                            ? date(
                                                'H:i:s',
                                                strtotime(
                                                    $a['jam_masuk']
                                                )
                                            )

                                            : '-';

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

                                            : '-';

                                        ?>

                                    </td>


                                    <td>

                                        <?= !empty($a['finger_id'])

                                            ? htmlspecialchars(
                                                $a['finger_id']
                                            )

                                            : '-';

                                        ?>

                                    </td>

                                </tr>


                            <?php

                            endwhile;

                        else:

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
                            bi bi-calendar-x
                            fs-3
                        "></i>

                                    <br>

                                    Belum ada absensi
                                    hari ini untuk
                                    mata kuliah Anda.

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


    </div>


    <!-- JAVASCRIPT -->

    <script>
        function toggleMenu() {

            document
                .querySelector(".sidebar")
                .classList
                .toggle("active");

        }


        /* JAM REALTIME */

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


            let tanggal =
                now.getDate();


            let bln =
                bulan[now.getMonth()];


            let tahun =
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

                `${h}, ${tanggal} ${bln} ${tahun}
        | ${jam}:${menit}:${detik}`;

        }


        setInterval(
            updateJam,
            1000
        );

        updateJam();
    </script>


</body>

</html>