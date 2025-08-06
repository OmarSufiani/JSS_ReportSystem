<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initial display logic remains
$form_data = $_SESSION['form_data'] ?? ['admno' => '', 'subject_id' => '', 'term' => '', 'exam_type' => ''];
$results = $_SESSION['result'] ?? [];
$errorMsg = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Score Lookup</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <h3 class="mb-4 text-center">Check Student Score</h3>
    <div class="mb-3">
        <a href="dashboard.php" class="btn btn-outline-primary">&larr; Back to Dashboard</a>
    </div>

    <?php if ($errorMsg): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($errorMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- SEARCH FILTER -->
    <div class="mb-4">
        <input type="text" class="form-control" id="searchInput" placeholder="Search by name, class, subject, term, exam, score, performance...">
    </div>

    <!-- RESULTS TABLE (AJAX Target) -->
    <div id="resultsContainer">
        <!-- Results will load here -->
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const resultsContainer = document.getElementById("resultsContainer");

    function fetchResults(query = '') {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "fetch_results.php?q=" + encodeURIComponent(query), true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                resultsContainer.innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    // Load initial results
    fetchResults();

    // Live search event
    searchInput.addEventListener("keyup", function () {
        const query = searchInput.value.trim();
        fetchResults(query);
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
