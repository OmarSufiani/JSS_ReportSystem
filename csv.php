<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle POST and store result in session
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admno = $_POST['admno'] ?? '';
    $subject_id = $_POST['subject_id'] ?? '';
    $term = $_POST['term'] ?? '';
    $exam_type = $_POST['exam_type'] ?? '';

    $_SESSION['form_data'] = compact('admno', 'subject_id', 'term', 'exam_type');

    if ($admno && $subject_id && $term && $exam_type) {
        $query = "
            SELECT 
                s.name AS student_name,
                c.name AS class_name,
                sub.name AS subject_name,
                sc.Score,
                sc.performance,
                sc.tcomments
            FROM student s
            JOIN score sc ON s.id = sc.std_id
            JOIN class c ON s.class_id = c.id
            JOIN subject sub ON sc.subject_id = sub.id
            WHERE s.admno = ?
            AND sc.subject_id = ?
            AND sc.term = ?
            AND sc.exam_type = ?
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("siss", $admno, $subject_id, $term, $exam_type);
        $stmt->execute();
        $result = $stmt->get_result();

        $_SESSION['result'] = [];

        while ($row = $result->fetch_assoc()) {
            $_SESSION['result'][] = $row;
        }

        if (empty($_SESSION['result'])) {
            $_SESSION['error'] = "No records found for this student by the selected term, exam, and subject.";
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Please fill in all fields.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get saved data from session
$form_data = $_SESSION['form_data'] ?? ['admno' => '', 'subject_id' => '', 'term' => '', 'exam_type' => ''];
$admno = $form_data['admno'];
$subject_id = $form_data['subject_id'];
$term = $form_data['term'];
$exam_type = $form_data['exam_type'];

$results = $_SESSION['result'] ?? [];

$errorMsg = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Score Lookup</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">

    <h3 class="mb-4 text-center">Check Student Score</h3>

    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-outline-primary">
            &larr; Back to Dashboard
        </a>
    </div>

    <?php if ($errorMsg): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($errorMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
        <div class="col-md-6 col-lg-3">
            <label for="admno" class="form-label">Admission Number</label>
            <input type="text" class="form-control" id="admno" name="admno" value="<?= htmlspecialchars($admno) ?>" required>
        </div>

        <div class="col-md-6 col-lg-3">
            <label for="subject_id" class="form-label">Subject</label>
            <select class="form-select" id="subject_id" name="subject_id" required>
                <option value="" disabled <?= empty($subject_id) ? 'selected' : '' ?>>Select Subject</option>
                <?php
                $subject_query = mysqli_query($conn, "SELECT id, name FROM subject");
                while ($subject = mysqli_fetch_assoc($subject_query)) {
                    $selected = ($subject_id == $subject['id']) ? 'selected' : '';
                    echo "<option value='{$subject['id']}' $selected>{$subject['name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-6 col-lg-3">
            <label for="term" class="form-label">Term</label>
            <select class="form-select" id="term" name="term" required>
                <option value="">-- Select Term --</option>
                <option value="Term 1" <?= ($term === 'Term 1') ? 'selected' : '' ?>>Term 1</option>
                <option value="Term 2" <?= ($term === 'Term 2') ? 'selected' : '' ?>>Term 2</option>
                <option value="Term 3" <?= ($term === 'Term 3') ? 'selected' : '' ?>>Term 3</option>
            </select>
        </div>

        <div class="col-md-6 col-lg-3">
            <label for="exam_type" class="form-label">Exam Type</label>
            <select class="form-select" id="exam_type" name="exam_type" required>
                <option value="">-- Select Exam Type --</option>
                <option value="Mid Term" <?= ($exam_type === 'Mid Term') ? 'selected' : '' ?>>Mid Term</option>
                <option value="End Term" <?= ($exam_type === 'End Term') ? 'selected' : '' ?>>End Term</option>
            </select>
        </div>

        <div class="col-12 text-center">
            <button type="submit" class="btn btn-primary mt-3">Get Results</button>
        </div>
    </form>

    <hr class="my-4">

    <?php if (!empty($results)): ?>
        <div class='alert alert-info'>
            <strong>Selected Subject ID:</strong> <?= htmlspecialchars($subject_id) ?><br>
            <strong>Selected Term:</strong> <?= htmlspecialchars($term) ?><br>
            <strong>Selected Exam Type:</strong> <?= htmlspecialchars($exam_type) ?>
        </div>

        <div class='table-responsive'>
            <table class='table table-bordered table-striped align-middle'>
                <thead class='table-dark'>
                    <tr>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Score</th>
                        <th>Performance</th>
                        <th>Teacher's Comment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['class_name']) ?></td>
                            <td><?= htmlspecialchars($row['subject_name']) ?></td>
                            <td><?= htmlspecialchars($row['Score']) ?></td>
                            <td><?= htmlspecialchars($row['performance']) ?></td>
                            <td><?= htmlspecialchars($row['tcomments']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php unset($_SESSION['result'], $_SESSION['form_data']); ?>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
