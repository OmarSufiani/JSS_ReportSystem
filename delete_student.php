<?php
session_start();
include 'db.php';

$success = '';
$error = '';

// Handle deletion
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $deleteSql = "DELETE FROM users WHERE id = $deleteId";

    if (mysqli_query($conn, $deleteSql)) {
        $success = "User deleted successfully.";
    } else {
        $error = "Error deleting user.";
    }
}

// Fetch all users
$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

<a href="dashboard.php" class="btn btn-outline-primary mb-4 btn-sm">&larr; Back to Dashboard</a>

<h3 class="mb-3">All Registered Users</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Created At</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['FirstName']) ?></td>
                <td><?= htmlspecialchars($row['LastName']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= $row['role'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="delete_student.php?delete_id=<?= $row['id'] ?>"
                       onclick="return confirm('Are you sure you want to delete this user?');"
                       class="btn btn-sm btn-danger">🗑 Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
