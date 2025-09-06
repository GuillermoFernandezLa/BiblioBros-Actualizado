<?php
/*
 * load_messages.php – MySQL version
 */
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
$user_id = (int) $_SESSION['user_id'];
$chat_id = isset($_GET['chat_id']) ? (int) $_GET['chat_id'] : 0;

if ($chat_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid chat_id']);
    exit;
}

require_once __DIR__ . '/../config.php';

try {
    // Verifica que el usuario participa en ese chat
    $chk = $pdo->prepare("
        SELECT 1
          FROM chats c
          JOIN requests r ON r.id = c.request_id
         WHERE c.id = :chat_id
           AND (r.mentee_id = :uid OR r.mentor_id = :uid)
         LIMIT 1
    ");
    $chk->execute([':chat_id' => $chat_id, ':uid' => $user_id]);

    if (!$chk->fetch()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }

    // Cargar mensajes ordenados por tiempo
    $stmt = $pdo->prepare("
        SELECT sender_id, content, timestamp
          FROM messages
         WHERE chat_id = :chat_id
         ORDER BY timestamp ASC
    ");
    $stmt->execute([':chat_id' => $chat_id]);

    $out = [];
    while ($row = $stmt->fetch()) {
        $out[] = [
            'sender_id' => $row['sender_id'], // ← importante
            'text'      => $row['content'],
            'time'      => date("H:i", strtotime($row['timestamp']))
        ];
    }

    echo json_encode($out);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
