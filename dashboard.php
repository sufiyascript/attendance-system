<?php
// dashboard.php (updated to include Subject dropdown)
require 'config.php';
if (!isset($_SESSION['teacher_id'])) {
  header("Location: login.php");
  exit;
}

$teacher_name = $_SESSION['teacher_name'];

// fetch departments
$departments = $pdo->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();

$students = [];
$selected_dept = $_GET['department'] ?? '';
$selected_sem = $_GET['semester'] ?? '';

if ($selected_dept && $selected_sem) {
  $stmt = $pdo->prepare("SELECT * FROM students WHERE department_id = ? AND semester = ? ORDER BY roll_number");
  $stmt->execute([$selected_dept, $selected_sem]);
  $students = $stmt->fetchAll();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard - Attendance</title>
  <link rel="stylesheet" href="style.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body>
  <header>
    <h1>Welcome, <?=htmlspecialchars($teacher_name)?></h1>
    <a class="logout" href="logout.php">Logout</a>
  </header>

  <section class="card">
    <h2>Mark Attendance</h2>

    <!-- Selection form (department + semester) -->
    <form method="get" class="inline-form" id="selectForm">
      <label>Department</label>
      <select name="department" id="deptSelect" required>
        <option value="">--Select--</option>
        <?php foreach($departments as $d): ?>
          <option value="<?=$d['id']?>" <?=($selected_dept==$d['id'])?'selected':''?>><?=htmlspecialchars($d['department_name'])?></option>
        <?php endforeach; ?>
      </select>

      <label>Semester</label>
      <select name="semester" id="semSelect" required>
        <option value="">--Select--</option>
        <?php for($s=1;$s<=6;$s++): ?>
          <option value="<?=$s?>" <?=($selected_sem==$s)?'selected':''?>><?=$s?></option>
        <?php endfor; ?>
      </select>

      <button type="submit">Show Students</button>
    </form>

    <?php if ($students): ?>
      <!-- Attendance form (includes subject select) -->
      <form method="post" action="process_attendance.php" id="attendanceForm">
        <input type="hidden" name="department" value="<?=htmlspecialchars($selected_dept)?>">
        <input type="hidden" name="semester" value="<?=htmlspecialchars($selected_sem)?>">

        <label>Date</label>
        <input type="date" name="date" value="<?=date('Y-m-d')?>" required>

        <label>Subject</label>
        <select name="subject" id="subjectSelect" required>
          <option value="">--Select Subject--</option>
          <!-- JS will populate this based on department+semester -->
        </select>

        <table class="table">
          <thead><tr><th>Roll</th><th>Name</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach($students as $st): ?>
              <tr>
                <td><?=htmlspecialchars($st['roll_number'])?></td>
                <td><?=htmlspecialchars($st['name'])?></td>
                <td>
                  <label><input type="radio" name="status[<?=$st['id']?>]" value="Present" checked> Present</label>
                  <label><input type="radio" name="status[<?=$st['id']?>]" value="Absent"> Absent</label>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <button type="submit">Submit Attendance</button>
      </form>
    <?php elseif ($selected_dept): ?>
      <p>No students found for selected department/semester.</p>
    <?php endif; ?>

  </section>

<script>
/*
  Subject mapping (hardcoded) based on the list you provided.
  department IDs mapping (from your DB):
   1 = Computer Science (CSE)
   2 = Mining Engineering (MIN)
   3 = Civil Engineering (CIV)
   4 = Mechanical Engineering (ME)
   5 = Electrical Engineering (EE)
*/

// Common semester subjects (same for ALL departments)
const semCommon = {
  1: [
    "Mathematics-I (BS101/M-I)",
    "Applied Physics-I (BS103)",
    "Applied Chemistry (BS105)",
    "Communication Skills in English (HS101)",
    "Engineering Graphics (EG101)",
    "Engineering Workshop Practice (EWP101)",
    "Applied Physics-I Lab (BS107)",
    "Applied Chemistry Lab (BS109)",
    "Communication Skills in English Lab (HS105)",
    "Sports and Yoga (HS103)"
  ],
  2: [
    "Mathematics-II (BS102/M-II)",
    "Applied Physics-II (BS104)",
    "Introduction to IT Systems (ES102)",
    "Fundamentals of Electrical & Electronics Engineering (FEEE102)",
    "Engineering Mechanics (EM102)",
    "Applied Physics-II Lab (BS106)",
    "Introduction to IT Systems Lab (ES102L)",
    "Fundamentals of Electrical & Electronics Engineering Lab (FEEE102L)",
    "Engineering Mechanics Lab (EM102L)"
  ]
};

// Department specific subjects (semester -> array)
const subjects = {
  // CSE (dept 1)
  "1-3": [
    "Python (COPC201)",
    "Object Oriented Programming using Java (COPC203)",
    "Data Structures (COPC205)",
    "Computer Networks (COPC207)",
    "Digital Electronics & Computer Organization (COPC209)"
  ],
  "1-4": [
    "Software Engineering (COPC401)",
    "Web Technology (COPC403)",
    "Operating Systems (COPC405)",
    "Computer Graphics & Multimedia (COPC407)"
  ],
  "1-5": [
    "Microprocessor & Microcontroller (COPC301)",
    "Internet of Things (IoT) (COPC303)",
    "Program Elective-1 (Mobile Computing / Advanced Computer Network) (COPE304)",
    "Program Elective-2 (Theory of Automata / Fundamentals of AI) (COPE305)",
    "Program Elective-3 (Computer Graphics / Digital Image Processing) (COPE306)",
    "Microprocessor & Microcontroller Lab (CST307)",
    "Summer Internship-2 (SI301)",
    "Major Project (PR302)"
  ],
  "1-6": [
    "Mobile Application Development (COPC302)",
    "Cloud Computing (COPC304)",
    "Program Elective (Advanced Databases / Machine Learning) (COPE306)",
    "Entrepreneurship and Start-ups (HS302)",
    "Open Elective (OE302)",
    "Major Project-2 (PR304)"
  ],

  // Mechanical (dept 4)
  "4-3": [
    "Mechanical Engineering Drawing (MEPC201)",
    "Mechanical Engineering Materials (MEPC203)",
    "Strength of Materials (MEPC205)",
    "Manufacturing Processes-I (MEPC207)",
    "Thermal Engineering-I (MEPC209)",
    "Mechanical Engineering Drawing Practice (MEPC211)",
    "Materials Testing Lab (MEPC213)",
    "Thermal Engineering-I Lab (MEPC215)",
    "Manufacturing Processes-I Practice (MEPC217)"
  ],
  "4-4": [
    "Theory of Machine (MEPC401)",
    "Manufacturing Process-II (MEPC403)",
    "Thermal Engineering-II (MEPC405)",
    "Engineering Metrology (MEPC407)",
    "Computer Aided Machine Drawing Practice (MEPC409)"
  ],
  "4-5": [
    "Power Engineering (MEPC301)",
    "Advanced Manufacturing Processes (MEPC303)",
    "Fluid Mechanics and Machinery (MEPC309)",
    "Program Elective (without Lab) (MEPE301)",
    "Program Elective (with Lab) (MEPE303)",
    "Power Engineering Lab (MEPC311)"
  ],
  "4-6": [
    "Design of Machine Elements (MEPC302)",
    "Work, Organization & Management (MEPC304)",
    "Program Elective (with Lab) (MEPE302)",
    "Entrepreneurship and Start-ups (HS302)",
    "Open Elective (Compulsory) (MEOE302)",
    "Open Elective (MEOE304)"
  ],

  // Civil (dept 3)
  "3-3": [
    "Building Construction & Materials (CEPC201)",
    "Surveying (CEPC203)",
    "Strength of Materials (CEPC205)",
    "Fluid Mechanics (CEPC207)",
    "Engineering Geology (CEPC209)"
  ],
  "3-4": [
    "Hydraulics (CEPC401)",
    "Advanced Surveying (CEPC402)",
    "Theory of Structure (CEPC403)",
    "Geotechnical Engineering (CEPC404)",
    "Design of RCC and Steel Structure (CEPC405)",
    "Basic Surveying Field Practices (CEPC406S)",
    "Civil Engineering Lab-II (CEPC407S)",
    "Elective-I (Precast and Prestressed Concrete / Rural Construction Technology) (CEPE408)",
    "Minor Project (CEPR409S)"
  ],
  "3-5": [
    "Water Resource Engineering (CEPC501)",
    "Estimating, Costing and Valuation (CEPC502)",
    "Design of RCC and Steel Structure Practices (CEPC503S)",
    "Estimating, Costing and Valuation Practices (CEPC504S)",
    "Water Resource Engineering Practices (CEPC505S)",
    "Elective-II (Advanced Design of Structures / Traffic Engineering) (CEPE506)",
    "Elective-III (Building Services and Maintenance / Repair and Maintenance of Structures) (CEPE507)",
    "Internship-II after fourth Semester (CEI508)",
    "Major Project I (CEPR509S)",
    "Safety Engineering & Management in Construction Sector (CEPC510)"
  ],
  "3-6": [
    "Transportation Engineering (CEPC601)",
    "Environmental Engineering (CEPC603)",
    "Construction Planning & Management (CEPC605)",
    "Entrepreneurship and Start-ups (HS302)",
    "Major Project II (CEPR607S)"
  ],

  // Electrical (dept 5)
  "5-3": [
    "Electrical Circuit & Network (EEPC201)",
    "Electrical Machine I (EEPC203)",
    "Basic Electronics (EEPC205)",
    "Programming concept using C (EEPC207)",
    "Electrical Measuring Instrument (EEPC209)",
    "Electrical Workshop I (EEPC211L)",
    "Elements of Mechanical Engineering (EEPC213)",
    "Professional Practices I (EEPC215L)"
  ],
  "5-4": [
    "Electric Circuits (EEPC401)",
    "D.C. Generator (EEPC403)",
    "D.C. Motor (EEPC405)",
    "Single phase Transformer (EEPC407)",
    "Electrical Machine II (EEPC409)",
    "Electrical Machines Lab (EEPC411L)",
    "Power Electronics Lab (EEPC413L)"
  ],
  "5-5": [
    "Power System (EEPC501)",
    "Control System (EEPC503)",
    "Power Electronics (EEPC505)",
    "Program Elective I (EEPE507)",
    "Control System Lab (EEPC509L)",
    "Power System Lab (EEPC511L)"
  ],
  "5-6": [
    "Electrical Design Estimation & Costing (EEPC601)",
    "Electrical Installation, Maintenance, Testing (EEPC603)",
    "Industrial Management (EEPC605)",
    "Elective II (Industrial Automation / Process Control / Control of Electrical Machine / Computer Hardware & Networking) (EEPE607)",
    "Industrial Project (EEPC609L)",
    "Electrical Workshop II (EEPC611L)",
    "Professional Practice-IV (EEPC613L)",
    "General Viva voce (EEPC615)"
  ],

  // Mining (dept 2) - descriptive names as provided
  "2-3": [
    "Introduction to Mining Engineering",
    "Mining Methods and Techniques",
    "Mine Surveying Basics",
    "Rock Mechanics and Ground Control",
    "Mineralogy and Geology related to Mining",
    "Mining Machinery and Equipment",
    "Safety and Environmental Aspects in Mining",
    "Laboratory and Practical sessions relevant to mining subjects"
  ],
  "2-4": [
    "Mine Ventilation (MINPC401)",
    "Underground Metalliferous Mining (MINPC402)",
    "Mine Survey-I (MINPC403)",
    "Elementary Rock Mechanics & Strata Control (MINPC404)",
    "Electro-technology in Mining (MINPC405)",
    "Mine Ventilation Lab (MINPC411)",
    "Mine Survey-I Lab (MINPC412)",
    "Strata Control Lab (MINPC413)",
    "Electro-technology in Mining Lab (MINPC414)",
    "Elective-I (Special Underground Mining / Surface Mining-II) (MINPE42*)",
    "Minor Project (MINPR451)"
  ],
  "2-5": [
    "Mine Management, Legislation & General Safety - I",
    "Mine Ventilation - I",
    "Mine Surveying - I",
    "Electrical Engineering & Mechanical Engineering (related to mining)",
    "Industrial Training (Practical training during semester)"
  ],
  "2-6": [
    "Mine Management, Legislation & General Safety - II",
    "Mining Machinery - I",
    "Mining Machinery - II",
    "Mine Ventilation - II",
    "Mine Surveying - II",
    "Project Work & Seminar",
    "Grand Viva"
  ]
};

// utility to populate subject select element
function populateSubjects(deptId, sem) {
  const select = document.getElementById('subjectSelect');
  if (!select) return;
  select.innerHTML = '<option value="">--Select Subject--</option>';

  // 1 and 2 are same across all departments
  if (sem == '1' || sem == '2') {
    const arr = semCommon[sem] || [];
    arr.forEach(s => {
      const opt = document.createElement('option');
      opt.value = s;
      opt.textContent = s;
      select.appendChild(opt);
    });
    return;
  }

  const key = deptId + '-' + sem;
  if (subjects[key] && subjects[key].length) {
    subjects[key].forEach(s => {
      const opt = document.createElement('option');
      opt.value = s;
      opt.textContent = s;
      select.appendChild(opt);
    });
    return;
  }

  // fallback: show a notice option
  const opt = document.createElement('option');
  opt.value = '';
  opt.textContent = 'No subject list found for selected Dept/Sem';
  select.appendChild(opt);
}

document.addEventListener('DOMContentLoaded', function() {
  const deptEl = document.getElementById('deptSelect');
  const semEl = document.getElementById('semSelect');

  // If page initially loaded with department+semester selected, populate subjects
  const initialDept = "<?=htmlspecialchars($selected_dept)?>";
  const initialSem = "<?=htmlspecialchars($selected_sem)?>";
  if (initialDept && initialSem) {
    populateSubjects(initialDept, initialSem);
  }

  // When teacher changes selection in the top form, we can (optionally) pre-populate
  // the subject list so teacher sees it immediately after clicking Show Students or before.
  deptEl && deptEl.addEventListener('change', function(){ 
    const d = deptEl.value; const s = semEl.value;
    if (d && s) populateSubjects(d, s);
  });
  semEl && semEl.addEventListener('change', function(){
    const d = deptEl.value; const s = semEl.value;
    if (d && s) populateSubjects(d, s);
  });
});
</script>
</body>
</html>
