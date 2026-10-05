<?php
include "config.php";
session_start();
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Lupa Password - Smart Absensi</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
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
           CARD
        ========================= */

        .forgot-card {

            width: 850px;

            max-width: 100%;

            min-height: 480px;

            background: white;

            border-radius: 14px;

            overflow: hidden;

            display: flex;

            box-shadow:
                0 8px 30px rgba(0, 0, 0, .12);

        }


        /* =========================
           KIRI
        ========================= */

        .brand-section {

            width: 45%;

            background:
                linear-gradient(135deg,
                    #edf6ff,
                    #f8fbff);

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            text-align: center;

            padding: 40px;

        }


        .fingerprint-icon {

            width: 145px;

            height: 145px;

            border-radius: 50%;

            background: white;

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 25px;

            box-shadow:
                0 5px 20px rgba(13, 110, 253, .12);

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

            font-size: 14px;

            line-height: 1.7;

            color: #333;

            margin: 0;

        }


        /* =========================
           KANAN
        ========================= */

        .form-section {

            width: 55%;

            padding: 45px;

            display: flex;

            flex-direction: column;

            justify-content: center;

        }


        .form-title {

            font-size: 25px;

            font-weight: 700;

            text-align: center;

            color: #222;

            margin-bottom: 7px;

        }


        .form-subtitle {

            text-align: center;

            color: #777;

            font-size: 13px;

            line-height: 1.6;

            margin-bottom: 25px;

        }


        .form-control {

            height: 45px;

            border-radius: 8px;

            border: 1px solid #ddd;

            font-size: 13px;

        }


        .form-control:focus {

            border-color: #0d6efd;

            box-shadow:
                0 0 0 2px rgba(13, 110, 253, .08);

        }


        .email-icon {

            position: relative;

        }


        .email-icon i {

            position: absolute;

            left: 14px;

            top: 50%;

            transform:
                translateY(-50%);

            color: #777;

            z-index: 2;

        }


        .email-icon input {

            padding-left: 40px;

        }


        .btn-main {

            height: 45px;

            background: #0d6efd;

            border: none;

            border-radius: 8px;

            color: white;

            font-size: 13px;

            font-weight: 600;

            transition: .2s;

        }


        .btn-main:hover {

            background: #0b5ed7;

            transform:
                translateY(-1px);

        }


        .back-login {

            text-align: center;

            margin-top: 17px;

            font-size: 13px;

        }


        .back-login a {

            color: #0d6efd;

            text-decoration: none;

            font-weight: 600;

        }


        .back-login a:hover {

            text-decoration: underline;

        }


        .copyright {

            text-align: center;

            color: #999;

            font-size: 11px;

            margin-top: 25px;

        }


        /* =========================
           MOBILE
        ========================= */

        @media(max-width: 768px) {

            body {

                padding: 15px;

            }


            .forgot-card {

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
        class="forgot-card"
        data-aos="fade-up"
        data-aos-duration="700">


        <!-- =========================
         BRANDING
    ========================== -->

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



        <!-- =========================
         FORM
    ========================== -->

        <div class="form-section">


            <div class="form-title">

                Lupa Password

            </div>


            <div class="form-subtitle">

                Masukkan email yang terdaftar.

                <br>

                Kami akan mengirimkan kode OTP
                untuk verifikasi.

            </div>


            <form
                action="verifikasi_otp.php"
                method="POST">


                <div class="email-icon mb-3">

                    <i class="bi bi-envelope"></i>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Masukkan Email"
                        required>

                </div>


                <button
                    type="submit"
                    class="btn-main w-100">

                    <i class="bi bi-send"></i>

                    Kirim OTP

                </button>


            </form>


            <div class="back-login">

                Ingat password?

                <a href="login.php">

                    Kembali Login

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
    </script>


</body>

</html>