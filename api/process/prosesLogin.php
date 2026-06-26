<?php
ob_start();
require_once __DIR__ . '/../service/koneksi.php';

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM users WHERE email=?"
);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

$result =
    mysqli_stmt_get_result(
        $stmt
    );

$user =
    mysqli_fetch_assoc(
        $result
    );

function flash($name, $value)
{

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

function loginCookie($name, $value)
{

    setcookie(
        $name,
        $value,
        [
            'expires' => time() + 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None'
        ]
    );

}

if ($user) {

    if (
        password_verify(
            $password,
            $user['password']
        )
    ) {

        loginCookie(
            "email",
            $user['email']
        );

        loginCookie(
            "nama",
            $user['nama']
        );

        loginCookie(
            "role",
            $user['role']
        );

        if (
            $user['role']
            === "admin"
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

    } else {

        flash(
            "error",
            "Password salah!"
        );

        header(
            "Location: ../login.php"
        );

        exit;

    }

} else {

    flash(
        "error",
        "Email tidak ditemukan!"
    );

    header(
        "Location: ../login.php"
    );

    exit;

}
?>