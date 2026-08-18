<?php
// process_attendance.php — show Present / Absent counts only (stores subject in DB)
require 'config.php';
if (!isset($_SESSION['teacher_id'])) {
  header("Location: login.php");
  exit;
}

$teacher_id = $_SESSION['teacher_id'];
$department = $_POST['department'] ?? null;
$semester = $_POST['semester'] ?? null;
$date = $_POST['date'] ?? null;
$subject = trim($_POST['subject'] ?? '');
$status_arr = $_POST['status'] ?? [];

if (!$department || !$semester || !$date) {
  die('Missing data. Go back and try again.');
}

// optional: ensure subject isn't too long
if ($subject !== '' && mb_strlen($subject) > 250) {
  $subject = mb_substr($subject, 0, 250);
}

$inserted_present = 0;
$inserted_absent  = 0;
$skipped = 0;

$pdo->beginTransaction();

try {
  // prevent duplicate by student_id + date
  $checkStmt = $pdo->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ?");
  // INSERT now includes subject column
  $insStmt = $pdo->prepare("INSERT INTO attendance (student_id, department_id, semester, date, subject, status, marked_by) VALUES (?, ?, ?, ?, ?, ?, ?)");

  foreach ($status_arr as $student_id => $status) {
    // normalize status (in case of unexpected casing)
    $status_norm = trim($status);

    // prevent duplicates
    $checkStmt->execute([$student_id, $date]);
    if ($checkStmt->fetch()) {
      $skipped++;
      continue;
    }

    // insert with subject (can be empty string)
    $insStmt->execute([$student_id, $department, $semester, $date, $subject, $status_norm, $teacher_id]);

    // count only the inserted students by status
    if (strcasecmp($status_norm, 'Present') === 0) {
      $inserted_present++;
    } else {
      // anything not exactly "Present" will be treated as Absent
      $inserted_absent++;
    }
  }

  $pdo->commit();
} catch (Exception $e) {
  $pdo->rollBack();
  die("Error saving attendance: " . $e->getMessage());
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Attendance Submitted</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="card">
    <h2>Attendance Saved</h2>
    <p>Date: <?=htmlspecialchars($date)?></p>
    <?php
  // fetch department name
  $dept_name = '';
  if ($department) {
    $stmt = $pdo->prepare("SELECT department_name FROM departments WHERE id = ?");
    $stmt->execute([$department]);
    $row = $stmt->fetch();
    if ($row) $dept_name = $row['department_name'];
  }
  ?>
  <p>Department: <?=htmlspecialchars($dept_name)?> | Semester: <?=htmlspecialchars($semester)?></p>

    <?php if ($subject): ?>
      <p>Subject: <?=htmlspecialchars($subject)?></p>
    <?php else: ?>
      <p>Subject: <em>Not provided</em></p>
    <?php endif; ?>

    <!-- New: show Present / Absent counts only -->
    <p>
      Present: <strong><?= $inserted_present ?></strong>
      &nbsp;|&nbsp;
      Absent: <strong><?= $inserted_absent ?></strong>
    </p>

    <p><a href="dashboard.php">Mark another class</a> 
  </div>
</body>
</html>
