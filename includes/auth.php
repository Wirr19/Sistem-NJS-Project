<?php
session_start();

require_once __DIR__ . '/koneksi.php';

$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = $_POST['password'];

$query = mysqli_query($koneksi, "
SELECT *
FROM user
WHERE username='$username'
");

if (mysqli_num_rows($query) == 1) {

    $user = mysqli_fetch_assoc($query);

    if (password_verify($password, $user['password'])) {

        $_SESSION['login'] = true;

        $_SESSION['id_pengguna'] = $user['id_pengguna'];

        $_SESSION['username'] = $user['username'];

        $_SESSION['nama'] = $user['nama'];

        // SESSION ROLE
        $_SESSION['role'] = $user['role'];

        header("Location: ../index.php");
        exit();
    }
}

echo "
<script>

alert('Username atau Password salah!');

window.location='../login.php';

</script>";
