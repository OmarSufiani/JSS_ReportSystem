<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $sql = "INSERT INTO class (name) VALUES ('$name')";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "✅ Class added successfully!";
    } else {
        $_SESSION['message'] = "❌ Failed to add class.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Class</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Hide alert after 3 seconds
        document.addEventListener("DOMContentLoaded", function () {
            const alertBox = document.getElementById("message-box");
            if (alertBox) {
                setTimeout(() => alertBox.style.display = "none", 3000);
            }
        });
    </script>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-3">&larr; Back to Dashboard</a>

            <h4 class="mb-4">Add Class</h4>

            <?php if ($message): ?>
                <div id="message-box" class="alert <?= str_starts_with($message, '✅') ? 'alert-success' : 'alert-danger' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="d-grid gap-3">
                <div class="form-group">
                    <label for="name" class="form-label">Class Name</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Enter Class Name" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Class</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
