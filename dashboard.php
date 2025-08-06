<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Ramzy School System</title>
    <!-- ✅ Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card mx-auto shadow-lg" style="max-width: 600px;">
        <div class="card-body text-center">
            <!-- Small Bootstrap Back Button -->
            <div class="text-start mb-3">
                <a href="logout.php" class="btn btn-outline-primary btn-sm">
                    &larr; Logout
                </a>
            </div>

            <h2 class="mb-4">📘 School Dashboard</h2>
<div class="d-grid gap-3">
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <a class="btn btn-primary" href="add_student.php">➕ Add Student</a>
        <a class="btn btn-primary" href="add_teacher.php">➕ Add Teacher</a>
        <a class="btn btn-primary" href="add_subject.php">➕ Add Subject</a>
        <a class="btn btn-primary" href="add_class.php">➕ Add Class</a>
        <a class="btn btn-primary" href="add_user.php">➕ Add_User</a>
        <a class="btn btn-primary" href="tsubject_class.php">➕ Teacher_subject/class</a>
        <a class="btn btn-primary" href="delete_student.php">➕ Delete_student</a>
    <?php endif; ?>

    <!-- These buttons are visible to all users -->
    <a class="btn btn-primary" href="add_score.php">➕ Add Score</a>
    <a class="btn btn-primary" href="upload.php">➕ Upload</a>
    <a class="btn btn-primary" href="csv.php">➕ View Report</a>
    <a class="btn btn-primary" href="report_form.php">➕ Download Report Form</a>
    <a class="btn btn-primary" href="student_subject.php">➕ Add student_subject</a>
    

    <!-- Settings visible to all -->
    <a class="btn btn-secondary" href="update_user.php">⚙️ Settings</a>
</div>

        </div>
    </div>
</div>

<!-- ✅ Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
