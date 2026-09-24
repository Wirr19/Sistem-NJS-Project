<?php

session_start();

require_once __DIR__ . '/includes/koneksi.php';

/* =========================================================
   CEK LOGIN
========================================================= */
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

/* =========================================================
   PROSES HAPUS
   Hanya owner
========================================================= */
if (isset($_GET['hapus'])) {

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
        echo "<script>
                alert('Akses ditolak. Hanya owner yang dapat menghapus data.');
                window.location='stok.php';
              </script>";
        exit;
    }

    $id_stok = (int) $_GET['hapus'];

    if ($id_stok <= 0) {
        echo "<script>
                alert('ID stok tidak valid.');
                window.location='stok.php';
              </script>";
        exit;
    }

    $cek = mysqli_prepare(
        $koneksi,
        "SELECT id_stok FROM stok WHERE id_stok = ?"
    );

    mysqli_stmt_bind_param($cek, "i", $id_stok);
    mysqli_stmt_execute($cek);

    $hasil_cek = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($hasil_cek) === 0) {

        mysqli_stmt_close($cek);

        echo "<script>
                alert('Data stok tidak ditemukan.');
                window.location='stok.php';
              </script>";
        exit;
    }

    mysqli_stmt_close($cek);

    $hapus = mysqli_prepare(
        $koneksi,
        "DELETE FROM stok WHERE id_stok = ?"
    );

    mysqli_stmt_bind_param($hapus, "i", $id_stok);

    if (mysqli_stmt_execute($hapus)) {

        mysqli_stmt_close($hapus);

        echo "<script>
                alert('Data stok berhasil dihapus.');
                window.location='stok.php';
              </script>";
        exit;
    } else {

        $error = mysqli_stmt_error($hapus);

        mysqli_stmt_close($hapus);

        echo "<script>
                alert('Data stok gagal dihapus: " . addslashes($error) . "');
                window.location='stok.php';
              </script>";
        exit;
    }
}

/* =========================================================
   PROSES EDIT
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {

    $id_stok       = (int) ($_POST['id_stok'] ?? 0);
    $nama_bahan    = trim($_POST['nama_bahan'] ?? '');
    $jenis         = trim($_POST['jenis'] ?? '');
    $jumlah_stok   = (int) ($_POST['jumlah_stok'] ?? 0);
    $satuan        = trim($_POST['satuan'] ?? '');
    $stok_minimum  = (int) ($_POST['stok_minimum'] ?? 0);

    /* -----------------------------------------------------
       VALIDASI ID
    ----------------------------------------------------- */
    if ($id_stok <= 0) {

        echo "<script>
                alert('ID stok tidak valid.');
                window.location='stok.php';
              </script>";
        exit;
    }

    /* -----------------------------------------------------
       VALIDASI FIELD WAJIB
    ----------------------------------------------------- */
    if (
        $nama_bahan === '' ||
        $jenis === '' ||
        $satuan === ''
    ) {

        echo "<script>
                alert('Nama bahan, jenis, dan satuan wajib diisi.');
                window.location='stok.php';
              </script>";
        exit;
    }

    /* -----------------------------------------------------
       VALIDASI NAMA BAHAN
    ----------------------------------------------------- */
    if (!preg_match('/^[A-Za-z\s]+$/', $nama_bahan)) {

        echo "<script>
                alert('Nama bahan hanya boleh berisi huruf dan spasi.');
                window.location='stok.php';
              </script>";
        exit;
    }

    /* -----------------------------------------------------
       VALIDASI JUMLAH STOK
    ----------------------------------------------------- */
    if ($jumlah_stok < 0 || $jumlah_stok > 100000) {

        echo "<script>
                alert('Jumlah stok harus antara 0 sampai 100000.');
                window.location='stok.php';
              </script>";
        exit;
    }

    /* -----------------------------------------------------
       VALIDASI STOK MINIMUM
    ----------------------------------------------------- */
    if ($stok_minimum < 0) {

        echo "<script>
                alert('Stok minimum tidak boleh kurang dari 0.');
                window.location='stok.php';
              </script>";
        exit;
    }

    if ($stok_minimum > $jumlah_stok) {

        echo "<script>
                alert('Stok minimum tidak boleh lebih besar dari jumlah stok.');
                window.location='stok.php';
              </script>";
        exit;
    }

    /* -----------------------------------------------------
       CEK DUPLIKAT NAMA BAHAN
    ----------------------------------------------------- */
    $cek_duplikat = mysqli_prepare(
        $koneksi,
        "SELECT id_stok
         FROM stok
         WHERE LOWER(nama_bahan) = LOWER(?)
         AND id_stok != ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param(
        $cek_duplikat,
        "si",
        $nama_bahan,
        $id_stok
    );

    mysqli_stmt_execute($cek_duplikat);

    $hasil_duplikat = mysqli_stmt_get_result($cek_duplikat);

    if (mysqli_num_rows($hasil_duplikat) > 0) {

        mysqli_stmt_close($cek_duplikat);

        echo "<script>
                alert('Nama bahan tersebut sudah tersedia.');
                window.location='stok.php';
              </script>";
        exit;
    }

    mysqli_stmt_close($cek_duplikat);

    /* -----------------------------------------------------
       UPDATE DATA
    ----------------------------------------------------- */
    $update = mysqli_prepare(
        $koneksi,
        "UPDATE stok
         SET nama_bahan = ?,
             jenis = ?,
             jumlah_stok = ?,
             satuan = ?,
             stok_minimum = ?
         WHERE id_stok = ?"
    );

    mysqli_stmt_bind_param(
        $update,
        "ssisis",
        $nama_bahan,
        $jenis,
        $jumlah_stok,
        $satuan,
        $stok_minimum,
        $id_stok
    );

    /*
     * Karena parameter terakhir adalah integer,
     * gunakan binding yang benar di bawah.
     */
    mysqli_stmt_close($update);

    $update = mysqli_prepare(
        $koneksi,
        "UPDATE stok
         SET nama_bahan = ?,
             jenis = ?,
             jumlah_stok = ?,
             satuan = ?,
             stok_minimum = ?
         WHERE id_stok = ?"
    );

    mysqli_stmt_bind_param(
        $update,
        "ssisis",
        $nama_bahan,
        $jenis,
        $jumlah_stok,
        $satuan,
        $stok_minimum,
        $id_stok
    );

    if (mysqli_stmt_execute($update)) {

        mysqli_stmt_close($update);

        echo "<script>
                alert('Data stok berhasil diperbarui.');
                window.location='stok.php';
              </script>";
        exit;
    } else {

        $error = mysqli_stmt_error($update);

        mysqli_stmt_close($update);

        echo "<script>
                alert('Data stok gagal diperbarui: " . addslashes($error) . "');
                window.location='stok.php';
              </script>";
        exit;
    }
}

/* =========================================================
   SEARCH
========================================================= */
$keyword = trim($_GET['keyword'] ?? '');

/* =========================================================
   QUERY DATA STOK
========================================================= */
$sql = "
    SELECT
        stok.*,
        user.nama
    FROM stok
    INNER JOIN user
        ON stok.id_pengguna = user.id_pengguna
    WHERE stok.nama_bahan LIKE ?
    ORDER BY stok.nama_bahan ASC
";

$search = '%' . $keyword . '%';

$stmt = mysqli_prepare($koneksi, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $search
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

/* =========================================================
   DATA ADMIN
========================================================= */
$nama_admin = $_SESSION['nama_petugas']
    ?? $_SESSION['nama']
    ?? 'Administrator';

$nama_admin = trim($nama_admin);

if ($nama_admin === '') {
    $nama_admin = 'Administrator';
}

$inisial_admin = strtoupper(
    substr($nama_admin, 0, 1)
);

if ($inisial_admin === '') {
    $inisial_admin = 'A';
}

$current_page = basename($_SERVER['PHP_SELF']);

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Data Stok | Sistem Buket</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Poppins -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Sidebar -->
    <link
        rel="stylesheet"
        href="assets/css/sidebar.css">

    <!-- Stok -->
    <link
        rel="stylesheet"
        href="assets/css/stok.css">

</head>

<body>

    <div class="d-flex stok-layout">

        <!-- =====================================================
         SIDEBAR
    ====================================================== -->
        <?php include __DIR__ . "/sidebar.php"; ?>


        <!-- =====================================================
         CONTENT
    ====================================================== -->
        <div class="flex-grow-1 stok-content">

            <div class="stok-page">

                <div class="container-fluid stok-container">

                    <!-- =================================================
                     HEADER
                ================================================== -->
                    <div class="stok-page-header">

                        <!-- JUDUL HALAMAN -->
                        <div class="stok-title-area">

                            <h3>
                                Data Stok
                            </h3>

                            <p>
                                Kelola data stok bahan buket
                            </p>

                        </div>


                        <!-- =================================================
                         ADMINISTRATOR
                    ================================================== -->
                        <div class="dropdown stok-admin-dropdown">

                            <button
                                class="user-dropdown-btn"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">

                                <!-- AVATAR -->
                                <div class="avatar-circle">
                                    <?= htmlspecialchars($inisial_admin); ?>
                                </div>

                                <!-- INFORMASI ADMIN -->
                                <div class="user-info">

                                    <div class="name">
                                        <?= htmlspecialchars($nama_admin); ?>
                                    </div>

                                    <div class="role">
                                        Admin
                                    </div>

                                </div>

                                <!-- CHEVRON -->
                                <i class="fa-solid fa-chevron-down"></i>

                            </button>


                            <!-- DROPDOWN -->
                            <ul class="dropdown-menu dropdown-menu-end">

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
                     CARD DATA STOK
                ================================================== -->
                    <div class="card-custom">

                        <!-- HEADER CARD -->
                        <div class="stok-card-header">

                            <div>

                                <h3>
                                    Data Stok
                                </h3>

                                <p>
                                    Daftar stok bahan yang tersedia
                                </p>

                            </div>


                            <!-- TAMBAH DATA -->
                            <a
                                href="tambah_stok.php"
                                class="btn-purple">

                                <i class="fa-solid fa-plus"></i>

                                Tambah Data

                            </a>

                        </div>


                        <!-- =================================================
                         SEARCH
                    ================================================== -->
                        <div class="stok-search-wrapper">

                            <form
                                method="GET"
                                class="stok-search-form">

                                <div class="stok-search-box">

                                    <i class="fa-solid fa-magnifying-glass"></i>

                                    <input
                                        type="text"
                                        name="keyword"
                                        value="<?= htmlspecialchars($keyword); ?>"
                                        placeholder="Cari nama bahan...">

                                </div>

                                <button
                                    type="submit"
                                    class="btn-search">

                                    Cari

                                </button>

                                <?php if ($keyword !== '') { ?>

                                    <a
                                        href="stok.php"
                                        class="btn-reset">

                                        <i class="fa-solid fa-rotate-left"></i>

                                        Reset

                                    </a>

                                <?php } ?>

                            </form>

                        </div>


                        <!-- =================================================
                         TABLE
                    ================================================== -->
                        <div class="table-responsive">

                            <table class="table table-custom table-hover align-middle">

                                <thead>

                                    <tr>

                                        <th width="60">
                                            No
                                        </th>

                                        <th>
                                            Nama Bahan
                                        </th>

                                        <th>
                                            Jenis
                                        </th>

                                        <th>
                                            Jumlah
                                        </th>

                                        <th>
                                            Satuan
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th width="150">
                                            Aksi
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    <?php

                                    $no = 1;

                                    if (mysqli_num_rows($result) > 0) {

                                        while ($row = mysqli_fetch_assoc($result)) {

                                            $jumlah = (int) $row['jumlah_stok'];

                                            $minimum = (int) $row['stok_minimum'];

                                            /* STATUS STOK */
                                            if ($jumlah <= 0) {

                                                $status = 'Habis';
                                                $status_class = 'status-habis';
                                            } elseif ($jumlah <= $minimum) {

                                                $status = 'Menipis';
                                                $status_class = 'status-menipis';
                                            } else {

                                                $status = 'Aman';
                                                $status_class = 'status-aman';
                                            }

                                    ?>

                                            <tr>

                                                <!-- NO -->
                                                <td>
                                                    <?= $no++; ?>
                                                </td>


                                                <!-- NAMA BAHAN -->
                                                <td>

                                                    <div class="nama-bahan">
                                                        <?= htmlspecialchars($row['nama_bahan']); ?>
                                                    </div>

                                                </td>


                                                <!-- JENIS -->
                                                <td>
                                                    <?= htmlspecialchars($row['jenis']); ?>
                                                </td>


                                                <!-- JUMLAH -->
                                                <td>

                                                    <strong>
                                                        <?= number_format($jumlah, 0, ',', '.'); ?>
                                                    </strong>

                                                </td>


                                                <!-- SATUAN -->
                                                <td>
                                                    <?= htmlspecialchars($row['satuan']); ?>
                                                </td>


                                                <!-- STATUS -->
                                                <td>

                                                    <span class="stok-status <?= $status_class; ?>">

                                                        <?= $status; ?>

                                                    </span>

                                                    <div class="stok-minimum">

                                                        Min:
                                                        <?= number_format($minimum, 0, ',', '.'); ?>

                                                    </div>

                                                </td>


                                                <!-- AKSI -->
                                                <td>

                                                    <div class="action-buttons">

                                                        <!-- EDIT -->
                                                        <button
                                                            type="button"
                                                            class="btn-action btn-edit"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalEditStok"

                                                            data-id="<?= (int) $row['id_stok']; ?>"

                                                            data-nama="<?= htmlspecialchars(
                                                                            $row['nama_bahan'],
                                                                            ENT_QUOTES
                                                                        ); ?>"

                                                            data-jenis="<?= htmlspecialchars(
                                                                            $row['jenis'],
                                                                            ENT_QUOTES
                                                                        ); ?>"

                                                            data-jumlah="<?= (int) $row['jumlah_stok']; ?>"

                                                            data-satuan="<?= htmlspecialchars(
                                                                                $row['satuan'],
                                                                                ENT_QUOTES
                                                                            ); ?>"

                                                            data-minimum="<?= (int) $row['stok_minimum']; ?>">

                                                            <i class="fa-solid fa-pen-to-square"></i>

                                                        </button>


                                                        <!-- HAPUS -->
                                                        <?php if (
                                                            isset($_SESSION['role']) &&
                                                            $_SESSION['role'] === 'owner'
                                                        ) { ?>

                                                            <a
                                                                href="stok.php?hapus=<?= (int) $row['id_stok']; ?>"
                                                                class="btn-action btn-delete"
                                                                onclick="return confirm('Yakin ingin menghapus data stok ini?')">

                                                                <i class="fa-solid fa-trash"></i>

                                                            </a>

                                                        <?php } ?>

                                                    </div>

                                                </td>

                                            </tr>

                                        <?php

                                        }
                                    } else {

                                        ?>

                                        <tr>

                                            <td
                                                colspan="7"
                                                class="empty-table">

                                                <div class="empty-data">

                                                    <i class="fa-solid fa-box-open"></i>

                                                    <h5>
                                                        <?= $keyword !== ''
                                                            ? 'Data stok tidak ditemukan'
                                                            : 'Belum ada data stok'; ?>
                                                    </h5>

                                                    <p>
                                                        <?= $keyword !== ''
                                                            ? 'Coba gunakan kata kunci pencarian yang lain.'
                                                            : 'Silakan tambahkan data stok terlebih dahulu.'; ?>
                                                    </p>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
     MODAL EDIT STOK
========================================================= -->
    <div
        class="modal fade"
        id="modalEditStok"
        tabindex="-1"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content modal-stok">

                <div class="modal-header">

                    <div>

                        <h5 class="modal-title">
                            Edit Data Stok
                        </h5>

                        <p>
                            Perbarui informasi stok bahan
                        </p>

                    </div>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                    </button>

                </div>


                <form method="POST">

                    <div class="modal-body">

                        <input
                            type="hidden"
                            name="edit"
                            value="1">

                        <input
                            type="hidden"
                            name="id_stok"
                            id="edit_id_stok">


                        <!-- NAMA BAHAN -->
                        <div class="modal-form-group">

                            <label for="edit_nama_bahan">
                                Nama Bahan
                            </label>

                            <input
                                type="text"
                                name="nama_bahan"
                                id="edit_nama_bahan"
                                class="form-control"
                                required>

                        </div>


                        <!-- JENIS -->
                        <div class="modal-form-group">

                            <label for="edit_jenis">
                                Jenis
                            </label>

                            <select
                                name="jenis"
                                id="edit_jenis"
                                class="form-control"
                                required>

                                <option value="">
                                    Pilih Jenis
                                </option>

                                <option value="Bunga">
                                    Bunga
                                </option>

                                <option value="Daun">
                                    Daun
                                </option>

                                <option value="Kemasan">
                                    Kemasan
                                </option>

                                <option value="Aksesoris">
                                    Aksesoris
                                </option>

                                <option value="Lainnya">
                                    Lainnya
                                </option>

                            </select>

                        </div>


                        <!-- JUMLAH -->
                        <div class="modal-form-group">

                            <label for="edit_jumlah_stok">
                                Jumlah Stok
                            </label>

                            <input
                                type="number"
                                name="jumlah_stok"
                                id="edit_jumlah_stok"
                                class="form-control"
                                min="0"
                                max="100000"
                                required>

                        </div>


                        <!-- SATUAN -->
                        <div class="modal-form-group">

                            <label for="edit_satuan">
                                Satuan
                            </label>

                            <select
                                name="satuan"
                                id="edit_satuan"
                                class="form-control"
                                required>

                                <option value="">
                                    Pilih Satuan
                                </option>

                                <option value="pcs">
                                    pcs
                                </option>

                                <option value="batang">
                                    batang
                                </option>

                                <option value="lembar">
                                    lembar
                                </option>

                                <option value="meter">
                                    meter
                                </option>

                                <option value="roll">
                                    roll
                                </option>

                                <option value="pack">
                                    pack
                                </option>

                            </select>

                        </div>


                        <!-- MINIMUM -->
                        <div class="modal-form-group mb-0">

                            <label for="edit_stok_minimum">
                                Stok Minimum
                            </label>

                            <input
                                type="number"
                                name="stok_minimum"
                                id="edit_stok_minimum"
                                class="form-control"
                                min="0"
                                max="100000"
                                required>

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn-modal-cancel"
                            data-bs-dismiss="modal">

                            Batal

                        </button>

                        <button
                            type="submit"
                            class="btn-modal-save">

                            <i class="fa-solid fa-floppy-disk me-1"></i>

                            Simpan Perubahan

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <!-- Bootstrap JS -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


    <script>
        /* =========================================================
   MODAL EDIT
========================================================= */

        const modalEditStok = document.getElementById('modalEditStok');

        if (modalEditStok) {

            modalEditStok.addEventListener(
                'show.bs.modal',
                function(event) {

                    const button = event.relatedTarget;

                    if (!button) {
                        return;
                    }

                    document.getElementById('edit_id_stok').value =
                        button.getAttribute('data-id');

                    document.getElementById('edit_nama_bahan').value =
                        button.getAttribute('data-nama');

                    document.getElementById('edit_jenis').value =
                        button.getAttribute('data-jenis');

                    document.getElementById('edit_jumlah_stok').value =
                        button.getAttribute('data-jumlah');

                    document.getElementById('edit_satuan').value =
                        button.getAttribute('data-satuan');

                    document.getElementById('edit_stok_minimum').value =
                        button.getAttribute('data-minimum');
                }
            );
        }
    </script>

</body>

</html>