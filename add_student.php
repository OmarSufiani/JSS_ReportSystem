<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $admno = $_POST['admno'];
    $class_id = $_POST['class_id'];

    $sql = "INSERT INTO student (name, admno, class_id) VALUES ('$name', '$admno', '$class_id')";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "✅ Student added successfully!";
    } else {
        $_SESSION['error'] = "❌ Error adding student.";
    }

    // Redirect to avoid resubmission
    header("Location: add_student.php");
    exit();
}

$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);

$classes = mysqli_query($conn, "SELECT * FROM class");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">

            <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-3">&larr; Back to Dashboard</a>

            <h4 class="mb-4">Add New Student</h4>

            <?php if ($success): ?>
                <div id="msg" class="alert alert-success"><?= $success ?></div>
            <?php elseif ($error): ?>
                <div id="msg" class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" class="d-grid gap-3">
                <div>
                    <label for="name" class="form-label">Student Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter student name" required>
                </div>

                <div>
                    <label for="admno" class="form-label">Admission Number</label>
                    <input type="number" name="admno" id="admno" class="form-control" placeholder="Enter admission number" required>
                </div>

                <div>
                    <label for="class_id" class="form-label">Select Class</label>
                    <select name="class_id" id="class_id" class="form-select" required>
                        <option value="">-- Choose Class --</option>
                        <?php while($row = mysqli_fetch_assoc($classes)) { ?>
                            <option value="<?= $row['id'] ?>"><?= $row['name'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Add Student</button>
            </form>

        </div>
    </div>
</div>

<!-- Auto-hide alerts after 3 seconds -->
<script>
    setTimeout(() => {
        const msg = document.getElementById('msg');
        if (msg) msg.style.display = 'none';
    }, 3000);
</script>

</body>
</html>
