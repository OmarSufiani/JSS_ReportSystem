<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generateStudentReport($student_id, $term, $year, $conn) {
    // Fetch student info
    $stu_q = $conn->prepare("SELECT id, firstname, lastname, admno, class_id, school_id FROM student WHERE id = ?");
    if (!$stu_q) {
        die("Prepare failed (student query): " . $conn->error);
    }
    $stu_q->bind_param("i", $student_id);
    $stu_q->execute();
    $stu_q->bind_result($id, $firstname, $lastname, $admno, $class_id, $school_id);
    if (!$stu_q->fetch()) {
        $stu_q->close();
        return false;
    }
    $stu_q->close();

    $student = [
        'id' => $id,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'admno' => $admno,
        'class_id' => $class_id,
        'school_id' => $school_id
    ];

    // Get class and school name
    // NOTE: school column is 'school_name' per your DB dump
    $class_q = $conn->prepare("SELECT c.name AS class_name, sch.school_name AS school_name 
                               FROM class c 
                               JOIN school sch ON c.school_id = sch.id
                               WHERE c.id = ?");
    if (!$class_q) {
        die("Prepare failed (class query): " . $conn->error);
    }
    $class_q->bind_param("i", $class_id);
    $class_q->execute();
    $class = $class_q->get_result()->fetch_assoc();
    $class_q->close();

    $class_name = $class['class_name'] ?? 'Unknown';
    $school_name = $class['school_name'] ?? 'Unknown School';

    // Get scores
    $sql = "
        SELECT s.id AS subject_id, s.name AS subject_name, sc.Score, sc.performance, sc.tcomments
        FROM score AS sc
        JOIN subject AS s ON sc.subject_id = s.id
        WHERE sc.std_id = ? AND sc.term = ? AND YEAR(sc.created_at) = ? AND sc.school_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed (scores query): " . $conn->error);
    }
    $stmt->bind_param("isii", $student_id, $term, $year, $school_id);
    $stmt->execute();
    $scores = $stmt->get_result();
    $stmt->close();

    if ($scores->num_rows === 0) return false;

    $studentScores = [];
    while ($row = $scores->fetch_assoc()) {
        $studentScores[] = $row;
    }

    // Count total students in class
    $stmtCount = $conn->prepare("SELECT COUNT(*) AS cnt FROM student WHERE class_id = ?");
    if (!$stmtCount) {
        die("Prepare failed (count students query): " . $conn->error);
    }
    $stmtCount->bind_param("i", $class_id);
    $stmtCount->execute();
    $countResult = $stmtCount->get_result()->fetch_assoc();
    $stmtCount->close();
    $totalStudents = $countResult['cnt'] ?? 0;

    // Subject Ranks
    $subjectRanks = [];
    foreach ($studentScores as $scoreEntry) {
        $subject_id = $scoreEntry['subject_id'];

        $rankSql = "
            SELECT std_id, Score FROM score
            WHERE subject_id = ? AND term = ? AND YEAR(created_at) = ? 
            AND std_id IN (SELECT id FROM student WHERE class_id = ?) AND school_id = ?
            ORDER BY Score DESC";
        $rankStmt = $conn->prepare($rankSql);
        if (!$rankStmt) {
            die("Prepare failed (rank query): " . $conn->error);
        }
        $rankStmt->bind_param("isiii", $subject_id, $term, $year, $class_id, $school_id);
        $rankStmt->execute();
        $rankResult = $rankStmt->get_result();
        $rankStmt->close();

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

    // Average score
    $avgScoreSql = "SELECT AVG(Score) as avgScore FROM score WHERE std_id = ? AND term = ? AND YEAR(created_at) = ? AND school_id = ?";
    $avgStmt = $conn->prepare($avgScoreSql);
    if (!$avgStmt) {
        die("Prepare failed (avg score query): " . $conn->error);
    }
    $avgStmt->bind_param("isii", $student_id, $term, $year, $school_id);
    $avgStmt->execute();
    $avgResult = $avgStmt->get_result()->fetch_assoc();
    $avgStmt->close();
    $studentAvg = floatval($avgResult['avgScore'] ?? 0);

    // Class ranking
    $classAvgSql = "
        SELECT std_id, AVG(Score) as avgScore FROM score 
        WHERE term = ? AND YEAR(created_at) = ? AND std_id IN (SELECT id FROM student WHERE class_id = ?)
        AND school_id = ?
        GROUP BY std_id
        ORDER BY avgScore DESC";
    $classAvgStmt = $conn->prepare($classAvgSql);
    if (!$classAvgStmt) {
        die("Prepare failed (class rank query): " . $conn->error);
    }
    $classAvgStmt->bind_param("siii", $term, $year, $class_id, $school_id);
    $classAvgStmt->execute();
    $classAvgResult = $classAvgStmt->get_result();
    $classAvgStmt->close();

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

    // School ranking
    $schoolAvgSql = "
        SELECT std_id, AVG(Score) as avgScore FROM score 
        WHERE term = ? AND YEAR(created_at) = ? 
        AND std_id IN (SELECT id FROM student WHERE school_id = ?)
        GROUP BY std_id
        ORDER BY avgScore DESC";
    $schoolAvgStmt = $conn->prepare($schoolAvgSql);
    if (!$schoolAvgStmt) {
        die("Prepare failed (school rank query): " . $conn->error);
    }
    $schoolAvgStmt->bind_param("sii", $term, $year, $school_id);
    $schoolAvgStmt->execute();
    $schoolAvgResult = $schoolAvgStmt->get_result();
    $schoolAvgStmt->close();

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

    // Comments
    if ($studentAvg >= 70) $overallComment = "Excellent";
    elseif ($studentAvg >= 60) $overallComment = "Good";
    elseif ($studentAvg >= 50) $overallComment = "Average";
    else $overallComment = "Put more effort";

    // HTML for PDF
    $html = "<h1 style='text-align: center;'>" . htmlspecialchars($school_name, ENT_QUOTES) . "</h1>";
    $html .= "<h2 style='text-align: center;'>STUDENT REPORT FORM</h2>";
    $html .= "<p><strong>Student:</strong> " . htmlspecialchars($student['firstname'] . ' ' . $student['lastname'], ENT_QUOTES) . " (Adm: " . htmlspecialchars($student['admno'], ENT_QUOTES) . ")</p>";
    $html .= "<p><strong>Class:</strong> " . htmlspecialchars($class_name, ENT_QUOTES) . "</p>";
    $html .= "<p><strong>Term:</strong> " . htmlspecialchars($term, ENT_QUOTES) . ", <strong>Year:</strong> " . htmlspecialchars($year, ENT_QUOTES) . "</p>";
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
                    <td>" . htmlspecialchars($row['subject_name'], ENT_QUOTES) . "</td>
                    <td>" . htmlspecialchars($row['Score'], ENT_QUOTES) . "</td>
                    <td>" . htmlspecialchars($row['performance'], ENT_QUOTES) . "</td>
                    <td>" . htmlspecialchars($row['tcomments'], ENT_QUOTES) . "</td>
                    <td>{$rankInfo['rank']}/{$rankInfo['total']}</td>
                  </tr>";
    }

    $html .= "</table>";
    $html .= "<h3>Class Position: {$studentClassRank} out of {$classCount}</h3>";
    $html .= "<h3>School Position: {$studentSchoolRank} out of {$schoolCount}</h3>";
     $html .= "<br>";
    $html .= "<h3>Teacher's Comment:<br> {$overallComment}</h3>";
    $html .= "<br>";

                // Grading system table
                $html .= '
                <h3>Grading System & Performance Comments</h3>
                <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th>Performance</th>
                            <th>Meaning</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>M.E</td>
                            <td>Meeting Expectation</td>
                        </tr>
                        <tr>
                            <td>A.E</td>
                            <td>Approaching Expectation</td>
                        </tr>
                        <tr>
                            <td>B.E</td>
                            <td>Below Expectation</td>
                        </tr>
                        <tr>
                            <td>E.E</td>
                            <td>Exceeding Expectation</td>
                        </tr>
                    </tbody>
                </table>';

   // Pull per-exam average for this student across all time, ordered oldest->newest
    $histSql = "
        SELECT DATE(sc.created_at) AS exam_date,
               sc.term,
               COALESCE(sc.exam_type, 'Exam') AS exam_type,
               ROUND(AVG(sc.Score), 2) AS avg_score
        FROM score sc
        WHERE sc.std_id = ? AND sc.school_id = ?
        GROUP BY DATE(sc.created_at), sc.term, sc.exam_type
        ORDER BY exam_date ASC
    ";
    $histStmt = $conn->prepare($histSql);
    if (!$histStmt) {
        die('Prepare failed (history query): ' . $conn->error);
    }
    $histStmt->bind_param('ii', $student_id, $school_id);
    $histStmt->execute();
    $histRes = $histStmt->get_result();
    $histStmt->close();

    $exams = [];
    while ($h = $histRes->fetch_assoc()) {
        $exams[] = $h; // [exam_date, term, exam_type, avg_score]
    }

    if (count($exams) >= 1) {
        // Compute VAP = last_avg - first_avg
        $firstAvg = floatval($exams[0]['avg_score']);
        $lastAvg  = floatval($exams[count($exams)-1]['avg_score']);
        $vap      = $lastAvg - $firstAvg;

        $vapText = 'No change from the first exam.';
        if ($vap > 0)  $vapText = 'Improving by +' . number_format($vap, 2) . ' points (vs first exam).';
        if ($vap < 0)  $vapText = 'Declining by ' . number_format(abs($vap), 2) . ' points (vs first exam).';

        // Build a simple inline SVG line chart (Dompdf-safe)
        // Chart area
        $svgW = 700;    // width
        $svgH = 260;    // height
        $padL = 50;     // left padding for y-axis labels
        $padR = 10;
        $padT = 20;
        $padB = 40;

        $plotW = $svgW - $padL - $padR;
        $plotH = $svgH - $padT - $padB;

        // X positions: equally spaced by exam index
        $n = count($exams);
        $xStep = ($n > 1) ? ($plotW / ($n - 1)) : 0;

        // Y scale from min/max avg scores with a small margin
        $scores = array_map(function($e){ return floatval($e['avg_score']); }, $exams);
        $minS = min($scores);
        $maxS = max($scores);
        if ($minS === $maxS) { $minS = max(0, $minS - 5); $maxS = $maxS + 5; }
        $minS = floor($minS);
        $maxS = ceil($maxS);

        // Helper to scale Y (higher score -> higher on chart)
        $scaleY = function($val) use ($minS, $maxS, $padT, $plotH) {
            if ($maxS == $minS) return $padT + $plotH/2;
            $t = ($val - $minS) / ($maxS - $minS);  // 0..1
            // invert because SVG y grows downward
            return $padT + (1 - $t) * $plotH;
        };

        // Build polyline points string and ticks/labels
        $poly = '';
        $circles = '';
        $xLabels = '';
        for ($i = 0; $i < $n; $i++) {
            $x = $padL + ($n > 1 ? $i * $xStep : $plotW/2);
            $y = $scaleY($scores[$i]);
            $poly   .= ($i ? ' ' : '') . $x . ',' . $y;
            $circles .= '<circle cx="'. $x .'" cy="'. $y .'" r="3" />';
            // Use short label: index & year (or date)
            $label = htmlspecialchars(substr($exams[$i]['exam_date'], 0, 10) . ' (' . $exams[$i]['term'] . ', ' . $exams[$i]['exam_type'] . ')', ENT_QUOTES);
            $xLabels .= '<text x="'. $x .'" y="'. ($svgH - 15) .'" font-size="10" text-anchor="middle" transform="rotate(0 '. $x .' '. ($svgH - 15) .')">'. $label .'</text>';
        }

        // Y-axis ticks (every 10 between minS and maxS, at least 3 ticks)
        $ticks = [];
        $range = max(10, ($maxS - $minS));
        $step  = ($range <= 30) ? 10 : 20;
        $ytick = ceil($minS / $step) * $step;
        while ($ytick <= $maxS) {
            $ticks[] = $ytick;
            $ytick += $step;
        }
        if (count($ticks) < 3) {
            $ticks = [$minS, ($minS+$maxS)/2, $maxS];
        }

        $yGrid = '';
        $yLabels = '';
        foreach ($ticks as $t) {
            $yy = $scaleY($t);
            $yGrid   .= '<line x1="'. $padL .'" y1="'. $yy .'" x2="'. ($svgW - $padR) .'" y2="'. $yy .'" stroke="#ddd" stroke-width="1" />';
            $yLabels .= '<text x="'. ($padL - 8) .'" y="'. ($yy + 4) .'" font-size="10" text-anchor="end">'. htmlspecialchars((string)$t, ENT_QUOTES) .'</text>';
        }

        // Compose SVG
        $svg = '
        <svg width="'. $svgW .'" height="'. $svgH .'" xmlns="http://www.w3.org/2000/svg">
            <rect x="0" y="0" width="'. $svgW .'" height="'. $svgH .'" fill="#ffffff" />
            <!-- Axes -->
            <line x1="'. $padL .'" y1="'. $padT .'" x2="'. $padL .'" y2="'. ($svgH - $padB) .'" stroke="#000" stroke-width="1"/>
            <line x1="'. $padL .'" y1="'. ($svgH - $padB) .'" x2="'. ($svgW - $padR) .'" y2="'. ($svgH - $padB) .'" stroke="#000" stroke-width="1"/>
            <!-- Grid & Y labels -->
            '. $yGrid .'
            '. $yLabels .'
            <!-- X labels -->
            '. $xLabels .'
            <!-- Line -->
            <polyline fill="none" stroke="#0077cc" stroke-width="2" points="'. $poly .'" />
            '. $circles .'
            <!-- Titles -->
            <text x="'. ($svgW/2) .'" y="14" font-size="14" text-anchor="middle">Performance Trend (Average Score per Exam)</text>
            <text x="'. ($svgW/2) .'" y="'. ($svgH - 4) .'" font-size="11" text-anchor="middle">Exams (date • term • type)</text>
            <text x="10" y="14" font-size="11">Scores</text>
        </svg>';

        $html .= '<br><h3>Performance Trend & VAP (Average Score)</h3>';
        $html .= '<p><strong>VAP:</strong> ' . htmlspecialchars($vapText, ENT_QUOTES) . '</p>';

        // Embed SVG directly (Dompdf supports inline SVG)
        $html .= $svg;

        // Also show a compact table of the exam averages
        $html .= '<br><table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width:100%;">';
        $html .= '<thead><tr style="background-color:#f2f2f2;"><th>#</th><th>Date</th><th>Term</th><th>Exam Type</th><th>Avg Score</th></tr></thead><tbody>';
        foreach ($exams as $idx => $e) {
            $html .= '<tr>';
            $html .= '<td>'. ($idx+1) .'</td>';
            $html .= '<td>'. htmlspecialchars($e['exam_date'], ENT_QUOTES) .'</td>';
            $html .= '<td>'. htmlspecialchars($e['term'], ENT_QUOTES) .'</td>';
            $html .= '<td>'. htmlspecialchars($e['exam_type'], ENT_QUOTES) .'</td>';
            $html .= '<td>'. htmlspecialchars(number_format((float)$e['avg_score'], 2), ENT_QUOTES) .'</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
    } else {
        // No history beyond current report’s scores
        $html .= '<br><h3>Performance Trend & VAP (Average Score)</h3>';
        $html .= '<p>No prior exam history found for this student to plot a trend or compute VAP.</p>';
    }




    $html .= '<div style="position: absolute; bottom: 1in; width: 100%; display: flex; justify-content: space-between;">
                <p>Teacher\'s Name___________________________________Signature_______________________________</p>
                <p>Principal\'s Name__________________________________Signature_______________________________ </p>
              </div>';

    // PDF generation
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = sys_get_temp_dir() . "/report_{$student_id}_{$term}_{$year}.pdf";
    file_put_contents($filename, $dompdf->output());

    return $filename;
}

// --- Handle GET request ---
$currentYear = date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && (isset($_GET['id']) || isset($_GET['class_id']))) {
    $term = $_GET['term'] ?? 'Term 1';
    $year = intval($_GET['year'] ?? $currentYear);

    if (isset($_GET['id']) && $_GET['id'] !== "") {
        $student_id = intval($_GET['id']);
        $filename = generateStudentReport($student_id, $term, $year, $conn);

        if (!$filename || !file_exists($filename)) {
            die("No scores found for the selected student, term, and year.");
        }

        if (ob_get_level()) ob_end_clean();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filename));
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);

        readfile($filename);
        unlink($filename);
        exit;
    }

    if (isset($_GET['class_id']) && $_GET['class_id'] !== "") {
        $class_id = intval($_GET['class_id']);
        $stmt = $conn->prepare("SELECT id FROM student WHERE class_id = ?");
        if (!$stmt) {
            die("Prepare failed (class students query): " . $conn->error);
        }
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        $students = $stmt->get_result();
        $stmt->close();

        if (!class_exists('ZipArchive')) {
            die('ZipArchive PHP extension is not enabled.');
        }

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

        if (ob_get_level()) ob_end_clean();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipFilename) . '"');
        header('Content-Length: ' . filesize($zipFilename));
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);

        flush();
        readfile($zipFilename);
        unlink($zipFilename);

        foreach ($generatedFiles as $file) {
            if (file_exists($file)) unlink($file);
        }
        exit;
    }
}
