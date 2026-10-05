<?php

include "config.php";
session_start();

if (
    !isset($_SESSION['login']) ||
    $_SESSION['role'] != 'mahasiswa'
) {
    header("Location: login.php");
    exit;
}


/* =====================================================
   AMBIL USER LOGIN
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

$nama = $mhs['nama'] ?? 'Mahasiswa';


/* =====================================================
   FILTER HARI
===================================================== */

$hari_filter = $_GET['hari'] ?? '';

$hari_filter = trim($hari_filter);

$hari_filter = mysqli_real_escape_string(
    $conn,
    $hari_filter
);


/* =====================================================
   QUERY JADWAL MAHASISWA
=====================================================

   KUNCI:
   j.matkul_id = k.matkul_id
   j.kelas     = k.kelas
   j.semester  = k.semester

   Jadi jadwal harus benar-benar cocok
   dengan KRS mahasiswa.
===================================================== */

$sql = "

SELECT

    k.id AS krs_id,

    k.matkul_id,

    k.semester AS krs_semester,

    k.kelas AS krs_kelas,


    j.id AS jadwal_id,

    j.hari,

    j.jam_masuk,

    j.jam_selesai,

    j.kelas AS jadwal_kelas,

    j.semester AS jadwal_semester,


    m.kode,

    m.nama_matkul,

    m.dosen,

    m.sks,


    r.nama_ruang


FROM krs k


INNER JOIN jadwal j

    ON j.matkul_id = k.matkul_id

    AND j.kelas = k.kelas

    AND j.semester = k.semester


INNER JOIN matkul m

    ON m.id = k.matkul_id


LEFT JOIN ruangan r

    ON r.id = j.ruangan_id


WHERE k.mahasiswa_id='$mahasiswa_id'

";


/* =====================================================
   FILTER HARI
===================================================== */

if ($hari_filter != '') {

    $sql .= "
        AND j.hari='$hari_filter'
    ";
}


/* =====================================================
   URUTKAN JADWAL
===================================================== */

$sql .= "

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

    j.jam_masuk ASC,

    m.nama_matkul ASC

";


$jadwal = mysqli_query($conn, $sql);


if (!$jadwal) {

    die("Error query jadwal: "
        . mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Jadwal Mahasiswa</title>

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

            top: 0;
            left: 0;

            width: 250px;
            height: 100vh;

            background: #0d6efd;

            padding: 22px 18px;

            overflow-y: auto;

            z-index: 1000;

            transition: left .3s ease;
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

            gap: 10px;

            color: white;

            text-decoration: none;

            padding: 13px 15px;

            margin-bottom: 8px;

            border-radius: 8px;

            transition: .25s;

        }

        .sidebar a:hover,
        .sidebar a.active {

            background: rgba(255, 255, 255, .18);

        }

        .main {

            margin-left: 250px;

            width: calc(100% - 250px);

            min-height: 100vh;

            padding: 25px;

        }

        .box {

            background: white;

            border-radius: 14px;

            padding: 20px;

            margin-bottom: 20px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, .07);

        }

        .page-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

        }

        .page-title {

            font-size: 24px;

            font-weight: bold;

            margin-bottom: 7px;

        }

        .page-subtitle {

            color: #64748b;

            margin: 0;

        }

        .page-icon {

            width: 65px;

            height: 65px;

            border-radius: 14px;

            background: #e8f1ff;

            color: #0d6efd;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 32px;

        }

        .filter-box {

            background: #f8fafc;

            border-radius: 12px;

            padding: 15px;

            margin-top: 20px;

        }

        .form-label {

            font-weight: 600;

            font-size: 14px;

        }

        .form-select {

            min-height: 45px;

            border-radius: 9px;

        }

        .btn-primary {

            background: #0d6efd;

            border-color: #0d6efd;

            border-radius: 9px;

            min-height: 45px;

            font-weight: 600;

        }

        .info-card {

            display: flex;

            align-items: center;

            gap: 14px;

            background: #f8fafc;

            border-radius: 12px;

            padding: 15px;

            margin-bottom: 20px;

        }

        .info-icon {

            width: 45px;

            height: 45px;

            min-width: 45px;

            border-radius: 10px;

            background: #e8f1ff;

            color: #0d6efd;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 21px;

        }

        .info-text {

            font-size: 13px;

            color: #64748b;

        }

        .info-text strong {

            display: block;

            color: #1e293b;

            font-size: 15px;

            margin-bottom: 2px;

        }

        .table-responsive {

            width: 100%;

            overflow-x: auto;

            -webkit-overflow-scrolling: touch;

        }

        .table {

            margin-bottom: 0;

            min-width: 1050px;

        }

        .table th {

            background: #0d6efd;

            color: white;

            white-space: nowrap;

            vertical-align: middle;

            padding: 12px;

        }

        .table td {

            vertical-align: middle;

            white-space: nowrap;

            padding: 12px;

        }

        .kode {

            font-weight: bold;

            color: #0d6efd;

        }

        .matkul {

            font-weight: 600;

        }

        .badge-kelas {

            background: #e8f1ff;

            color: #0d6efd;

            padding: 7px 10px;

            border-radius: 7px;

        }

        .badge-jam {

            background: #f1f5f9;

            color: #334155;

            padding: 7px 10px;

            border-radius: 7px;

            font-weight: 600;

        }

        .empty-box {

            text-align: center;

            padding: 50px 20px;

            color: #64748b;

        }

        .empty-box i {

            font-size: 55px;

            color: #94a3b8;

            display: block;

            margin-bottom: 15px;

        }

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

        }

        .sidebar-overlay {

            display: none;

            position: fixed;

            inset: 0;

            background: rgba(0, 0, 0, .4);

            z-index: 900;

        }

        .sidebar-overlay.active {

            display: block;

        }


        @media(max-width:768px) {

            .sidebar {

                left: -270px;

                width: 250px;

                box-shadow:
                    3px 0 15px rgba(0, 0, 0, .2);

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

                padding: 75px 12px 20px;

            }

            .box {

                padding: 15px;

            }

            .page-title {

                font-size: 20px;

            }

            .page-icon {

                width: 50px;

                height: 50px;

                font-size: 25px;

            }

        }

        @media(max-width:480px) {

            .main {

                padding:
                    70px 10px 15px;

            }

            .box {

                padding: 13px;

            }

            .page-title {

                font-size: 18px;

            }

            .page-icon {

                display: none;

            }

        }
    </style>

</head>


<body>


    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
        onclick="toggleMenu()">
    </div>


    <div
        class="menu-btn"
        onclick="toggleMenu()">

        <i class="bi bi-list"></i>

    </div>


    <div
        class="sidebar"
        id="sidebar">

        <h4>

            <i class="bi bi-mortarboard-fill"></i>

            Mahasiswa

        </h4>


        <a href="dashboard_mahasiswa.php">

            <i class="bi bi-speedometer2"></i>

            <span>Dashboard</span>

        </a>


        <a href="isi_krs.php">

            <i class="bi bi-journal-check"></i>

            <span>KRS</span>

        </a>


        <a
            href="jadwal_mahasiswa.php"
            class="active">

            <i class="bi bi-calendar-event"></i>

            <span>Jadwal</span>

        </a>


        <a href="profil_mahasiswa.php">

            <i class="bi bi-person-circle"></i>

            <span>Profil</span>

        </a>


        <a
            href="logout.php"
            onclick="return confirm('Yakin ingin logout?')">

            <i class="bi bi-box-arrow-right"></i>

            <span>Logout</span>

        </a>

    </div>


    <div class="main">


        <div class="box">

            <div class="page-header">

                <div>

                    <div class="page-title">

                        Jadwal Kuliah 📅

                    </div>

                    <p class="page-subtitle">

                        Halo,
                        <b><?= htmlspecialchars($nama) ?></b> 👋

                        <br>

                        Jadwal berdasarkan KRS kamu.

                    </p>

                </div>


                <div class="page-icon">

                    <i class="bi bi-calendar3"></i>

                </div>

            </div>


            <div class="filter-box">

                <form
                    method="GET"
                    class="row g-2 align-items-end">


                    <div class="col-md-10">

                        <label class="form-label">

                            <i class="bi bi-calendar-week"></i>

                            Pilih Hari

                        </label>


                        <select
                            name="hari"
                            class="form-select">

                            <option value="">

                                Semua Hari

                            </option>


                            <?php

                            $hari_arr = [
                                'Senin',
                                'Selasa',
                                'Rabu',
                                'Kamis',
                                'Jumat',
                                'Sabtu'
                            ];

                            foreach ($hari_arr as $h) {

                            ?>

                                <option
                                    value="<?= $h ?>"
                                    <?= $hari_filter == $h ? 'selected' : '' ?>>

                                    <?= $h ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>


                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="btn btn-primary w-100">

                            <i class="bi bi-search"></i>

                            Tampilkan

                        </button>

                    </div>


                </form>

            </div>

        </div>


        <div class="info-card">

            <div class="info-icon">

                <i class="bi bi-info-circle"></i>

            </div>


            <div class="info-text">

                <strong>
                    Jadwal Perkuliahan
                </strong>

                Jadwal hanya menampilkan mata kuliah
                yang sudah terdapat pada KRS mahasiswa
                yang sedang login.

            </div>

        </div>


        <div class="box">


            <div class="d-flex justify-content-between
            align-items-center mb-3">

                <h5 class="mb-0">

                    <i class="bi bi-calendar-event text-primary"></i>

                    Daftar Jadwal

                </h5>

            </div>


            <div class="table-responsive">

                <table
                    class="table table-bordered table-hover align-middle">


                    <thead>

                        <tr>

                            <th class="text-center">
                                No
                            </th>

                            <th>
                                Hari
                            </th>

                            <th>
                                Kode
                            </th>

                            <th>
                                Mata Kuliah
                            </th>

                            <th>
                                Dosen
                            </th>

                            <th>
                                Jam Masuk
                            </th>

                            <th>
                                Jam Selesai
                            </th>

                            <th>
                                Ruangan
                            </th>

                            <th class="text-center">
                                Kelas
                            </th>

                            <th class="text-center">
                                Semester
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php

                        $no = 1;


                        if (mysqli_num_rows($jadwal) > 0) {

                            while ($j = mysqli_fetch_assoc($jadwal)) {

                        ?>


                                <tr>


                                    <td class="text-center">

                                        <?= $no++ ?>

                                    </td>


                                    <td>

                                        <span class="fw-semibold">

                                            <?= htmlspecialchars(
                                                $j['hari'] ?: '-'
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <span class="kode">

                                            <?= htmlspecialchars(
                                                $j['kode'] ?: '-'
                                            ) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <span class="matkul">

                                            <?= htmlspecialchars(
                                                $j['nama_matkul'] ?: '-'
                                            ) ?>

                                        </span>

                                        <br>

                                        <small class="text-muted">

                                            <?= (int)$j['sks'] ?> SKS

                                        </small>

                                    </td>


                                    <td>

                                        <?= htmlspecialchars(
                                            $j['dosen'] ?: '-'
                                        ) ?>

                                    </td>


                                    <td>

                                        <span class="badge-jam">

                                            <i class="bi bi-clock"></i>

                                            <?php

                                            if (!empty($j['jam_masuk'])) {

                                                echo date(
                                                    "H:i",
                                                    strtotime($j['jam_masuk'])
                                                );
                                            } else {

                                                echo '-';
                                            }

                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <span class="badge-jam">

                                            <i class="bi bi-clock-history"></i>

                                            <?php

                                            if (!empty($j['jam_selesai'])) {

                                                echo date(
                                                    "H:i",
                                                    strtotime($j['jam_selesai'])
                                                );
                                            } else {

                                                echo '-';
                                            }

                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <i class="bi bi-geo-alt text-primary"></i>

                                        <?= htmlspecialchars(
                                            $j['nama_ruang'] ?: '-'
                                        ) ?>

                                    </td>


                                    <td class="text-center">

                                        <span class="badge-kelas">

                                            <?= htmlspecialchars(
                                                $j['krs_kelas'] ?: '-'
                                            ) ?>

                                        </span>

                                    </td>


                                    <td class="text-center">

                                        <span class="badge bg-primary">

                                            Semester
                                            <?= (int)$j['krs_semester'] ?>

                                        </span>

                                    </td>


                                </tr>


                            <?php

                            }
                        } else {

                            ?>


                            <tr>

                                <td colspan="10">

                                    <div class="empty-box">

                                        <i class="bi bi-calendar-x"></i>

                                        <h5>

                                            Jadwal Belum Tersedia

                                        </h5>

                                        <p class="mb-0">

                                            Belum ada jadwal yang sesuai
                                            dengan KRS kamu.

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

            const sidebar =
                document.getElementById("sidebar");

            const overlay =
                document.getElementById("sidebarOverlay");


            sidebar.classList.toggle("active");

            overlay.classList.toggle("active");

        }


        document
            .querySelectorAll(".sidebar a")
            .forEach(function(link) {

                link.addEventListener(
                    "click",
                    function() {

                        if (window.innerWidth <= 768) {

                            document
                                .getElementById("sidebar")
                                .classList
                                .remove("active");


                            document
                                .getElementById("sidebarOverlay")
                                .classList
                                .remove("active");

                        }

                    }
                );

            });
    </script>


</body>

</html>