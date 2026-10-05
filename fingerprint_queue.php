<?php
include "config.php";
session_start();

/* ================= ENROLL PROCESS ================= */
if (isset($_POST['enroll'])) {

    $id = $_POST['mahasiswa_id'] ?? '';

    if ($id == '') {
        die("ERROR EMPTY");
    }
    /* ================= CEK QUEUE AKTIF ================= */
    $cek = mysqli_query($conn, "
        SELECT id
        FROM fingerprint_queue
        WHERE mahasiswa_id='$id'
        AND status!='DONE'
    ");

    if (mysqli_num_rows($cek) > 0) {

        echo "<script>
            alert('Mahasiswa masih dalam queue');
            window.location='fingerprint_queue.php';
        </script>";

        exit;
    }

    /* ================= INSERT QUEUE ================= */
    mysqli_query($conn, "
        INSERT INTO fingerprint_queue
        (mahasiswa_id, status, created_at)
        VALUES
        ('$id','WAITING',NOW())
    ");

    echo "<script>
        alert('ENROLL dikirim');
        window.location='fingerprint_queue.php';
    </script>";

    exit;
}

/* ================= DELETE QUEUE ================= */
if (isset($_GET['hapus'])) {

    $id = intval($_GET['hapus'] ?? 0);

    mysqli_query($conn, "
        DELETE FROM fingerprint_queue
        WHERE id='$id'
    ");

    header("Location: fingerprint_queue.php");
    exit;
}

/* ================= MAHASISWA LIST ================= */
$cari = "";

if (isset($_GET['cari'])) {
    $cari = $_GET['cari'];
}

$mahasiswa = mysqli_query($conn, "
    SELECT id_mahasiswa, nim, nama 
    FROM mahasiswa
    WHERE 
    nama LIKE '%$cari%'
    OR nim LIKE '%$cari%'
    ORDER BY nama ASC
");

/* ================= QUEUE LIST (FINAL FIX) ================= */
$data = mysqli_query($conn, "
    SELECT fq.*, m.nim, m.nama
    FROM fingerprint_queue fq
    LEFT JOIN mahasiswa m 
        ON m.id_mahasiswa = fq.mahasiswa_id
    ORDER BY fq.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Smart Absensi</title>

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

        .topbar {
            background: #fff;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .05);
            margin-bottom: 25px;
        }

        .card-box {
            border: none;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .05);
        }

        .card-box h3 {
            font-weight: bold;
        }

        .welcome {
            font-size: 15px;
            color: #666;
        }

        #jam {
            font-size: 14px;
            color: #0d6efd;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
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

    <!-- MAIN -->
    <div class="main">

        <!-- TOPBAR -->
        <div class="topbar d-flex justify-content-between align-items-center">

            <h5 class="m-0">
                Fingerprint Queue
            </h5>


            <div class="d-flex gap-2">

                <form method="GET">

                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari Nama / NIM..."
                        value="<?= $cari ?>">

                </form>


                <button
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#tambah">

                    + ENROLL

                </button>

            </div>


        </div>

        <!-- TABLE -->
        <div class="card">
            <div class="card-body">

                <table class="table table-hover text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php $no = 1;
                        while ($d = mysqli_fetch_assoc($data)) { ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= $d['nim'] ?: '-' ?></td>
                                <td><?= $d['nama'] ?: '-' ?></td>

                                <td>
                                    <?php
                                    $badge = 'secondary';

                                    if ($d['status'] == 'WAITING') {
                                        $badge = 'secondary';
                                    } elseif ($d['status'] == 'PROCESS') {
                                        $badge = 'warning text-dark';
                                    } elseif ($d['status'] == 'DONE') {
                                        $badge = 'success';
                                    }
                                    ?>

                                    <span class="badge bg-<?= $badge ?>">
                                        <?= $d['status'] ?>
                                    </span>
                                </td>

                                <td>
                                    <a href="?hapus=<?= $d['id'] ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus queue?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>

                            </tr>
                        <?php } ?>
                    </tbody>

                </table>

            </div>
        </div>

    </div>

    <!-- MODAL ENROLL -->
    <div class="modal fade" id="tambah">
        <div class="modal-dialog">
            <div class="modal-content p-3">

                <form method="POST">

                    <h5 class="mb-3">Tambah ENROLL</h5>

                    <select name="mahasiswa_id" class="form-control mb-3" required>
                        <option value="">Pilih Mahasiswa</option>

                        <?php while ($m = mysqli_fetch_assoc($mahasiswa)) { ?>
                            <option value="<?= $m['id_mahasiswa'] ?>">
                                <?= $m['nim'] ?> - <?= $m['nama'] ?>
                            </option>
                        <?php } ?>

                    </select>

                    <button class="btn btn-primary w-100" name="enroll">
                        Kirim ke ESP32
                    </button>

                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>