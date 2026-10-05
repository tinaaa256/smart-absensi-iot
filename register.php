<?php
include "config.php";
session_start();

// jika sudah login arahkan sesuai role
if (isset($_SESSION['login'])) {

    if ($_SESSION['role'] == 'admin') {
        header("Location: dashboard_admin.php");
    } elseif ($_SESSION['role'] == 'dosen') {
        header("Location: dashboard_dosen.php");
    } elseif ($_SESSION['role'] == 'mahasiswa') {
        header("Location: dashboard_mahasiswa.php");
    } else {
        session_destroy();
        header("Location: login.php");
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Register - Smart Absensi</title>


    <!-- BOOTSTRAP -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- ICON -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
        rel="stylesheet">


    <!-- AOS -->

    <link
        href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css"
        rel="stylesheet">


    <style>
        * {
            box-sizing: border-box;
        }

        body {

            margin: 0;

            min-height: 100vh;

            background: #eef4fb;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 25px;

        }


        /* =========================
           CARD UTAMA
        ========================= */

        .register-card {

            width: 900px;

            max-width: 100%;

            min-height: 590px;

            background: white;

            border-radius: 14px;

            overflow: hidden;

            box-shadow:
                0 8px 30px rgba(0, 0, 0, .12);

            display: flex;

        }


        /* =========================
           BAGIAN KIRI
        ========================= */

        .brand-section {

            width: 42%;

            background:
                linear-gradient(135deg,
                    #edf6ff,
                    #f8fbff);

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            padding: 40px;

            text-align: center;

        }


        .fingerprint-icon {

            width: 145px;

            height: 145px;

            border-radius: 50%;

            background: #fff;

            display: flex;

            align-items: center;

            justify-content: center;

            box-shadow:
                0 5px 20px rgba(13, 110, 253, .12);

            margin-bottom: 25px;

        }


        .fingerprint-icon i {

            font-size: 85px;

            color: #0d6efd;

        }


        .brand-title {

            color: #0d6efd;

            font-size: 25px;

            font-weight: 700;

            margin-bottom: 12px;

        }


        .brand-text {

            color: #333;

            font-size: 14px;

            line-height: 1.7;

            margin: 0;

        }


        /* =========================
           BAGIAN FORM
        ========================= */

        .form-section {

            width: 58%;

            padding: 38px 45px;

            display: flex;

            flex-direction: column;

            justify-content: center;

        }


        .form-title {

            text-align: center;

            font-size: 25px;

            font-weight: 700;

            margin-bottom: 5px;

            color: #222;

        }


        .form-subtitle {

            text-align: center;

            font-size: 13px;

            color: #777;

            margin-bottom: 22px;

        }


        .form-control,
        .form-select {

            height: 43px;

            border-radius: 8px;

            border: 1px solid #ddd;

            font-size: 13px;

        }


        .form-control:focus,
        .form-select:focus {

            border-color: #0d6efd;

            box-shadow:
                0 0 0 2px rgba(13, 110, 253, .08);

        }


        .input-group .form-control {

            border-radius:
                8px 0 0 8px;

        }


        .input-group-text {

            background: #fff;

            border:
                1px solid #ddd;

            border-left: none;

            border-radius:
                0 8px 8px 0;

            cursor: pointer;

        }


        .btn-register {

            height: 43px;

            background: #0d6efd;

            border: none;

            border-radius: 8px;

            color: white;

            font-size: 13px;

            font-weight: 600;

            transition: .2s;

        }


        .btn-register:hover {

            background: #0b5ed7;

            transform:
                translateY(-1px);

        }


        .login-link {

            text-align: center;

            margin-top: 15px;

            font-size: 13px;

            color: #777;

        }


        .login-link a {

            color: #0d6efd;

            font-weight: 600;

            text-decoration: none;

        }


        .login-link a:hover {

            text-decoration: underline;

        }


        .copyright {

            text-align: center;

            font-size: 11px;

            color: #999;

            margin-top: 20px;

        }


        /* =========================
           MOBILE
        ========================= */

        @media(max-width: 768px) {

            body {

                padding: 15px;

            }


            .register-card {

                flex-direction: column;

                min-height: auto;

            }


            .brand-section {

                width: 100%;

                padding: 25px 20px;

            }


            .fingerprint-icon {

                width: 90px;

                height: 90px;

                margin-bottom: 12px;

            }


            .fingerprint-icon i {

                font-size: 52px;

            }


            .brand-title {

                font-size: 20px;

                margin-bottom: 5px;

            }


            .brand-text {

                font-size: 12px;

            }


            .form-section {

                width: 100%;

                padding: 30px 25px;

            }


            .form-title {

                font-size: 22px;

            }

        }
    </style>

</head>


<body>


    <div
        class="register-card"
        data-aos="fade-up"
        data-aos-duration="700">


        <!-- =================================
         KIRI / BRANDING
    ================================== -->

        <div class="brand-section">


            <div class="fingerprint-icon">

                <i class="bi bi-fingerprint"></i>

            </div>


            <div class="brand-title">

                SMART ABSENSI

            </div>


            <p class="brand-text">

                Sistem Informasi Absensi Mahasiswa

                <br>

                Berbasis IoT

                <br>

                Menggunakan ESP32 dan

                <br>

                Fingerprint AS608

            </p>


        </div>



        <!-- =================================
         KANAN / REGISTER
    ================================== -->

        <div class="form-section">


            <div class="form-title">

                Daftar Akun

            </div>


            <div class="form-subtitle">

                Silakan isi data mahasiswa

            </div>


            <form
                action="proses_register.php"
                method="POST">


                <!-- USERNAME + EMAIL -->

                <div class="row g-2">

                    <div class="col-md-6">

                        <input
                            type="text"
                            name="username"
                            class="form-control"
                            placeholder="Username"
                            required>

                    </div>


                    <div class="col-md-6">

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="Email"
                            required>

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="input-group mt-2">

                    <input
                        type="password"
                        id="pass1"
                        name="password"
                        class="form-control"
                        placeholder="Password"
                        required>

                    <span
                        class="input-group-text"
                        onclick="togglePass()">

                        <i
                            id="eyeIcon"
                            class="bi bi-eye"></i>

                    </span>

                </div>


                <!-- NIM + NAMA -->

                <div class="row g-2 mt-1">

                    <div class="col-md-5">

                        <input
                            type="text"
                            name="nim"
                            class="form-control"
                            placeholder="NIM"
                            required>

                    </div>


                    <div class="col-md-7">

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            placeholder="Nama Lengkap"
                            required>

                    </div>

                </div>


                <!-- SEMESTER + PRODI -->

                <div class="row g-2 mt-1">

                    <div class="col-md-5">

                        <select
                            name="semester"
                            class="form-select"
                            required>

                            <option value="">
                                Semester
                            </option>

                            <?php
                            for ($i = 1; $i <= 8; $i++) {
                            ?>

                                <option value="<?= $i ?>">

                                    Semester <?= $i ?>

                                </option>

                            <?php
                            }
                            ?>

                        </select>

                    </div>


                    <div class="col-md-7">

                        <input
                            type="text"
                            name="prodi"
                            class="form-control"
                            placeholder="Program Studi"
                            required>

                    </div>

                </div>


                <!-- DOSEN PA -->

                <input
                    type="text"
                    name="pa"
                    class="form-control mt-2"
                    placeholder="Dosen PA">


                <!-- FINGER ID -->

                <input
                    type="text"
                    name="finger_id"
                    class="form-control mt-2"
                    placeholder="Finger ID (opsional)">


                <!-- BUTTON -->

                <button
                    type="submit"
                    class="btn-register w-100 mt-3">

                    <i class="bi bi-person-plus"></i>

                    Daftar

                </button>


            </form>


            <!-- LOGIN -->

            <div class="login-link">

                Sudah punya akun?

                <a href="login.php">

                    Login

                </a>

            </div>


            <div class="copyright">

                © 2026 Smart Absensi

            </div>


        </div>

    </div>



    <!-- AOS -->

    <script
        src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js">
    </script>


    <script>
        AOS.init({

            duration: 600,

            once: true

        });


        function togglePass() {

            let p =
                document.getElementById("pass1");

            let i =
                document.getElementById("eyeIcon");


            if (p.type === "password") {

                p.type = "text";

                i.classList.replace(
                    "bi-eye",
                    "bi-eye-slash"
                );

            } else {

                p.type = "password";

                i.classList.replace(
                    "bi-eye-slash",
                    "bi-eye"
                );

            }

        }
    </script>


</body>

</html>