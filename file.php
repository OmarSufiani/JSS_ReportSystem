<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$uploadDir = 'uploads/';
$files = scandir($uploadDir);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard - File Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">📁 Admin Dashboard - File Manager</h1>
    <div>
      <a href="dashboard.php" class="btn btn-outline-primary me-2">← Back to Dashboard</a>
      <a href="logout.php" class="btn btn-warning">Logout</a>
    </div>
  </div>

  <h3 class="mb-3">Uploaded Files</h3>

  <?php if (count($files) <= 2): ?>
    <div class="alert alert-info">No files uploaded yet.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-secondary">
          <tr>
            <th>#</th>
            <th>File Name</th>
            <th>Size (KB)</th>
            <th>Download</th>
            <th>Delete</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $i = 1;
        foreach ($files as $file) {
          if ($file === '.' || $file === '..') continue;
          $filePath = $uploadDir . $file;
          $fileSize = round(filesize($filePath) / 1024, 2);
          echo "<tr>
                  <td>{$i}</td>
                  <td>{$file}</td>
                  <td>{$fileSize}</td>
                  <td><a class='btn btn-sm btn-outline-primary' href='{$filePath}' download>Download</a></td>
                  <td>
                    <form action='delete.php' method='get' onsubmit=\"return confirm('Are you sure you want to delete this file?');\">
                      <input type='hidden' name='file' value='" . htmlspecialchars($file, ENT_QUOTES) . "'>
                      <button type='submit' class='btn btn-sm btn-danger'>Delete</button>
                    </form>
                  </td>
                </tr>";
          $i++;
        }
        ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
