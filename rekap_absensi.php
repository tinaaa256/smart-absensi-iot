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
   FILTER
===================================================== */

$matkul   = $_GET['matkul'] ?? '';
$kelas    = $_GET['kelas'] ?? '';
$semester = $_GET['semester'] ?? '';

$nama_safe = mysqli_real_escape_string($conn, $nama);


/* =====================================================
   FILTER UTAMA
===================================================== */

$where = "WHERE mk.dosen='$nama_safe'";

if ($matkul != "") {
    $matkul_safe = mysqli_real_escape_string($conn, $matkul);
    $where .= " AND mk.id='$matkul_safe'";
}

if ($kelas != "") {
    $kelas_safe = mysqli_real_escape_string($conn, $kelas);
    $where .= " AND k.kelas='$kelas_safe'";
}

if ($semester != "") {
    $semester_safe = mysqli_real_escape_string($conn, $semester);
    $where .= " AND k.semester='$semester_safe'";
}


/* =====================================================
   DATA FILTER MATA KULIAH
===================================================== */

$listMatkul = mysqli_query($conn, "
    SELECT *
    FROM matkul
    WHERE dosen='$nama_safe'
    ORDER BY nama_matkul
");


/* =====================================================
   DATA FILTER KELAS
   HANYA KELAS DARI MATA KULIAH DOSEN
===================================================== */

$listKelas = mysqli_query($conn, "
    SELECT DISTINCT k.kelas
    FROM krs k
    JOIN matkul mk
        ON mk.id = k.matkul_id
    WHERE mk.dosen='$nama_safe'
    ORDER BY k.kelas
");


/* =====================================================
   DATA FILTER SEMESTER
   HANYA SEMESTER DARI MATA KULIAH DOSEN
===================================================== */

$listSemester = mysqli_query($conn, "
    SELECT DISTINCT k.semester
    FROM krs k
    JOIN matkul mk
        ON mk.id = k.matkul_id
    WHERE mk.dosen='$nama_safe'
    ORDER BY k.semester
");

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>Rekap Absensi - Smart Absensi</title>


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
           TOPBAR
        ===================================================== */

        .topbar {

            background: white;

            padding: 20px;

            border-radius: 12px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, .05);

            margin-bottom: 20px;

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

        }


        /* =====================================================
           TABLE
        ===================================================== */

        .table {

            font-size: 13px;

            vertical-align: middle;

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
           FILTER
        ===================================================== */

        .filter-label {

            font-weight: 600;

            margin-bottom: 6px;

        }


        /* =====================================================
           MOBILE BUTTON
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
         MOBILE MENU
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
             TOPBAR
        ===================================================== -->

        <div class="topbar">

            <h4 class="mb-1">

                Rekap Absensi Mahasiswa

            </h4>

            <small class="text-muted">

                Rekap berdasarkan KRS dan 12 pertemuan perkuliahan

            </small>

        </div>


        <!-- =====================================================
             CARD
        ===================================================== -->

        <div class="card-box">


            <!-- =================================================
                 FILTER
            ================================================= -->

            <form method="GET">

                <div class="row g-3">


                    <!-- MATA KULIAH -->

                    <div class="col-md-4">

                        <label class="filter-label">

                            Mata Kuliah

                        </label>

                        <select
                            name="matkul"
                            class="form-select">

                            <option value="">

                                Semua Mata Kuliah

                            </option>


                            <?php

                            while (
                                $m =
                                mysqli_fetch_assoc(
                                    $listMatkul
                                )
                            ) {

                            ?>

                                <option
                                    value="<?= $m['id'] ?>"
                                    <?= (
                                        $matkul ==
                                        $m['id']
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>>

                                    <?= htmlspecialchars(
                                        $m['nama_matkul']
                                    ) ?>

                                </option>

                            <?php

                            }

                            ?>

                        </select>

                    </div>


                    <!-- KELAS -->

                    <div class="col-md-3">

                        <label class="filter-label">

                            Kelas

                        </label>

                        <select
                            name="kelas"
                            class="form-select">

                            <option value="">

                                Semua Kelas

                            </option>


                            <?php

                            while (
                                $k =
                                mysqli_fetch_assoc(
                                    $listKelas
                                )
                            ) {

                            ?>

                                <option
                                    value="<?= htmlspecialchars(
                                                $k['kelas']
                                            ) ?>"
                                    <?= (
                                        $kelas ==
                                        $k['kelas']
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>>

                                    <?= htmlspecialchars(
                                        $k['kelas']
                                    ) ?>

                                </option>

                            <?php

                            }

                            ?>

                        </select>

                    </div>


                    <!-- SEMESTER -->

                    <div class="col-md-3">

                        <label class="filter-label">

                            Semester

                        </label>

                        <select
                            name="semester"
                            class="form-select">

                            <option value="">

                                Semua Semester

                            </option>


                            <?php

                            while (
                                $s =
                                mysqli_fetch_assoc(
                                    $listSemester
                                )
                            ) {

                            ?>

                                <option
                                    value="<?= $s['semester'] ?>"
                                    <?= (
                                        $semester ==
                                        $s['semester']
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>>

                                    Semester
                                    <?= $s['semester'] ?>

                                </option>

                            <?php

                            }

                            ?>

                        </select>

                    </div>


                    <!-- BUTTON -->

                    <div
                        class="
                            col-md-2
                            d-flex
                            align-items-end
                        ">

                        <button
                            type="submit"
                            class="btn btn-primary w-100">

                            <i class="bi bi-search"></i>

                            Filter

                        </button>

                    </div>

                </div>

            </form>


            <hr class="my-4">


            <!-- =================================================
                 TABLE
            ================================================= -->

            <div class="table-responsive">

                <table
                    class="
                        table
                        table-bordered
                        table-hover
                    ">

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>NIM</th>

                            <th>Nama</th>

                            <th>Mata Kuliah</th>

                            <th>Kelas</th>

                            <th>Semester</th>

                            <th>Hadir</th>

                            <th>Total Pertemuan</th>

                            <th>Persentase</th>

                            <th>Status Ujian</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php

                        $no = 1;


                        /* =================================================
                           AMBIL MAHASISWA DARI KRS
                        ================================================= */

                        $query = mysqli_query($conn, "

                            SELECT

                                m.id_mahasiswa,

                                m.nim,

                                m.nama,

                                k.kelas,

                                k.semester,

                                mk.id AS matkul_id,

                                mk.nama_matkul


                            FROM krs k


                            JOIN mahasiswa m

                                ON m.id_mahasiswa =
                                   k.mahasiswa_id


                            JOIN matkul mk

                                ON mk.id =
                                   k.matkul_id


                            $where


                            ORDER BY

                                mk.nama_matkul ASC,

                                k.kelas ASC,

                                m.nama ASC

                        ");


                        if (!$query) {

                            die("Error query: "
                                .
                                mysqli_error($conn));
                        }


                        if (
                            mysqli_num_rows(
                                $query
                            ) > 0
                        ) {


                            while (
                                $d =
                                mysqli_fetch_assoc(
                                    $query
                                )
                            ) {


                                $idmhs =
                                    (int)$d['id_mahasiswa'];


                                $idmatkul =
                                    (int)$d['matkul_id'];


                                /* =================================================
                                   TOTAL PERTEMUAN
                                   SETIAP MATA KULIAH = 12 PERTEMUAN
                                ================================================= */

                                $jml_pertemuan = 12;


                                /* =================================================
                                   TOTAL HADIR
                                   DIAMBIL DARI TABEL ABSENSI
                                ================================================= */

                                $qHadir =
                                    mysqli_query(
                                        $conn,
                                        "

                                    SELECT
                                        COUNT(*) AS total

                                    FROM absensi

                                    WHERE mahasiswa_id='$idmhs'

                                    AND matkul_id='$idmatkul'

                                    AND UPPER(
                                        TRIM(status)
                                    )='HADIR'

                                    "
                                    );


                                if (!$qHadir) {

                                    $jml_hadir = 0;
                                } else {

                                    $dataHadir =
                                        mysqli_fetch_assoc(
                                            $qHadir
                                        );


                                    $jml_hadir =
                                        (int)(
                                            $dataHadir['total']
                                            ?? 0
                                        );
                                }


                                /* =================================================
                                   PERSENTASE KEHADIRAN
                                ================================================= */

                                if (
                                    $jml_pertemuan > 0
                                ) {

                                    $persen =
                                        (
                                            $jml_hadir
                                            /
                                            $jml_pertemuan
                                        )
                                        * 100;
                                } else {

                                    $persen = 0;
                                }


                                /* =================================================
                                   BATASI MAKSIMAL 100%
                                ================================================= */

                                $persen =
                                    min(
                                        100,
                                        $persen
                                    );


                        ?>

                                <tr>

                                    <!-- NO -->

                                    <td>

                                        <?= $no++ ?>

                                    </td>


                                    <!-- NIM -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $d['nim']
                                        ) ?>

                                    </td>


                                    <!-- NAMA -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $d['nama']
                                        ) ?>

                                    </td>


                                    <!-- MATA KULIAH -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $d['nama_matkul']
                                        ) ?>

                                    </td>


                                    <!-- KELAS -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $d['kelas']
                                        ) ?>

                                    </td>


                                    <!-- SEMESTER -->

                                    <td>

                                        <?= $d['semester'] ?>

                                    </td>


                                    <!-- HADIR -->

                                    <td>

                                        <span
                                            class="
                                                badge
                                                bg-success
                                            ">

                                            <?= $jml_hadir ?>

                                        </span>

                                    </td>


                                    <!-- TOTAL PERTEMUAN -->

                                    <td>

                                        <?= $jml_pertemuan ?>

                                    </td>


                                    <!-- PERSENTASE -->

                                    <td>

                                        <?= number_format(
                                            $persen,
                                            2
                                        ) ?>%

                                    </td>


                                    <!-- STATUS UJIAN -->

                                    <td>

                                        <?php

                                        if (
                                            $persen >= 75
                                        ) {

                                        ?>

                                            <span
                                                class="
                                                    badge
                                                    bg-success
                                                ">

                                                Layak Ujian

                                            </span>

                                        <?php

                                        } else {

                                        ?>

                                            <span
                                                class="
                                                    badge
                                                    bg-danger
                                                ">

                                                Tidak Layak

                                            </span>

                                        <?php

                                        }

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

                                    <i
                                        class="
                                            bi
                                            bi-inbox
                                            fs-3
                                        "></i>

                                    <br>

                                    Tidak ada data mahasiswa
                                    sesuai filter.

                                </td>

                            </tr>

                        <?php

                        }

                        ?>

                    </tbody>

                </table>

            </div>


            <hr>


            <!-- =================================================
                 EXPORT
            ================================================= -->

            <div
                class="
                    d-flex
                    justify-content-end
                ">

                <a
                    href="export_rekap_excel.php?matkul=<?= urlencode($matkul) ?>&kelas=<?= urlencode($kelas) ?>&semester=<?= urlencode($semester) ?>"
                    class="btn btn-success">

                    <i
                        class="
                            bi
                            bi-file-earmark-excel
                        "></i>

                    Export Excel

                </a>

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