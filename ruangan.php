<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama_ruangan = $_POST['nama_ruang'];
    $lantai = $_POST['lantai'];
    $kapasitas = $_POST['kapasitas'];

    mysqli_query($conn, "
        UPDATE ruangan 
        SET nama_ruang='$nama_ruangan',
            lantai='$lantai',
            kapasitas='$kapasitas'
        WHERE id='$id'
    ");

    header("Location: ruangan.php");
    exit;
}
function generateKode($conn)
{
    $q = mysqli_query($conn, "SELECT MAX(id) as max_id FROM ruangan");
    $d = mysqli_fetch_assoc($q);
    $id = $d['max_id'] + 1;

    return "R" . str_pad($id, 3, "0", STR_PAD_LEFT);
}
/* SIMPAN */
if (isset($_POST['simpan'])) {
    $kode_ruangan = generateKode($conn); // gunakan fungsi untuk menghasilkan kode
    $nama_ruangan = $_POST['nama_ruang'];
    $lantai = $_POST['lantai'];
    $kapasitas = $_POST['kapasitas'];

    mysqli_query($conn, "
        INSERT INTO ruangan (kode_ruang, nama_ruang, lantai, kapasitas)
        VALUES ('$kode_ruangan', '$nama_ruangan', '$lantai', '$kapasitas')
    ");
}

/* HAPUS */
if (isset($_GET['hapus'])) {
    mysqli_query($conn, "DELETE FROM ruangan WHERE id='$_GET[hapus]'");
}

/* DATA */
$data = mysqli_query($conn, "SELECT * FROM ruangan ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Ruangan - Smart Absensi</title>

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
        }

        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .main {
            margin-left: 250px;
            padding: 25px;
        }

        .topbar {
            background: #fff;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
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
            <h5 class="mb-0">Data Ruangan
            </h5>

            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                + Tambah Ruangan
            </button>
        </div>

        <!-- TABLE -->
        <div class="card p-3">

            <table class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Ruangan</th>
                        <th>Lantai</th>
                        <th>Kapasitas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $no = 1;
                    if (mysqli_num_rows($data) > 0) {
                        while ($d = mysqli_fetch_assoc($data)) {
                    ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= $d['kode_ruang']; ?></td>
                                <td><?= $d['nama_ruang']; ?></td>
                                <td><?= $d['lantai']; ?></td>
                                <td><?= $d['kapasitas']; ?></td>
                                <td>
                                    <button
                                        class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEdit<?= $d['id']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <a href="?hapus=<?= $d['id']; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus data?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>

                            <!-- MODAL EDIT -->
                            <div class="modal fade" id="modalEdit<?= $d['id']; ?>">
                                <div class="modal-dialog">
                                    <div class="modal-content p-3">

                                        <h5>Edit Ruangan</h5>

                                        <form method="POST">
                                            <input type="hidden" name="id" value="<?= $d['id']; ?>">

                                            <input type="text" name="nama_ruang" class="form-control mb-2" value="<?= $d['nama_ruang']; ?>" required>

                                            <select name="lantai" class="form-control mb-2">
                                                <option value="1" <?= $d['lantai'] == 1 ? 'selected' : ''; ?>>Lantai 1</option>
                                                <option value="2" <?= $d['lantai'] == 2 ? 'selected' : ''; ?>>Lantai 2</option>
                                                <option value="3" <?= $d['lantai'] == 3 ? 'selected' : ''; ?>>Lantai 3</option>
                                                <option value="4" <?= $d['lantai'] == 4 ? 'selected' : ''; ?>>Lantai 4</option>
                                            </select>

                                            <input type="number" name="kapasitas" class="form-control mb-2" value="<?= $d['kapasitas']; ?>" required>

                                            <button class="btn btn-primary w-100" name="update">Update</button>
                                        </form>

                                    </div>
                                </div>
                            </div>

                        <?php }
                    } else { ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                <i class="bi bi-inbox"></i> Belum ada data
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>

    </div>

    <!-- MODAL TAMBAH -->
    <div class="modal fade" id="modalTambah">
        <div class="modal-dialog">
            <div class="modal-content p-3">

                <h5>Tambah Ruangan</h5>

                <form method="POST">
                    <input type="hidden" name="kode_ruang" value="<?= generateKode($conn); ?>"> <!-- Kode otomatis -->

                    <input type="text" name="nama_ruang" class="form-control mb-2" placeholder="Nama Ruangan" required>

                    <select name="lantai" class="form-control mb-2" required>
                        <option value="1">Lantai 1</option>
                        <option value="2">Lantai 2</option>
                        <option value="3">Lantai 3</option>
                        <option value="4">Lantai 4</option>
                    </select>

                    <input type="number" name="kapasitas" class="form-control mb-2" placeholder="Kapasitas" required>

                    <button class="btn btn-primary w-100" name="simpan">Simpan</button>

                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>