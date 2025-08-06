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
    $sql = "INSERT INTO subject (name) VALUES ('$name')";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "✅ Subject added!";
    } else {
        $_SESSION['message'] = "❌ Error adding subject.";
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
<html>
<head>
    <title>Add Subject</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Auto-hide alert
        document.addEventListener("DOMContentLoaded", function () {
            const alertBox = document.getElementById("message-box");
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

<h3 class="mb-3">Add New Subject</h3>

<?php if ($message): ?>
    <div id="message-box" class="alert <?= str_starts_with($message, '✅') ? 'alert-success' : 'alert-danger' ?>">
        <?= $message ?>
    </div>
<?php endif; ?>

<form method="POST" class="border p-4 rounded bg-light shadow-sm">
    <div class="mb-3">
        <label for="name" class="form-label">Subject Name</label>
        <input type="text" name="name" id="name" class="form-control" placeholder="Enter Subject Name" required>
    </div>

    <button type="submit" class="btn btn-success">Add Subject</button>
</form>

</body>
</html>
