<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = $success = '';

// Fetch school and class options
$schools = $conn->query("SELECT id, school_name FROM school ORDER BY school_name");
$classes = $conn->query("SELECT id, name FROM class ORDER BY name");

// Handle form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and collect form data
    $firstname = $conn->real_escape_string(trim($_POST['firstname']));
    $lastname = $conn->real_escape_string(trim($_POST['lastname']));
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $guardian_name = $conn->real_escape_string(trim($_POST['guardian_name']));
    $guardian_phone = $conn->real_escape_string(trim($_POST['guardian_phone']));
    $address = $conn->real_escape_string(trim($_POST['address']));
    $status = $_POST['status'];
    $admno = $conn->real_escape_string(trim($_POST['admno']));
    $school_id = (int) $_POST['school_id'];
    $class_id = (int) $_POST['class_id'];

    // Handle photo upload
    $photo_path = '';
    $targetDir = "uploads/students/";
    if (!empty($_FILES['photo']['name'])) {
        $fileName = time() . '_' . basename($_FILES["photo"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        // Validate file type
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowedTypes)) {
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFilePath)) {
                $photo_path = $targetFilePath;
            } else {
                $error = "Failed to upload photo.";
            }
        } else {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    // Insert only if no upload error
    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO student (firstname, lastname, gender, dob, guardian_name, guardian_phone, address, status, photo, admno, school_id, class_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssii", $firstname, $lastname, $gender, $dob, $guardian_name, $guardian_phone, $address, $status, $photo_path, $admno, $school_id, $class_id);

        if ($stmt->execute()) {
            $success = "Student added successfully.";
        } else {
            $error = "Database error: " . $stmt->error;
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Add Student</h4>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <input type="text" name="firstname" class="form-control" placeholder="First Name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input type="text" name="lastname" class="form-control" placeholder="Last Name" required>
                    </div>
                </div>

                <div class="mb-3">
                    <select name="gender" class="form-select" required>
                        <option value="">-- Select Gender --</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Date of Birth:</label>
                    <input type="date" name="dob" class="form-control" required>
                </div>

                <div class="mb-3">
                    <input type="text" name="guardian_name" class="form-control" placeholder="Guardian Name" required>
                </div>

                <div class="mb-3">
                    <input type="text" name="guardian_phone" class="form-control" placeholder="Guardian Phone" required>
                </div>

                <div class="mb-3">
                    <textarea name="address" class="form-control" placeholder="Address" required></textarea>
                </div>

                <div class="mb-3">
                    <select name="status" class="form-select" required>
                        <option value="">-- Select Status --</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="transferred">Transferred</option>
                        <option value="graduated">Graduated</option>
                    </select>
                </div>

                <div class="mb-3">
                    <input type="text" name="admno" class="form-control" placeholder="Admission Number" required>
                </div>

                <div class="mb-3">
                    <select name="school_id" class="form-select" required>
                        <option value="">-- Select School --</option>
                        <?php while($row = $schools->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['school_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <select name="class_id" class="form-select" required>
                        <option value="">-- Select Class --</option>
                        <?php while($row = $classes->fetch_assoc()): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Upload Photo:</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                </div>

                <button type="submit" class="btn btn-success">Add Student</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
