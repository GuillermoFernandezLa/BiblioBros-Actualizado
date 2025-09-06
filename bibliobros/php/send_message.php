<?php
/*
 * send_message.php – MySQL version
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$user_id = (int) $_SESSION['user_id'];
$chat_id = isset($_POST['chat_id']) ? (int) $_POST['chat_id'] : 0;
$message = trim($_POST['message'] ?? '');

if ($chat_id <= 0 || $message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

require_once __DIR__ . '/../config.php';

try {
    // Verifica que el usuario es parte del chat activo
    $chk = $pdo->prepare("
        SELECT 1
          FROM chats c
          JOIN requests r ON r.id = c.request_id
         WHERE c.id = :chat_id
           AND c.active = TRUE
           AND (r.mentee_id = :uid OR r.mentor_id = :uid)
         LIMIT 1
    ");
    $chk->execute([':chat_id' => $chat_id, ':uid' => $user_id]);

    if (!$chk->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    // Insertar el mensaje
    $ins = $pdo->prepare("
        INSERT INTO messages (chat_id, sender_id, content)
        VALUES (:chat_id, :uid, :content)
    ");
    $ins->execute([
        ':chat_id' => $chat_id,
        ':uid'     => $user_id,
        ':content' => $message
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
