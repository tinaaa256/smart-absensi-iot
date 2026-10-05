<?php
include "config.php";
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

/* ================= START SCAN ================= */
if (isset($_POST['start_scan'])) {
    mysqli_query($conn, "
        UPDATE fingerprint_queue 
        SET status='WAITING'
        WHERE status != 'DONE'
    ");
}

/* ================= STOP SCAN ================= */
if (isset($_POST['stop_scan'])) {
    mysqli_query($conn, "
        UPDATE fingerprint_queue 
        SET status='STOP'
        WHERE status != 'DONE'
    ");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi - Smart Absensi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            background: #f1f5f9;
            font-family: Arial, Helvetica, sans-serif;
        }

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
            background: rgba(255, 255, 255, .15);
            padding-left: 28px;
        }

        .sidebar a i {
            margin-right: 10px;
        }

        .main {
            margin-left: 250px;
            padding: 25px;
        }

        .card-box {
            background: #fff;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .05);
        }
    </style>
</head>

<body>

    <div class="sidebar">

        <h4>Smart Absensi</h4>

        <a href="dashboard_admin.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="mahasiswa.php"><i class="bi bi-people-fill"></i> Mahasiswa</a>
        <a href="matkul.php"><i class="bi bi-book-fill"></i> Mata Kuliah</a>
        <a href="jadwal.php"><i class="bi bi-calendar-event"></i> Jadwal Kuliah</a>
        <a href="krs.php"><i class="bi bi-journal-check"></i> KRS</a>
        <a href="fingerprint_queue.php"><i class="bi bi-fingerprint"></i> Fingerprint</a>
        <a href="absensi.php"><i class="bi bi-clipboard-check"></i> Absensi</a>
        <a href="ruangan.php"><i class="bi bi-building"></i> Ruangan</a>
        <a href="dosen.php"><i class="bi bi-person-badge-fill"></i> Dosen</a>
        <a href="laporan.php"><i class="bi bi-file-earmark-text"></i> Laporan</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>

    </div>

    <div class="main">

        <div class="card-box mb-3">

            <h5 class="mb-3">Kontrol Absensi</h5>

            <form method="POST">

                <button type="submit" name="start_scan" class="btn btn-success">
                    Mulai Scan
                </button>

                <button type="submit" name="stop_scan" class="btn btn-danger">
                    Stop Scan
                </button>

            </form>

        </div>

        <div class="card-box">

            <h5 class="mb-3">Data Absensi</h5>

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

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
                        </tr>
                    </thead>

                    <tbody id="live-absensi">
                    </tbody>

                    </tbody>

                </table>

            </div>

        </div>

    </div>
    <!-- REALTIME AUTO UPDATE TANPA RELOAD -->
    <script>
        function loadAbsensi() {
            fetch("realtime_absensi.php?ts=" + Date.now())
                .then(res => res.text())
                .then(data => {
                    document.getElementById("live-absensi").innerHTML = data;
                });
        }

        setInterval(loadAbsensi, 2000);
        loadAbsensi();
    </script>
</body>

</html>