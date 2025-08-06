<?php
if (isset($_GET['file'])) {
    $file = basename($_GET['file']); // sanitize input to avoid path traversal
    $filePath = 'uploads/' . $file;

    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            // Redirect back to the dashboard after deletion
            header("Location: admin.php"); // adjust this if needed
            exit();
        } else {
            echo "Error deleting file.";
        }
    } else {
        echo "File does not exist.";
    }
} else {
    echo "No file specified.";
}
?>
