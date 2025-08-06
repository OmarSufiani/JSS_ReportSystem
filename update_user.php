<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = $_POST['FirstName'];
    $last = $_POST['LastName'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql = "UPDATE users SET FirstName='$first', LastName='$last', email='$email', password='$password', role='$role' WHERE id=$user_id";
    } else {
        $sql = "UPDATE users SET FirstName='$first', LastName='$last', email='$email', role='$role' WHERE id=$user_id";
    }

    if (mysqli_query($conn, $sql)) {
        $message = '<div class="alert alert-success">User details updated!</div>';
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);
    } else {
        $message = '<div class="alert alert-danger">Error: ' . mysqli_error($conn) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <a href="dashboard.php" class="btn btn-outline-primary mb-3">&larr; Back to Dashboard</a>

    <div class="card">
        <div class="card-header">
            <h4>Edit Profile</h4>
        </div>
        <div class="card-body">
            <?= $message ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="FirstName" class="form-control" value="<?= htmlspecialchars($user['FirstName']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="LastName" class="form-control" value="<?= htmlspecialchars($user['LastName']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Details</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
