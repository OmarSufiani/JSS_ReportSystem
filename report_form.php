<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
if (isset($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
}

$selected_school_id = isset($_GET['school_id']) ? intval($_GET['school_id']) : 0;

// Fetch schools (uses correct school_name column)
$schools = [];
$school_result = $conn->query("SELECT id, school_name FROM school ORDER BY school_name ASC");
while ($row = $school_result->fetch_assoc()) {
    $schools[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Report Card</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-primary">Generate Report Card</h2>

    <a href="dashboard.php" class="btn btn-outline-primary mb-3">&larr; Back to Dashboard</a>

    <form method="GET" action="" class="card p-4 shadow-sm">

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center"><?= $success ?></div>
        <?php endif; ?>

        <div class="row mb-3">
            <div class="col-md-4">
                <label for="school_id" class="form-label">Select School:</label>
                <select name="school_id" id="school_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- All Schools --</option>
                    <?php foreach ($schools as $school): ?>
                        <option value="<?= $school['id'] ?>" <?= ($school['id'] == $selected_school_id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($school['school_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Selecting a school filters students and classes below.</small>
            </div>

            <div class="col-md-4">
                <label for="term" class="form-label">Select Term:</label>
                <select name="term" id="term" class="form-select" required>
                    <option value="Term 1">Term 1</option>
                    <option value="Term 2">Term 2</option>
                    <option value="Term 3">Term 3</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="year" class="form-label">Select Year:</label>
                <input type="number" name="year" id="year" class="form-control" value="<?= date('Y'); ?>" required />
            </div>
        </div>

        <fieldset class="border rounded p-3 mb-3">
            <legend class="float-none w-auto px-2">Select Report Type</legend>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="student_id" class="form-label">Generate by Student:</label>
                    <select name="id" id="student_id" class="form-select">
                        <option value="">-- Select Student --</option>
                        <?php
                        $studentSql = "SELECT id, firstname, admno FROM student";
                        if ($selected_school_id) {
                            $studentSql .= " WHERE school_id = " . $selected_school_id;
                        }
                        $studentSql .= " ORDER BY id ASC";

                        $student_result = $conn->query($studentSql);
                        while ($row = $student_result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>" . htmlspecialchars($row['firstname']) . " (Adm: " . htmlspecialchars($row['admno']) . ")</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="class_id" class="form-label">Or Generate for Whole Class:</label>
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">-- Select Class --</option>
                        <?php
                        $classSql = "
                            SELECT DISTINCT c.id AS class_id, c.name AS class_name 
                            FROM class c 
                            JOIN student s ON s.class_id = c.id";
                        if ($selected_school_id) {
                            $classSql .= " WHERE s.school_id = " . $selected_school_id;
                        }
                        $classSql .= " ORDER BY c.name ASC";

                        $class_result = $conn->query($classSql);
                        while ($row = $class_result->fetch_assoc()) {
                            echo "<option value='{$row['class_id']}'>" . htmlspecialchars($row['class_name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </fieldset>

        <input type="hidden" name="school_id" value="<?= $selected_school_id ?>">
        <button type="submit" formaction="generate_report_card.php" class="btn btn-primary w-100">Generate Report</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
