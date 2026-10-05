<?php
include "config.php";
session_start();

// Jika sudah login, arahkan sesuai role
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

    <title>Login - Smart Absensi</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
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

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                linear-gradient(135deg,
                    #eaf4ff,
                    #f8fbff);

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 20px;

        }


        /* ==============================
           LOGIN CONTAINER
        ============================== */

        .login-container {

            width: 900px;

            max-width: 100%;

            min-height: 520px;

            background: #ffffff;

            border-radius: 16px;

            overflow: hidden;

            box-shadow:
                0 12px 35px rgba(0, 0, 0, .12);

            display: flex;

        }


        /* ==============================
           LEFT SIDE
        ============================== */

        .login-left {

            width: 50%;

            background:
                linear-gradient(135deg,
                    #eaf5ff,
                    #f8fbff);

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            text-align: center;

            padding: 45px 35px;

            border-right:
                1px solid #e4edf7;

        }


        /* FINGERPRINT */

        .fingerprint-box {

            width: 170px;

            height: 170px;

            position: relative;

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 15px;

        }


        .fingerprint-icon {

            font-size: 120px;

            color: #1688d8;

            line-height: 1;

            filter:
                drop-shadow(0 5px 5px rgba(0, 120, 220, .15));

        }


        /* SENSOR */

        .sensor {

            position: absolute;

            right: 0;

            bottom: 10px;

            width: 55px;

            height: 65px;

            background:
                linear-gradient(145deg,
                    #1f2937,
                    #111827);

            border-radius:
                5px 5px 9px 9px;

            box-shadow:
                0 7px 12px rgba(0, 0, 0, .2);

        }


        .sensor::before {

            content: "";

            position: absolute;

            width: 38px;

            height: 25px;

            background: #0f172a;

            left: 8px;

            top: 8px;

            border-radius: 4px;

        }


        .sensor::after {

            content: "";

            position: absolute;

            width: 18px;

            height: 7px;

            background: #16a34a;

            left: 18px;

            bottom: 10px;

            border-radius: 5px;

            box-shadow:
                0 0 7px rgba(34, 197, 94, .8);

        }


        /* TEXT LEFT */

        .brand-title {

            color: #0877c9;

            font-size: 27px;

            font-weight: 800;

            letter-spacing: .5px;

            margin-top: 5px;

            margin-bottom: 10px;

        }


        .brand-description {

            color: #27364a;

            font-size: 14px;

            line-height: 1.8;

            max-width: 300px;

            margin: 0;

        }


        /* ==============================
           RIGHT SIDE
        ============================== */

        .login-right {

            width: 50%;

            padding:
                55px 65px;

            display: flex;

            flex-direction: column;

            justify-content: center;

        }


        .login-title {

            font-size: 30px;

            font-weight: 700;

            text-align: center;

            color: #182536;

            margin-bottom: 8px;

        }


        .login-subtitle {

            text-align: center;

            color: #7b8794;

            font-size: 13px;

            margin-bottom: 32px;

        }


        /* INPUT */

        .input-group-custom {

            margin-bottom: 17px;

            position: relative;

        }


        .input-group-custom .input-icon {

            position: absolute;

            left: 15px;

            top: 50%;

            transform: translateY(-50%);

            color: #7d8b99;

            font-size: 15px;

            z-index: 5;

        }


        .input-group-custom input {

            width: 100%;

            height: 48px;

            border:
                1px solid #d8e0e8;

            border-radius: 7px;

            padding:
                0 45px;

            font-size: 13px;

            outline: none;

            transition: .2s;

            background: #fff;

        }


        .input-group-custom input:focus {

            border-color: #1688d8;

            box-shadow:
                0 0 0 3px rgba(22, 136, 216, .10);

        }


        .input-group-custom input::placeholder {

            color: #a4adb7;

        }


        /* EYE */

        .password-eye {

            position: absolute;

            right: 15px;

            top: 50%;

            transform: translateY(-50%);

            color: #7d8b99;

            cursor: pointer;

            z-index: 5;

        }


        /* BUTTON */

        .btn-login {

            width: 100%;

            height: 48px;

            border: none;

            border-radius: 7px;

            background:
                linear-gradient(135deg,
                    #0877d1,
                    #1264c8);

            color: white;

            font-size: 13px;

            font-weight: bold;

            letter-spacing: .3px;

            transition: .2s;

            margin-top: 5px;

        }


        .btn-login:hover {

            transform:
                translateY(-1px);

            box-shadow:
                0 6px 14px rgba(13, 110, 253, .25);

        }


        /* LINKS */

        .login-links {

            text-align: center;

            margin-top: 18px;

            font-size: 12px;

        }


        .login-links a {

            color: #0877d1;

            text-decoration: none;

        }


        .login-links a:hover {

            text-decoration: underline;

        }


        /* FOOTER */

        .copyright {

            text-align: center;

            font-size: 11px;

            color: #9aa3ad;

            margin-top: 28px;

        }


        /* ==============================
           MOBILE
        ============================== */

        @media(max-width: 768px) {

            body {

                padding: 15px;

            }


            .login-container {

                width: 100%;

                min-height: auto;

                flex-direction: column;

            }


            .login-left {

                width: 100%;

                padding:
                    35px 25px;

                border-right: none;

                border-bottom:
                    1px solid #e4edf7;

            }


            .login-right {

                width: 100%;

                padding:
                    35px 25px;

            }


            .fingerprint-box {

                width: 140px;

                height: 130px;

            }


            .fingerprint-icon {

                font-size: 90px;

            }


            .sensor {

                width: 45px;

                height: 55px;

            }


            .brand-title {

                font-size: 23px;

            }


            .brand-description {

                font-size: 13px;

            }


            .login-title {

                font-size: 26px;

            }

        }
    </style>

</head>


<body>


    <div
        class="login-container"
        data-aos="fade-up"
        data-aos-duration="700">


        <!-- =================================
             BAGIAN KIRI
        ================================== -->

        <div class="login-left">


            <div class="fingerprint-box">

                <!-- Fingerprint -->

                <i class="
                    bi bi-fingerprint
                    fingerprint-icon
                "></i>


                <!-- Gambaran alat fingerprint -->

                <div class="sensor"></div>

            </div>


            <div class="brand-title">

                SMART ABSENSI

            </div>


            <p class="brand-description">

                Absensi Mahasiswa Berbasis IoT
                <br>

                Menggunakan ESP32 dan
                <br>

                Fingerprint AS608

            </p>


        </div>


        <!-- =================================
             BAGIAN KANAN
        ================================== -->

        <div class="login-right">


            <h1 class="login-title">

                Login

            </h1>


            <p class="login-subtitle">

                Silakan masuk menggunakan akun Anda

            </p>


            <form
                action="proses_login.php"
                method="POST">


                <!-- USERNAME -->

                <div class="input-group-custom">

                    <i class="
                        bi bi-person
                        input-icon
                    "></i>


                    <input
                        type="text"
                        name="username"
                        placeholder="Username"
                        autocomplete="username"
                        required>

                </div>


                <!-- PASSWORD -->

                <div class="input-group-custom">

                    <i class="
                        bi bi-lock
                        input-icon
                    "></i>


                    <input
                        type="password"
                        id="pass1"
                        name="password"
                        placeholder="Password"
                        autocomplete="current-password"
                        required>


                    <i
                        id="eyeIcon"
                        class="
                            bi bi-eye
                            password-eye
                        "
                        onclick="togglePass()">
                    </i>

                </div>


                <!-- LOGIN -->

                <button
                    type="submit"
                    class="btn-login">

                    LOGIN

                </button>


                <!-- LINKS -->

                <div class="login-links">

                    <a href="register.php">
                        Daftar
                    </a>

                    <span class="text-muted">
                        &nbsp;|&nbsp;
                    </span>

                    <a href="lupa_password.php">
                        Lupa Password?
                    </a>

                </div>


            </form>


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
            duration: 700,
            once: true
        });


        function togglePass() {

            const password =
                document.getElementById("pass1");

            const icon =
                document.getElementById("eyeIcon");


            if (password.type === "password") {

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