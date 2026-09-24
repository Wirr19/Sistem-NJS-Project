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
   ID PENGGUNA LOGIN
========================================================= */
$id_pengguna = isset($_SESSION['id_pengguna'])
    ? (int) $_SESSION['id_pengguna']
    : 0;

if ($id_pengguna <= 0) {
    die("ID pengguna tidak valid.");
}

/* =========================================================
   FILTER PERIODE
========================================================= */
$periode_awal = isset($_GET['periode_awal'])
    ? trim($_GET['periode_awal'])
    : '';

$periode_akhir = isset($_GET['periode_akhir'])
    ? trim($_GET['periode_akhir'])
    : '';

/* =========================================================
   VALIDASI FORMAT PERIODE
   Format: YYYY-MM
========================================================= */
if (
    $periode_awal !== '' &&
    !preg_match('/^\d{4}-\d{2}$/', $periode_awal)
) {
    $periode_awal = '';
}

if (
    $periode_akhir !== '' &&
    !preg_match('/^\d{4}-\d{2}$/', $periode_akhir)
) {
    $periode_akhir = '';
}

/* =========================================================
   QUERY DATA PERAMALAN
========================================================= */
$sql = "
    SELECT
        id_peramalan,
        id_pengguna,
        periode,
        nama_buket,
        biaya_promosi,
        jumlah_pengunjung,
        nilai_a,
        nilai_b1,
        nilai_b2,
        nilai_b,
        hasil_prediksi,
        nilai_mape,
        mad,
        mse,
        tanggal_proses
    FROM peramalan
    WHERE id_pengguna = ?
";

$params = [$id_pengguna];
$types = "i";

/* =========================================================
   FILTER PERIODE AWAL
========================================================= */
if ($periode_awal !== '') {

    $sql .= " AND periode >= ? ";

    $params[] = $periode_awal;
    $types .= "s";
}

/* =========================================================
   FILTER PERIODE AKHIR
========================================================= */
if ($periode_akhir !== '') {

    $sql .= " AND periode <= ? ";

    $params[] = $periode_akhir;
    $types .= "s";
}

/* =========================================================
   URUTKAN DATA
========================================================= */
$sql .= "
    ORDER BY
        periode ASC,
        id_peramalan ASC
";

/* =========================================================
   PREPARE QUERY
========================================================= */
$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die("Query laporan peramalan gagal: "
        . mysqli_error($koneksi));
}

/* =========================================================
   BIND PARAMETER
========================================================= */
mysqli_stmt_bind_param(
    $stmt,
    $types,
    ...$params
);

/* =========================================================
   EKSEKUSI
========================================================= */
if (!mysqli_stmt_execute($stmt)) {
    die("Eksekusi laporan peramalan gagal: "
        . mysqli_stmt_error($stmt));
}

/* =========================================================
   AMBIL HASIL
========================================================= */
$result = mysqli_stmt_get_result($stmt);

$data_laporan = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data_laporan[] = $row;
}

mysqli_stmt_close($stmt);

/* =========================================================
   NAMA BULAN INDONESIA
========================================================= */
$nama_bulan = [
    1  => 'Januari',
    2  => 'Februari',
    3  => 'Maret',
    4  => 'April',
    5  => 'Mei',
    6  => 'Juni',
    7  => 'Juli',
    8  => 'Agustus',
    9  => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

/* =========================================================
   FUNGSI FORMAT PERIODE
========================================================= */
function formatPeriodeIndonesia($periode, $nama_bulan)
{
    if (preg_match('/^(\d{4})-(\d{2})$/', $periode, $match)) {

        $tahun = $match[1];
        $bulan = (int) $match[2];

        if (isset($nama_bulan[$bulan])) {
            return $nama_bulan[$bulan] . ' ' . $tahun;
        }
    }

    return htmlspecialchars($periode);
}

/* =========================================================
   TOTAL PREDIKSI
   Hanya menghitung baris yang tidak memiliki MAPE,
   karena MAPE NULL digunakan sebagai indikator
   prediksi periode berikutnya.
========================================================= */
$total_prediksi = 0;
$jumlah_prediksi = 0;

foreach ($data_laporan as $row) {

    if (
        $row['nilai_mape'] === null ||
        $row['nilai_mape'] === ''
    ) {

        $total_prediksi += (float) (
            $row['hasil_prediksi'] ?? 0
        );

        $jumlah_prediksi++;
    }
}

/* =========================================================
   NAMA ADMIN
========================================================= */
$nama_admin = $_SESSION['nama_petugas']
    ?? $_SESSION['nama']
    ?? 'Administrator';

/* =========================================================
   INISIAL ADMIN
========================================================= */
$inisial_admin = strtoupper(
    substr(
        trim($nama_admin),
        0,
        1
    )
);

if ($inisial_admin === '') {
    $inisial_admin = 'A';
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Laporan Peramalan | Sistem Buket
    </title>

    <!-- =====================================================
         BOOTSTRAP
    ====================================================== -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- =====================================================
         BOOTSTRAP ICONS
    ====================================================== -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet">

    <!-- =====================================================
         FONT POPPINS
    ====================================================== -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- =====================================================
         SIDEBAR CSS
    ====================================================== -->
    <link
        rel="stylesheet"
        href="assets/css/sidebar.css">

    <!-- =====================================================
         LAPORAN CSS
    ====================================================== -->
    <link
        rel="stylesheet"
        href="assets/css/laporan.css">

    <!-- =====================================================
         STOK CSS
         Untuk komponen administrator
    ====================================================== -->
    <link
        rel="stylesheet"
        href="assets/css/stok.css">

</head>

<body>

    <div class="d-flex">

        <!-- =====================================================
         SIDEBAR
    ====================================================== -->
        <?php include __DIR__ . "/sidebar.php"; ?>


        <!-- =====================================================
         CONTENT UTAMA
    ====================================================== -->
        <div class="flex-grow-1">

            <div class="stok-page">

                <div class="container-fluid px-4 py-4">


                    <!-- =================================================
                     HEADER
                ================================================== -->
                    <div
                        class="d-flex justify-content-between align-items-center mb-4">

                        <!-- JUDUL -->
                        <div>

                            <h3 class="fw-bold mb-1">
                                Laporan
                            </h3>

                            <p class="text-muted mb-0">
                                Laporan hasil peramalan
                                penjualan buket
                            </p>

                        </div>


                        <!-- =================================================
                         ADMINISTRATOR
                    ================================================== -->
                        <div class="dropdown">

                            <button
                                class="user-dropdown-btn"
                                data-bs-toggle="dropdown"
                                type="button">

                                <!-- AVATAR -->
                                <div class="avatar-circle">

                                    <?= htmlspecialchars(
                                        $inisial_admin
                                    ); ?>

                                </div>


                                <!-- USER INFO -->
                                <div class="user-info">

                                    <div class="name">

                                        <?= htmlspecialchars(
                                            $nama_admin
                                        ); ?>

                                    </div>

                                    <div class="role">
                                        Admin
                                    </div>

                                </div>


                                <!-- CHEVRON -->
                                <i
                                    class="fa-solid fa-chevron-down"></i>

                            </button>


                            <!-- DROPDOWN -->
                            <ul
                                class="dropdown-menu dropdown-menu-end shadow border-0">

                                <li>

                                    <div
                                        class="dropdown-item-text">

                                        <strong>

                                            <?= htmlspecialchars(
                                                $nama_admin
                                            ); ?>

                                        </strong>

                                        <br>

                                        <small class="text-muted">
                                            Login Aktif
                                        </small>

                                    </div>

                                </li>


                                <li>

                                    <hr
                                        class="dropdown-divider">

                                </li>


                                <li>

                                    <a
                                        class="dropdown-item text-danger"
                                        href="logout.php"
                                        onclick="return confirm('Yakin ingin logout?')">

                                        <i
                                            class="fa-solid fa-right-from-bracket me-2"></i>

                                        Logout

                                    </a>

                                </li>

                            </ul>

                        </div>

                    </div>


                    <!-- =================================================
                     CARD FILTER
                ================================================== -->
                    <div
                        class="card-custom laporan-filter-card">

                        <!-- HEADER -->
                        <div
                            class="laporan-card-header d-flex justify-content-between align-items-center mb-4">

                            <div>

                                <h3>
                                    Laporan
                                </h3>

                                <small class="text-muted">
                                    Laporan hasil peramalan
                                    penjualan buket
                                </small>

                            </div>


                            <!-- CETAK PDF -->
                            <a
                                href="includes/laporan/cetak_laporan.php?periode_awal=<?= urlencode($periode_awal); ?>&periode_akhir=<?= urlencode($periode_akhir); ?>"
                                class="btn-cetak"
                                target="_blank">

                                <i
                                    class="fa-solid fa-file-pdf"></i>

                                Cetak Laporan PDF

                            </a>

                        </div>


                        <!-- =================================================
                         FORM FILTER
                    ================================================== -->
                        <form method="GET">

                            <div class="filter-row">


                                <!-- JENIS LAPORAN -->
                                <div class="form-group">

                                    <label>
                                        Jenis Laporan
                                    </label>

                                    <input
                                        type="text"
                                        class="form-control"
                                        value="Laporan Peramalan"
                                        readonly>

                                </div>


                                <!-- DARI PERIODE -->
                                <div class="form-group">

                                    <label>
                                        Dari Periode
                                    </label>

                                    <input
                                        type="month"
                                        name="periode_awal"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                                    $periode_awal
                                                ); ?>">

                                </div>


                                <!-- SAMPAI PERIODE -->
                                <div class="form-group">

                                    <label>
                                        Sampai Periode
                                    </label>

                                    <input
                                        type="month"
                                        name="periode_akhir"
                                        class="form-control"
                                        value="<?= htmlspecialchars(
                                                    $periode_akhir
                                                ); ?>">

                                </div>


                                <!-- BUTTON -->
                                <div class="form-group">

                                    <button
                                        type="submit"
                                        class="btn-tampilkan">

                                        <i
                                            class="fa-solid fa-magnifying-glass"></i>

                                        Tampilkan

                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>


                    <!-- =================================================
                     CARD HASIL LAPORAN
                ================================================== -->
                    <div
                        class="card-custom hasil-laporan-card">

                        <!-- HEADER -->
                        <div class="hasil-header">

                            <h3>
                                Hasil Laporan
                            </h3>

                            <small class="text-muted">
                                Hasil peramalan regresi linier berganda
                            </small>

                        </div>


                        <?php if (count($data_laporan) > 0) { ?>


                            <!-- =================================================
                             INFO JUMLAH DATA
                        ================================================== -->
                            <div class="mb-3">

                                <small class="text-muted">

                                    Menampilkan

                                    <strong>
                                        <?= count($data_laporan); ?>
                                    </strong>

                                    data hasil peramalan

                                </small>

                            </div>


                            <!-- =================================================
                             TABLE
                        ================================================== -->
                            <div class="table-responsive">

                                <table
                                    class="table table-custom table-hover align-middle">

                                    <!-- HEADER -->
                                    <thead>

                                        <tr>

                                            <th width="50">
                                                No
                                            </th>

                                            <th>
                                                Periode
                                            </th>

                                            <th>
                                                Nama Buket
                                            </th>

                                            <th>
                                                Biaya Promosi
                                            </th>

                                            <th>
                                                Pengunjung
                                            </th>

                                            <th>
                                                Hasil Prediksi
                                            </th>

                                            <th>
                                                MAD
                                            </th>

                                            <th>
                                                MSE
                                            </th>

                                            <th>
                                                MAPE
                                            </th>

                                            <th>
                                                Status
                                            </th>

                                            <th>
                                                Tanggal Proses
                                            </th>

                                        </tr>

                                    </thead>


                                    <!-- BODY -->
                                    <tbody>

                                        <?php
                                        $no = 1;
                                        ?>

                                        <?php foreach (
                                            $data_laporan as $row
                                        ) { ?>

                                            <?php

                                            $mape =
                                                $row['nilai_mape'];

                                            $mad =
                                                $row['mad'];

                                            $mse =
                                                $row['mse'];

                                            /*
                                         * MAPE NULL =
                                         * prediksi periode berikutnya.
                                         */
                                            $is_prediksi =
                                                (
                                                    $mape === null ||
                                                    $mape === ''
                                                );

                                            ?>

                                            <tr>


                                                <!-- NO -->
                                                <td>
                                                    <?= $no++; ?>
                                                </td>


                                                <!-- PERIODE -->
                                                <td>

                                                    <?= formatPeriodeIndonesia(
                                                        $row['periode'],
                                                        $nama_bulan
                                                    ); ?>

                                                </td>


                                                <!-- NAMA BUKET -->
                                                <td>

                                                    <?= htmlspecialchars(
                                                        $row['nama_buket']
                                                            ?? '-'
                                                    ); ?>

                                                </td>


                                                <!-- BIAYA PROMOSI -->
                                                <td>

                                                    Rp
                                                    <?= number_format(
                                                        (float)(
                                                            $row['biaya_promosi']
                                                            ?? 0
                                                        ),
                                                        0,
                                                        ',',
                                                        '.'
                                                    ); ?>

                                                </td>


                                                <!-- PENGUNJUNG -->
                                                <td>

                                                    <?= number_format(
                                                        (int)(
                                                            $row['jumlah_pengunjung']
                                                            ?? 0
                                                        ),
                                                        0,
                                                        ',',
                                                        '.'
                                                    ); ?>

                                                </td>


                                                <!-- HASIL PREDIKSI -->
                                                <td>

                                                    <strong>

                                                        <?= number_format(
                                                            (float)(
                                                                $row['hasil_prediksi']
                                                                ?? 0
                                                            ),
                                                            2,
                                                            ',',
                                                            '.'
                                                        ); ?>

                                                    </strong>

                                                    <span class="ms-1">
                                                        buket
                                                    </span>

                                                </td>


                                                <!-- MAD -->
                                                <td>

                                                    <?php if (
                                                        $mad !== null &&
                                                        $mad !== ''
                                                    ) { ?>

                                                        <?= number_format(
                                                            (float)$mad,
                                                            2,
                                                            ',',
                                                            '.'
                                                        ); ?>

                                                    <?php } else { ?>

                                                        <span
                                                            class="text-muted">
                                                            -
                                                        </span>

                                                    <?php } ?>

                                                </td>


                                                <!-- MSE -->
                                                <td>

                                                    <?php if (
                                                        $mse !== null &&
                                                        $mse !== ''
                                                    ) { ?>

                                                        <?= number_format(
                                                            (float)$mse,
                                                            2,
                                                            ',',
                                                            '.'
                                                        ); ?>

                                                    <?php } else { ?>

                                                        <span
                                                            class="text-muted">
                                                            -
                                                        </span>

                                                    <?php } ?>

                                                </td>


                                                <!-- MAPE -->
                                                <td>

                                                    <?php if (
                                                        $mape !== null &&
                                                        $mape !== ''
                                                    ) { ?>

                                                        <?= number_format(
                                                            (float)$mape,
                                                            2,
                                                            ',',
                                                            '.'
                                                        ); ?>%

                                                    <?php } else { ?>

                                                        <span
                                                            class="text-muted">
                                                            -
                                                        </span>

                                                    <?php } ?>

                                                </td>


                                                <!-- STATUS -->
                                                <td>

                                                    <?php if (
                                                        $is_prediksi
                                                    ) { ?>

                                                        <span
                                                            class="badge-mape mape-baik">
                                                            Prediksi
                                                        </span>

                                                    <?php } else { ?>

                                                        <?php

                                                        $mapeValue =
                                                            (float)$mape;

                                                        if (
                                                            $mapeValue <= 10
                                                        ) {

                                                            $status =
                                                                "Sangat Baik";

                                                            $class =
                                                                "mape-sangat-baik";
                                                        } elseif (
                                                            $mapeValue <= 20
                                                        ) {

                                                            $status =
                                                                "Baik";

                                                            $class =
                                                                "mape-baik";
                                                        } elseif (
                                                            $mapeValue <= 50
                                                        ) {

                                                            $status =
                                                                "Cukup";

                                                            $class =
                                                                "mape-cukup";
                                                        } else {

                                                            $status =
                                                                "Kurang";

                                                            $class =
                                                                "mape-kurang";
                                                        }

                                                        ?>

                                                        <span
                                                            class="badge-mape <?= $class; ?>">
                                                            <?= $status; ?>
                                                        </span>

                                                    <?php } ?>

                                                </td>


                                                <!-- TANGGAL PROSES -->
                                                <td>

                                                    <?php

                                                    if (
                                                        !empty($row['tanggal_proses'])
                                                    ) {

                                                        $timestamp =
                                                            strtotime(
                                                                $row['tanggal_proses']
                                                            );

                                                        if (
                                                            $timestamp !== false
                                                        ) {

                                                            echo date(
                                                                'd/m/Y H:i',
                                                                $timestamp
                                                            );
                                                        } else {

                                                            echo '-';
                                                        }
                                                    } else {

                                                        echo '-';
                                                    }

                                                    ?>

                                                </td>

                                            </tr>

                                        <?php } ?>

                                    </tbody>


                                    <!-- =================================================
                                     FOOTER
                                ================================================== -->
                                    <tfoot>

                                        <tr>

                                            <td colspan="5">
                                                Total Prediksi
                                            </td>

                                            <td>

                                                <?= number_format(
                                                    $total_prediksi,
                                                    2,
                                                    ',',
                                                    '.'
                                                ); ?>

                                                buket

                                            </td>

                                            <td colspan="5">

                                                <?php if (
                                                    $jumlah_prediksi > 0
                                                ) { ?>

                                                    <small class="text-muted">
                                                        <?= $jumlah_prediksi; ?>
                                                        periode prediksi
                                                    </small>

                                                <?php } ?>

                                            </td>

                                        </tr>

                                    </tfoot>

                                </table>

                            </div>


                        <?php } else { ?>


                            <!-- =================================================
                             DATA KOSONG
                        ================================================== -->
                            <div class="empty-data">

                                <i
                                    class="fa-solid fa-chart-line"></i>

                                <h5>
                                    Tidak ada data peramalan
                                </h5>

                                <p>
                                    Belum terdapat data peramalan
                                    pada periode yang dipilih.
                                </p>

                                <div class="mt-3">

                                    <a
                                        href="peramalan.php"
                                        class="btn btn-primary">

                                        <i
                                            class="fa-solid fa-chart-line me-1"></i>

                                        Proses Peramalan

                                    </a>

                                </div>

                            </div>


                        <?php } ?>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
     BOOTSTRAP JAVASCRIPT
====================================================== -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>