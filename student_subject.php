<?php
session_start();
include 'db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];

    $sql = "INSERT INTO student_subject (student_id, subject_id) VALUES ('$student_id', '$subject_id')";
    if (mysqli_query($conn, $sql)) {
        $success = "Subject assigned to student successfully!";
    } else {
        $error = "Error: Unable to assign subject.";
    }
}

$students = mysqli_query($conn, "SELECT * FROM student");
$subjects = mysqli_query($conn, "SELECT * FROM subject");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assign Subject</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-outline-primary mb-4">
        &larr; Back to Dashboard
    </a>

    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="mb-4 text-primary">Assign Subject to Student</h4>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="student_id" class="form-label">Student:</label>
                    <select class="form-select" name="student_id" id="student_id" required>
                        <option value="">Select Student</option>
                        <?php while($row = mysqli_fetch_assoc($students)) { ?>
                            <option value="<?= $row['id'] ?>">
                                <?= $row['name'] ?> (Adm: <?= $row['admno'] ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="subject_id" class="form-label">Subject:</label>
                    <select class="form-select" name="subject_id" id="subject_id" required>
                        <option value="">Select Subject</option>
                        <?php while($sub = mysqli_fetch_assoc($subjects)) { ?>
                            <option value="<?= $sub['id'] ?>"><?= $sub['name'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-success w-100">Assign Subject</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
