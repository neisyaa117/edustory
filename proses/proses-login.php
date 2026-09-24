<?php

session_start();

require_once "../config/koneksi.php";

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $koneksi->prepare(
    "SELECT id, nama, password FROM users WHERE email = ?"
);

$stmt->bind_param("s", $email);
$stmt->execute();

$hasil = $stmt->get_result();

if ($hasil->num_rows === 0) {

    $_SESSION['pesan'] = "Email atau password salah.";
    header("Location: ../login.php");
    exit;
}

$user = $hasil->fetch_assoc();

if (!password_verify($password, $user['password'])) {

    $_SESSION['pesan'] = "Email atau password salah.";
    header("Location: ../login.php");
    exit;
}

session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['nama'] = $user['nama'];

header("Location: ../dashboard.php");
exit;