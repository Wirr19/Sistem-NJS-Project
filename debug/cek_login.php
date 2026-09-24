<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jika belum login
if (!isset($_SESSION['login'])) {

    header("Location: login.php");
    exit();
}

// Jika session login bukan true
if ($_SESSION['login'] != true) {

    header("Location: login.php");
    exit();
}
