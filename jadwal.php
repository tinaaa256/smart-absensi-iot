<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// UPDATE
if (isset($_POST['update'])) {

    $id = $_POST['id'];
    $hari = $_POST['hari'];
    $jam_masuk = $_POST['jam_masuk'];
    $jam_selesai = $_POST['jam_selesai'];
    $kelas = $_POST['kelas'];
    $semester = $_POST['semester'];

    if ($jam_masuk >= $jam_selesai) {
        echo "<script>alert('Jam selesai harus lebih besar dari jam masuk!');</script>";
        exit;
    }

    mysqli_query($conn, "
        UPDATE jadwal SET
        hari='$hari',
        jam_masuk='$jam_masuk',
        jam_selesai='$jam_selesai',
        kelas='$kelas',
        semester='$semester'
        WHERE id='$id'
    ");

    header("Location: jadwal.php");
    exit;
}

// SIMPAN
if (isset($_POST['simpan'])) {

    $hari        = $_POST['hari'];
    $jam_masuk   = $_POST['jam_masuk'] . ":00";
    $jam_selesai = $_POST['jam_selesai'] . ":00";
    $kelas       = $_POST['kelas'];
    $semester    = $_POST['semester'];
    $matkul_id   = $_POST['matkul_id'];
    $ruangan_id  = $_POST['ruangan_id'];

    if ($jam_masuk >= $jam_selesai) {
        echo "<script>alert('Jam selesai harus lebih besar dari jam masuk!');</script>";
        exit;
    }

    // CEK BENTROK
    $cek = mysqli_query($conn, "
        SELECT * FROM jadwal 
        WHERE hari='$hari'
        AND ruangan_id='$ruangan_id'
        AND (
         (
    '$jam_masuk' < jam_selesai
    AND
    '$jam_selesai' > jam_masuk
)
        )
    ");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Ruangan sudah dipakai di jam tersebut!');</script>";
    } else {

        mysqli_query($conn, "
            INSERT INTO jadwal (hari, jam_masuk, jam_selesai, matkul_id, ruangan_id, kelas, semester)
            VALUES ('$hari','$jam_masuk','$jam_selesai','$matkul_id','$ruangan_id','$kelas','$semester')
        ");

        echo "<script>
            alert('Jadwal berhasil disimpan');
            window.location='jadwal.php';
        </script>";
    }
}

// HAPUS
if (isset($_GET['hapus'])) {
    mysqli_query($conn, "DELETE FROM jadwal WHERE id='$_GET[hapus]'");
    header("Location: jadwal.php");
}

// DATA
$where = "";

if (!empty($_GET['hari'])) {

    $hari = $_GET['hari'];

    $where .= " AND j.hari='$hari' ";
}


if (!empty($_GET['matkul'])) {

    $matkul_cari = $_GET['matkul'];

    $where .= " AND m.nama_matkul LIKE '%$matkul_cari%' ";
}



$data = mysqli_query($conn, "

SELECT 
j.*, 
m.kode,
m.nama_matkul,
m.sks,
m.dosen,
r.nama_ruang,
r.lantai,
j.semester

FROM jadwal j

LEFT JOIN matkul m 
ON j.matkul_id=m.id

LEFT JOIN ruangan r 
ON j.ruangan_id=r.id

WHERE 1=1

$where

ORDER BY j.id DESC

");
$matkul = mysqli_query($conn, "SELECT * FROM matkul ORDER BY nama_matkul ASC");
$ruang  = mysqli_query($conn, "SELECT * FROM ruangan ORDER BY nama_ruang ASC");
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

        <div class="topbar">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="m-0">Jadwal Kuliah</h5>

                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambah">
                    + Tambah Jadwal
                </button>

            </div>


            <form method="GET">

                <div class="row g-2">

                    <div class="col-md-4">

                        <select name="hari" class="form-control">

                            <option value="">-- Semua Hari --</option>

                            <?php
                            $hari_list = [
                                "Senin",
                                "Selasa",
                                "Rabu",
                                "Kamis",
                                "Jumat",
                                "Sabtu"
                            ];

                            foreach ($hari_list as $h) {
                                echo "
                        <option value='$h' 
                        " . (($_GET['hari'] ?? '') == $h ? 'selected' : '') . ">
                        $h
                        </option>";
                            }

                            ?>

                        </select>

                    </div>


                    <div class="col-md-5">

                        <input
                            type="text"
                            name="matkul"
                            class="form-control"
                            placeholder="Cari mata kuliah..."
                            value="<?= $_GET['matkul'] ?? '' ?>">

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

        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>No</th>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Mata Kuliah</th>
                    <th>Dosen</th>
                    <th>Ruangan</th>
                    <th>Kelas</th>
                    <th>Semester</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php $no = 1;
                while ($d = mysqli_fetch_assoc($data)) { ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $d['hari'] ?></td>
                        <td><?= date("H:i", strtotime($d['jam_masuk'])) ?> - <?= date("H:i", strtotime($d['jam_selesai'])) ?></td>
                        <td><?= $d['nama_matkul'] ?></td>
                        <td><?= $d['dosen'] ?></td>
                        <td><?= $d['nama_ruang'] ?> (Lt <?= $d['lantai'] ?>)</td>
                        <td><?= $d['kelas'] ?></td>
                        <td><?= $d['semester'] ?></td>

                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">

                                <a href="#" class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#edit<?= $d['id'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <a href="?hapus=<?= $d['id'] ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Hapus data?')">
                                    <i class="bi bi-trash"></i>
                                </a>

                            </div>
                        </td>

                    </tr>

                    <!-- MODAL EDIT -->
                    <div class="modal fade" id="edit<?= $d['id'] ?>">
                        <div class="modal-dialog">
                            <div class="modal-content p-3">

                                <h5>Edit Jadwal</h5>

                                <form method="POST">

                                    <input type="hidden" name="id" value="<?= $d['id'] ?>">

                                    <select name="hari" class="form-control mb-2">
                                        <option <?= $d['hari'] == "Senin" ? "selected" : "" ?>>Senin</option>
                                        <option <?= $d['hari'] == "Selasa" ? "selected" : "" ?>>Selasa</option>
                                        <option <?= $d['hari'] == "Rabu" ? "selected" : "" ?>>Rabu</option>
                                        <option <?= $d['hari'] == "Kamis" ? "selected" : "" ?>>Kamis</option>
                                        <option <?= $d['hari'] == "Jumat" ? "selected" : "" ?>>Jumat</option>
                                        <option <?= $d['hari'] == "Sabtu" ? "selected" : "" ?>>Sabtu</option>
                                    </select>

                                    <input type="time" name="jam_masuk"
                                        value="<?= date('H:i', strtotime($d['jam_masuk'])) ?>"
                                        class="form-control mb-2">

                                    <input type="time" name="jam_selesai"
                                        value="<?= date('H:i', strtotime($d['jam_selesai'])) ?>"
                                        class="form-control mb-2">

                                    <select name="kelas" class="form-control mb-2">
                                        <option value="01" <?= $d['kelas'] == "01" ? "selected" : "" ?>>01</option>
                                        <option value="02" <?= $d['kelas'] == "02" ? "selected" : "" ?>>02</option>
                                    </select>

                                    <select name="semester" class="form-control mb-2">
                                        <?php for ($i = 1; $i <= 8; $i++) { ?>
                                            <option value="<?= $i ?>" <?= $d['semester'] == $i ? "selected" : "" ?>>
                                                Semester <?= $i ?>
                                            </option>
                                        <?php } ?>
                                    </select>

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

                <form method="POST">

                    <select name="hari" class="form-control mb-2">
                        <option>Senin</option>
                        <option>Selasa</option>
                        <option>Rabu</option>
                        <option>Kamis</option>
                        <option>Jumat</option>
                        <option>Sabtu</option>
                    </select>

                    <input type="time" name="jam_masuk" class="form-control mb-2">
                    <input type="time" name="jam_selesai" class="form-control mb-2">

                    <select name="matkul_id" class="form-control mb-2">
                        <?php while ($m = mysqli_fetch_assoc($matkul)) { ?>
                            <option value="<?= $m['id'] ?>">
                                <?= $m['kode'] ?> - <?= $m['nama_matkul'] ?>
                            </option>
                        <?php } ?>
                    </select>

                    <select name="ruangan_id" class="form-control mb-2">
                        <?php while ($r = mysqli_fetch_assoc($ruang)) { ?>
                            <option value="<?= $r['id'] ?>">
                                <?= $r['nama_ruang'] ?> (Lt <?= $r['lantai'] ?>)
                            </option>
                        <?php } ?>
                    </select>

                    <select name="kelas" class="form-control mb-2">
                        <option value="01">01</option>
                        <option value="02">02</option>
                    </select>

                    <select name="semester" class="form-control mb-2">
                        <?php for ($i = 1; $i <= 8; $i++) { ?>
                            <option value="<?= $i ?>">Semester <?= $i ?></option>
                        <?php } ?>
                    </select>

                    <button name="simpan" class="btn btn-primary w-100">Simpan</button>

                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>