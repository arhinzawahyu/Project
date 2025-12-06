<?php
require_once 'config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$answers = $input['answers'] ?? [];

if (empty($answers)) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada jawaban yang dikirim.']);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO responses (ip_address, user_agent) VALUES (?, ?)");
    $stmt->execute([$ip, $user_agent]);
    $response_id = $pdo->lastInsertId();

    foreach ($answers as $q_id => $value) {
        if (trim($value) !== '') {
            $pdo->prepare("INSERT INTO answers (response_id, question_id, answer_value) VALUES (?, ?, ?)")
                ->execute([$response_id, (int)$q_id, trim($value)]);
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => '✅ Terima kasih! Respons Anda telah disimpan.']);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Submit error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data. Coba lagi.']);
}
?>