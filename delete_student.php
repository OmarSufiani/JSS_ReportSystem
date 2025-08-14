<?php
session_start();
include 'db.php';

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM student WHERE id = $delete_id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch students
$result = mysqli_query($conn, "SELECT * FROM student ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3 class="mb-4">Student List</h3>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Adm No</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>DOB</th>
                    <th>Guardian</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>School ID</th>
                    <th>Class ID</th>
                    <th>Photo</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($student = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?= $student['id'] ?></td>
                        <td><?= htmlspecialchars($student['admno']) ?></td>
                        <td><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?></td>
                        <td><?= $student['gender'] ?></td>
                        <td><?= $student['dob'] ?></td>
                        <td><?= htmlspecialchars($student['guardian_name']) ?></td>
                        <td><?= htmlspecialchars($student['guardian_phone']) ?></td>
                        <td><?= htmlspecialchars($student['address']) ?></td>
                        <td><?= $student['status'] ?></td>
                        <td><?= $student['school_id'] ?></td>
                        <td><?= $student['class_id'] ?></td>
                        <td>
                            <?php if (!empty($student['photo'])): ?>
                                <img src="uploads/students/<?= $student['photo'] ?>" width="50" height="50" alt="Photo">
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?delete_id=<?= $student['id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
