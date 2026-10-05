<?php
include "config.php";

/* ================= AMBIL QUEUE ================= */
$data = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT * FROM fingerprint_queue
    WHERE status='WAITING'
    ORDER BY id ASC
    LIMIT 1
"));

if (!$data) {
    exit("NONE|0|0");
}

/* ================= LOCK ================= */
mysqli_query($conn, "
    UPDATE fingerprint_queue
    SET status='PROCESS'
    WHERE id='{$data['id']}'
");

/* ================= GENERATE FINGER ID AMAN ================= */
$check = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COALESCE(MAX(finger_id),0) + 1 AS next_id
    FROM mahasiswa
"));

$next_id = intval($check['next_id']);

echo $data['mahasiswa_id'] . "|" . $next_id . "|" . $data['id'];
