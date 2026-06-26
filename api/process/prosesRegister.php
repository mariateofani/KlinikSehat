<?php

ob_start();

require_once __DIR__ . '/../service/koneksi.php';

$nama =
    $_POST['nama'] ?? '';

$email =
    $_POST['email'] ?? '';

$password =
    $_POST['password'] ?? '';

function flash(
    $name,
    $value
) {

    setcookie(
        $name,
        $value,
        [
            'expires' => time() + 5,
            'path' => '/',
            'secure' => true,
            'httponly' => false,
            'samesite' => 'None'
        ]
    );

}

if (
    empty($nama)
    ||
    empty($email)
    ||
    empty($password)
) {

    flash(
        "error",
        "Semua field wajib diisi!"
    );

    header(
        "Location: ../register.php"
    );

    exit;

}

$hash =
    password_hash(
        $password,
        PASSWORD_DEFAULT
    );

$role =
    "user";

$cek =
    $koneksi->prepare(
        "SELECT id_user FROM users WHERE email=?"
    );

$cek->bind_param(
    "s",
    $email
);

$cek->execute();

$cek->store_result();

if (
    $cek->num_rows
    > 0
) {

    flash(
        "error",
        "Email sudah terdaftar!"
    );

    header(
        "Location: ../register.php"
    );

    exit;

}

$stmt =
    $koneksi->prepare(
        "INSERT INTO users (nama,email,password,role)
VALUES (?,?,?,?)"
    );

$stmt->bind_param(
    "ssss",
    $nama,
    $email,
    $hash,
    $role
);

if (
    $stmt->execute()
) {

    flash(
        "success",
        "Register berhasil!"
    );

    header(
        "Location: ../login.php"
    );

} else {

    flash(
        "error",
        "Register gagal!"
    );

    header(
        "Location: ../register.php"
    );

}

exit;