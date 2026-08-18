<?php
require '../config.php';
unset($_SESSION['is_admin']);
session_destroy();
header("Location: admin_login.php");
exit;
