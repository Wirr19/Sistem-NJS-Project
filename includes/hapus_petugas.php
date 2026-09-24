<?php
session_start();
require_once __DIR__ . '/koneksi.php';


if (isset($_POST['hapus'])) {

    $id = (int)$_POST['id_petugas'];

    mysqli_query(
        $koneksi,
        "DELETE FROM tb_petugas
         WHERE id_petugas = $id"
    );

    echo "<script>
            alert('Petugas berhasil dihapus');
            window.location='../laporan.php';
          </script>";
}
?>
</form>