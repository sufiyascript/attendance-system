<?php
// reports.php — updated to include subject in listing & CSV export
require '../config.php';
if (!isset($_SESSION['teacher_id']) && empty($_SESSION['is_admin'])) {
  // not teacher, not admin → redirect
  header("Location: admin_login.php");
  exit;
}


$departments = $pdo->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();

$filter_dept = $_GET['department'] ?? '';
$filter_sem = $_GET['semester'] ?? '';
$filter_date = $_GET['date'] ?? '';

$where = [];
$params = [];

if ($filter_dept) { $where[] = 'a.department_id = ?'; $params[] = $filter_dept; }
if ($filter_sem) { $where[] = 'a.semester = ?'; $params[] = $filter_sem; }
if ($filter_date) { $where[] = 'a.date = ?'; $params[] = $filter_date; }

$sql = "SELECT a.*, s.name AS student_name, s.roll_number, d.department_name, t.name AS teacher_name
        FROM attendance a
        JOIN students s ON a.student_id = s.id
        JOIN departments d ON a.department_id = d.id
        JOIN teachers t ON a.marked_by = t.id";

if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY a.date DESC, d.department_name, s.roll_number LIMIT 1000";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="attendance_report.csv"');
  $out = fopen('php://output', 'w');
  // include Subject column
  fputcsv($out, ['Date','Department','Semester','Roll','Student','Subject','Status','Marked By','Timestamp']);
  foreach ($rows as $r) {
    fputcsv($out, [$r['date'],$r['department_name'],$r['semester'],$r['roll_number'],$r['student_name'],$r['subject'],$r['status'],$r['teacher_name'],$r['timestamp']]);
  }
  fclose($out);
  exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Reports</title>
  <link rel="stylesheet" href="../style.css">
</head>
<body>
  <header>
    <h1>Attendance Reports</h1>
    <a href="admin_logout.php">Logout</a>
  </header>
  <div class="card">
    <form method="get" class="inline-form">
      <label>Department</label>
      <select name="department">
        <option value="">All</option>
        <?php foreach($departments as $d): ?>
          <option value="<?=$d['id']?>" <?=($filter_dept==$d['id'])?'selected':''?>><?=htmlspecialchars($d['department_name'])?></option>
        <?php endforeach; ?>
      </select>

      <label>Semester</label>
      <select name="semester">
        <option value="">All</option>
        <?php for($s=1;$s<=6;$s++): ?>
          <option value="<?=$s?>" <?=($filter_sem==$s)?'selected':''?>><?=$s?></option>
        <?php endfor; ?>
      </select>

      <label>Date</label>
      <input type="date" name="date" value="<?=htmlspecialchars($filter_date)?>">

      <button type="submit">Filter</button>
      <button type="submit" name="export" value="csv">Export CSV</button>
    </form>

    <table class="table">
      <thead><tr><th>Date</th><th>Dept</th><th>Sem</th><th>Roll</th><th>Student</th><th>Subject</th><th>Status</th><th>Marked By</th><th>Time</th></tr></thead>
      <tbody>
        <?php foreach($rows as $r): ?>
          <tr>
            <td><?=htmlspecialchars($r['date'])?></td>
            <td><?=htmlspecialchars($r['department_name'])?></td>
            <td><?=htmlspecialchars($r['semester'])?></td>
            <td><?=htmlspecialchars($r['roll_number'])?></td>
            <td><?=htmlspecialchars($r['student_name'])?></td>
            <td><?=htmlspecialchars($r['subject'])?></td>
            <td><?=htmlspecialchars($r['status'])?></td>
            <td><?=htmlspecialchars($r['teacher_name'])?></td>
            <td><?=htmlspecialchars($r['timestamp'])?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
