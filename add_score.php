<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

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

        $stmt = $conn->prepare("INSERT INTO score (std_id, subject_id, term, exam_type, class_id, Score, performance, tcomments) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissidss", $student_id, $subject_id, $term, $exam_type, $class_id, $score, $performance, $tcomments);
        $stmt->execute();
    }

    // Set success message
    $successMessage = "Scores inserted successfully!";
}

// Fetch dropdown options
$classes = mysqli_query($conn, "SELECT * FROM class");
$subjects = mysqli_query($conn, "SELECT * FROM subject");

// Get selected class students
$students = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class']) && !isset($_POST['scores'])) {
    $class_id = $_POST['class'];
    $students = mysqli_query($conn, "SELECT * FROM student WHERE class_id = $class_id");
}
?>

<!-- Include Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Success Message -->
<?php if (!empty($successMessage)): ?>
    <div class="alert alert-success alert-dismissible fade show text-center" role="alert" id="success-alert">
        <?= $successMessage ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <script>
        // Auto-hide and reload after 3 seconds
        setTimeout(() => {
            document.getElementById('success-alert').style.display = 'none';
            window.location.href = window.location.pathname;
        }, 3000);
    </script>
<?php endif; ?>

<div class="container py-4">
    <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-3">
        &larr; Back to Dashboard
    </a>

    <form method="POST" class="card p-3">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label">Class</label>
                <select name="class" class="form-select" required onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= $class['id'] ?>" <?= (isset($_POST['class']) && $_POST['class'] == $class['id']) ? 'selected' : '' ?>>
                            <?= $class['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Subject</label>
                <select name="subject" class="form-select" required>
                    <option value="">Select Subject</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= $subject['id'] ?>"><?= $subject['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Term</label>
                <select name="term" class="form-select" required>
                    <option value="">Select Term</option>
                    <option value="Term 1">Term 1</option>
                    <option value="Term 2">Term 2</option>
                    <option value="Term 3">Term 3</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Exam Type</label>
                <select name="exam_type" class="form-select" required>
                    <option value="">Select Exam Type</option>
                    <option value="Mid Term">Mid Term</option>
                    <option value="End Term">End Term</option>
                </select>
            </div>
        </div>

        <?php if (!empty($students) && mysqli_num_rows($students) > 0): ?>
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
                        <?php $i = 1; while ($std = mysqli_fetch_assoc($students)): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= $std['name'] ?></td>
                                <td><?= $std['admno'] ?></td>
                                <td>
                                    <input type="number" name="scores[<?= $std['id'] ?>]" class="form-control" step="0.01" required>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-success">Submit Scores</button>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['scores'])): ?>
            <div class="alert alert-warning mt-3">No students found for the selected class.</div>
        <?php endif; ?>
    </form>
</div>
