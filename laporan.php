<?php
include "config.php";
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

/* =====================================================
   KONFIGURASI
===================================================== */

$totalPertemuan = 12;
$batasKehadiran = 75;

/* =====================================================
   FILTER
===================================================== */

$semester = $_GET['semester'] ?? '';
$kelas    = $_GET['kelas'] ?? '';
$matkul   = $_GET['matkul'] ?? '';

/* =====================================================
   ESCAPE INPUT
===================================================== */

$semesterSafe = mysqli_real_escape_string($conn, $semester);
$kelasSafe    = mysqli_real_escape_string($conn, $kelas);
$matkulSafe   = mysqli_real_escape_string($conn, $matkul);

$filterAktif = (
    $semester !== '' ||
    $kelas !== '' ||
    $matkul !== ''
);

/* =====================================================
   EXPORT EXCEL
   Export menggunakan format HTML yang dapat dibuka Excel
   tanpa library tambahan.
===================================================== */

if (isset($_GET['export']) && $_GET['export'] == '1') {

    $queryExport = "
        SELECT
            m.nim,
            m.nama,
            k.semester,
            k.kelas,
            mk.nama_matkul,

            COUNT(
                CASE
                    WHEN a.jam_selesai IS NOT NULL
                    THEN 1
                END
            ) AS hadir

        FROM mahasiswa m

        INNER JOIN krs k
            ON k.mahasiswa_id = m.id_mahasiswa

        INNER JOIN matkul mk
            ON mk.id = k.matkul_id

        LEFT JOIN absensi a
            ON a.mahasiswa_id = m.id_mahasiswa
            AND a.matkul_id = k.matkul_id
            AND a.jam_selesai IS NOT NULL

        WHERE
            ('$semesterSafe' = '' OR k.semester = '$semesterSafe')
            AND
            ('$kelasSafe' = '' OR k.kelas = '$kelasSafe')
            AND
            ('$matkulSafe' = '' OR mk.id = '$matkulSafe')

        GROUP BY
            m.id_mahasiswa,
            m.nim,
            m.nama,
            k.semester,
            k.kelas,
            k.matkul_id,
            mk.nama_matkul

        ORDER BY
            k.semester ASC,
            k.kelas ASC,
            m.nim ASC
    ";

    $export = mysqli_query($conn, $queryExport);

    if (!$export) {
        die("Query export gagal: " . mysqli_error($conn));
    }

    $filename = "laporan_absensi_" . date('Y-m-d_H-i-s') . ".xls";

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            table {
                border-collapse: collapse;
                width: 100%;
            }

            th {
                background: #0d6efd;
                color: white;
                font-weight: bold;
            }

            th, td {
                border: 1px solid #000;
                padding: 8px;
            }

            .layak {
                color: green;
                font-weight: bold;
            }

            .tidak-layak {
                color: red;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
    ";

    echo "<h2>LAPORAN KEHADIRAN MAHASISWA</h2>";
    echo "<p>Total Pertemuan: <b>$totalPertemuan</b></p>";

    if ($semester !== '') {
        echo "<p>Semester: <b>" . htmlspecialchars($semester) . "</b></p>";
    }

    if ($kelas !== '') {
        echo "<p>Kelas: <b>" . htmlspecialchars($kelas) . "</b></p>";
    }

    echo "
    <table>
        <tr>
            <th>No</th>
            <th>NIM</th>
            <th>Nama</th>
            <th>Semester</th>
            <th>Kelas</th>
            <th>Mata Kuliah</th>
            <th>Hadir</th>
            <th>Total Pertemuan</th>
            <th>Persentase</th>
            <th>Status</th>
        </tr>
    ";

    $noExport = 1;

    while ($row = mysqli_fetch_assoc($export)) {

        $hadir = (int)$row['hadir'];

        $persen = ($totalPertemuan > 0)
            ? ($hadir / $totalPertemuan) * 100
            : 0;

        $persen = round($persen, 1);

        $status = ($persen >= $batasKehadiran)
            ? "LAYAK UJIAN"
            : "TIDAK LAYAK";

        $classStatus = ($persen >= $batasKehadiran)
            ? "layak"
            : "tidak-layak";

        echo "
        <tr>
            <td>{$noExport}</td>
            <td>" . htmlspecialchars($row['nim']) . "</td>
            <td>" . htmlspecialchars($row['nama']) . "</td>
            <td>" . htmlspecialchars($row['semester']) . "</td>
            <td>" . htmlspecialchars($row['kelas']) . "</td>
            <td>" . htmlspecialchars($row['nama_matkul']) . "</td>
            <td>{$hadir}</td>
            <td>{$totalPertemuan}</td>
            <td>{$persen}%</td>
            <td class='{$classStatus}'>{$status}</td>
        </tr>
        ";

        $noExport++;
    }

    echo "
    </table>
    </body>
    </html>
    ";

    exit;
}

/* =====================================================
   DATA LAPORAN
===================================================== */

$dataLaporan = [];

if ($filterAktif) {

    $queryLaporan = "
        SELECT
            m.id_mahasiswa,
            m.nim,
            m.nama,
            k.semester,
            k.kelas,
            k.matkul_id,
            mk.nama_matkul,

            COUNT(
                CASE
                    WHEN a.jam_selesai IS NOT NULL
                    THEN 1
                END
            ) AS hadir

        FROM mahasiswa m

        INNER JOIN krs k
            ON k.mahasiswa_id = m.id_mahasiswa

        INNER JOIN matkul mk
            ON mk.id = k.matkul_id

        LEFT JOIN absensi a
            ON a.mahasiswa_id = m.id_mahasiswa
            AND a.matkul_id = k.matkul_id
            AND a.jam_selesai IS NOT NULL

        WHERE
            ('$semesterSafe' = '' OR k.semester = '$semesterSafe')
            AND
            ('$kelasSafe' = '' OR k.kelas = '$kelasSafe')
            AND
            ('$matkulSafe' = '' OR mk.id = '$matkulSafe')

        GROUP BY
            m.id_mahasiswa,
            m.nim,
            m.nama,
            k.semester,
            k.kelas,
            k.matkul_id,
            mk.nama_matkul

        ORDER BY
            k.semester ASC,
            k.kelas ASC,
            m.nim ASC
    ";

    $resultLaporan = mysqli_query($conn, $queryLaporan);

    if (!$resultLaporan) {
        die("Query laporan gagal: " . mysqli_error($conn));
    }

    while ($row = mysqli_fetch_assoc($resultLaporan)) {

        $row['hadir'] = (int)$row['hadir'];

        $row['persen'] = ($totalPertemuan > 0)
            ? round(($row['hadir'] / $totalPertemuan) * 100, 1)
            : 0;

        $row['status'] = ($row['persen'] >= $batasKehadiran)
            ? 'LAYAK UJIAN'
            : 'TIDAK LAYAK';

        $dataLaporan[] = $row;
    }
}

/* =====================================================
   DATA MATA KULIAH
===================================================== */

$matkulQuery = mysqli_query(
    $conn,
    "SELECT id, nama_matkul FROM matkul ORDER BY nama_matkul ASC"
);

?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laporan Absensi - Smart Absensi</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f1f5f9;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* SIDEBAR */

        .sidebar {
            width: 250px;
            height: 100vh;
            background: #0d6efd;
            position: fixed;
            left: 0;
            top: 0;
            padding-top: 20px;
            overflow-y: auto;
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
            padding: 14px 22px;
            text-decoration: none;
            transition: .2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255, 255, 255, .16);
        }

        /* MAIN */

        .main {
            margin-left: 250px;
            padding: 25px;
        }

        .page-title {
            font-weight: bold;
            color: #1e293b;
        }

        .card-box {
            background: white;
            border-radius: 14px;
            padding: 20px;
            border: none;
            box-shadow: 0 3px 15px rgba(0, 0, 0, .06);
        }

        .filter-title {
            font-weight: bold;
            color: #334155;
        }

        .table-responsive {
            border-radius: 10px;
        }

        table {
            vertical-align: middle !important;
        }

        .badge {
            padding: 8px 10px;
        }

        .chart-container {
            height: 330px;
            position: relative;
        }

        /* MOBILE */

        @media (max-width: 768px) {

            .sidebar {
                width: 70px;
            }

            .sidebar h4 {
                font-size: 0;
            }

            .sidebar h4:after {
                content: "SA";
                font-size: 20px;
            }

            .sidebar a {
                justify-content: center;
                padding: 15px 5px;
            }

            .sidebar a i {
                margin: 0;
                font-size: 20px;
            }

            .sidebar a {
                font-size: 0;
            }

            .main {
                margin-left: 70px;
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

        <h4>Smart Absensi</h4>

        <a href="dashboard_admin.php">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard</span>
        </a>

        <a href="mahasiswa.php">
            <i class="bi bi-people-fill"></i>
            <span>Mahasiswa</span>
        </a>

        <a href="matkul.php">
            <i class="bi bi-book-fill"></i>
            <span>Mata Kuliah</span>
        </a>

        <a href="jadwal.php">
            <i class="bi bi-calendar-event"></i>
            <span>Jadwal Kuliah</span>
        </a>

        <a href="absensi.php">
            <i class="bi bi-clipboard-check"></i>
            <span>Absensi</span>
        </a>

        <a href="dosen.php">
            <i class="bi bi-person-badge-fill"></i>
            <span>Dosen</span>
        </a>

        <a href="laporan.php" class="active">
            <i class="bi bi-file-earmark-text"></i>
            <span>Laporan</span>
        </a>

        <a href="logout.php">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
        </a>

    </div>


    <!-- =====================================================
     MAIN
===================================================== -->

    <div class="main">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>
                <h3 class="page-title mb-1">
                    Laporan Absensi Mahasiswa
                </h3>

                <div class="text-muted">
                    Rekap kehadiran dari <?= $totalPertemuan ?> pertemuan
                </div>
            </div>

        </div>


        <!-- =================================================
         FILTER
    ================================================== -->

        <div class="card-box mb-4">

            <div class="filter-title mb-3">
                <i class="bi bi-funnel-fill"></i>
                Filter Laporan
            </div>

            <form method="GET">

                <div class="row g-3">

                    <!-- SEMESTER -->

                    <div class="col-md-4">

                        <label class="form-label">
                            Semester
                        </label>

                        <select name="semester" class="form-select">

                            <option value="">
                                Semua Semester
                            </option>

                            <?php for ($i = 1; $i <= 8; $i++) { ?>

                                <option
                                    value="<?= $i ?>"
                                    <?= ($semester == $i) ? 'selected' : '' ?>>
                                    Semester <?= $i ?>
                                </option>

                            <?php } ?>

                        </select>

                    </div>


                    <!-- KELAS -->

                    <div class="col-md-4">

                        <label class="form-label">
                            Kelas
                        </label>

                        <select name="kelas" class="form-select">

                            <option value="">
                                Semua Kelas
                            </option>

                            <option
                                value="01"
                                <?= ($kelas == '01') ? 'selected' : '' ?>>
                                01
                            </option>

                            <option
                                value="02"
                                <?= ($kelas == '02') ? 'selected' : '' ?>>
                                02
                            </option>

                        </select>

                    </div>


                    <!-- MATA KULIAH -->

                    <div class="col-md-4">

                        <label class="form-label">
                            Mata Kuliah
                        </label>

                        <select name="matkul" class="form-select">

                            <option value="">
                                Semua Mata Kuliah
                            </option>

                            <?php while ($mk = mysqli_fetch_assoc($matkulQuery)) { ?>

                                <option
                                    value="<?= $mk['id'] ?>"
                                    <?= ($matkul == $mk['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($mk['nama_matkul']) ?>
                                </option>

                            <?php } ?>

                        </select>

                    </div>

                </div>


                <div class="d-flex gap-2 mt-4">

                    <button class="btn btn-primary">

                        <i class="bi bi-search"></i>
                        Tampilkan

                    </button>


                    <a
                        href="laporan.php"
                        class="btn btn-secondary">

                        <i class="bi bi-arrow-clockwise"></i>
                        Reset

                    </a>


                    <?php if ($filterAktif) { ?>

                        <a
                            href="laporan.php?export=1&semester=<?= urlencode($semester) ?>&kelas=<?= urlencode($kelas) ?>&matkul=<?= urlencode($matkul) ?>"
                            class="btn btn-success">

                            <i class="bi bi-file-earmark-excel"></i>
                            Export Excel

                        </a>

                    <?php } ?>

                </div>

            </form>

        </div>


        <!-- =================================================
         GRAFIK
    ================================================== -->

        <div class="card-box mb-4">

            <div class="d-flex justify-content-between mb-3">

                <div>

                    <h5 class="fw-bold mb-1">
                        Grafik Kehadiran
                    </h5>

                    <small class="text-muted">
                        Persentase rata-rata kehadiran mahasiswa
                    </small>

                </div>

            </div>

            <div class="chart-container">

                <canvas id="chart"></canvas>

            </div>

        </div>


        <!-- =================================================
         TABEL
    ================================================== -->

        <?php if ($filterAktif) { ?>

            <div class="card-box">

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <h5 class="fw-bold mb-1">
                            Data Kehadiran
                        </h5>

                        <small class="text-muted">
                            Total pertemuan: <?= $totalPertemuan ?>
                            | Batas kelayakan: <?= $batasKehadiran ?>%
                        </small>

                    </div>

                    <span class="badge bg-primary">
                        <?= count($dataLaporan) ?> Data
                    </span>

                </div>


                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead class="table-primary">

                            <tr>

                                <th>No</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Semester</th>
                                <th>Kelas</th>
                                <th>Mata Kuliah</th>
                                <th>Hadir</th>
                                <th>Total</th>
                                <th>Persentase</th>
                                <th>Status</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (count($dataLaporan) > 0) { ?>

                                <?php foreach ($dataLaporan as $no => $d) { ?>

                                    <tr>

                                        <td>
                                            <?= $no + 1 ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($d['nim']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($d['nama']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($d['semester']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($d['kelas']) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($d['nama_matkul']) ?>
                                        </td>

                                        <td class="fw-bold">
                                            <?= $d['hadir'] ?>
                                        </td>

                                        <td>
                                            <?= $totalPertemuan ?>
                                        </td>

                                        <td class="fw-bold">
                                            <?= $d['persen'] ?>%
                                        </td>

                                        <td>

                                            <?php if ($d['persen'] >= $batasKehadiran) { ?>

                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i>
                                                    LAYAK UJIAN
                                                </span>

                                            <?php } else { ?>

                                                <span class="badge bg-danger">
                                                    <i class="bi bi-x-circle"></i>
                                                    TIDAK LAYAK
                                                </span>

                                            <?php } ?>

                                        </td>

                                    </tr>

                                <?php } ?>

                            <?php } else { ?>

                                <tr>

                                    <td colspan="10" class="text-center py-4 text-muted">

                                        Tidak ada data absensi.

                                    </td>

                                </tr>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

            </div>

        <?php } else { ?>

            <div class="card-box text-center py-5">

                <i
                    class="bi bi-funnel"
                    style="font-size:45px;color:#94a3b8;"></i>

                <h5 class="mt-3">
                    Silakan pilih filter terlebih dahulu
                </h5>

                <p class="text-muted mb-0">
                    Pilih semester, kelas, atau mata kuliah
                    untuk menampilkan laporan.
                </p>

            </div>

        <?php } ?>

    </div>


    <!-- =====================================================
     CHART
===================================================== -->

    <script>
        let chart = null;

        function loadChart() {

            const semester = encodeURIComponent("<?= $semester ?>");
            const kelas = encodeURIComponent("<?= $kelas ?>");
            const matkul = encodeURIComponent("<?= $matkul ?>");

            fetch(
                    "api_grafik.php?semester=" +
                    semester +
                    "&kelas=" +
                    kelas +
                    "&matkul=" +
                    matkul
                )

                .then(response => response.json())

                .then(result => {

                    const canvas = document.getElementById("chart");

                    if (chart) {
                        chart.destroy();
                    }

                    chart = new Chart(canvas, {

                        type: "line",

                        data: {

                            labels: result.label,

                            datasets: [{

                                label: "Persentase Kehadiran",

                                data: result.data,

                                borderWidth: 3,

                                fill: true,

                                tension: 0.35,

                                pointRadius: 4,

                                pointHoverRadius: 7

                            }]

                        },

                        options: {

                            responsive: true,

                            maintainAspectRatio: false,

                            scales: {

                                y: {

                                    beginAtZero: true,

                                    max: 100,

                                    ticks: {

                                        callback: function(value) {
                                            return value + "%";
                                        }

                                    }

                                }

                            },

                            plugins: {

                                tooltip: {

                                    callbacks: {

                                        label: function(context) {

                                            return "Kehadiran: " +
                                                context.parsed.y +
                                                "%";

                                        }

                                    }

                                }

                            }

                        }

                    });

                })

                .catch(error => {

                    console.error(
                        "Gagal mengambil data grafik:",
                        error
                    );

                });

        }


        /* LOAD AWAL */

        loadChart();


        /* UPDATE OTOMATIS */

        setInterval(loadChart, 5000);
    </script>

</body>

</html>