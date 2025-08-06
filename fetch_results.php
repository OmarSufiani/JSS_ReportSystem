<?php
include 'db.php';

$keyword = $_GET['q'] ?? '';
$keyword = mysqli_real_escape_string($conn, $keyword);

$query = "
    SELECT 
        s.name AS student_name,
        c.name AS class_name,
        sub.name AS subject_name,
        sc.Score,
        sc.performance,
        sc.tcomments,
        sc.term,
        sc.exam_type
    FROM student s
    JOIN score sc ON s.id = sc.std_id
    JOIN class c ON s.class_id = c.id
    JOIN subject sub ON sc.subject_id = sub.id
    WHERE 
        s.name LIKE '%$keyword%' OR
        c.name LIKE '%$keyword%' OR
        sub.name LIKE '%$keyword%' OR
        sc.term LIKE '%$keyword%' OR
        sc.exam_type LIKE '%$keyword%' OR
        sc.Score LIKE '%$keyword%' OR
        sc.performance LIKE '%$keyword%'
    ORDER BY s.name ASC
";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered table-striped align-middle'>
            <thead class='table-dark'>
                <tr>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Term</th>
                    <th>Exam Type</th>
                    <th>Score</th>
                    <th>Performance</th>
                    <th>Teacher's Comment</th>
                </tr>
            </thead>
            <tbody>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>" . htmlspecialchars($row['student_name']) . "</td>
                <td>" . htmlspecialchars($row['class_name']) . "</td>
                <td>" . htmlspecialchars($row['subject_name']) . "</td>
                <td>" . htmlspecialchars($row['term']) . "</td>
                <td>" . htmlspecialchars($row['exam_type']) . "</td>
                <td>" . htmlspecialchars($row['Score']) . "</td>
                <td>" . htmlspecialchars($row['performance']) . "</td>
                <td>" . htmlspecialchars($row['tcomments']) . "</td>
              </tr>";
    }

    echo "</tbody></table></div>";
} else {
    echo "<div class='alert alert-warning'>No results found.</div>";
}
?>
