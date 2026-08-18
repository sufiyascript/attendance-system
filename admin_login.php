<?php
// admin_login.php
require '../config.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  // Hardcoded admin credentials (you can change these)
  $admin_user = "admin";
  $admin_pass = "admin123"; // simple password for demo

  if ($username === $admin_user && $password === $admin_pass) {
    $_SESSION['is_admin'] = true;
    header("Location: reports.php");
    exit;
  } else {
    $err = "Invalid admin credentials";
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Login</title>
  <link rel="stylesheet" href="./style.css">
</head>
<body>
  <div class="card">
    <h2>Admin Login</h2>
    <?php if ($err): ?><div class="error"><?=htmlspecialchars($err)?></div><?php endif; ?>
    <form method="post">
      <label>Username</label>
      <input type="text" name="username" required>
      <label>Password</label>
      <input type="password" name="password" required>
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
