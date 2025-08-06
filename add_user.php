<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = $_POST['FirstName'];
    $last = $_POST['LastName'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    $sql = "INSERT INTO users (FirstName, LastName, email, password, role) 
            VALUES ('$first', '$last', '$email', '$password', '$role')";

    if (mysqli_query($conn, $sql)) {
        $successMessage = "✅ User registered!";
    }
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-4">
    <a href="dashboard.php" class="btn btn-sm btn-outline-primary mb-3">
        &larr; Dashboard
    </a>

    <?php if (!empty($successMessage)): ?>
        <div id="success-alert" class="alert alert-success">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h4>Register New User</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" name="FirstName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="LastName" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Register</button>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript to auto-hide and optionally refresh -->
<script>
    const alertBox = document.getElementById('success-alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.display = 'none';
        }, 3000); // Hide after 3 seconds

        setTimeout(() => {
            window.location.reload(); // Optional: Refresh page after 3 seconds
        }, 3000);
    }
</script>
