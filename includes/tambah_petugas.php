<?php
require_once __DIR__ . '/koneksi.php';

if (isset($_POST['simpan'])) {

    $username     = $_POST['username'];
    $password     = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nama_petugas = $_POST['nama_petugas'];
    $no_hp        = $_POST['no_hp'];

    $cek_username = mysqli_query($koneksi, "SELECT username FROM tb_petugas WHERE username = '$username'");

    if (mysqli_num_rows($cek_username) > 0) {
        echo "<script>
                alert('Gagal! Username $username sudah digunakan. Silakan cari username lain.');
                window.history.back();
              </script>";
        exit();
    } else {
        mysqli_query(
            $koneksi,
            "INSERT INTO tb_petugas
            (username, password, nama_petugas, no_hp)
            VALUES
            ('$username', '$password', '$nama_petugas', '$no_hp')"
        );

        header("Location: ../laporan.php");
        exit();
    }
}
