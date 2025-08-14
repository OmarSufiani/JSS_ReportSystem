<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header('Location: login.php');
    exit();
}

$school_id = $_SESSION['school_id'];
$successMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['scores']) && isset($_POST['subject'])) {
    $subject_id = $_POST['subject'];
    $term = $_POST['term'];
    $exam_type = $_POST['exam_type'];
    $class_id = $_POST['class'];

    foreach ($_POST['scores'] as $student_id => $score) {
        if ($score < 30) {
            $performance = "B.E";
            $tcomments = "Put more effort";
        } elseif ($score < 50) {
            $performance = "A.E";
            $tcomments = "Average";
        } elseif ($score < 70) {
            $performance = "M.E";
            $tcomments = "Good";
        } else {
            $performance = "E.E";
            $tcomments = "Excellent";
        }

        // Insert score with school_id included
        $stmt = $conn->prepare("INSERT INTO score (std_id, subject_id, term, exam_type, class_id, Score, performance, tcomments, school_id) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissidssi", $student_id, $subject_id, $term, $exam_type, $class_id, $score, $performance, $tcomments, $school_id);
        $stmt->execute();
        $stmt->close();
    }

    // Set success message
    $successMessage = "Scores inserted successfully!";
}

// Fetch classes for this school only
$classes_stmt = $conn->prepare("SELECT id, name FROM class WHERE school_id = ?");
$classes_stmt->bind_param("i", $school_id);
$classes_stmt->execute();
$classes_result = $classes_stmt->get_result();
$classes = $classes_result->fetch_all(MYSQLI_ASSOC);
$classes_stmt->close();

// Fetch subjects for this school only
$subjects_stmt = $conn->prepare("SELECT id, name FROM subject WHERE school_id = ?");
$subjects_stmt->bind_param("i", $school_id);
$subjects_stmt->execute();
$subjects_result = $subjects_stmt->get_result();
$subjects = $subjects_result->fetch_all(MYSQLI_ASSOC);
$subjects_stmt->close();

// Get selected class students for this school only
$students = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class']) && !isset($_POST['scores'])) {
    $class_id = $_POST['class'];

    $students_stmt = $conn->prepare("SELECT id, firstname, lastname, admno FROM student WHERE class_id = ? AND school_id = ?");
    $students_stmt->bind_param("ii", $class_id, $school_id);
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();
    $students = $students_result->fetch_all(MYSQLI_ASSOC);
    $students_stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Scores - Ramzy School System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-4">

    <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-3">&larr; Back to Dashboard</a>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success alert-dismissible fade show text-center" role="alert" id="success-alert">
            <?= htmlspecialchars($successMessage) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('success-alert').style.display = 'none';
                window.location.href = window.location.pathname;
            }, 3000);
        </script>
    <?php endif; ?>

    <form method="POST" class="card p-3">

        <div class="row g-3 mb-3">

            <div class="col-md-3">
                <label class="form-label">Class</label>
                <select name="class" class="form-select" required onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['id'] ?>" <?= (isset($_POST['class']) && $_POST['class'] == $class['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($class['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Subject</label>
                <select name="subject" class="form-select" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>"><?= htmlspecialchars($subject['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Term</label>
                <select name="term" class="form-select" required>
                    <option value="">Select Term</option>
                    <option value="Term 1" <?= (isset($_POST['term']) && $_POST['term'] == 'Term 1') ? 'selected' : '' ?>>Term 1</option>
                    <option value="Term 2" <?= (isset($_POST['term']) && $_POST['term'] == 'Term 2') ? 'selected' : '' ?>>Term 2</option>
                    <option value="Term 3" <?= (isset($_POST['term']) && $_POST['term'] == 'Term 3') ? 'selected' : '' ?>>Term 3</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Exam Type</label>
                <select name="exam_type" class="form-select" required>
                    <option value="">Select Exam Type</option>
                    <option value="Mid Term" <?= (isset($_POST['exam_type']) && $_POST['exam_type'] == 'Mid Term') ? 'selected' : '' ?>>Mid Term</option>
                    <option value="End Term" <?= (isset($_POST['exam_type']) && $_POST['exam_type'] == 'End Term') ? 'selected' : '' ?>>End Term</option>
                </select>
            </div>

        </div>

        <?php if (!empty($students)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Admission No.</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($students as $std): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($std['firstname'] . ' ' . $std['lastname']) ?></td>
                                <td><?= htmlspecialchars($std['admno']) ?></td>
                                <td>
                                    <input type="number" name="scores[<?= $std['id'] ?>]" class="form-control" step="0.01" min="0" max="100" required>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-success">Submit Scores</button>
        <?php elseif (isset($_POST['class'])): ?>
            <div class="alert alert-warning mt-3">No students found for the selected class.</div>
        <?php endif; ?>

    </form>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
