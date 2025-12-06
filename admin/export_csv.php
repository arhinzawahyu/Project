<?php
require_once '../includes/functions.php';
if (!is_admin_logged_in()) redirect('login.php');
require_once '../config/db.php';

$questions = $pdo->query("SELECT id, question_text FROM questions ORDER BY id")->fetchAll();
$responses = $pdo->query("SELECT id, ip_address, submitted_at FROM responses ORDER BY id")->fetchAll();

$output = fopen('php://output', 'w');

// Header
$header = ['Response ID', 'IP Address', 'Submitted At'];
foreach ($questions as $q) {
    $header[] = 'Q' . $q['id'];
}
fputcsv($output, $header);

// Data
foreach ($responses as $resp) {
    $row = [$resp['id'], $resp['ip_address'], $resp['submitted_at']];
    
    // Ambil jawaban untuk respons ini
    $answers = $pdo->prepare("SELECT question_id, answer_value FROM answers WHERE response_id = ? ORDER BY question_id");
    $answers->execute([$resp['id']]);
    $ans_assoc = [];
    while ($a = $answers->fetch()) {
        $ans_assoc[$a['question_id']] = $a['answer_value'];
    }
    
    foreach ($questions as $q) {
        $row[] = $ans_assoc[$q['id']] ?? '';
    }
    
    fputcsv($output, $row);
}

fclose($output);
exit;
?>