<?php
session_start();
include 'includes/koneksi.php';

// ==========================================
// CEK LOGIN
// ==========================================
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.php");
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];
$role = $_SESSION['role'] ?? '';


// ==========================================
// HAPUS DATA PENJUALAN
// HANYA OWNER
// ==========================================
if (isset($_GET['hapus'])) {

    if ($role !== 'owner') {
        echo "<script>
                alert('Akses ditolak. Hanya owner yang dapat menghapus data.');
                window.location='penjualan.php';
              </script>";
        exit;
    }

    $id_hapus = (int) $_GET['hapus'];

    // Cek apakah data tersedia
    $cek = mysqli_query(
        $koneksi,
        "SELECT id_penjualan
         FROM penjualan
         WHERE id_penjualan = $id_hapus"
    );

    if ($cek && mysqli_num_rows($cek) > 0) {

        $hapus = mysqli_query(
            $koneksi,
            "DELETE FROM penjualan
             WHERE id_penjualan = $id_hapus"
        );

        if ($hapus) {
            echo "<script>
                    alert('Data penjualan berhasil dihapus.');
                    window.location='penjualan.php';
                  </script>";
            exit;
        } else {
            echo "<script>
                    alert('Data penjualan gagal dihapus.');
                    window.location='penjualan.php';
                  </script>";
            exit;
        }
    } else {

        echo "<script>
                alert('Data penjualan tidak ditemukan.');
                window.location='penjualan.php';
              </script>";
        exit;
    }
}


// ==========================================
// TAMBAH DATA PENJUALAN
// ==========================================
if (isset($_POST['tambah'])) {

    $tanggal = trim($_POST['tanggal'] ?? '');
    $nama_buket = trim($_POST['nama_buket'] ?? '');
    $jumlah = trim($_POST['jumlah'] ?? '');

    // Validasi tanggal
    if ($tanggal === '') {
        echo "<script>
                alert('Tanggal wajib diisi.');
                window.location='penjualan.php';
              </script>";
        exit;
    }

    // Validasi nama buket
    if ($nama_buket === '') {
        echo "<script>
                alert('Nama buket wajib diisi.');
                window.location='penjualan.php';
              </script>";
        exit;
    }

    if (!preg_match('/^[A-Za-z0-9\s\-]+$/', $nama_buket)) {
        echo "<script>
                alert('Nama buket hanya boleh menggunakan huruf, angka, spasi, dan tanda -.');
                window.location='penjualan.php';
              </script>";
        exit;
    }

    // Validasi jumlah
    if ($jumlah === '' || !is_numeric($jumlah)) {
        echo "<script>
                alert('Jumlah penjualan wajib diisi dengan angka.');
                window.location='penjualan.php';
              </script>";
        exit;
    }

    $jumlah = (int) $jumlah;

    if ($jumlah <= 0) {
        echo "<script>
                alert('Jumlah penjualan harus lebih dari 0.');
                window.location='penjualan.php';
              </script>";
        exit;
    }

    if ($jumlah > 100000) {
        echo "<script>
                alert('Jumlah penjualan maksimal 100000.');
                window.location='penjualan.php';
              </script>";
        exit;
    }


    // ==========================================
    // CEK DATA DUPLIKAT
    // ==========================================
    $tanggal_db = mysqli_real_escape_string($koneksi, $tanggal);
    $nama_db = mysqli_real_escape_string($koneksi, $nama_buket);

    $cek_duplikat = mysqli_query(
        $koneksi,
        "SELECT id_penjualan
         FROM penjualan
         WHERE tanggal = '$tanggal_db'
         AND nama_buket = '$nama_db'
         AND jumlah = $jumlah
         LIMIT 1"
    );

    if ($cek_duplikat && mysqli_num_rows($cek_duplikat) > 0) {
        echo "<script>
                alert('Data penjualan dengan tanggal, nama buket, dan jumlah yang sama sudah ada.');
                window.location='penjualan.php';
              </script>";
        exit;
    }


    // ==========================================
    // INSERT
    // ==========================================
    $query_tambah = mysqli_query(
        $koneksi,
        "INSERT INTO penjualan
        (id_pengguna, tanggal, nama_buket, jumlah)
        VALUES
        ('$id_pengguna', '$tanggal_db', '$nama_db', '$jumlah')"
    );

    if ($query_tambah) {
        echo "<script>
                alert('Data penjualan berhasil ditambahkan.');
                window.location='penjualan.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Data penjualan gagal ditambahkan.');
                window.location='penjualan.php';
              </script>";
        exit;
    }
}


// ==========================================
// EDIT DATA PENJUALAN
// ==========================================
if (isset($_POST['edit'])) {

    $id_penjualan = (int) ($_POST['id_penjualan'] ?? 0);
    $tanggal = trim($_POST['tanggal'] ?? '');
    $nama_buket = trim($_POST['nama_buket'] ?? '');
    $jumlah = trim($_POST['jumlah'] ?? '');

    // Validasi ID
    if ($id_penjualan <= 0) {
        echo "<script>
                alert('Data penjualan tidak valid.');
                window.location='penjualan.php';
              </script>";
        exit;
    }

    // Validasi tanggal
    if ($tanggal === '') {
        echo "<script>
                alert('Tanggal wajib diisi.');
                window.location='penjualan.php';
              </script>";
        exit;
    }

    // Validasi nama buket
    if ($nama_buket === '') {
        echo "<script>
                alert('Nama buket wajib diisi.');
                window.location='penjualan.php';
              </script>";
        exit;
    }

    if (!preg_match('/^[A-Za-z0-9\s\-]+$/', $nama_buket)) {
        echo "<script>
                alert('Nama buket hanya boleh menggunakan huruf, angka, spasi, dan tanda -.');
                window.location='penjualan.php';
              </script>";
        exit;
    }

    // Validasi jumlah
    if ($jumlah === '' || !is_numeric($jumlah)) {
        echo "<script>
                alert('Jumlah penjualan wajib diisi dengan angka.');
                window.location='penjualan.php';
              </script>";
        exit;
    }

    $jumlah = (int) $jumlah;

    if ($jumlah <= 0) {
        echo "<script>
                alert('Jumlah penjualan harus lebih dari 0.');
                window.location='penjualan.php';
              </script>";
        exit;
    }

    if ($jumlah > 100000) {
        echo "<script>
                alert('Jumlah penjualan maksimal 100000.');
                window.location='penjualan.php';
              </script>";
        exit;
    }


    // ==========================================
    // UPDATE DATA
    // ==========================================
    $tanggal_db = mysqli_real_escape_string($koneksi, $tanggal);
    $nama_db = mysqli_real_escape_string($koneksi, $nama_buket);

    $query_edit = mysqli_query(
        $koneksi,
        "UPDATE penjualan
         SET tanggal = '$tanggal_db',
             nama_buket = '$nama_db',
             jumlah = $jumlah
         WHERE id_penjualan = $id_penjualan"
    );

    if ($query_edit) {
        echo "<script>
                alert('Data penjualan berhasil diperbarui.');
                window.location='penjualan.php';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('Data penjualan gagal diperbarui.');
                window.location='penjualan.php';
              </script>";
        exit;
    }
}


// ==========================================
// SEARCH
// ==========================================
$keyword = trim($_GET['keyword'] ?? '');

$keyword_db = mysqli_real_escape_string($koneksi, $keyword);


// ==========================================
// QUERY DATA PENJUALAN
// ==========================================
if ($keyword !== '') {

    $query = mysqli_query(
        $koneksi,
        "SELECT
            penjualan.id_penjualan,
            penjualan.id_pengguna,
            penjualan.tanggal,
            penjualan.nama_buket,
            penjualan.jumlah,
            user.nama
         FROM penjualan
         INNER JOIN user
            ON penjualan.id_pengguna = user.id_pengguna
         WHERE penjualan.nama_buket LIKE '%$keyword_db%'
         ORDER BY penjualan.tanggal DESC,
                  penjualan.id_penjualan DESC"
    );
} else {

    $query = mysqli_query(
        $koneksi,
        "SELECT
            penjualan.id_penjualan,
            penjualan.id_pengguna,
            penjualan.tanggal,
            penjualan.nama_buket,
            penjualan.jumlah,
            user.nama
         FROM penjualan
         INNER JOIN user
            ON penjualan.id_pengguna = user.id_pengguna
         ORDER BY penjualan.tanggal DESC,
                  penjualan.id_penjualan DESC"
    );
}


// ==========================================
// DATA ADMIN
// ==========================================
$nama_admin = $_SESSION['nama_petugas']
    ?? $_SESSION['nama']
    ?? 'Administrator';

$inisial = strtoupper(substr($nama_admin, 0, 1));

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Penjualan | Sistem Buket</title>


    <!-- ==========================================
         BOOTSTRAP
    ========================================== -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- ==========================================
         FONT AWESOME
    ========================================== -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


    <!-- ==========================================
         GOOGLE FONT
    ========================================== -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">


    <!-- ==========================================
         SIDEBAR CSS
    ========================================== -->
    <link
        rel="stylesheet"
        href="assets/css/sidebar.css">


    <!--
        TIDAK MENGGUNAKAN stok.css
        agar CSS halaman stok tidak menimpa halaman penjualan.
    -->


    <style>
        /* =====================================================
           GLOBAL
        ===================================================== */

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fb;
            color: #2d3748;
        }


        /* =====================================================
           LAYOUT
        ===================================================== */

        .penjualan-layout {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }


        /* =====================================================
           HALAMAN PENJUALAN
        ===================================================== */

        .penjualan-page {
            flex: 1;
            min-width: 0;
            padding: 30px;
            background: #f5f7fb;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .penjualan-header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }


        /* =====================================================
           JUDUL
        ===================================================== */

        .penjualan-title h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }

        .penjualan-title p {
            margin: 6px 0 0;
            font-size: 13px;
            color: #94a3b8;
        }


        /* =====================================================
           ADMIN DROPDOWN
        ===================================================== */

        .penjualan-user-dropdown {
            position: relative;
        }

        .penjualan-user-btn {
            display: flex;
            align-items: center;
            gap: 12px;

            border: 1px solid #edf0f7;
            background: #ffffff;

            border-radius: 40px;

            padding: 8px 14px 8px 8px;

            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);

            cursor: pointer;

            transition: 0.2s;
        }

        .penjualan-user-btn:hover {
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.09);
        }


        /* =====================================================
           AVATAR
        ===================================================== */

        .penjualan-avatar {
            width: 40px;
            height: 40px;

            border-radius: 50%;

            background: #4f46e5;
            color: #ffffff;

            display: flex;
            justify-content: center;
            align-items: center;

            font-size: 15px;
            font-weight: 700;

            flex-shrink: 0;
        }


        /* =====================================================
           INFO ADMIN
        ===================================================== */

        .penjualan-user-info {
            text-align: left;
            line-height: 1.2;
        }

        .penjualan-user-name {
            font-size: 13px;
            font-weight: 600;
            color: #1f2937;
        }

        .penjualan-user-role {
            margin-top: 3px;
            font-size: 11px;
            color: #94a3b8;
        }

        .penjualan-chevron {
            margin-left: 4px;
            color: #64748b;
            font-size: 11px;
        }


        /* =====================================================
           DROPDOWN MENU
        ===================================================== */

        .penjualan-dropdown-menu {
            display: none;

            position: absolute;

            right: 0;
            top: calc(100% + 8px);

            width: 180px;

            padding: 7px;

            background: #ffffff;

            border-radius: 12px;

            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.13);

            z-index: 9999;
        }

        .penjualan-dropdown-menu.show {
            display: block;
        }

        .penjualan-dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 9px;

            padding: 10px 12px;

            border-radius: 8px;

            text-decoration: none;

            color: #374151;

            font-size: 13px;
        }

        .penjualan-dropdown-menu a:hover {
            background: #eef2ff;
            color: #4f46e5;
        }


        /* =====================================================
           CARD
        ===================================================== */

        .penjualan-card {
            width: 100%;

            background: #ffffff;

            border: 1px solid #eef2f7;

            border-radius: 16px;

            padding: 25px;

            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.05);
        }


        /* =====================================================
           TOOLBAR
        ===================================================== */

        .penjualan-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 20px;

            margin-bottom: 22px;
        }


        /* =====================================================
           SEARCH
        ===================================================== */

        .penjualan-search {
            position: relative;
            width: 320px;
        }

        .penjualan-search i {
            position: absolute;

            left: 14px;
            top: 50%;

            transform: translateY(-50%);

            color: #94a3b8;

            font-size: 13px;

            pointer-events: none;
        }

        .penjualan-search input {
            width: 100%;
            height: 44px;

            padding: 0 14px 0 40px;

            border: 1px solid #dbe2ea;

            border-radius: 10px;

            background: #ffffff;

            color: #374151;

            font-family: 'Poppins', sans-serif;

            font-size: 13px;

            outline: none;

            transition: 0.2s;
        }

        .penjualan-search input::placeholder {
            color: #a0a7b4;
        }

        .penjualan-search input:focus {
            border-color: #4f46e5;

            box-shadow:
                0 0 0 3px rgba(79, 70, 229, 0.10);
        }


        /* =====================================================
           BUTTON TAMBAH
        ===================================================== */

        .penjualan-btn-tambah {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 8px;

            height: 44px;

            padding: 0 18px;

            background: #4f46e5;

            color: #ffffff;

            border: none;

            border-radius: 10px;

            font-family: 'Poppins', sans-serif;

            font-size: 13px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.2s;
        }

        .penjualan-btn-tambah:hover {
            background: #4338ca;

            color: #ffffff;

            transform: translateY(-1px);
        }


        /* =====================================================
           TABLE WRAPPER
        ===================================================== */

        .penjualan-table-wrapper {
            width: 100%;

            overflow-x: auto;

            border-radius: 12px;
        }


        /* =====================================================
           TABLE
        ===================================================== */

        .penjualan-table {
            width: 100%;

            margin: 0;

            border-collapse: collapse;

            background: #ffffff;
        }

        .penjualan-table thead th {
            padding: 14px 15px;

            background: #f8fafc;

            color: #64748b;

            border-bottom: 1px solid #e5e7eb;

            font-size: 12px;

            font-weight: 600;

            white-space: nowrap;

            text-align: left;
        }

        .penjualan-table tbody td {
            padding: 15px;

            color: #374151;

            border-bottom: 1px solid #f1f5f9;

            font-size: 13px;

            vertical-align: middle;
        }

        .penjualan-table tbody tr {
            transition: 0.2s;
        }

        .penjualan-table tbody tr:hover {
            background: #fafbff;
        }

        .penjualan-table tbody tr:last-child td {
            border-bottom: none;
        }


        /* =====================================================
           JUMLAH BADGE
        ===================================================== */

        .penjualan-jumlah {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            min-width: 45px;

            padding: 5px 10px;

            background: #eef2ff;

            color: #4f46e5;

            border-radius: 7px;

            font-size: 12px;

            font-weight: 600;
        }


        /* =====================================================
           ACTION
        ===================================================== */

        .penjualan-actions {
            display: flex;
            align-items: center;

            gap: 6px;
        }

        .penjualan-action {
            width: 34px;
            height: 34px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            border: none;

            border-radius: 8px;

            text-decoration: none;

            font-size: 12px;

            cursor: pointer;

            transition: 0.2s;
        }


        /* EDIT */

        .penjualan-edit {
            background: #eef2ff;
            color: #4f46e5;
        }

        .penjualan-edit:hover {
            background: #4f46e5;
            color: #ffffff;
        }


        /* DELETE */

        .penjualan-delete {
            background: #fff1f2;
            color: #dc3545;
        }

        .penjualan-delete:hover {
            background: #dc3545;
            color: #ffffff;
        }


        /* =====================================================
           EMPTY DATA
        ===================================================== */

        .penjualan-empty {
            text-align: center !important;

            padding: 55px 20px !important;

            color: #94a3b8 !important;
        }

        .penjualan-empty i {
            display: block;

            margin-bottom: 12px;

            color: #cbd5e1;

            font-size: 34px;
        }

        .penjualan-empty p {
            margin: 0;

            font-size: 13px;

            color: #94a3b8;
        }


        /* =====================================================
           MODAL
        ===================================================== */

        .penjualan-modal-content {
            border: none;

            border-radius: 18px;

            overflow: hidden;

            box-shadow: 0 20px 55px rgba(15, 23, 42, 0.18);
        }

        .penjualan-modal-header {
            display: flex;
            align-items: center;

            padding: 20px 24px;

            border-bottom: 1px solid #eef2f7;
        }

        .penjualan-modal-title {
            margin: 0;

            color: #1f2937;

            font-size: 17px;

            font-weight: 600;
        }

        .penjualan-modal-title i {
            color: #4f46e5;
        }

        .penjualan-modal-body {
            padding: 24px;
        }

        .penjualan-modal-footer {
            display: flex;
            justify-content: flex-end;

            gap: 10px;

            padding: 16px 24px;

            border-top: 1px solid #eef2f7;
        }


        /* =====================================================
           FORM
        ===================================================== */

        .penjualan-form-group {
            margin-bottom: 18px;
        }

        .penjualan-form-group:last-child {
            margin-bottom: 0;
        }

        .penjualan-form-label {
            display: block;

            margin-bottom: 7px;

            color: #374151;

            font-size: 13px;

            font-weight: 500;
        }

        .penjualan-form-control {
            width: 100%;

            height: 44px;

            padding: 0 13px;

            border: 1px solid #dbe2ea;

            border-radius: 9px;

            background: #ffffff;

            color: #374151;

            font-family: 'Poppins', sans-serif;

            font-size: 13px;

            outline: none;

            transition: 0.2s;
        }

        .penjualan-form-control:focus {
            border-color: #4f46e5;

            box-shadow:
                0 0 0 3px rgba(79, 70, 229, 0.10);
        }


        /* =====================================================
           MODAL BUTTON
        ===================================================== */

        .penjualan-btn-batal {
            height: 40px;

            padding: 0 17px;

            border: none;

            border-radius: 9px;

            background: #f1f5f9;

            color: #64748b;

            font-family: 'Poppins', sans-serif;

            font-size: 13px;

            font-weight: 500;

            cursor: pointer;
        }

        .penjualan-btn-batal:hover {
            background: #e2e8f0;
        }

        .penjualan-btn-simpan {
            height: 40px;

            padding: 0 18px;

            border: none;

            border-radius: 9px;

            background: #4f46e5;

            color: #ffffff;

            font-family: 'Poppins', sans-serif;

            font-size: 13px;

            font-weight: 500;

            cursor: pointer;
        }

        .penjualan-btn-simpan:hover {
            background: #4338ca;

            color: #ffffff;
        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 991px) {

            .penjualan-page {
                padding: 25px 20px;
            }

            .penjualan-user-info {
                display: none;
            }

            .penjualan-toolbar {
                align-items: stretch;
            }

            .penjualan-search {
                width: 100%;
            }

        }


        @media (max-width: 768px) {

            .penjualan-page {
                padding: 20px 15px;
            }

            .penjualan-header {
                align-items: flex-start;
            }

            .penjualan-title h3 {
                font-size: 20px;
            }

            .penjualan-title p {
                font-size: 12px;
            }

            .penjualan-toolbar {
                flex-direction: column;
            }

            .penjualan-search {
                width: 100%;
            }

            .penjualan-btn-tambah {
                width: 100%;
            }

            .penjualan-card {
                padding: 18px;
            }

            .penjualan-table thead th,
            .penjualan-table tbody td {
                padding: 12px;
            }

        }


        @media (max-width: 576px) {

            .penjualan-page {
                padding: 15px 10px;
            }

            .penjualan-card {
                padding: 15px;
                border-radius: 12px;
            }

            .penjualan-user-btn {
                padding: 6px;
            }

            .penjualan-avatar {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }

        }
    </style>

</head>


<body>


    <div class="penjualan-layout">


        <!-- =====================================================
         SIDEBAR
    ===================================================== -->

        <?php include 'sidebar.php'; ?>


        <!-- =====================================================
         CONTENT PENJUALAN
    ===================================================== -->

        <main class="penjualan-page">


            <!-- =================================================
             HEADER
        ================================================= -->

            <div class="penjualan-header">


                <!-- JUDUL -->

                <div class="penjualan-title">

                    <h3>
                        Data Penjualan
                    </h3>

                    <p>
                        Kelola data penjualan buket
                    </p>

                </div>


                <!-- =================================================
                 ADMIN
            ================================================= -->

                <div class="penjualan-user-dropdown">


                    <button
                        type="button"
                        class="penjualan-user-btn"
                        onclick="togglePenjualanDropdown()">


                        <div class="penjualan-avatar">

                            <?= htmlspecialchars($inisial); ?>

                        </div>


                        <div class="penjualan-user-info">

                            <div class="penjualan-user-name">

                                <?= htmlspecialchars($nama_admin); ?>

                            </div>

                            <div class="penjualan-user-role">

                                Admin

                            </div>

                        </div>


                        <i class="fa-solid fa-chevron-down penjualan-chevron"></i>


                    </button>


                    <!-- DROPDOWN -->

                    <div
                        class="penjualan-dropdown-menu"
                        id="penjualanDropdownMenu">


                        <a
                            href="logout.php"
                            onclick="return confirm('Yakin ingin logout?')">

                            <i class="fa-solid fa-right-from-bracket"></i>

                            Logout

                        </a>


                    </div>


                </div>


            </div>


            <!-- =================================================
             CARD DATA PENJUALAN
        ================================================= -->

            <div class="penjualan-card">


                <!-- =================================================
                 TOOLBAR
            ================================================= -->

                <div class="penjualan-toolbar">


                    <!-- SEARCH -->

                    <form
                        method="GET"
                        action="penjualan.php"
                        class="penjualan-search">


                        <i class="fa-solid fa-magnifying-glass"></i>


                        <input
                            type="text"
                            name="keyword"
                            value="<?= htmlspecialchars($keyword); ?>"
                            placeholder="Cari nama buket...">


                    </form>


                    <!-- TAMBAH -->

                    <button
                        type="button"
                        class="penjualan-btn-tambah"
                        data-bs-toggle="modal"
                        data-bs-target="#modalTambah">


                        <i class="fa-solid fa-plus"></i>

                        Tambah Penjualan


                    </button>


                </div>


                <!-- =================================================
                 TABLE
            ================================================= -->

                <div class="penjualan-table-wrapper">


                    <table class="penjualan-table">


                        <thead>

                            <tr>

                                <th width="60">
                                    No
                                </th>

                                <th>
                                    Nama User
                                </th>

                                <th>
                                    Nama Buket
                                </th>

                                <th>
                                    Jumlah
                                </th>

                                <th>
                                    Tanggal
                                </th>

                                <th width="120">
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php

                            $no = 1;

                            if ($query && mysqli_num_rows($query) > 0):

                                while ($data = mysqli_fetch_assoc($query)):

                            ?>


                                    <tr>


                                        <!-- NO -->

                                        <td>

                                            <?= $no++; ?>

                                        </td>


                                        <!-- NAMA USER -->

                                        <td>

                                            <?= htmlspecialchars($data['nama']); ?>

                                        </td>


                                        <!-- NAMA BUKET -->

                                        <td>

                                            <?= htmlspecialchars($data['nama_buket']); ?>

                                        </td>


                                        <!-- JUMLAH -->

                                        <td>

                                            <span class="penjualan-jumlah">

                                                <?= number_format(
                                                    (int)$data['jumlah'],
                                                    0,
                                                    ',',
                                                    '.'
                                                ); ?>

                                            </span>

                                        </td>


                                        <!-- TANGGAL -->

                                        <td>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime($data['tanggal'])
                                            ); ?>

                                        </td>


                                        <!-- AKSI -->

                                        <td>

                                            <div class="penjualan-actions">


                                                <!-- EDIT -->

                                                <button
                                                    type="button"
                                                    class="penjualan-action penjualan-edit"

                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEdit"

                                                    data-id="<?= $data['id_penjualan']; ?>"

                                                    data-tanggal="<?= htmlspecialchars(
                                                                        $data['tanggal'],
                                                                        ENT_QUOTES
                                                                    ); ?>"

                                                    data-buket="<?= htmlspecialchars(
                                                                    $data['nama_buket'],
                                                                    ENT_QUOTES
                                                                ); ?>"

                                                    data-jumlah="<?= $data['jumlah']; ?>"

                                                    title="Edit">


                                                    <i class="fa-solid fa-pen"></i>


                                                </button>


                                                <!-- HAPUS -->

                                                <?php if ($role === 'owner'): ?>


                                                    <a
                                                        href="penjualan.php?hapus=<?= $data['id_penjualan']; ?>"
                                                        class="penjualan-action penjualan-delete"

                                                        title="Hapus"

                                                        onclick="return confirm('Yakin ingin menghapus data penjualan ini?')">


                                                        <i class="fa-solid fa-trash"></i>


                                                    </a>


                                                <?php endif; ?>


                                            </div>

                                        </td>


                                    </tr>


                                <?php

                                endwhile;

                            else:

                                ?>


                                <!-- DATA KOSONG -->

                                <tr>

                                    <td
                                        colspan="6"
                                        class="penjualan-empty">


                                        <i class="fa-solid fa-receipt"></i>


                                        <?php if ($keyword !== ''): ?>

                                            <p>
                                                Data penjualan tidak ditemukan
                                            </p>

                                        <?php else: ?>

                                            <p>
                                                Belum ada data penjualan
                                            </p>

                                        <?php endif; ?>


                                    </td>

                                </tr>


                            <?php endif; ?>


                        </tbody>


                    </table>


                </div>


            </div>


        </main>


    </div>



    <!-- =========================================================
     MODAL TAMBAH
========================================================= -->

    <div
        class="modal fade"
        id="modalTambah"
        tabindex="-1"
        aria-labelledby="modalTambahLabel"
        aria-hidden="true">


        <div class="modal-dialog modal-dialog-centered">


            <div class="modal-content penjualan-modal-content">


                <form
                    method="POST"
                    action="penjualan.php">


                    <!-- HEADER -->

                    <div class="penjualan-modal-header">


                        <h5
                            class="penjualan-modal-title"
                            id="modalTambahLabel">


                            <i class="fa-solid fa-plus me-2"></i>

                            Tambah Penjualan


                        </h5>


                        <button
                            type="button"
                            class="btn-close ms-auto"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                        </button>


                    </div>


                    <!-- BODY -->

                    <div class="penjualan-modal-body">


                        <!-- TANGGAL -->

                        <div class="penjualan-form-group">


                            <label
                                for="tanggalTambah"
                                class="penjualan-form-label">

                                Tanggal

                            </label>


                            <input
                                type="date"
                                class="penjualan-form-control"
                                id="tanggalTambah"
                                name="tanggal"
                                value="<?= date('Y-m-d'); ?>"
                                required>


                        </div>


                        <!-- NAMA BUKET -->

                        <div class="penjualan-form-group">


                            <label
                                for="namaBuketTambah"
                                class="penjualan-form-label">

                                Nama Buket

                            </label>


                            <input
                                type="text"
                                class="penjualan-form-control"
                                id="namaBuketTambah"
                                name="nama_buket"
                                placeholder="Masukkan nama buket"
                                maxlength="100"
                                required>


                        </div>


                        <!-- JUMLAH -->

                        <div class="penjualan-form-group">


                            <label
                                for="jumlahTambah"
                                class="penjualan-form-label">

                                Jumlah

                            </label>


                            <input
                                type="number"
                                class="penjualan-form-control"
                                id="jumlahTambah"
                                name="jumlah"
                                placeholder="Masukkan jumlah penjualan"
                                min="1"
                                max="100000"
                                required>


                        </div>


                    </div>


                    <!-- FOOTER -->

                    <div class="penjualan-modal-footer">


                        <button
                            type="button"
                            class="penjualan-btn-batal"
                            data-bs-dismiss="modal">

                            Batal

                        </button>


                        <button
                            type="submit"
                            name="tambah"
                            class="penjualan-btn-simpan">


                            <i class="fa-solid fa-floppy-disk me-1"></i>

                            Simpan


                        </button>


                    </div>


                </form>


            </div>


        </div>


    </div>



    <!-- =========================================================
     MODAL EDIT
========================================================= -->

    <div
        class="modal fade"
        id="modalEdit"
        tabindex="-1"
        aria-labelledby="modalEditLabel"
        aria-hidden="true">


        <div class="modal-dialog modal-dialog-centered">


            <div class="modal-content penjualan-modal-content">


                <form
                    method="POST"
                    action="penjualan.php">


                    <!-- HEADER -->

                    <div class="penjualan-modal-header">


                        <h5
                            class="penjualan-modal-title"
                            id="modalEditLabel">


                            <i class="fa-solid fa-pen me-2"></i>

                            Edit Penjualan


                        </h5>


                        <button
                            type="button"
                            class="btn-close ms-auto"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                        </button>


                    </div>


                    <!-- BODY -->

                    <div class="penjualan-modal-body">


                        <!-- ID -->

                        <input
                            type="hidden"
                            name="id_penjualan"
                            id="editId">


                        <!-- TANGGAL -->

                        <div class="penjualan-form-group">


                            <label
                                for="editTanggal"
                                class="penjualan-form-label">

                                Tanggal

                            </label>


                            <input
                                type="date"
                                class="penjualan-form-control"
                                id="editTanggal"
                                name="tanggal"
                                required>


                        </div>


                        <!-- NAMA BUKET -->

                        <div class="penjualan-form-group">


                            <label
                                for="editBuket"
                                class="penjualan-form-label">

                                Nama Buket

                            </label>


                            <input
                                type="text"
                                class="penjualan-form-control"
                                id="editBuket"
                                name="nama_buket"
                                maxlength="100"
                                required>


                        </div>


                        <!-- JUMLAH -->

                        <div class="penjualan-form-group">


                            <label
                                for="editJumlah"
                                class="penjualan-form-label">

                                Jumlah

                            </label>


                            <input
                                type="number"
                                class="penjualan-form-control"
                                id="editJumlah"
                                name="jumlah"
                                min="1"
                                max="100000"
                                required>


                        </div>


                    </div>


                    <!-- FOOTER -->

                    <div class="penjualan-modal-footer">


                        <button
                            type="button"
                            class="penjualan-btn-batal"
                            data-bs-dismiss="modal">

                            Batal

                        </button>


                        <button
                            type="submit"
                            name="edit"
                            class="penjualan-btn-simpan">


                            <i class="fa-solid fa-floppy-disk me-1"></i>

                            Simpan Perubahan


                        </button>


                    </div>


                </form>


            </div>


        </div>


    </div>



    <!-- =========================================================
     BOOTSTRAP JS
========================================================= -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>



    <script>
        /* =====================================================
       DROPDOWN ADMIN
    ===================================================== */

        function togglePenjualanDropdown() {

            const menu = document.getElementById(
                'penjualanDropdownMenu'
            );

            if (menu) {

                menu.classList.toggle('show');

            }

        }


        /* Tutup dropdown jika klik di luar */

        document.addEventListener('click', function(event) {

            const dropdown =
                document.querySelector(
                    '.penjualan-user-dropdown'
                );

            const menu =
                document.getElementById(
                    'penjualanDropdownMenu'
                );


            if (
                dropdown &&
                menu &&
                !dropdown.contains(event.target)
            ) {

                menu.classList.remove('show');

            }

        });



        /* =====================================================
           MODAL EDIT
        ===================================================== */

        const modalEdit =
            document.getElementById('modalEdit');


        if (modalEdit) {

            modalEdit.addEventListener(
                'show.bs.modal',
                function(event) {

                    const button =
                        event.relatedTarget;


                    if (!button) {
                        return;
                    }


                    const id =
                        button.getAttribute('data-id');


                    const tanggal =
                        button.getAttribute('data-tanggal');


                    const buket =
                        button.getAttribute('data-buket');


                    const jumlah =
                        button.getAttribute('data-jumlah');


                    document.getElementById(
                        'editId'
                    ).value = id;


                    document.getElementById(
                        'editTanggal'
                    ).value = tanggal;


                    document.getElementById(
                        'editBuket'
                    ).value = buket;


                    document.getElementById(
                        'editJumlah'
                    ).value = jumlah;

                }
            );

        }



        /* =====================================================
           MODAL TAMBAH
        ===================================================== */

        const modalTambah =
            document.getElementById('modalTambah');


        if (modalTambah) {

            modalTambah.addEventListener(
                'shown.bs.modal',
                function() {

                    const input =
                        document.getElementById(
                            'namaBuketTambah'
                        );


                    if (input) {

                        input.focus();

                    }

                }
            );

        }
    </script>


</body>

</html>