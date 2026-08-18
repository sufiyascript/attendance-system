<?php
// login.php
require 'config.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($username && $password) {
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && $password === $user['password']) {
      // login success
      $_SESSION['teacher_id'] = $user['id'];
      $_SESSION['teacher_name'] = $user['name'];
      header("Location: dashboard.php");
      exit;
    } else {
      $err = "Invalid credentials";
    }
  } else {
    $err = "Please enter username and password";
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login - Attendance System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="card">
    <h2>Teacher Login</h2>
    <?php if ($err): ?><div class="error"><?=htmlspecialchars($err)?></div><?php endif; ?>
    <form method="post">
      <label>Username</label>
      <input name="username" required maxlength="50">
      <label>Password</label>
      <input type="password" name="password" required maxlength="50">
      <button type="submit">Login</button>
    </form>
    <p>Demo: admin / 123</p>
  </div>
</body>
</html>
