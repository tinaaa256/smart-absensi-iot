<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

/* =========================
   FILTER
========================= */

$search   = $_GET['search'] ?? '';
$semester = $_GET['semester'] ?? '';


/* =========================
   QUERY KRS
   SEMESTER DIAMBIL DARI
   PROFIL MAHASISWA
========================= */

$sql = "
SELECT
    k.mahasiswa_id,
    m.nama,
    m.semester
FROM krs k
JOIN mahasiswa m
    ON m.id_mahasiswa = k.mahasiswa_id
WHERE 1
";


/* =========================
   FILTER NAMA
========================= */

if ($search != '') {

    $search_safe = mysqli_real_escape_string(
        $conn,
        $search
    );

    $sql .= "
        AND m.nama LIKE '%$search_safe%'
    ";
}


/* =========================
   FILTER SEMESTER MAHASISWA
========================= */

if ($semester != '') {

    $semester_safe = mysqli_real_escape_string(
        $conn,
        $semester
    );

    $sql .= "
        AND m.semester = '$semester_safe'
    ";
}


/* =========================
   GROUP
   1 MAHASISWA + 1 SEMESTER
   = 1 BARIS
========================= */

$sql .= "

GROUP BY
    k.mahasiswa_id,
    m.nama,
    m.semester

ORDER BY
    m.nama ASC

";


$data = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        KRS Mahasiswa - Smart Absensi
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
        }


        body {

            background: #f1f5f9;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {

            width: 250px;

            height: 100vh;

            background: #0d6efd;

            position: fixed;

            top: 0;

            left: 0;

            padding-top: 20px;

        }


        .sidebar h4 {

            color: #fff;

            text-align: center;

            margin-bottom: 30px;

            font-weight: bold;

        }


        .sidebar a {

            display: block;

            color: #fff;

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


        /* =========================
           MAIN
        ========================= */

        .main {

            margin-left: 250px;

            padding: 25px;

        }


        .card-box {

            background: #fff;

            padding: 25px;

            border-radius: 14px;

            box-shadow:
                0 2px 12px rgba(0, 0, 0, .05);

        }


        /* =========================
           SIDE PANEL
        ========================= */

        .sidepanel {

            height: 100%;

            width: 0;

            position: fixed;

            top: 0;

            right: 0;

            background: #fff;

            overflow-x: hidden;

            transition: 0.3s;

            box-shadow:
                -5px 0 15px rgba(0, 0, 0, 0.15);

            z-index: 999;

            padding-top: 60px;

        }


        .sidepanel a.closebtn {

            position: absolute;

            top: 10px;

            right: 15px;

            font-size: 30px;

            text-decoration: none;

            color: #000;

        }


        .side-content {

            padding: 20px;

        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 768px) {

            .sidebar {

                width: 200px;

            }

            .main {

                margin-left: 200px;

                padding: 15px;

            }

            .sidepanel {

                max-width: 100%;

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

        <div class="card-box">


            <h4 class="mb-4">

                KRS Mahasiswa

            </h4>



            <!-- =================================================
             FILTER
        ================================================== -->

            <form
                method="GET"
                class="row g-2 mb-3">


                <!-- SEARCH -->

                <div class="col-md-5">

                    <input

                        type="text"

                        name="search"

                        class="form-control"

                        placeholder="Cari nama mahasiswa..."

                        value="<?= htmlspecialchars($search) ?>">

                </div>



                <!-- SEMESTER -->

                <div class="col-md-3">

                    <select
                        name="semester"
                        class="form-control">


                        <option value="">

                            Semua Semester

                        </option>


                        <?php

                        for (
                            $i = 1;
                            $i <= 8;
                            $i++
                        ) {

                        ?>

                            <option

                                value="<?= $i ?>"

                                <?= (
                                    $semester == $i
                                )
                                    ? 'selected'
                                    : ''
                                ?>>

                                Semester <?= $i ?>

                            </option>


                        <?php } ?>


                    </select>

                </div>



                <!-- BUTTON -->

                <div class="col-md-2">

                    <button
                        type="submit"
                        class="btn btn-primary w-100">

                        Filter

                    </button>

                </div>


            </form>



            <!-- =================================================
             TABLE KRS
        ================================================== -->

            <div class="table-responsive">


                <table
                    class="table
                       table-bordered
                       table-striped">


                    <thead class="table-primary">


                        <tr>

                            <th>
                                No
                            </th>

                            <th>
                                Nama
                            </th>

                            <th>
                                Semester
                            </th>

                            <th>
                                Aksi
                            </th>

                        </tr>


                    </thead>



                    <tbody>


                        <?php

                        $no = 1;


                        if (
                            mysqli_num_rows($data)
                            > 0
                        ) {


                            while (
                                $d =
                                mysqli_fetch_assoc($data)
                            ) {


                        ?>


                                <tr>


                                    <!-- NO -->

                                    <td>

                                        <?= $no++ ?>

                                    </td>



                                    <!-- NAMA -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $d['nama']
                                        ) ?>

                                    </td>



                                    <!-- SEMESTER PROFIL -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $d['semester']
                                        ) ?>

                                    </td>



                                    <!-- AKSI -->

                                    <td>


                                        <!-- =====================
                                     DETAIL BIRU
                                ====================== -->

                                        <button

                                            type="button"

                                            class="
                                        btn
                                        btn-primary
                                        btn-sm
                                    "

                                            onclick="
                                        openPanel(
                                            <?= $d['mahasiswa_id'] ?>,
                                            '<?= $d['semester'] ?>'
                                        )
                                    ">

                                            Detail

                                        </button>



                                        <!-- =====================
                                     CETAK MERAH
                                ====================== -->

                                        <a

                                            href="
                                        export_krs_pdf.php
                                        ?id=<?= $d['mahasiswa_id'] ?>
                                        &s=<?= $d['semester'] ?>
                                    "

                                            class="
                                        btn
                                        btn-danger
                                        btn-sm
                                    ">

                                            Cetak

                                        </a>


                                    </td>


                                </tr>


                            <?php


                            }
                        } else {


                            ?>


                            <tr>


                                <td
                                    colspan="4"
                                    class="text-center text-muted">

                                    Tidak ada data KRS.

                                </td>


                            </tr>


                        <?php } ?>


                    </tbody>


                </table>


            </div>


        </div>

    </div>



    <!-- =====================================================
     SIDE PANEL DETAIL
===================================================== -->

    <div
        id="panel"
        class="sidepanel">


        <!-- CLOSE -->

        <a

            href="javascript:void(0)"

            class="closebtn"

            onclick="closePanel()">

            &times;

        </a>



        <div class="side-content">


            <h5 class="mb-3">

                Detail KRS

            </h5>


            <div id="isi">

                Loading...

            </div>


        </div>


    </div>



    <!-- =====================================================
     JAVASCRIPT
===================================================== -->

    <script>
        function openPanel(
            id,
            semester
        ) {


            /* BUKA PANEL */

            document
                .getElementById('panel')
                .style.width = "450px";



            /* LOADING */

            document
                .getElementById('isi')
                .innerHTML =

                "<div class='text-center p-3'>" +

                "<div class='spinner-border text-primary'>" +

                "</div>" +

                "<br><br>" +

                "Loading..." +

                "</div>";



            /* AMBIL DETAIL */

            fetch(

                    "krs_detail.php?id=" +

                    encodeURIComponent(id) +

                    "&s=" +

                    encodeURIComponent(semester)

                )


                .then(
                    response =>
                    response.text()
                )


                .then(
                    data => {

                        document
                            .getElementById('isi')
                            .innerHTML = data;

                    }
                )


                .catch(
                    error => {

                        document
                            .getElementById('isi')
                            .innerHTML =

                            "<div class='alert alert-danger'>" +

                            "Gagal memuat detail KRS." +

                            "</div>";

                    }
                );

        }



        function closePanel() {


            document
                .getElementById('panel')
                .style.width = "0";

        }
    </script>


</body>

</html>