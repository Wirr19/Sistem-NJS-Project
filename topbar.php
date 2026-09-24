<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!-- TOPBAR -->
<div class="topbar">

    <!-- Bagian kiri sengaja kosong -->
    <div class="topbar-left"></div>

    <!-- Administrator -->
    <div class="dropdown">

        <button
            class="user-dropdown-btn"
            data-bs-toggle="dropdown"
            type="button">

            <div class="avatar-circle">
                <?= strtoupper(substr($_SESSION['nama_petugas'] ?? 'A', 0, 1)); ?>
            </div>

            <div class="user-info">

                <div class="name">
                    <?= htmlspecialchars($_SESSION['nama_petugas'] ?? 'Administrator'); ?>
                </div>

                <div class="role">
                    Admin
                </div>

            </div>

            <i class="fa-solid fa-chevron-down"></i>

        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-4">

            <li>

                <div class="dropdown-item-text">

                    <strong>
                        <?= isset($_SESSION['nama_petugas'])
                            ? htmlspecialchars($_SESSION['nama_petugas'])
                            : 'Administrator'; ?>
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

                <a class="dropdown-item" href="#">

                    <i class="fa-solid fa-user me-2"></i>

                    Profil

                </a>

            </li>

            <li>

                <a class="dropdown-item" href="#">

                    <i class="fa-solid fa-gear me-2"></i>

                    Pengaturan

                </a>

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