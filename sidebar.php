<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar desktop-sidebar">

    <!-- Logo -->
    <div class="sidebar-header">

        <div class="brand-logo">
            <img src="./assets/gambar/logo.jpg.jpeg" alt="Logo">
        </div>

        <div class="brand-text">
            <h5>NJS PROJECT</h5>
            <span>Management System</span>
        </div>

    </div>

    <!-- Menu -->
    <div class="menu-header">
        MAIN MENU
    </div>

    <a href="index.php"
        class="nav-link-custom <?= ($current_page == 'index.php') ? 'active' : ''; ?>">

        <i class="fa-solid fa-house"></i>

        <span>Dashboard</span>

    </a>

    <a href="penjualan.php"
        class="nav-link-custom <?= ($current_page == 'penjualan.php') ? 'active' : ''; ?>">

        <i class="fa-solid fa-chart-column"></i>

        <span>Data Penjualan</span>

    </a>

    <a href="stok.php"
        class="nav-link-custom <?= ($current_page == 'stok.php') ? 'active' : ''; ?>">

        <i class="fa-solid fa-boxes-stacked"></i>

        <span>Stok</span>

    </a>

    <a href="peramalan.php"
        class="nav-link-custom <?= ($current_page == 'peramalan.php') ? 'active' : ''; ?>">

        <i class="fa-solid fa-chart-line"></i>

        <span>Peramalan</span>

    </a>

    <a href="laporan.php"
        class="nav-link-custom <?= ($current_page == 'laporan.php') ? 'active' : ''; ?>">

        <i class="fa-solid fa-file-lines"></i>

        <span>Laporan</span>

    </a>

    <div class="sidebar-footer">

        <a href="logout.php" class="nav-link-custom">

            <i class="fa-solid fa-right-from-bracket"></i>

            <span>Logout</span>

        </a>

    </div>

</div>