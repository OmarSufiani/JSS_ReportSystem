<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';

// Capture and show success message from session
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $enrolment_no = $_POST['enrolment_no'];
    $name = $_POST['name'];
    $subject_id = $_POST['subject_id'];

    $sql = "INSERT INTO teacher (user_id, enrolment_no, name, subject_id) 
            VALUES ('$user_id', '$enrolment_no', '$name', '$subject_id')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "✅ Teacher added successfully!";
        header("Location: add_teacher.php");
        exit();
    } else {
        $error = "❌ Error: " . mysqli_error($conn);
    }
}

// Fetch users and subjects
$users = mysqli_query($conn, "SELECT id, FirstName, LastName FROM users");
$subjects = mysqli_query($conn, "SELECT * FROM subject");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

<a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">
    &larr; Back to Dashboard
</a>

<h3 class="mb-3">Add Teacher</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<form method="POST" class="border p-4 rounded bg-light shadow-sm">

    <div class="mb-3">
        <label for="user_id" class="form-label">User Account</label>
        <select name="user_id" id="user_id" class="form-select" required>
            <option value="">Select User</option>
            <?php while($row = mysqli_fetch_assoc($users)) { ?>
                <option value="<?= $row['id'] ?>">
                    <?= $row['FirstName'] . ' ' . $row['LastName'] ?> (ID: <?= $row['id'] ?>)
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="name" class="form-label">Teacher Name</label>
        <input type="text" name="name" id="name" class="form-control" placeholder="Enter Full Name" required>
    </div>

    <div class="mb-3">
        <label for="enrolment_no" class="form-label">Teacher Enrolment Number</label>
        <input type="text" name="enrolment_no" id="enrolment_no" class="form-control" placeholder="Enter Enrolment Number" required>
    </div>

    <div class="mb-3">
        <label for="subject_id" class="form-label">Subject</label>
        <select name="subject_id" id="subject_id" class="form-select" required>
            <option value="">Select Subject</option>
            <?php while($sub = mysqli_fetch_assoc($subjects)) { ?>
                <option value="<?= $sub['id'] ?>"><?= $sub['name'] ?></option>
            <?php } ?>
        </select>
    </div>

    <button type="submit" class="btn btn-success">Add Teacher</button>
</form>

</body>
</html>
