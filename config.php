<?php
// config.php
session_start();

$db_host = '127.0.0.1';
$db_name = 'attendance_system';
$db_user = 'root';
$db_pass = 'appuser'; // set your mysql root password if any

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
  $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (Exception $e) {
  die('Database connection failed: ' . $e->getMessage());
}
?>
