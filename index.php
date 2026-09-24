<?php
session_start();
require_once __DIR__ . '/includes/koneksi.php';

/* ======================================================
   CEK LOGIN
====================================================== */

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit();
}

/* ======================================================
   TOTAL JUMLAH PENJUALAN
   Menghitung total unit/buket yang terjual
====================================================== */

$qTotal = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(jumlah), 0) AS total
    FROM penjualan
");

$total_penjualan = mysqli_fetch_assoc($qTotal);

/* ======================================================
   PERIODE TERAKHIR HASIL PERAMALAN
====================================================== */

$qPeriode = mysqli_query($koneksi, "
    SELECT periode
    FROM peramalan
    ORDER BY id_peramalan DESC
    LIMIT 1
");

if ($qPeriode && mysqli_num_rows($qPeriode) > 0) {

    $periode = mysqli_fetch_assoc($qPeriode);
    $periode_terakhir = $periode['periode'];
} else {

    $periode_terakhir = "-";
}

/* ======================================================
   MAPE TERBARU
====================================================== */

$qMape = mysqli_query($koneksi, "
    SELECT nilai_mape
    FROM peramalan
    WHERE nilai_mape IS NOT NULL
    ORDER BY id_peramalan DESC
    LIMIT 1
");

if ($qMape && mysqli_num_rows($qMape) > 0) {

    $mape = mysqli_fetch_assoc($qMape);

    $rata_mape = round((float)$mape['nilai_mape'], 2);
} else {

    $rata_mape = 0;
}

/* ======================================================
   STATUS AKURASI
====================================================== */

$status = "Belum Ada Data";

if ($rata_mape > 0) {

    if ($rata_mape <= 10) {

        $status = "Sangat Baik";
    } elseif ($rata_mape <= 20) {

        $status = "Baik";
    } elseif ($rata_mape <= 50) {

        $status = "Cukup";
    } else {

        $status = "Kurang";
    }
}

/* ======================================================
   DATA PENJUALAN TERBARU
====================================================== */

$data_penjualan = mysqli_query($koneksi, "
    SELECT *
    FROM penjualan
    ORDER BY tanggal DESC
    LIMIT 5
");

/* ======================================================
   DATA GRAFIK PENJUALAN
   Berdasarkan jumlah penjualan aktual setiap bulan
====================================================== */

$grafik = mysqli_query($koneksi, "
    SELECT
        DATE_FORMAT(tanggal, '%b %Y') AS bulan,
        SUM(jumlah) AS total
    FROM penjualan
    GROUP BY YEAR(tanggal), MONTH(tanggal)
    ORDER BY YEAR(tanggal), MONTH(tanggal)
");

$label = [];
$data = [];

if ($grafik) {

    while ($row = mysqli_fetch_assoc($grafik)) {

        $label[] = $row['bulan'];
        $data[] = (int)$row['total'];
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | NJS Project</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Bootstrap Icons -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Poppins -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Sidebar -->
    <link rel="stylesheet" href="assets/css/sidebar.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }

        /* ==========================================
           HEADER
        ========================================== */

        .dashboard-page {
            min-height: 100vh;
        }

        .page-header {
            min-height: 70px;
        }

        .page-header h3 {
            color: #111827;
            font-size: 24px;
        }

        /* ==========================================
           USER DROPDOWN
        ========================================== */

        .user-dropdown-btn {
            display: flex;
            align-items: center;
            gap: 14px;
            background: #fff;
            border: 1px solid #edf0f7;
            border-radius: 40px;
            padding: 8px 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .06);
            transition: .3s;
            cursor: pointer;
        }

        .user-dropdown-btn:hover {
            box-shadow: 0 10px 30px rgba(0, 0, 0, .09);
        }

        .user-dropdown-btn::after {
            display: none;
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #5b5ce9;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 15px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .user-info {
            text-align: left;
            line-height: 1.2;
        }

        .user-info .name {
            font-size: 15px;
            font-weight: 600;
            color: #1f2937;
        }

        .user-info .role {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 3px;
        }

        .user-dropdown-btn>i {
            color: #64748b;
            font-size: 14px;
        }

        .dropdown-menu {
            border: none !important;
            border-radius: 14px !important;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .12);
            padding: 8px 0;
            overflow: hidden;
        }

        .dropdown-item-text {
            padding: 10px 16px;
        }

        .dropdown-item {
            padding: 10px 16px;
            color: #374151;
            font-size: 14px;
        }

        .dropdown-item:hover {
            background: #eef2ff;
        }

        .dropdown-item.text-danger:hover {
            background: #fef2f2;
        }

        .dropdown-divider {
            margin: 6px 0;
        }

        /* ==========================================
           DASHBOARD CARD
        ========================================== */

        .dashboard-card {
            background: #fff;
            border: 1px solid #edf0f7;
            border-radius: 18px;
            padding: 24px;
            min-height: 145px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .04);
            transition: .3s;
        }

        .dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
        }

        .dashboard-card small {
            color: #94a3b8;
            font-size: 13px;
            font-weight: 500;
        }

        .dashboard-card h2 {
            font-size: 28px;
            font-weight: 700;
            margin-top: 10px;
            margin-bottom: 0;
            color: #111827;
        }

        .dashboard-card h5 {
            color: #111827;
            margin-bottom: 0;
        }

        .icon-box {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* ==========================================
           CHART
        ========================================== */

        .chart-box {
            background: #fff;
            border: 1px solid #edf0f7;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .04);
        }

        .chart-wrapper {
            position: relative;
            height: 330px;
            width: 100%;
        }

        /* ==========================================
           TABLE
        ========================================== */

        .table-box {
            background: #fff;
            border: 1px solid #edf0f7;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, .04);
        }

        .table-box table {
            margin-bottom: 0;
        }

        .table-box thead th {
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            border-bottom: 1px solid #edf0f7;
            padding: 14px 12px;
        }

        .table-box tbody td {
            color: #374151;
            font-size: 14px;
            padding: 15px 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-box tbody tr:last-child td {
            border-bottom: none;
        }

        /* ==========================================
           RESPONSIVE
        ========================================== */

        @media (max-width: 768px) {

            .container-fluid {
                padding-left: 18px !important;
                padding-right: 18px !important;
            }

            .page-header {
                align-items: flex-start !important;
                gap: 20px;
            }

            .page-header h3 {
                font-size: 21px;
            }

            .page-header p {
                font-size: 12px;
            }

            .user-info {
                display: none;
            }

            .user-dropdown-btn {
                padding: 7px 10px;
            }

            .dashboard-card {
                min-height: 125px;
            }

            .chart-wrapper {
                height: 280px;
            }

        }
    </style>

</head>

<body>

    <div class="d-flex dashboard-page">

        <?php include 'sidebar.php'; ?>

        <div class="flex-grow-1">

            <div class="container-fluid px-4 py-4">

                <!-- ==========================================
                 HEADER
            ========================================== -->

                <div class="page-header d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h3 class="fw-bold mb-1">
                            Dashboard
                        </h3>

                        <p class="text-muted mb-0">
                            Selamat datang di NJS Project Management System
                        </p>

                    </div>

                    <!-- USER -->

                    <div class="dropdown">

                        <button
                            class="user-dropdown-btn"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">

                            <div class="avatar-circle">

                                <?= strtoupper(
                                    substr(
                                        $_SESSION['nama_petugas'] ?? 'A',
                                        0,
                                        1
                                    )
                                ); ?>

                            </div>

                            <div class="user-info">

                                <div class="name">

                                    <?= htmlspecialchars(
                                        $_SESSION['nama_petugas'] ?? 'Administrator'
                                    ); ?>

                                </div>

                                <div class="role">
                                    Admin
                                </div>

                            </div>

                            <i class="fa-solid fa-chevron-down"></i>

                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>

                                <div class="dropdown-item-text">

                                    <strong>
                                        <?= htmlspecialchars(
                                            $_SESSION['nama_petugas'] ?? 'Administrator'
                                        ); ?>
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


                <!-- ==========================================
                 CARD STATISTIK
            ========================================== -->

                <div class="row g-4">

                    <!-- TOTAL PENJUALAN -->

                    <div class="col-xl-3 col-lg-6 col-md-6">

                        <div class="dashboard-card">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small>
                                        Total Penjualan
                                    </small>

                                    <h2>
                                        <?= number_format(
                                            (int)$total_penjualan['total'],
                                            0,
                                            ',',
                                            '.'
                                        ); ?>
                                    </h2>

                                </div>

                                <div class="icon-box bg-primary-subtle">

                                    <i class="fa-solid fa-cart-shopping text-primary"></i>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- PERIODE TERAKHIR -->

                    <div class="col-xl-3 col-lg-6 col-md-6">

                        <div class="dashboard-card">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small>
                                        Periode Terakhir
                                    </small>

                                    <h5 class="fw-bold mt-3">

                                        <?= htmlspecialchars($periode_terakhir); ?>

                                    </h5>

                                </div>

                                <div class="icon-box bg-success-subtle">

                                    <i class="fa-solid fa-calendar-days text-success"></i>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- MAPE -->

                    <div class="col-xl-3 col-lg-6 col-md-6">

                        <div class="dashboard-card">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small>
                                        Rata-rata MAPE
                                    </small>

                                    <h2>

                                        <?= $rata_mape; ?>%

                                    </h2>

                                </div>

                                <div class="icon-box bg-warning-subtle">

                                    <i class="fa-solid fa-chart-line text-warning"></i>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- STATUS AKURASI -->

                    <div class="col-xl-3 col-lg-6 col-md-6">

                        <div class="dashboard-card">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small>
                                        Status Akurasi
                                    </small>

                                    <h5 class="fw-bold mt-3">

                                        <?= htmlspecialchars($status); ?>

                                    </h5>

                                </div>

                                <div class="icon-box bg-danger-subtle">

                                    <i class="fa-solid fa-circle-check text-danger"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ==========================================
                 GRAFIK
            ========================================== -->

                <div class="chart-box mt-4">

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div>

                            <h5 class="fw-bold mb-1">
                                Grafik Tren Penjualan
                            </h5>

                            <small class="text-muted">
                                Data penjualan aktual setiap bulan
                            </small>

                        </div>

                    </div>

                    <div class="chart-wrapper">

                        <canvas id="grafikPenjualan"></canvas>

                    </div>

                </div>


                <!-- ==========================================
                 DATA PENJUALAN TERBARU
            ========================================== -->

                <div class="table-box mt-4">

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div>

                            <h5 class="fw-bold mb-1">
                                Data Penjualan Terbaru
                            </h5>

                            <small class="text-muted">
                                Menampilkan 5 data penjualan terakhir
                            </small>

                        </div>

                    </div>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead>

                                <tr>

                                    <th>
                                        Tanggal
                                    </th>

                                    <th>
                                        Nama Buket
                                    </th>

                                    <th class="text-center">
                                        Jumlah
                                    </th>

                                </tr>

                            </thead>

                            <tbody>

                                <?php if ($data_penjualan && mysqli_num_rows($data_penjualan) > 0) { ?>

                                    <?php while ($row = mysqli_fetch_assoc($data_penjualan)) { ?>

                                        <tr>

                                            <td>
                                                <?= date(
                                                    'd M Y',
                                                    strtotime($row['tanggal'])
                                                ); ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $row['nama_buket']
                                                ); ?>
                                            </td>

                                            <td class="text-center">

                                                <span class="badge bg-primary rounded-pill px-3 py-2">

                                                    <?= (int)$row['jumlah']; ?>

                                                </span>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                <?php } else { ?>

                                    <tr>

                                        <td
                                            colspan="3"
                                            class="text-center text-muted py-4">

                                            Belum ada data penjualan.

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


    <!-- Bootstrap JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <script>
        const ctx = document.getElementById('grafikPenjualan');

        new Chart(ctx, {

            type: 'line',

            data: {

                labels: <?= json_encode($label); ?>,

                datasets: [{

                    label: 'Jumlah Penjualan',

                    data: <?= json_encode($data); ?>,

                    borderColor: '#4f46e5',

                    backgroundColor: 'rgba(79,70,229,.08)',

                    borderWidth: 3,

                    fill: true,

                    tension: .4,

                    pointRadius: 4,

                    pointHoverRadius: 6

                }]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                plugins: {

                    legend: {

                        display: true,

                        position: 'top',

                        labels: {

                            font: {
                                family: 'Poppins'
                            }

                        }

                    }

                },

                scales: {

                    y: {

                        beginAtZero: true,

                        ticks: {

                            precision: 0

                        }

                    },

                    x: {

                        grid: {

                            display: false

                        }

                    }

                }

            }

        });
    </script>

</body>

</html>