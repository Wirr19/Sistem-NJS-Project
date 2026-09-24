<?php

// Password yang ingin dimasukkan ke database
$password = "owner123";

// Membuat hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h3>Generate Hash Password</h3>";

echo "<b>Password :</b> " . $password . "<br><br>";

echo "<b>Hash :</b><br>";

echo $hash;
