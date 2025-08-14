<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['school_id'])) {
    header('Location: login.php');
    exit();
}

$school_id = $_SESSION['school_id'];
$success = '';
$error = '';

// Show success message if set
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $enrolment_no = trim($_POST['enrolment_no']);
    $name = trim($_POST['name']);

    if (empty($user_id) || empty($enrolment_no) || empty($name)) {
        $error = "❌ All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO teacher (user_id, enrolment_no, name, school_id) 
                                VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $enrolment_no, $name, $school_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "✅ Teacher added successfully!";
            header("Location: add_teacher.php");
            exit();
        } else {
            $error = "❌ Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Fetch users for current school
$users = mysqli_query($conn, "SELECT id, FirstName, LastName FROM users WHERE school_id = $school_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const alertBox = document.querySelector(".alert");
            if (alertBox) {
                setTimeout(() => alertBox.style.display = "none", 3000);
            }
        });
    </script>
</head>
<body class="container py-4">

<a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">
    &larr; Back to Dashboard
</a>

<h3 class="mb-3">Add Teacher</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php elseif ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="border p-4 rounded bg-light shadow-sm">

    <div class="mb-3">
        <label for="user_id" class="form-label">User Account</label>
        <select name="user_id" id="user_id" class="form-select" required>
            <option value="">Select User</option>
            <?php while ($row = mysqli_fetch_assoc($users)) { ?>
                <option value="<?= $row['id'] ?>">
                    <?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?> (ID: <?= $row['id'] ?>)
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="name" class="form-label">Teacher Name</label>
        <input type="text" name="name" id="name" class="form-control" placeholder="Enter Full Name" required>
    </div>

    <div class="mb-3">
        <label for="enrolment_no" class="form-label">Enrolment Number</label>
        <input type="text" name="enrolment_no" id="enrolment_no" class="form-control" placeholder="Enter Enrolment Number" required>
    </div>

    <button type="submit" class="btn btn-success">Add Teacher</button>
</form>

</body>
</html>
