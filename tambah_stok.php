<?php
session_start();

require_once __DIR__ . '/includes/koneksi.php';


/* ============================================
   CEK LOGIN
============================================ */

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}


/* ============================================
   PROSES SIMPAN
============================================ */

if (isset($_POST['simpan'])) {

    $id_pengguna = $_SESSION['id_pengguna'];

    $nama_bahan = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['nama_bahan'])
    );

    $jenis = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['jenis'])
    );

    $jumlah_stok = (int) $_POST['jumlah_stok'];

    $satuan = mysqli_real_escape_string(
        $koneksi,
        trim($_POST['satuan'])
    );

    $stok_minimum = (int) $_POST['stok_minimum'];


    /* ============================================
       VALIDASI
    ============================================ */

    if (
        empty($nama_bahan) ||
        empty($jenis) ||
        empty($satuan)
    ) {

        echo "<script>
        alert('Semua data wajib diisi.');
        window.history.back();
        </script>";

        exit;
    }


    if ($jumlah_stok <= 0) {

        echo "<script>
        alert('Jumlah stok harus lebih dari 0.');
        window.history.back();
        </script>";

        exit;
    }


    if ($jumlah_stok > 100000) {

        echo "<script>
        alert('Jumlah stok terlalu besar.');
        window.history.back();
        </script>";

        exit;
    }


    if ($stok_minimum < 0) {

        echo "<script>
        alert('Stok minimum tidak boleh negatif.');
        window.history.back();
        </script>";

        exit;
    }


    if ($stok_minimum > $jumlah_stok) {

        echo "<script>
        alert('Stok minimum tidak boleh lebih besar dari jumlah stok.');
        window.history.back();
        </script>";

        exit;
    }


    if (!preg_match("/^[A-Za-z\s]+$/", $nama_bahan)) {

        echo "<script>
        alert('Nama bahan hanya boleh berisi huruf.');
        window.history.back();
        </script>";

        exit;
    }


    /* ============================================
       CEK DUPLIKAT NAMA BAHAN
    ============================================ */

    $cekNama = mysqli_query($koneksi, "
        SELECT id_stok
        FROM stok
        WHERE LOWER(TRIM(nama_bahan))
        =
        LOWER(TRIM('$nama_bahan'))
    ");


    if (mysqli_num_rows($cekNama) > 0) {

        echo "<script>
        alert('Nama bahan sudah digunakan.');
        window.history.back();
        </script>";

        exit;
    }


    /* ============================================
       INSERT DATA
    ============================================ */

    $insert = mysqli_query($koneksi, "
        INSERT INTO stok
        (
            id_pengguna,
            nama_bahan,
            jenis,
            jumlah_stok,
            satuan,
            stok_minimum
        )
        VALUES
        (
            '$id_pengguna',
            '$nama_bahan',
            '$jenis',
            '$jumlah_stok',
            '$satuan',
            '$stok_minimum'
        )
    ");


    if ($insert) {

        echo "<script>
        alert('Data stok berhasil ditambahkan.');
        window.location='stok.php';
        </script>";
    } else {

        echo "<script>
        alert('Data stok gagal ditambahkan.');
        window.history.back();
        </script>";
    }

    exit;
}


/* ============================================
   DATA ADMIN
============================================ */

$nama_admin = $_SESSION['nama_petugas']
    ?? 'Administrator';

$inisial = strtoupper(
    substr($nama_admin, 0, 1)
);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Tambah Stok | Sistem Buket</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- Font Awesome -->

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet">


    <!-- Google Font -->

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">


    <!-- Sidebar -->

    <link
        rel="stylesheet"
        href="assets/css/sidebar.css">


    <!-- CSS Stok -->

    <link
        rel="stylesheet"
        href="assets/css/stok.css">


</head>


<body>


    <div class="stok-layout">


        <!-- =====================================================
         SIDEBAR
    ====================================================== -->

        <?php include 'sidebar.php'; ?>


        <!-- =====================================================
         CONTENT
    ====================================================== -->

        <main class="stok-page">


            <!-- =================================================
             HEADER
        ================================================== -->

            <div class="stok-header">


                <!-- JUDUL -->

                <div class="stok-title">

                    <h3>
                        Tambah Stok
                    </h3>

                    <p>
                        Tambahkan bahan dan jumlah stok baru
                    </p>

                </div>


                <!-- =================================================
                 ADMINISTRATOR
            ================================================== -->

                <div class="stok-user-dropdown">


                    <button
                        type="button"
                        class="stok-user-btn"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">


                        <div class="stok-avatar">

                            <?= htmlspecialchars($inisial); ?>

                        </div>


                        <div class="stok-user-info">

                            <div class="stok-user-name">

                                <?= htmlspecialchars($nama_admin); ?>

                            </div>

                            <div class="stok-user-role">

                                Admin

                            </div>

                        </div>


                        <i class="fa-solid fa-chevron-down stok-chevron"></i>


                    </button>


                    <!-- DROPDOWN -->

                    <ul
                        class="dropdown-menu dropdown-menu-end stok-dropdown-menu">


                        <li>

                            <div class="dropdown-item-text">

                                <strong>

                                    <?= htmlspecialchars($nama_admin); ?>

                                </strong>

                                <br>

                                <small class="text-muted">

                                    Login Aktif

                                </small>

                            </div>

                        </li>


                        <li>

                            <hr class="dropdown-divider">

                        </li>


                        <li>

                            <a
                                class="dropdown-item text-danger"
                                href="logout.php"
                                onclick="return confirm('Yakin ingin logout?')">


                                <i class="fa-solid fa-right-from-bracket me-2"></i>

                                Logout


                            </a>

                        </li>


                    </ul>


                </div>


            </div>


            <!-- =================================================
             FORM CARD
        ================================================== -->

            <div class="stok-card stok-form-card">


                <!-- HEADER CARD -->

                <div class="stok-card-header">


                    <div>

                        <h4>
                            Form Tambah Stok
                        </h4>

                        <p>
                            Isi data bahan yang ingin ditambahkan
                        </p>

                    </div>


                    <!-- KEMBALI -->

                    <a
                        href="stok.php"
                        class="stok-btn-reset">


                        <i class="fa-solid fa-arrow-left"></i>

                        Kembali


                    </a>


                </div>


                <!-- =================================================
                 FORM
            ================================================== -->

                <form
                    method="POST"
                    action="">


                    <div class="row">


                        <!-- =========================================
                         NAMA BAHAN
                    ========================================== -->

                        <div class="col-md-6">

                            <div class="stok-form-group">


                                <label
                                    for="nama_bahan"
                                    class="stok-form-label">

                                    Nama Bahan

                                </label>


                                <input
                                    type="text"
                                    name="nama_bahan"
                                    id="nama_bahan"
                                    class="stok-form-control"
                                    placeholder="Contoh: Mawar"
                                    maxlength="100"
                                    required>


                            </div>

                        </div>


                        <!-- =========================================
                         JENIS
                    ========================================== -->

                        <div class="col-md-6">

                            <div class="stok-form-group">


                                <label
                                    for="jenis"
                                    class="stok-form-label">

                                    Jenis

                                </label>


                                <select
                                    name="jenis"
                                    id="jenis"
                                    class="stok-form-control"
                                    required>


                                    <option value="">
                                        -- Pilih Jenis --
                                    </option>


                                    <option value="Bunga Segar">
                                        Bunga Segar
                                    </option>


                                    <option value="Bunga Artificial">
                                        Bunga Artificial
                                    </option>


                                    <option value="Kertas">
                                        Kertas
                                    </option>


                                    <option value="Pita">
                                        Pita
                                    </option>


                                    <option value="Aksesoris">
                                        Aksesoris
                                    </option>


                                    <option value="Lainnya">
                                        Lainnya
                                    </option>


                                </select>


                            </div>

                        </div>


                        <!-- =========================================
                         JUMLAH STOK
                    ========================================== -->

                        <div class="col-md-6">

                            <div class="stok-form-group">


                                <label
                                    for="jumlah_stok"
                                    class="stok-form-label">

                                    Jumlah Stok

                                </label>


                                <input
                                    type="number"
                                    name="jumlah_stok"
                                    id="jumlah_stok"
                                    class="stok-form-control"
                                    placeholder="Masukkan jumlah stok"
                                    min="1"
                                    max="100000"
                                    required>


                            </div>

                        </div>


                        <!-- =========================================
                         SATUAN
                    ========================================== -->

                        <div class="col-md-6">

                            <div class="stok-form-group">


                                <label
                                    for="satuan"
                                    class="stok-form-label">

                                    Satuan

                                </label>


                                <select
                                    name="satuan"
                                    id="satuan"
                                    class="stok-form-control"
                                    required>


                                    <option value="">
                                        -- Pilih Satuan --
                                    </option>


                                    <option value="Tangkai">
                                        Tangkai
                                    </option>


                                    <option value="Ikat">
                                        Ikat
                                    </option>


                                    <option value="Pack">
                                        Pack
                                    </option>


                                    <option value="Roll">
                                        Roll
                                    </option>


                                    <option value="Pcs">
                                        Pcs
                                    </option>


                                    <option value="Lembar">
                                        Lembar
                                    </option>


                                </select>


                            </div>

                        </div>


                        <!-- =========================================
                         STOK MINIMUM
                    ========================================== -->

                        <div class="col-md-6">

                            <div class="stok-form-group">


                                <label
                                    for="stok_minimum"
                                    class="stok-form-label">

                                    Stok Minimum

                                </label>


                                <input
                                    type="number"
                                    name="stok_minimum"
                                    id="stok_minimum"
                                    class="stok-form-control"
                                    placeholder="Contoh: 10"
                                    min="0"
                                    max="100000"
                                    required>


                                <div class="stok-form-help">

                                    Digunakan sebagai batas untuk status stok menipis.

                                </div>


                            </div>

                        </div>


                    </div>


                    <!-- =================================================
                     FORM FOOTER
                ================================================== -->

                    <div class="stok-form-footer">


                        <a
                            href="stok.php"
                            class="stok-btn-cancel">


                            Batal


                        </a>


                        <button
                            type="submit"
                            name="simpan"
                            class="stok-btn-save">


                            <i class="fa-solid fa-floppy-disk me-1"></i>

                            Simpan Data


                        </button>


                    </div>


                </form>


            </div>


        </main>


    </div>


    <!-- =========================================================
     BOOTSTRAP JS
========================================================= -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


</body>

</html>