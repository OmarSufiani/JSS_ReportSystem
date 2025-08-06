<?php 
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Optional: Example logic if you're handling messages through GET or session
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
if (isset($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generate Report Card</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4 text-primary">Generate Report Card</h2>

    <a href="dashboard.php" class="btn btn-outline-primary mb-3">&larr; Back to Dashboard</a>

    <form method="GET" action="generate_report_card.php" class="card p-4 shadow-sm">
        
        <!-- 🟢 Success and 🔴 Error messages here -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger text-center"><?= $error ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success text-center"><?= $success ?></div>
        <?php endif; ?>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="term" class="form-label">Select Term:</label>
                <select name="term" id="term" class="form-select" required>
                    <option value="Term 1">Term 1</option>
                    <option value="Term 2">Term 2</option>
                    <option value="Term 3">Term 3</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="year" class="form-label">Select Year:</label>
                <input type="number" name="year" id="year" class="form-control" value="<?= date('Y'); ?>" required>
            </div>
        </div>

        <fieldset class="border rounded p-3 mb-3">
            <legend class="float-none w-auto px-2">Select Report Type</legend>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="student_id" class="form-label">Generate by Student:</label>
                    <select name="id" id="student_id" class="form-select">
                        <option value="">-- Select Student --</option>
                        <?php
                        $result = $conn->query("SELECT id, name, admno FROM student ORDER BY name ASC");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['name']} (Adm: {$row['admno']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="class_id" class="form-label">Or Generate for Whole Class:</label>
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">-- Select Class --</option>
                        <?php
                        $result = $conn->query("
                            SELECT DISTINCT c.id AS class_id, c.name AS class_name
                            FROM student s
                            JOIN class c ON s.class_id = c.id
                            ORDER BY c.name ASC
                        ");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['class_id']}'>{$row['class_name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </fieldset>

        <button type="submit" class="btn btn-primary w-100">Generate Report</button>
    </form>
</div>

<!-- Bootstrap JS (optional if using dropdowns or modals) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
