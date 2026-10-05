<?php
include "config.php";

$id = $_GET['id'] ?? '';
$id = mysqli_real_escape_string($conn, $id);

$data = mysqli_query($conn, "
    SELECT
        m.nama_matkul,
        m.sks,
        k.kelas
    FROM krs k
    JOIN matkul m ON m.id = k.matkul_id
    WHERE k.mahasiswa_id = '$id'
    ORDER BY m.nama_matkul ASC
");
?>

<table class="table table-sm table-bordered">

    <thead class="table-primary">
        <tr>
            <th>No</th>
            <th>Mata Kuliah</th>
            <th>SKS</th>
            <th>Kelas</th>
        </tr>
    </thead>

    <tbody>

        <?php
        $no = 1;

        while ($d = mysqli_fetch_assoc($data)) {
        ?>

            <tr>
                <td><?= $no++ ?></td>

                <td>
                    <?= htmlspecialchars($d['nama_matkul']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($d['sks']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($d['kelas']) ?>
                </td>
            </tr>

        <?php } ?>

    </tbody>

</table>