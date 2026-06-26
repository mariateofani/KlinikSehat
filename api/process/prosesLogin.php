<?php

ob_start();
session_start();

require_once __DIR__ . '/../service/koneksi.php';

/* ambil data form */
$email =
    trim(
        $_POST['email'] ?? ''
    );

$password =
    $_POST['password'] ?? '';

function flash($name, $value)
{
    setcookie(
        $name,
        $value,
        [
            'expires' => time() + 5,
            'path' => '/',
            'secure' => false, // localhost
            'httponly' => false,
            'samesite' => 'Lax'
        ]
    );
}

/* validasi */
if (
    empty($email)
    ||
    empty($password)
) {

    flash(
        "error",
        "Email dan password wajib diisi!"
    );

    header(
        "Location: ../login.php"
    );

    exit;
}

/* cari user */
$stmt =
    mysqli_prepare(
        $koneksi,
        "SELECT * FROM users WHERE email=?"
    );

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute(
    $stmt
);

$result =
    mysqli_stmt_get_result(
        $stmt
    );

$user =
    mysqli_fetch_assoc(
        $result
    );

/* cek login */
if (
    $user
    &&
    password_verify(
        $password,
        $user['password']
    )
) {

    // tetap pakai session
    $_SESSION['email'] =
        $user['email'];

    $_SESSION['nama'] =
        $user['nama'];

    $_SESSION['role'] =
        $user['role'];

    if (
        $user['role']
        === 'admin'
    ) {

        header(
            "Location: ../dashboard_admin.php"
        );

    } else {

        header(
            "Location: ../dashboard.php"
        );

    }

    exit;

}

flash(
    "error",
    $user
        ? "Password salah!"
        : "Email tidak ditemukan!"
);

header(
    "Location: ../login.php"
);

exit;