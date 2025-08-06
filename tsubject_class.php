<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];
    $class_id = $_POST['class_id'];

    $sql = "INSERT INTO tsubject_class (teacher_id, subject_id, class_id) VALUES ('$teacher_id', '$subject_id', '$class_id')";
    mysqli_query($conn, $sql);
    echo '<div class="alert alert-success text-center">✅ Assigned teacher to subject and class!</div>';
}

$teachers = mysqli_query($conn, "SELECT teacher.id AS teacher_id, teacher.user_id, teacher.name FROM teacher ORDER BY teacher.name ASC");
$subjects = mysqli_query($conn, "SELECT * FROM subject");
$classes = mysqli_query($conn, "SELECT * FROM class");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Teacher to Subject/Class</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-4">&larr; Back to Dashboard</a>

    <div class="card shadow p-4">
        <h4 class="mb-4 text-center">Assign Teacher to Subject and Class</h4>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Teacher</label>
                <select name="teacher_id" class="form-select" required>
                    <option value="">Select Teacher</option>
                    <?php while ($t = mysqli_fetch_assoc($teachers)) { ?>
                        <option value="<?= $t['teacher_id'] ?>">
                            <?= htmlspecialchars($t['name']) ?> (User ID: <?= $t['user_id'] ?>)
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select" required>
                    <option value="">Select Subject</option>
                    <?php while ($s = mysqli_fetch_assoc($subjects)) { ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select" required>
                    <option value="">Select Class</option>
                    <?php while ($c = mysqli_fetch_assoc($classes)) { ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-success">Assign</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
