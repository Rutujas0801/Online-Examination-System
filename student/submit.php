<?php
require_once __DIR__ . '/../includes/functions.php';
$me = require_role('student');

if (!is_post()) redirect('student/index.php');
check_csrf();

$examId = (int)post('exam_id');
$exam = q('SELECT * FROM exams WHERE id = ?', [$examId])->fetch();
if (!$exam) { flash('error', 'Exam not found.'); redirect('student/index.php'); }

// Already submitted: never allow a second attempt or a changed answer
$existing = q('SELECT id FROM results WHERE user_id = ? AND exam_id = ?', [$me['id'], $examId])->fetchColumn();
if ($existing) redirect('student/result.php?id=' . $existing);

$used = q('SELECT TIMESTAMPDIFF(SECOND, started_at, NOW()) FROM exam_starts WHERE user_id = ? AND exam_id = ?', [$me['id'], $examId])->fetchColumn();
if ($used === false || $used === null) { flash('error', 'Please start the exam first.'); redirect('student/instructions.php?id=' . $examId); }

// Answers that arrive long after the timer ended are ignored
$late = (int)$used > $exam['duration_minutes'] * 60 + GRACE_SECONDS;
$given = (!$late && isset($_POST['answer']) && is_array($_POST['answer'])) ? $_POST['answer'] : [];

$questions = q('SELECT q.id, q.correct_option FROM questions q
                JOIN exam_questions eq ON eq.question_id = q.id WHERE eq.exam_id = ?', [$examId])->fetchAll();
$totalQ = count($questions);
if (!$totalQ) { flash('error', 'This exam has no questions.'); redirect('student/index.php'); }

// Marking happens here on the server; only the selected letters come from the browser
$correct = 0;
$rows = [];
foreach ($questions as $row) {
    $pick = $given[$row['id']] ?? null;
    if (!is_string($pick) || !in_array($pick, ['A', 'B', 'C', 'D'], true)) $pick = null;
    $ok = $pick !== null && $pick === $row['correct_option'];
    if ($ok) $correct++;
    $rows[] = [$row['id'], $pick, $ok ? 1 : 0];
}
$score = round($correct * $exam['total_marks'] / $totalQ, 2);
$percentage = round($correct * 100 / $totalQ, 2);

$pdo = db();
$pdo->beginTransaction();
try {
    q('INSERT INTO results (user_id, exam_id, correct_count, total_questions, score, total_marks, percentage)
       VALUES (?, ?, ?, ?, ?, ?, ?)', [$me['id'], $examId, $correct, $totalQ, $score, $exam['total_marks'], $percentage]);
    $resultId = (int)$pdo->lastInsertId();
    foreach ($rows as $r) {
        q('INSERT INTO student_answers (result_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)',
          [$resultId, $r[0], $r[1], $r[2]]);
    }
    $pdo->commit();
} catch (PDOException $ex) {
    $pdo->rollBack();
    // A double click or second tab hits the UNIQUE key: show the stored result
    $existing = q('SELECT id FROM results WHERE user_id = ? AND exam_id = ?', [$me['id'], $examId])->fetchColumn();
    if ($existing) redirect('student/result.php?id=' . $existing);
    flash('error', 'Could not save your result. Please contact the administrator.');
    redirect('student/index.php');
}

if ($late) flash('error', 'Time was already over, so your answers were not counted.');
else flash('success', 'Exam submitted successfully.');
redirect('student/result.php?id=' . $resultId);
