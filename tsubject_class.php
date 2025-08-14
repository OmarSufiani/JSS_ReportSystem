<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header('Location: login.php');
    exit();
}

$school_id = $_SESSION['school_id'];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $subject_id = $_POST['subject_id'];
    $class_id = $_POST['class_id'];

    // Optional: prevent duplicate assignment
    $check = mysqli_query($conn, "SELECT * FROM tsubject_class WHERE teacher_id = $teacher_id AND subject_id = $subject_id AND class_id = $class_id AND school_id = $school_id");
    if (mysqli_num_rows($check) > 0) {
        $success = '<div class="alert alert-warning text-center">⚠️ This assignment already exists.</div>';
    } else {
        // Insert with school_id
        $stmt = $conn->prepare("INSERT INTO tsubject_class (teacher_id, subject_id, class_id, school_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $teacher_id, $subject_id, $class_id, $school_id);

        if ($stmt->execute()) {
            $success = '<div class="alert alert-success text-center">✅ Assigned teacher to subject and class!</div>';
        } else {
            $success = '<div class="alert alert-danger text-center">❌ Error assigning teacher.</div>';
        }
        $stmt->close();
    }
}

// Fetch filtered data by school
$teachers = mysqli_query($conn, "SELECT id AS teacher_id, user_id, name FROM teacher WHERE school_id = $school_id ORDER BY name ASC");
$subjects = mysqli_query($conn, "SELECT id, name FROM subject WHERE school_id = $school_id");
$classes = mysqli_query($conn, "SELECT id, name FROM class WHERE school_id = $school_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assign Teacher to Subject/Class</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-4">&larr; Back to Dashboard</a>

    <div class="card shadow p-4">
        <h4 class="mb-4 text-center">Assign Teacher to Subject and Class</h4>

        <?= $success ?>

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
