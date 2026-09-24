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
   DATA PENGGUNA
========================================================= */

$id_pengguna = isset($_SESSION['id_pengguna'])
    ? (int) $_SESSION['id_pengguna']
    : 0;

if ($id_pengguna <= 0) {

    echo "<script>
        alert('Data pengguna tidak ditemukan.');
        window.location='login.php';
    </script>";

    exit;
}


/* =========================================================
   DATA ADMIN
   Mengikuti struktur penjualan.php / stok.php
========================================================= */

$nama_admin = $_SESSION['nama_petugas']
    ?? $_SESSION['nama']
    ?? 'Administrator';

$nama_admin = trim($nama_admin);

if ($nama_admin === '') {
    $nama_admin = 'Administrator';
}

$inisial = strtoupper(substr($nama_admin, 0, 1));


/* =========================================================
   VARIABEL
========================================================= */

$pesan = '';

$error = '';

$hasilProses = false;

$nilaiA = 0;

$nilaiB1 = 0;

$nilaiB2 = 0;

$nilaiMAD = 0;

$nilaiMSE = 0;

$nilaiMAPE = 0;

$hasilPrediksi = 0;

$periodePrediksi = '';

$biayaPromosiPrediksi = 0;

$jumlahPengunjungPrediksi = 0;


/* =========================================================
   PROSES PERAMALAN
========================================================= */

if (isset($_POST['proses'])) {

    $biayaPromosiPrediksi = isset(
        $_POST['biaya_promosi_prediksi']
    )
        ? (float) $_POST['biaya_promosi_prediksi']
        : 0;

    $jumlahPengunjungPrediksi = isset(
        $_POST['jumlah_pengunjung_prediksi']
    )
        ? (int) $_POST['jumlah_pengunjung_prediksi']
        : 0;


    /* =====================================================
       VALIDASI INPUT
    ===================================================== */

    if ($biayaPromosiPrediksi < 0) {

        $error = "Biaya promosi tidak boleh negatif.";
    } elseif ($jumlahPengunjungPrediksi < 0) {

        $error = "Jumlah pengunjung tidak boleh negatif.";
    } else {


        /* =================================================
           AMBIL DATA HISTORIS
        ================================================= */

        $sqlData = "
            SELECT
                periode,
                SUM(biaya_promosi) AS biaya_promosi,
                SUM(jumlah_pengunjung) AS jumlah_pengunjung,
                SUM(jumlah_penjualan) AS jumlah_penjualan
            FROM data_peramalan
            GROUP BY periode
            ORDER BY periode ASC
        ";

        $resultData = mysqli_query(
            $koneksi,
            $sqlData
        );


        if (!$resultData) {

            $error =
                "Gagal mengambil data peramalan: "
                . mysqli_error($koneksi);
        } else {

            $dataRegresi = [];


            while ($row = mysqli_fetch_assoc($resultData)) {

                $dataRegresi[] = [

                    'periode' =>
                    $row['periode'],

                    'x1' =>
                    (float) $row['biaya_promosi'],

                    'x2' =>
                    (float) $row['jumlah_pengunjung'],

                    'y' =>
                    (float) $row['jumlah_penjualan']

                ];
            }


            /* =================================================
               MINIMAL DATA
            ================================================= */

            if (count($dataRegresi) < 3) {

                $error =
                    "Data historis minimal 3 periode untuk melakukan regresi linier berganda.";
            } else {


                /* =================================================
                   JUMLAH DATA
                ================================================= */

                $n = count($dataRegresi);


                $sumX1 = 0;

                $sumX2 = 0;

                $sumY = 0;

                $sumX1X1 = 0;

                $sumX2X2 = 0;

                $sumX1X2 = 0;

                $sumX1Y = 0;

                $sumX2Y = 0;


                foreach ($dataRegresi as $data) {

                    $x1 = $data['x1'];

                    $x2 = $data['x2'];

                    $y = $data['y'];


                    $sumX1 += $x1;

                    $sumX2 += $x2;

                    $sumY += $y;

                    $sumX1X1 += $x1 * $x1;

                    $sumX2X2 += $x2 * $x2;

                    $sumX1X2 += $x1 * $x2;

                    $sumX1Y += $x1 * $y;

                    $sumX2Y += $x2 * $y;
                }


                /* =================================================
                   MATRIKS REGRESI
                ================================================= */

                $A = [

                    [
                        $n,
                        $sumX1,
                        $sumX2
                    ],

                    [
                        $sumX1,
                        $sumX1X1,
                        $sumX1X2
                    ],

                    [
                        $sumX2,
                        $sumX1X2,
                        $sumX2X2
                    ]

                ];


                $B = [

                    $sumY,

                    $sumX1Y,

                    $sumX2Y

                ];


                /* =================================================
                   DETERMINAN MATRIKS
                ================================================= */

                $det =
                    $A[0][0] *
                    (
                        ($A[1][1] * $A[2][2])
                        -
                        ($A[1][2] * $A[2][1])
                    )
                    -
                    $A[0][1] *
                    (
                        ($A[1][0] * $A[2][2])
                        -
                        ($A[1][2] * $A[2][0])
                    )
                    +
                    $A[0][2] *
                    (
                        ($A[1][0] * $A[2][1])
                        -
                        ($A[1][1] * $A[2][0])
                    );


                if (abs($det) < 0.000000001) {

                    $error =
                        "Perhitungan regresi tidak dapat dilakukan karena data tidak dapat membentuk matriks yang valid.";
                } else {


                    /* =================================================
                       CRAMER - NILAI A
                    ================================================= */

                    $Aa = [

                        [
                            $B[0],
                            $A[0][1],
                            $A[0][2]
                        ],

                        [
                            $B[1],
                            $A[1][1],
                            $A[1][2]
                        ],

                        [
                            $B[2],
                            $A[2][1],
                            $A[2][2]
                        ]

                    ];


                    $detAa =
                        $Aa[0][0] *
                        (
                            ($Aa[1][1] * $Aa[2][2])
                            -
                            ($Aa[1][2] * $Aa[2][1])
                        )
                        -
                        $Aa[0][1] *
                        (
                            ($Aa[1][0] * $Aa[2][2])
                            -
                            ($Aa[1][2] * $Aa[2][0])
                        )
                        +
                        $Aa[0][2] *
                        (
                            ($Aa[1][0] * $Aa[2][1])
                            -
                            ($Aa[1][1] * $Aa[2][0])
                        );


                    /* =================================================
                       CRAMER - NILAI B1
                    ================================================= */

                    $Ab1 = [

                        [
                            $A[0][0],
                            $B[0],
                            $A[0][2]
                        ],

                        [
                            $A[1][0],
                            $B[1],
                            $A[1][2]
                        ],

                        [
                            $A[2][0],
                            $B[2],
                            $A[2][2]
                        ]

                    ];


                    $detAb1 =
                        $Ab1[0][0] *
                        (
                            ($Ab1[1][1] * $Ab1[2][2])
                            -
                            ($Ab1[1][2] * $Ab1[2][1])
                        )
                        -
                        $Ab1[0][1] *
                        (
                            ($Ab1[1][0] * $Ab1[2][2])
                            -
                            ($Ab1[1][2] * $Ab1[2][0])
                        )
                        +
                        $Ab1[0][2] *
                        (
                            ($Ab1[1][0] * $Ab1[2][1])
                            -
                            ($Ab1[1][1] * $Ab1[2][0])
                        );


                    /* =================================================
                       CRAMER - NILAI B2
                    ================================================= */

                    $Ab2 = [

                        [
                            $A[0][0],
                            $A[0][1],
                            $B[0]
                        ],

                        [
                            $A[1][0],
                            $A[1][1],
                            $B[1]
                        ],

                        [
                            $A[2][0],
                            $A[2][1],
                            $B[2]
                        ]

                    ];


                    $detAb2 =
                        $Ab2[0][0] *
                        (
                            ($Ab2[1][1] * $Ab2[2][2])
                            -
                            ($Ab2[1][2] * $Ab2[2][1])
                        )
                        -
                        $Ab2[0][1] *
                        (
                            ($Ab2[1][0] * $Ab2[2][2])
                            -
                            ($Ab2[1][2] * $Ab2[2][0])
                        )
                        +
                        $Ab2[0][2] *
                        (
                            ($Ab2[1][0] * $Ab2[2][1])
                            -
                            ($Ab2[1][1] * $Ab2[2][0])
                        );


                    /* =================================================
                       KOEFISIEN
                    ================================================= */

                    $nilaiA =
                        $detAa / $det;

                    $nilaiB1 =
                        $detAb1 / $det;

                    $nilaiB2 =
                        $detAb2 / $det;


                    /* =================================================
                       HITUNG MAD MSE MAPE
                    ================================================= */

                    $totalMAD = 0;

                    $totalMSE = 0;

                    $totalAPE = 0;

                    $jumlahAPE = 0;


                    foreach ($dataRegresi as $data) {

                        $x1 = $data['x1'];

                        $x2 = $data['x2'];

                        $aktual = $data['y'];


                        $prediksiHistoris =
                            $nilaiA
                            +
                            ($nilaiB1 * $x1)
                            +
                            ($nilaiB2 * $x2);


                        $selisih =
                            $aktual - $prediksiHistoris;


                        $totalMAD += abs($selisih);

                        $totalMSE += pow($selisih, 2);


                        if ($aktual != 0) {

                            $totalAPE +=
                                abs(
                                    $selisih / $aktual
                                ) * 100;

                            $jumlahAPE++;
                        }
                    }


                    $nilaiMAD =
                        $totalMAD / $n;


                    $nilaiMSE =
                        $totalMSE / $n;


                    if ($jumlahAPE > 0) {

                        $nilaiMAPE =
                            $totalAPE / $jumlahAPE;
                    } else {

                        $nilaiMAPE = 0;
                    }


                    /* =================================================
                       PERIODE TERAKHIR
                    ================================================= */

                    $queryLatest = mysqli_query(
                        $koneksi,
                        "
                        SELECT MAX(periode) AS periode_terakhir
                        FROM data_peramalan
                        "
                    );


                    $latestData =
                        mysqli_fetch_assoc(
                            $queryLatest
                        );


                    $periodeTerakhir =
                        $latestData['periode_terakhir'];


                    $tanggalPeriode =
                        DateTime::createFromFormat(
                            'Y-m',
                            $periodeTerakhir
                        );


                    if ($tanggalPeriode) {

                        $tanggalPeriode->modify('+1 month');

                        $periodePrediksi =
                            $tanggalPeriode->format('Y-m');
                    } else {

                        $periodePrediksi =
                            date('Y-m');
                    }


                    /* =================================================
                       PREDIKSI
                    ================================================= */

                    $hasilPrediksi =
                        $nilaiA
                        +
                        ($nilaiB1 * $biayaPromosiPrediksi)
                        +
                        ($nilaiB2 * $jumlahPengunjungPrediksi);


                    if ($hasilPrediksi < 0) {

                        $hasilPrediksi = 0;
                    }


                    /* =================================================
                       SIMPAN HASIL
                    ================================================= */

                    mysqli_begin_transaction($koneksi);


                    try {


                        /* ---------------------------------------------
                           HAPUS HASIL LAMA USER
                        --------------------------------------------- */

                        $hapusLama = mysqli_query(
                            $koneksi,
                            "
                            DELETE FROM peramalan
                            WHERE id_pengguna='$id_pengguna'
                            "
                        );


                        if (!$hapusLama) {

                            throw new Exception(
                                mysqli_error($koneksi)
                            );
                        }


                        /* ---------------------------------------------
                           SIMPAN HASIL HISTORIS
                        --------------------------------------------- */

                        foreach ($dataRegresi as $data) {

                            $periode =
                                mysqli_real_escape_string(
                                    $koneksi,
                                    $data['periode']
                                );


                            $prediksiHistoris =
                                $nilaiA
                                +
                                ($nilaiB1 * $data['x1'])
                                +
                                ($nilaiB2 * $data['x2']);


                            if ($prediksiHistoris < 0) {

                                $prediksiHistoris = 0;
                            }


                            $x1Database =
                                (float) $data['x1'];

                            $x2Database =
                                (float) $data['x2'];


                            $sqlInsert = "
                                INSERT INTO peramalan
                                (
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
                                    mse
                                )
                                VALUES
                                (
                                    '$id_pengguna',
                                    '$periode',
                                    'Semua Buket',
                                    '$x1Database',
                                    '$x2Database',
                                    '$nilaiA',
                                    '$nilaiB1',
                                    '$nilaiB2',
                                    '0',
                                    '$prediksiHistoris',
                                    '$nilaiMAPE',
                                    '$nilaiMAD',
                                    '$nilaiMSE'
                                )
                            ";


                            $insert =
                                mysqli_query(
                                    $koneksi,
                                    $sqlInsert
                                );


                            if (!$insert) {

                                throw new Exception(
                                    mysqli_error($koneksi)
                                );
                            }
                        }


                        /* ---------------------------------------------
                           SIMPAN PREDIKSI MASA DEPAN
                        --------------------------------------------- */

                        $sqlFuture = "
                            INSERT INTO peramalan
                            (
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
                                mse
                            )
                            VALUES
                            (
                                '$id_pengguna',
                                '$periodePrediksi',
                                'Semua Buket',
                                '$biayaPromosiPrediksi',
                                '$jumlahPengunjungPrediksi',
                                '$nilaiA',
                                '$nilaiB1',
                                '$nilaiB2',
                                '0',
                                '$hasilPrediksi',
                                NULL,
                                NULL,
                                NULL
                            )
                        ";


                        $insertFuture =
                            mysqli_query(
                                $koneksi,
                                $sqlFuture
                            );


                        if (!$insertFuture) {

                            throw new Exception(
                                mysqli_error($koneksi)
                            );
                        }


                        mysqli_commit($koneksi);


                        $hasilProses = true;

                        $pesan =
                            "Peramalan berhasil diproses dan disimpan.";
                    } catch (Exception $e) {

                        mysqli_rollback($koneksi);

                        $error =
                            "Gagal menyimpan hasil peramalan: "
                            . $e->getMessage();
                    }
                }
            }
        }
    }
}


/* =========================================================
   DATA GRAFIK
========================================================= */

$labelsGrafik = [];

$aktualGrafik = [];

$prediksiGrafik = [];


$queryGrafik = mysqli_query(
    $koneksi,
    "
    SELECT
        periode,
        hasil_prediksi
    FROM peramalan
    WHERE id_pengguna='$id_pengguna'
    ORDER BY periode ASC
    "
);


if ($queryGrafik) {

    while (
        $rowGrafik =
        mysqli_fetch_assoc($queryGrafik)
    ) {

        $periodeGrafik =
            $rowGrafik['periode'];


        $labelsGrafik[] =
            $periodeGrafik;


        $prediksiGrafik[] =
            round(
                (float)
                $rowGrafik['hasil_prediksi'],
                2
            );


        /* ---------------------------------------------
           DATA AKTUAL
        --------------------------------------------- */

        $periodeAman =
            mysqli_real_escape_string(
                $koneksi,
                $periodeGrafik
            );


        $queryAktual = mysqli_query(
            $koneksi,
            "
            SELECT
                SUM(jumlah_penjualan) AS aktual
            FROM data_peramalan
            WHERE periode='$periodeAman'
            "
        );


        if ($queryAktual) {

            $rowAktual =
                mysqli_fetch_assoc(
                    $queryAktual
                );


            if (
                $rowAktual &&
                $rowAktual['aktual'] !== null
            ) {

                $aktualGrafik[] =
                    (float)
                    $rowAktual['aktual'];
            } else {

                $aktualGrafik[] = null;
            }
        } else {

            $aktualGrafik[] = null;
        }
    }
}


/* =========================================================
   FORMAT PERIODE
========================================================= */

$periodeTampilan =
    $periodePrediksi;


if ($periodeTampilan !== '') {

    $tanggalTampil =
        DateTime::createFromFormat(
            'Y-m',
            $periodeTampilan
        );


    if ($tanggalTampil) {

        $bulanIndonesia = [

            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'

        ];


        $periodeTampilan =
            $bulanIndonesia[(int)
                $tanggalTampil->format('n')]
            . ' '
            .
            $tanggalTampil->format('Y');
    }
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
        Peramalan | Sistem Buket
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">


    <!-- Font Awesome -->

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet">


    <!-- Poppins -->

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">


    <!-- Sidebar -->

    <link
        rel="stylesheet"
        href="assets/css/sidebar.css">


    <!-- CSS Peramalan -->

    <link
        rel="stylesheet"
        href="assets/css/peramalan.css">

</head>


<body>


    <div class="peramalan-layout">


        <!-- =====================================================
         SIDEBAR
    ====================================================== -->

        <?php include 'sidebar.php'; ?>


        <!-- =====================================================
         HALAMAN UTAMA
    ====================================================== -->

        <main class="peramalan-page">


            <!-- =================================================
             HEADER
        ================================================== -->

            <div class="peramalan-header">


                <!-- JUDUL -->

                <div class="peramalan-title">

                    <h3>
                        Peramalan
                    </h3>

                    <p>
                        Analisis dan prediksi penjualan
                    </p>

                </div>


                <!-- =================================================
                 USER
            ================================================== -->

                <div class="user-dropdown">


                    <button
                        class="user-dropdown-btn"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">

                        <!-- Avatar -->

                        <div class="avatar-circle">

                            <?= htmlspecialchars($inisial); ?>

                        </div>


                        <!-- Nama -->

                        <div class="user-info">

                            <div class="name">

                                <?= htmlspecialchars($nama_admin); ?>

                            </div>

                            <div class="role">

                                Admin

                            </div>

                        </div>


                        <!-- Chevron -->

                        <i class="fa-solid fa-chevron-down"></i>

                    </button>


                    <!-- Dropdown -->

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

                                <i
                                    class="fa-solid fa-right-from-bracket me-2"></i>

                                Logout

                            </a>

                        </li>


                    </ul>

                </div>


            </div>


            <!-- =================================================
             ALERT SUKSES
        ================================================== -->

            <?php if ($pesan !== '') { ?>

                <div
                    class="alert alert-success peramalan-alert">

                    <i
                        class="fa-solid fa-circle-check me-2"></i>

                    <?= htmlspecialchars($pesan); ?>

                </div>

            <?php } ?>


            <!-- =================================================
             ALERT ERROR
        ================================================== -->

            <?php if ($error !== '') { ?>

                <div
                    class="alert alert-danger peramalan-alert">

                    <i
                        class="fa-solid fa-circle-exclamation me-2"></i>

                    <?= htmlspecialchars($error); ?>

                </div>

            <?php } ?>


            <!-- =================================================
             CARD PROSES
        ================================================== -->

            <div class="peramalan-card">


                <div class="peramalan-card-header">

                    <h4>
                        Proses Peramalan Penjualan
                    </h4>

                    <p>
                        Masukkan nilai variabel untuk melakukan prediksi penjualan periode berikutnya.
                    </p>

                </div>


                <form
                    method="POST"
                    action="peramalan.php">


                    <div class="row g-4">


                        <!-- BIAYA PROMOSI -->

                        <div class="col-md-6">

                            <label
                                for="biaya_promosi_prediksi"
                                class="peramalan-label">

                                Biaya Promosi

                            </label>


                            <div
                                class="peramalan-input-wrapper">

                                <span class="peramalan-prefix">

                                    Rp

                                </span>


                                <input
                                    type="number"
                                    name="biaya_promosi_prediksi"
                                    id="biaya_promosi_prediksi"
                                    class="peramalan-input"
                                    min="0"
                                    step="0.01"
                                    value="<?= htmlspecialchars($biayaPromosiPrediksi); ?>"
                                    required>

                            </div>


                            <small class="peramalan-help">

                                Nilai biaya promosi yang diperkirakan pada periode berikutnya.

                            </small>

                        </div>


                        <!-- JUMLAH PENGUNJUNG -->

                        <div class="col-md-6">

                            <label
                                for="jumlah_pengunjung_prediksi"
                                class="peramalan-label">

                                Jumlah Pengunjung

                            </label>


                            <div
                                class="peramalan-input-wrapper">

                                <i
                                    class="fa-solid fa-users peramalan-input-icon"></i>


                                <input
                                    type="number"
                                    name="jumlah_pengunjung_prediksi"
                                    id="jumlah_pengunjung_prediksi"
                                    class="peramalan-input"
                                    min="0"
                                    value="<?= htmlspecialchars($jumlahPengunjungPrediksi); ?>"
                                    required>

                            </div>


                            <small class="peramalan-help">

                                Perkiraan jumlah pengunjung pada periode berikutnya.

                            </small>

                        </div>


                    </div>


                    <!-- =================================================
                     METODE
                ================================================== -->

                    <div class="peramalan-method">


                        <div class="peramalan-method-icon">

                            <i class="fa-solid fa-chart-line"></i>

                        </div>


                        <div>

                            <div class="peramalan-method-title">

                                Metode Peramalan

                            </div>

                            <div class="peramalan-method-text">

                                Regresi Linier Berganda

                            </div>

                            <small>

                                Y = a + b1X1 + b2X2

                            </small>

                        </div>


                    </div>


                    <!-- =================================================
                     BUTTON
                ================================================== -->

                    <div class="peramalan-form-footer">

                        <button
                            type="submit"
                            name="proses"
                            class="peramalan-btn">

                            <i class="fa-solid fa-play"></i>

                            Jalankan

                        </button>

                    </div>


                </form>


            </div>


            <!-- =================================================
             HASIL
        ================================================== -->

            <?php if ($hasilProses) { ?>


                <div class="peramalan-summary">


                    <!-- PERIODE -->

                    <div class="peramalan-summary-card">

                        <div class="summary-icon">

                            <i
                                class="fa-regular fa-calendar"></i>

                        </div>


                        <div>

                            <span>
                                Periode Prediksi
                            </span>

                            <strong>

                                <?= htmlspecialchars(
                                    $periodeTampilan
                                ); ?>

                            </strong>

                        </div>

                    </div>


                    <!-- PREDIKSI -->

                    <div class="peramalan-summary-card">

                        <div class="summary-icon">

                            <i
                                class="fa-solid fa-bullseye"></i>

                        </div>


                        <div>

                            <span>
                                Hasil Prediksi
                            </span>

                            <strong>

                                <?= number_format(
                                    $hasilPrediksi,
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </strong>

                        </div>

                    </div>


                    <!-- MAPE -->

                    <div class="peramalan-summary-card">

                        <div class="summary-icon">

                            <i
                                class="fa-solid fa-percent"></i>

                        </div>


                        <div>

                            <span>
                                MAPE
                            </span>

                            <strong>

                                <?= number_format(
                                    $nilaiMAPE,
                                    2,
                                    ',',
                                    '.'
                                ); ?>%

                            </strong>

                        </div>

                    </div>


                </div>


                <!-- =================================================
                 KOEFISIEN
            ================================================== -->

                <div class="peramalan-coefficient">


                    <div class="coefficient-title">

                        <i
                            class="fa-solid fa-calculator"></i>

                        Koefisien Regresi

                    </div>


                    <div class="coefficient-items">


                        <div>

                            <span>
                                Nilai a
                            </span>

                            <strong>

                                <?= number_format(
                                    $nilaiA,
                                    4,
                                    ',',
                                    '.'
                                ); ?>

                            </strong>

                        </div>


                        <div>

                            <span>
                                Nilai b1
                            </span>

                            <strong>

                                <?= number_format(
                                    $nilaiB1,
                                    4,
                                    ',',
                                    '.'
                                ); ?>

                            </strong>

                        </div>


                        <div>

                            <span>
                                Nilai b2
                            </span>

                            <strong>

                                <?= number_format(
                                    $nilaiB2,
                                    4,
                                    ',',
                                    '.'
                                ); ?>

                            </strong>

                        </div>


                        <div>

                            <span>
                                MAD
                            </span>

                            <strong>

                                <?= number_format(
                                    $nilaiMAD,
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </strong>

                        </div>


                        <div>

                            <span>
                                MSE
                            </span>

                            <strong>

                                <?= number_format(
                                    $nilaiMSE,
                                    2,
                                    ',',
                                    '.'
                                ); ?>

                            </strong>

                        </div>


                    </div>


                </div>


            <?php } ?>


            <!-- =================================================
             GRAFIK
        ================================================== -->

            <div class="peramalan-card grafik-card">


                <div class="peramalan-card-header">

                    <h4>
                        Grafik Hasil Peramalan
                    </h4>

                    <p>
                        Perbandingan penjualan aktual dan hasil prediksi.
                    </p>

                </div>


                <div class="chart-container">

                    <canvas id="forecastChart"></canvas>

                </div>


            </div>


        </main>

    </div>


    <!-- =========================================================
     BOOTSTRAP JS
========================================================= -->

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    <!-- =========================================================
     CHART JS
========================================================= -->

    <script
        src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <script>
        const labelsGrafik =
            <?= json_encode($labelsGrafik); ?>;

        const aktualGrafik =
            <?= json_encode($aktualGrafik); ?>;

        const prediksiGrafik =
            <?= json_encode($prediksiGrafik); ?>;


        const chartElement =
            document.getElementById('forecastChart');


        if (chartElement) {

            new Chart(
                chartElement, {

                    type: 'line',

                    data: {

                        labels: labelsGrafik,

                        datasets: [

                            {

                                label: 'Penjualan Aktual',

                                data: aktualGrafik,

                                tension: 0.35,

                                borderWidth: 2,

                                pointRadius: 4,

                                spanGaps: true

                            },


                            {

                                label: 'Hasil Prediksi',

                                data: prediksiGrafik,

                                tension: 0.35,

                                borderWidth: 2,

                                pointRadius: 4

                            }

                        ]

                    },


                    options: {

                        responsive: true,

                        maintainAspectRatio: false,


                        interaction: {

                            mode: 'index',

                            intersect: false

                        },


                        plugins: {

                            legend: {

                                display: true,

                                position: 'top',

                                labels: {

                                    usePointStyle: true,

                                    padding: 20,

                                    font: {

                                        family: 'Poppins',

                                        size: 11

                                    }

                                }

                            }

                        },


                        scales: {

                            x: {

                                grid: {

                                    display: false

                                },

                                ticks: {

                                    font: {

                                        family: 'Poppins',

                                        size: 10

                                    },

                                    color: '#8fa2c0'

                                }

                            },


                            y: {

                                beginAtZero: true,

                                grid: {

                                    color: '#edf1f6'

                                },

                                ticks: {

                                    font: {

                                        family: 'Poppins',

                                        size: 10

                                    },

                                    color: '#8fa2c0'

                                }

                            }

                        }

                    }

                }

            );

        }
    </script>


</body>

</html>