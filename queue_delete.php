<?php
include "config.php";

$id = $_GET['hapus'] ?? '';

mysqli_query($conn, "
    DELETE FROM fingerprint_queue
    WHERE id='$id'
");

header("Location: fingerprint_queue.php");
exit;
