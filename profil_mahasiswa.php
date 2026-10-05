<?php
include "config.php";
session_start();

if (!isset($_SESSION['login']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: login.php");
    exit;
}


/* =========================
   AMBIL ID USER LOGIN
========================= */
$user_id = $_SESSION['id'];


/* =========================
   AMBIL ID MAHASISWA DARI USERS
========================= */
$qUser = mysqli_query($conn, "
SELECT mahasiswa_id
FROM users
WHERE id='$user_id'
");

$user = mysqli_fetch_assoc($qUser);


if (!$user || $user['mahasiswa_id'] == NULL) {
    die("Akun belum terhubung ke data mahasiswa");
}


$mahasiswa_id = $user['mahasiswa_id'];


/* =========================
   AMBIL DATA MAHASISWA
========================= */
$qMhs = mysqli_query($conn, "
SELECT *
FROM mahasiswa
WHERE id_mahasiswa='$mahasiswa_id'
");


$m = mysqli_fetch_assoc($qMhs);


if (!$m) {
    die("Data mahasiswa tidak ditemukan");
}

/* =========================
   UPDATE PROFIL
========================= */
if (isset($_POST['simpan'])) {

    $nama      = $_POST['nama'];
    $password  = $_POST['password'];
    $semester  = $_POST['semester'];
    $pa        = $_POST['pa'];
    $prodi     = $_POST['prodi'];

    mysqli_query($conn, "
    UPDATE mahasiswa SET
    nama='$nama',
    password='$password',
    semester='$semester',
    pa='$pa',
    prodi='$prodi'
    WHERE id_mahasiswa='$mahasiswa_id'
    ");

    mysqli_query($conn, "
    UPDATE users SET
    password='$password'
    WHERE id='$user_id'
    ");

    $_SESSION['nama'] = $nama;

    echo "<script>
    alert('Profil berhasil diperbarui');
    window.location='profil_mahasiswa.php';
    </script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Profil Mahasiswa - Smart Absensi</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">


    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {

            background: #f1f5f9;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            color: #1e293b;

            overflow-x: hidden;

        }


        /* =========================
           SIDEBAR
        ========================= */

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


        .sidebar a:hover {

            background:
                rgba(255, 255, 255, .18);

            padding-left: 21px;

        }


        .sidebar a i {

            font-size: 18px;

        }


        /* =========================
           MAIN
        ========================= */

        .main {

            margin-left: 250px;

            width:
                calc(100% - 250px);

            min-height: 100vh;

            padding: 25px;

        }


        /* =========================
           PROFILE HEADER
        ========================= */

        .profile-header {

            background: white;

            border-radius: 14px;

            padding: 22px;

            margin-bottom: 20px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, .07);

            display: flex;

            align-items: center;

            gap: 18px;

        }


        .profile-icon {

            width: 70px;

            height: 70px;

            min-width: 70px;

            border-radius: 50%;

            background: #e8f1ff;

            color: #0d6efd;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 38px;

        }


        .profile-header h3 {

            font-size: 24px;

            font-weight: bold;

            margin-bottom: 5px;

        }


        .profile-header p {

            color: #64748b;

            margin: 0;

        }


        /* =========================
           CARD
        ========================= */

        .box {

            background: white;

            border-radius: 14px;

            padding: 22px;

            margin-bottom: 20px;

            box-shadow:
                0 2px 10px rgba(0, 0, 0, .07);

        }


        .section-title {

            display: flex;

            align-items: center;

            gap: 9px;

            font-size: 18px;

            font-weight: bold;

            margin-bottom: 20px;

        }


        .section-title i {

            color: #0d6efd;

            font-size: 21px;

        }


        /* =========================
           FORM
        ========================= */

        .form-label {

            font-weight: 600;

            font-size: 14px;

            margin-bottom: 7px;

        }


        .form-control,
        .form-select {

            min-height: 45px;

            border-radius: 9px;

            border-color: #dbe2ea;

        }


        .form-control:focus,
        .form-select:focus {

            border-color: #0d6efd;

            box-shadow:
                0 0 0 .2rem rgba(13, 110, 253, .12);

        }


        .readonly {

            background: #f8fafc;

            color: #64748b;

        }


        .btn-primary {

            background: #0d6efd;

            border-color: #0d6efd;

            min-height: 45px;

            border-radius: 9px;

            font-weight: 600;

        }


        .btn-primary:hover {

            background: #0b5ed7;

        }


        /* =========================
           FINGERPRINT
        ========================= */

        .finger-card {

            background: #f8fafc;

            border-radius: 12px;

            padding: 16px;

            margin-top: 5px;

        }


        .finger-icon {

            width: 45px;

            height: 45px;

            border-radius: 10px;

            background: #e8f1ff;

            color: #0d6efd;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 22px;

            margin-bottom: 12px;

        }


        .status-ok {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            background: #d1e7dd;

            color: #146c43;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 600;

        }


        .status-ok::before {

            content: "✓";

            font-weight: bold;

        }


        .status-no {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            background: #f8d7da;

            color: #b02a37;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 600;

        }


        .status-no::before {

            content: "!";

            font-weight: bold;

        }


        /* =========================
           MENU HP
        ========================= */

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

            box-shadow:
                0 3px 10px rgba(0, 0, 0, .2);

        }


        /* =========================
           OVERLAY
        ========================= */

        .sidebar-overlay {

            display: none;

            position: fixed;

            inset: 0;

            background:
                rgba(0, 0, 0, .4);

            z-index: 900;

        }


        .sidebar-overlay.active {

            display: block;

        }


        /* =========================
           TABLET
        ========================= */

        @media(max-width:992px) {

            .main {

                padding: 20px;

            }

        }


        /* =========================
           HP
        ========================= */

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

                padding:
                    75px 12px 20px;

            }


            .box {

                padding: 16px;

                border-radius: 12px;

            }


            .profile-header {

                padding: 17px;

            }


            .profile-icon {

                width: 55px;

                height: 55px;

                min-width: 55px;

                font-size: 29px;

            }


            .profile-header h3 {

                font-size: 19px;

            }


            .profile-header p {

                font-size: 13px;

            }


            .section-title {

                font-size: 17px;

            }


            .form-label {

                font-size: 13px;

            }


            .form-control,
            .form-select {

                min-height: 43px;

                font-size: 14px;

            }

        }


        /* =========================
           HP KECIL
        ========================= */

        @media(max-width:480px) {

            .main {

                padding:
                    70px 10px 15px;

            }


            .box {

                padding: 14px;

            }


            .profile-header {

                gap: 12px;

            }


            .profile-icon {

                width: 48px;

                height: 48px;

                min-width: 48px;

                font-size: 25px;

            }


            .profile-header h3 {

                font-size: 17px;

            }


            .profile-header p {

                font-size: 12px;

            }

        }
    </style>

</head>


<body>


    <!-- =========================
     OVERLAY
========================= -->

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
        onclick="toggleMenu()">
    </div>


    <!-- =========================
     MENU HP
========================= -->

    <div
        class="menu-btn"
        onclick="toggleMenu()">

        <i class="bi bi-list"></i>

    </div>


    <!-- =========================
     SIDEBAR
========================= -->

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


        <a href="jadwal_mahasiswa.php">

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


    <!-- =========================
     MAIN
========================= -->

    <div class="main">


        <!-- =========================
         PROFILE HEADER
    ========================= -->

        <div class="profile-header">

            <div class="profile-icon">

                <i class="bi bi-person-fill"></i>

            </div>


            <div>

                <h3>

                    Profil Mahasiswa

                </h3>

                <p>

                    Kelola informasi data diri kamu.

                </p>

            </div>

        </div>


        <!-- =========================
         DATA PROFIL
    ========================= -->

        <div class="box">

            <div class="section-title">

                <i class="bi bi-person-vcard-fill"></i>

                Informasi Mahasiswa

            </div>


            <form method="POST">


                <!-- NIM -->

                <div class="mb-3">

                    <label class="form-label">

                        NIM

                    </label>

                    <input
                        type="text"
                        class="form-control readonly"
                        value="<?= htmlspecialchars($m['nim']); ?>"
                        readonly>

                </div>


                <!-- NAMA -->

                <div class="mb-3">

                    <label class="form-label">

                        Nama Mahasiswa

                    </label>

                    <input
                        type="text"
                        name="nama"
                        class="form-control"
                        value="<?= htmlspecialchars($m['nama']); ?>"
                        required>

                </div>


                <!-- PASSWORD -->

                <div class="mb-3">

                    <label class="form-label">

                        Password

                    </label>

                    <div class="input-group">

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            value="<?= htmlspecialchars($m['password']); ?>"
                            required>

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="togglePassword()">

                            <i
                                class="bi bi-eye"
                                id="eyeIcon">
                            </i>

                        </button>

                    </div>

                    <small class="text-muted">

                        Gunakan password yang mudah kamu ingat.

                    </small>

                </div>


                <!-- SEMESTER -->

                <div class="mb-3">

                    <label class="form-label">

                        Semester

                    </label>

                    <select
                        name="semester"
                        class="form-select">

                        <?php

                        for ($i = 1; $i <= 8; $i++) {

                        ?>

                            <option
                                value="<?= $i; ?>"
                                <?= ($m['semester'] == $i)
                                    ? 'selected'
                                    : ''; ?>>

                                Semester <?= $i; ?>

                            </option>

                        <?php

                        }

                        ?>

                    </select>

                </div>


                <!-- DOSEN PA -->

                <div class="mb-3">

                    <label class="form-label">

                        Dosen Pembimbing Akademik

                    </label>

                    <input
                        type="text"
                        name="pa"
                        class="form-control"
                        value="<?= htmlspecialchars($m['pa']); ?>">

                </div>


                <!-- PRODI -->

                <div class="mb-3">

                    <label class="form-label">

                        Program Studi

                    </label>

                    <input
                        type="text"
                        name="prodi"
                        class="form-control"
                        value="<?= htmlspecialchars($m['prodi']); ?>">

                </div>


                <!-- FINGERPRINT -->

                <div class="finger-card mb-4">

                    <div class="finger-icon">

                        <i class="bi bi-fingerprint"></i>

                    </div>


                    <label class="form-label">

                        Finger ID

                    </label>


                    <input
                        type="text"
                        class="form-control readonly mb-3"
                        value="<?= $m['finger_id']
                                    ? htmlspecialchars($m['finger_id'])
                                    : '-'; ?>"
                        readonly>


                    <label class="form-label">

                        Status Fingerprint

                    </label>


                    <div>

                        <?php if ($m['finger_id']) { ?>

                            <span class="status-ok">

                                Sudah Terdaftar

                            </span>

                        <?php } else { ?>

                            <span class="status-no">

                                Belum Terdaftar

                            </span>

                        <?php } ?>

                    </div>

                </div>


                <!-- SIMPAN -->

                <button
                    type="submit"
                    name="simpan"
                    class="btn btn-primary w-100">

                    <i class="bi bi-save"></i>

                    Simpan Perubahan

                </button>


            </form>

        </div>


    </div>


    <script>
        /* =========================
   MENU HP
========================= */

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

                        if (
                            window.innerWidth <= 768
                        ) {

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


        /* =========================
           SHOW / HIDE PASSWORD
        ========================= */

        function togglePassword() {

            const password =
                document.getElementById("password");

            const icon =
                document.getElementById("eyeIcon");


            if (
                password.type === "password"
            ) {

                password.type = "text";

                icon.classList.remove(
                    "bi-eye"
                );

                icon.classList.add(
                    "bi-eye-slash"
                );

            } else {

                password.type = "password";

                icon.classList.remove(
                    "bi-eye-slash"
                );

                icon.classList.add(
                    "bi-eye"
                );

            }

        }
    </script>


</body>

</html>