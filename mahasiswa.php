<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

/* =========================
   TAMBAH
========================= */
if (isset($_POST['simpan'])) {

    $nama     = $_POST['nama'];
    $nim      = $_POST['nim'];
    $prodi    = $_POST['prodi'];
    $semester = $_POST['semester'];
    $pa       = $_POST['nama_dosen'];

    mysqli_query($conn, "
        INSERT INTO mahasiswa
        (nim,nama,password,semester,pa,prodi,finger_id)
        VALUES
        ('$nim','$nama','12345','$semester','$pa','$prodi',NULL)
    ");
}

/* =========================
   UPDATE
========================= */
if (isset($_POST['update'])) {

    $id       = $_POST['id'];
    $nama     = $_POST['nama'];
    $nim      = $_POST['nim'];
    $prodi    = $_POST['prodi'];
    $semester = $_POST['semester'];
    $pa       = $_POST['nama_dosen'];

    mysqli_query($conn, "
        UPDATE mahasiswa SET
        nama='$nama',
        nim='$nim',
        prodi='$prodi',
        semester='$semester',
        pa='$pa'
        WHERE id_mahasiswa='$id'
    ");

    header("Location: mahasiswa.php");
}

/* =========================
   HAPUS
========================= */
if (isset($_GET['hapus'])) {
    mysqli_query($conn, "DELETE FROM mahasiswa WHERE id_mahasiswa='$_GET[hapus]'");
    header("Location: mahasiswa.php");
}

/* =========================
   PENCARIAN
========================= */

$cari = "";

if (isset($_GET['cari'])) {
    $cari = $_GET['cari'];
}

/* =========================
   EXPORT EXCEL
========================= */

if (isset($_GET['export']) && $_GET['export'] == '1') {

    $cari = mysqli_real_escape_string($conn, $_GET['cari'] ?? '');

    $dataExcel = mysqli_query($conn, "
        SELECT *
        FROM mahasiswa
        WHERE
            nama LIKE '%$cari%'
            OR nim LIKE '%$cari%'
        ORDER BY id_mahasiswa DESC
    ");

    $filename = "data_mahasiswa_" . date('Y-m-d_H-i-s') . ".xls";

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "<table border='1'>";

    echo "
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>NIM</th>
            <th>Prodi</th>
            <th>Semester</th>
            <th>PA</th>
            <th>Finger ID</th>
        </tr>
    ";

    $no = 1;

    while ($d = mysqli_fetch_assoc($dataExcel)) {

        echo "
            <tr>
                <td>{$no}</td>
                <td>{$d['nama']}</td>
                <td>{$d['nim']}</td>
                <td>{$d['prodi']}</td>
                <td>{$d['semester']}</td>
                <td>{$d['pa']}</td>
                <td>" . ($d['finger_id'] ?: '-') . "</td>
            </tr>
        ";

        $no++;
    }

    echo "</table>";

    exit;
}
$data = mysqli_query($conn, "
SELECT * FROM mahasiswa
WHERE 
nama LIKE '%$cari%'
OR nim LIKE '%$cari%'
ORDER BY id_mahasiswa DESC
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


    <div class="main">

        <div class="topbar d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                Data Mahasiswa
            </h5>

            <div class="d-flex gap-2">

                <!-- PENCARIAN -->
                <form method="GET" class="d-flex gap-2">

                    <input
                        type="text"
                        name="cari"
                        class="form-control"
                        placeholder="Cari Nama / NIM..."
                        value="<?= htmlspecialchars($cari) ?>">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        <i class="bi bi-search"></i>

                    </button>

                </form>

                <!-- EXCEL -->
                <a
                    href="?export=1&cari=<?= urlencode($cari) ?>"
                    class="btn btn-success">

                    <i class="bi bi-file-earmark-excel"></i>
                    Excel

                </a>

                <!-- TAMBAH -->
                <button
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#tambah">

                    <i class="bi bi-plus-lg"></i>
                    Tambah

                </button>

            </div>

        </div>

        <table class="table table-bordered table-striped bg-white">
            <thead class="table-primary">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIM</th>
                    <th>Prodi</th>
                    <th>Semester</th>
                    <th>PA</th>
                    <th>Finger</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php $no = 1;
                while ($d = mysqli_fetch_assoc($data)) { ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $d['nama'] ?></td>
                        <td><?= $d['nim'] ?></td>
                        <td><?= $d['prodi'] ?></td>
                        <td><?= $d['semester'] ?></td>
                        <td><?= $d['pa'] ?></td>
                        <td><?= $d['finger_id'] ?: '-' ?></td>

                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">

                                <!-- EDIT -->
                                <a href="#" class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#edit<?= $d['id_mahasiswa'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- HAPUS -->
                                <a href="?hapus=<?= $d['id_mahasiswa'] ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Hapus data?')">
                                    <i class="bi bi-trash"></i>
                                </a>

                            </div>
                        </td>
                    </tr>

                    <!-- MODAL EDIT -->
                    <div class="modal fade" id="edit<?= $d['id_mahasiswa'] ?>">
                        <div class="modal-dialog">
                            <div class="modal-content p-3">

                                <h5>Edit Mahasiswa</h5>

                                <form method="POST">
                                    <input type="hidden" name="id" value="<?= $d['id_mahasiswa'] ?>">

                                    <input class="form-control mb-2" name="nama" value="<?= $d['nama'] ?>">
                                    <input class="form-control mb-2" name="nim" value="<?= $d['nim'] ?>">
                                    <input class="form-control mb-2" name="prodi" value="<?= $d['prodi'] ?>">

                                    <select name="semester" class="form-control mb-2">
                                        <?php for ($i = 1; $i <= 8; $i++) { ?>
                                            <option value="<?= $i ?>" <?= $d['semester'] == $i ? 'selected' : '' ?>>
                                                Semester <?= $i ?>
                                            </option>
                                        <?php } ?>
                                    </select>

                                    <input class="form-control mb-3" name="nama_dosen" value="<?= $d['pa'] ?>">

                                    <button name="update" class="btn btn-primary w-100">Update</button>
                                </form>

                            </div>
                        </div>
                    </div>

                <?php } ?>
            </tbody>
        </table>

    </div>

    <!-- MODAL TAMBAH -->
    <div class="modal fade" id="tambah">
        <div class="modal-dialog">
            <div class="modal-content p-3">

                <h5>Tambah Mahasiswa</h5>

                <form method="POST">
                    <input class="form-control mb-2" name="nama" placeholder="Nama">
                    <input class="form-control mb-2" name="nim" placeholder="NIM">
                    <input class="form-control mb-2" name="prodi" placeholder="Prodi">

                    <select name="semester" class="form-control mb-2">
                        <?php for ($i = 1; $i <= 8; $i++) { ?>
                            <option value="<?= $i ?>">Semester <?= $i ?></option>
                        <?php } ?>
                    </select>

                    <input class="form-control mb-3" name="nama_dosen" placeholder="PA">

                    <button name="simpan" class="btn btn-primary w-100">Simpan</button>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>