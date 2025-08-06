<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// --- Dropdown data for UI ---
$students = $conn->query("SELECT id, name, admno FROM student ORDER BY name");

$classes = $conn->query("
    SELECT DISTINCT c.id AS class_id, c.name AS class_name
    FROM student s
    JOIN class c ON s.class_id = c.id
    ORDER BY c.name
");

$currentYear = date("Y");
$years = range($currentYear, $currentYear - 5);

function generateStudentReport($student_id, $term, $year, $conn) {
    $stu_q = $conn->prepare("SELECT * FROM student WHERE id = ?");
    $stu_q->bind_param("i", $student_id);
    $stu_q->execute();
    $student = $stu_q->get_result()->fetch_assoc();
    if (!$student) return false;

    $class_q = $conn->prepare("SELECT name FROM class WHERE id = ?");
    $class_q->bind_param("i", $student['class_id']);
    $class_q->execute();
    $class = $class_q->get_result()->fetch_assoc();
    $class_name = $class ? $class['name'] : 'Unknown';

    $sql = "
        SELECT s.id AS subject_id, s.name AS subject_name, sc.Score, sc.performance, sc.tcomments
        FROM score AS sc
        JOIN subject AS s ON sc.subject_id = s.id
        WHERE sc.std_id = ? AND sc.term = ? AND YEAR(sc.created_at) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $student_id, $term, $year);
    $stmt->execute();
    $scores = $stmt->get_result();

    if ($scores->num_rows === 0) return null;

    $studentScores = [];
    while ($row = $scores->fetch_assoc()) {
        $studentScores[] = $row;
    }

    $class_id = $student['class_id'];
    $stmtCount = $conn->prepare("SELECT COUNT(*) AS cnt FROM student WHERE class_id = ?");
    $stmtCount->bind_param("i", $class_id);
    $stmtCount->execute();
    $totalStudents = $stmtCount->get_result()->fetch_assoc()['cnt'];

    $subjectRanks = [];
    foreach ($studentScores as $scoreEntry) {
        $subject_id = $scoreEntry['subject_id'];

        $rankSql = "
            SELECT std_id, Score FROM score
            WHERE subject_id = ? AND term = ? AND YEAR(created_at) = ? 
            AND std_id IN (SELECT id FROM student WHERE class_id = ?)
            ORDER BY Score DESC";
        $rankStmt = $conn->prepare($rankSql);
        $rankStmt->bind_param("isii", $subject_id, $term, $year, $class_id);
        $rankStmt->execute();
        $rankResult = $rankStmt->get_result();

        $rank = 1;
        $prevScore = null;
        $studentRank = null;
        $count = 0;

        while ($r = $rankResult->fetch_assoc()) {
            $count++;
            if ($prevScore !== null && $r['Score'] < $prevScore) {
                $rank = $count;
            }
            if ($r['std_id'] == $student_id) {
                $studentRank = $rank;
                break;
            }
            $prevScore = $r['Score'];
        }

        $subjectRanks[$subject_id] = [
            'rank' => $studentRank ?? $count,
            'total' => $count
        ];
    }

    $avgScoreSql = "SELECT AVG(Score) as avgScore FROM score WHERE std_id = ? AND term = ? AND YEAR(created_at) = ?";
    $avgStmt = $conn->prepare($avgScoreSql);
    $avgStmt->bind_param("isi", $student_id, $term, $year);
    $avgStmt->execute();
    $avgResult = $avgStmt->get_result()->fetch_assoc();
    $studentAvg = floatval($avgResult['avgScore']);

    $classAvgSql = "
        SELECT std_id, AVG(Score) as avgScore FROM score 
        WHERE term = ? AND YEAR(created_at) = ? AND std_id IN (SELECT id FROM student WHERE class_id = ?)
        GROUP BY std_id
        ORDER BY avgScore DESC";
    $classAvgStmt = $conn->prepare($classAvgSql);
    $classAvgStmt->bind_param("sii", $term, $year, $class_id);
    $classAvgStmt->execute();
    $classAvgResult = $classAvgStmt->get_result();

    $studentClassRank = null;
    $classRank = 1;
    $prevClassAvg = null;
    $classCount = 0;

    while ($row = $classAvgResult->fetch_assoc()) {
        $classCount++;
        if ($prevClassAvg !== null && floatval($row['avgScore']) < $prevClassAvg) {
            $classRank = $classCount;
        }
        if ($row['std_id'] == $student_id) {
            $studentClassRank = $classRank;
        }
        $prevClassAvg = floatval($row['avgScore']);
    }
    if ($studentClassRank === null) $studentClassRank = $classCount;

    $schoolAvgSql = "
        SELECT std_id, AVG(Score) as avgScore FROM score 
        WHERE term = ? AND YEAR(created_at) = ?
        GROUP BY std_id
        ORDER BY avgScore DESC";
    $schoolAvgStmt = $conn->prepare($schoolAvgSql);
    $schoolAvgStmt->bind_param("si", $term, $year);
    $schoolAvgStmt->execute();
    $schoolAvgResult = $schoolAvgStmt->get_result();

    $studentSchoolRank = null;
    $schoolRank = 1;
    $prevSchoolAvg = null;
    $schoolCount = 0;

    while ($row = $schoolAvgResult->fetch_assoc()) {
        $schoolCount++;
        if ($prevSchoolAvg !== null && floatval($row['avgScore']) < $prevSchoolAvg) {
            $schoolRank = $schoolCount;
        }
        if ($row['std_id'] == $student_id) {
            $studentSchoolRank = $schoolRank;
        }
        $prevSchoolAvg = floatval($row['avgScore']);
    }
    if ($studentSchoolRank === null) $studentSchoolRank = $schoolCount;

    if ($studentAvg >= 70) $overallComment = "Excellent";
    elseif ($studentAvg >= 60) $overallComment = "Good";
    elseif ($studentAvg >= 50) $overallComment = "Average";
    else $overallComment = "Put more effort";

    $html = "<h2>STUDENT REPORT FORM</h2>";
    $html .= "<p><strong>Student:</strong> {$student['name']} (Adm: {$student['admno']})</p>";
    $html .= "<p><strong>Class:</strong> $class_name</p>";
    $html .= "<p><strong>Term:</strong> $term, <strong>Year:</strong> $year</p>";

    $html .= "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%;'>
                <tr>
                    <th>Subject</th>
                    <th>Score</th>
                    <th>Performance</th>
                    <th>Teacher Comments</th>
                    <th>Subject Rank</th>
                </tr>";

    foreach ($studentScores as $row) {
        $subjectId = $row['subject_id'];
        $rankInfo = $subjectRanks[$subjectId] ?? ['rank' => '-', 'total' => '-'];
        $html .= "<tr>
                    <td>{$row['subject_name']}</td>
                    <td>{$row['Score']}</td>
                    <td>{$row['performance']}</td>
                    <td>{$row['tcomments']}</td>
                    <td>{$rankInfo['rank']}/{$rankInfo['total']}</td>
                  </tr>";
    }

    $html .= "</table>";

    $html .= "<h3>Class Position: {$studentClassRank} out of {$classCount}</h3>";
    $html .= "<h3>Overal Position: {$studentSchoolRank} out of {$schoolCount}</h3>";
    $html .= "<h3>Teacher's Comment:<br> {$overallComment} Performance</h3>";

    $html .= '
    <div style="position: absolute; bottom: 1in; width: 100%; display: flex; justify-content: space-between;">
    <p>Teacher\'s Name___________________________________Signature_______________________________</p>
    <p>Principal\'s Name__________________________________Signature_______________________________ </p>
    </div>';

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "ReportCard_{$student['admno']}_{$term}_{$year}.pdf";
    file_put_contents($filename, $dompdf->output());
    return $filename;
}

// --- Handle GET request for generating reports ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['id']) || isset($_GET['class_id']))) {
    $term = $_GET['term'] ?? 'Term 1';
    $year = intval($_GET['year'] ?? $currentYear);

    if (isset($_GET['id']) && $_GET['id'] !== "") {
        $student_id = intval($_GET['id']);
        $filename = generateStudentReport($student_id, $term, $year, $conn);

        if (!$filename || !file_exists($filename)) {
            die("No scores found for the selected student, term, and year.");
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
        unlink($filename);
        exit;
    }

    if (isset($_GET['class_id']) && $_GET['class_id'] !== "") {
        $class_id = intval($_GET['class_id']);
        $stmt = $conn->prepare("SELECT id FROM student WHERE class_id = ?");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $students = $stmt->get_result();

        $zip = new ZipArchive();
        $zipFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "Class_{$class_id}_Reports_{$term}_{$year}.zip";
        if ($zip->open($zipFilename, ZipArchive::CREATE) !== true) {
            die("Cannot create ZIP file.");
        }

        $reportsGenerated = 0;
        $generatedFiles = [];

        while ($stu = $students->fetch_assoc()) {
            $filename = generateStudentReport($stu['id'], $term, $year, $conn);
            if ($filename && file_exists($filename)) {
                $zip->addFile($filename, basename($filename));
                $generatedFiles[] = $filename;
                $reportsGenerated++;
            }
        }

                        $zip->close();
                if ($reportsGenerated === 0) {
                    if (file_exists($zipFilename)) unlink($zipFilename);
                    die("No scores found for any students in the selected class, term, and year.");
                }

                // Clean output buffer to prevent corruption
                if (ob_get_level()) {
                    ob_end_clean();
                }

                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($zipFilename) . '"');
                header('Content-Length: ' . filesize($zipFilename));

                // Flush system output buffer
                flush();

                // Read and send ZIP file
                readfile($zipFilename);

                // Delete the ZIP after sending
                unlink($zipFilename);

                // Delete all generated PDF files
                foreach ($generatedFiles as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }

                exit;

    }
}
?>
