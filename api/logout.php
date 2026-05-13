<?php
setcookie("email", "", time() - 3600, "/");
setcookie("nama", "", time() - 3600, "/");
setcookie("role", "", time() - 3600, "/");

header("Location: ../index.html");
exit;
?>