<?php
include "config.php";

$mahasiswa_id = intval($_POST['mahasiswa_id'] ?? 0);
$finger_id    = intval($_POST['finger_id'] ?? 0);
$queue_id     = intval($_POST['queue_id'] ?? 0);

if ($mahasiswa_id == 0 || $finger_id == 0 || $queue_id == 0) {
    exit("ERROR");
}

/* update queue */
mysqli_query($conn, "
UPDATE fingerprint_queue
SET status='DONE'
WHERE id='$queue_id'
");

/* update mahasiswa */
mysqli_query($conn, "
UPDATE mahasiswa
SET finger_id='$finger_id'
WHERE id_mahasiswa='$mahasiswa_id'
");

echo "OK";
