<?php
session_start();
include 'db.php';

$success = '';
$error = '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Fetch all schools
    $schools = mysqli_query($conn, "SELECT id, school_name FROM school");

    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['school_id'], $_POST['student_id'], $_POST['subject_id'])) {
        
        $school_id = intval($_POST['school_id']);
        $student_id = intval($_POST['student_id']);
        $subject_id = intval($_POST['subject_id']);

        $sql = "INSERT INTO student_subject (student_id, school_id, subject_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $student_id, $school_id, $subject_id);
        $stmt->execute();

        $success = "✅ Subject assigned to student successfully!";
        $stmt->close();
    }
} catch (mysqli_sql_exception $e) {
    $error = "❌ Error: " . htmlspecialchars($e->getMessage());
}

$selected_school_id = isset($_POST['school_id']) ? intval($_POST['school_id']) : null;

$students = [];
$subjects = [];
if ($selected_school_id) {
    $students = mysqli_query($conn,
        "SELECT id, firstname, lastname, admno FROM student WHERE school_id = $selected_school_id"
    );
    $subjects = mysqli_query($conn,
        "SELECT id, name FROM subject WHERE school_id = $selected_school_id"
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF‑8" />
  <meta name="viewport" content="width=device‑width, initial-scale=1" />
  <title>Assign Subject to Student</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">

<div class="container mt-5">
  <a href="dashboard.php" class="btn btn-outline-primary mb-4">&larr; Back to Dashboard</a>
  
  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="mb-4 text-primary">Assign Subject to Student</h4>
      
      <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>
      
      <form method="POST">
        <!-- School Dropdown -->
        <div class="mb-3">
          <label for="school_id" class="form-label">School</label>
          <select id="school_id" name="school_id" class="form-select" required onchange="this.form.submit()">
            <option value="">Select School</option>
            <?php while ($s = mysqli_fetch_assoc($schools)): ?>
              <option value="<?= $s['id'] ?>" <?= $selected_school_id === intval($s['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['school_name']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        
        <?php if ($selected_school_id): ?>
          <!-- Student Dropdown -->
          <div class="mb-3">
            <label for="student_id" class="form-label">Student</label>
            <select id="student_id" name="student_id" class="form-select" required>
              <option value="">Select Student</option>
              <?php while ($row = mysqli_fetch_assoc($students)): ?>
                <option value="<?= $row['id'] ?>">
                  <?= htmlspecialchars("{$row['firstname']} {$row['lastname']} (Adm: {$row['admno']})") ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          
          <!-- Subject Dropdown -->
          <div class="mb-3">
            <label for="subject_id" class="form-label">Subject</label>
            <select id="subject_id" name="subject_id" class="form-select" required>
              <option value="">Select Subject</option>
              <?php while ($sub = mysqli_fetch_assoc($subjects)): ?>
                <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['name']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          
          <button type="submit" class="btn btn-success w-100">Assign Subject</button>
        <?php endif; ?>
      </form>
      
    </div>
  </div>
</div>

</body>
</html>
