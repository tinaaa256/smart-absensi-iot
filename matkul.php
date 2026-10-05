<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

/* ================= TAMBAH ================= */
if (isset($_POST['simpan'])) {

    $kode = $_POST['kode'];
    $nama = $_POST['nama'];
    $sks = $_POST['sks'];
    $dosen = $_POST['dosen'];
    $prodi_id = $_POST['prodi_id'];

    $query = mysqli_query($conn, "
        INSERT INTO matkul (kode, nama_matkul, sks, dosen, prodi_id)
        VALUES ('$kode', '$nama', '$sks', '$dosen', '$prodi_id')
    ");

    if (!$query) {
        die("Error: " . mysqli_error($conn));
    }

    header("Location: matkul.php");
    exit;
}

/* ================= UPDATE ================= */
if (isset($_POST['update'])) {

    $id = $_POST['id'];

    $query = mysqli_query($conn, "
        UPDATE matkul SET
        kode='{$_POST['kode']}',
        nama_matkul='{$_POST['nama']}',
        sks='{$_POST['sks']}',
        dosen='{$_POST['dosen']}',
        prodi_id='{$_POST['prodi_id']}'
        WHERE id='$id'
    ");

    if (!$query) {
        die("Error: " . mysqli_error($conn));
    }

    header("Location: matkul.php");
    exit;
}

/* ================= HAPUS ================= */
if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];

    mysqli_query($conn, "DELETE FROM matkul WHERE id='$id'");

    header("Location: matkul.php");
    exit;
}

/* ================= DATA ================= */
$cari = $_GET['cari'] ?? '';

$data = mysqli_query($conn, "

SELECT 
m.*,
p.nama_prodi

FROM matkul m

LEFT JOIN prodi p 
ON m.prodi_id = p.id

WHERE 
m.kode LIKE '%$cari%'
OR m.nama_matkul LIKE '%$cari%'
OR m.dosen LIKE '%$cari%'

ORDER BY m.id DESC

");
$prodi = mysqli_query($conn, "SELECT * FROM prodi ORDER BY nama_prodi ASC");
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

        <div class="topbar">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="m-0">Mata Kuliah</h5>

                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambah">
                    + Tambah
                </button>

            </div>


            <form method="GET">

                <div class="row g-2">

                    <div class="col-md-9">

                        <input
                            type="text"
                            name="cari"
                            class="form-control"
                            placeholder="Cari kode, nama mata kuliah, atau dosen..."
                            value="<?= $_GET['cari'] ?? '' ?>">

                    </div>


                    <div class="col-md-3">

                        <button class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                            Cari
                        </button>

                    </div>


                </div>

            </form>

        </div>

        <!-- TABLE -->
        <table class="table table-bordered bg-white">
            <thead class="table-primary">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>SKS</th>
                    <th>Prodi</th>
                    <th>Dosen</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php $no = 1;
                while ($d = mysqli_fetch_assoc($data)) { ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $d['kode'] ?></td>
                        <td><?= $d['nama_matkul'] ?></td>
                        <td><?= $d['sks'] ?></td>
                        <td><?= $d['nama_prodi'] ?></td>
                        <td><?= $d['dosen'] ?></td>

                        <td>
                            <div class="d-flex gap-2">

                                <!-- EDIT -->
                                <a class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#edit<?= $d['id'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- HAPUS -->
                                <a href="?hapus=<?= $d['id'] ?>"
                                    onclick="return confirm('Hapus data?')"
                                    class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </a>

                            </div>
                        </td>
                    </tr>

                    <!-- EDIT MODAL -->
                    <div class="modal fade" id="edit<?= $d['id'] ?>">
                        <div class="modal-dialog">
                            <div class="modal-content p-3">

                                <form method="POST">
                                    <input type="hidden" name="id" value="<?= $d['id'] ?>">

                                    <input class="form-control mb-2" name="kode" value="<?= $d['kode'] ?>">
                                    <input class="form-control mb-2" name="nama" value="<?= $d['nama_matkul'] ?>">
                                    <input class="form-control mb-2" name="sks" value="<?= $d['sks'] ?>">
                                    <input class="form-control mb-2" name="dosen" value="<?= $d['dosen'] ?>">

                                    <select name="prodi_id" class="form-control mb-3">
                                        <?php
                                        $p = mysqli_query($conn, "SELECT * FROM prodi");
                                        while ($pr = mysqli_fetch_assoc($p)) { ?>
                                            <option value="<?= $pr['id'] ?>"
                                                <?= $pr['id'] == $d['prodi_id'] ? 'selected' : '' ?>>
                                                <?= $pr['nama_prodi'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>

                                    <button name="update" class="btn btn-primary w-100">
                                        Update
                                    </button>

                                </form>

                            </div>
                        </div>
                    </div>

                <?php } ?>
            </tbody>
        </table>

    </div>

    <!-- TAMBAH MODAL -->
    <div class="modal fade" id="tambah">
        <div class="modal-dialog">
            <div class="modal-content p-3">

                <form method="POST">

                    <input class="form-control mb-2" name="kode" placeholder="Kode" required>
                    <input class="form-control mb-2" name="nama" placeholder="Nama" required>
                    <input class="form-control mb-2" name="sks" placeholder="SKS" required>
                    <input class="form-control mb-2" name="dosen" placeholder="Dosen" required>

                    <select name="prodi_id" class="form-control mb-3" required>
                        <option value="">Pilih Prodi</option>
                        <?php while ($p = mysqli_fetch_assoc($prodi)) { ?>
                            <option value="<?= $p['id'] ?>">
                                <?= $p['nama_prodi'] ?>
                            </option>
                        <?php } ?>
                    </select>

                    <button name="simpan" class="btn btn-primary w-100">
                        Simpan
                    </button>

                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>