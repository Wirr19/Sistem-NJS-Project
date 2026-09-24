<?php
session_start();

require_once __DIR__ . '/includes/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['simpan'])) {

    $id_pengguna = $_SESSION['id_pengguna'];

    $tanggal = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['tanggal'])
    );

    $nama_buket = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['nama_buket'])
    );

    $jumlah = (int)$_POST['jumlah'];
    if (
        empty($tanggal) ||
        empty($nama_buket)
    ) {

        echo "<script>
    alert('Semua data wajib diisi.');
    window.history.back();
    </script>";
        exit;
    }

    if (!preg_match("/^[A-Za-z0-9\s\-]+$/", $nama_buket)) {

        echo "<script>
    alert('Nama buket tidak valid.');
    window.history.back();
    </script>";
        exit;
    }

    if ($jumlah <= 0) {

        echo "<script>
    alert('Jumlah harus lebih dari 0.');
    window.history.back();
    </script>";
        exit;
    }

    if ($jumlah > 100000) {

        echo "<script>
    alert('Jumlah terlalu besar.');
    window.history.back();
    </script>";
        exit;
    }
    $cek = mysqli_query($koneksi, "
SELECT id_penjualan
FROM penjualan
WHERE
tanggal='$tanggal'
AND LOWER(TRIM(nama_buket))=LOWER(TRIM('$nama_buket'))
AND jumlah='$jumlah'
");

    if (mysqli_num_rows($cek) > 0) {

        echo "<script>
    alert('Data penjualan sudah pernah dimasukkan.');
    window.history.back();
    </script>";

        exit;
    }

    $insert = mysqli_query($koneksi, "
        INSERT INTO penjualan
        (
            id_pengguna,
            tanggal,
            nama_buket,
            jumlah
        )
        VALUES
        (
            '$id_pengguna',
            '$tanggal',
            '$nama_buket',
            '$jumlah'
        )
    ");

    if ($insert) {

        echo "<script>

        alert('Data berhasil ditambahkan.');

        window.location='penjualan.php';

        </script>";
    } else {

        echo "<script>

        alert('Data gagal ditambahkan.');

        window.history.back();

        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Tambah Penjualan</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/sidebar.css">

    <link rel="stylesheet" href="assets/css/stok.css">

</head>

<body>

    <div class="d-flex">

        <?php include __DIR__ . "/sidebar.php"; ?>

        <div class="flex-grow-1 content-wrapper">

            <div class="container-fluid p-4">

                <div class="card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div>

                            <h3 class="">

                                Tambah Data Penjualan

                            </h3>

                            <small class="text-light">

                                Silakan isi data penjualan dengan lengkap.

                            </small>

                        </div>

                    </div>

                    <form method="POST">

                        <div class="row">

                            <!-- Tanggal -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label ">

                                    Tanggal Transaksi

                                </label>

                                <input
                                    type="date"
                                    name="tanggal"
                                    class="form-control"
                                    value="<?= date('Y-m-d'); ?>"
                                    required>

                            </div>

                            <!-- Nama Buket -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label ">

                                    Nama Buket

                                </label>
                                <input
                                    type="text"
                                    name="nama_buket"
                                    class="form-control"
                                    placeholder="Masukkan nama buket"
                                    maxlength="100"
                                    pattern="[A-Za-z0-9\s\-]+"
                                    required>

                            </div>

                            <!-- Jumlah -->

                            <div class="col-md-6 mb-4">

                                <label class="form-label ">

                                    Jumlah Terjual

                                </label>

                                <input
                                    type="number"
                                    name="jumlah"
                                    class="form-control"
                                    placeholder="Masukkan jumlah"
                                    min="1"
                                    max="100000"
                                    required>
                            </div>

                        </div>

                        <div class="mt-4">

                            <button
                                type="submit"
                                name="simpan"
                                class="btn btn-primary">

                                <i class="fa-solid fa-floppy-disk"></i>

                                Simpan

                            </button>

                            <a
                                href="penjualan.php"
                                class="btn btn-secondary">

                                <i class="fa-solid fa-arrow-left"></i>

                                Kembali

                            </a>

                        </div>

                    </form>