<?php
// accept_request.php – Assigns mentor to a pending request, creates chat, then redirects
require_once __DIR__ . '/../auth_guard.php'; // inicia sesión y PDO

$mentor_id = (int) $_SESSION['user_id'];
$request_id = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;
if ($request_id <= 0) {
    die("Missing or invalid request ID.");
}

try {
    // Usar objeto $pdo ya cargado por auth_guard.php
    $check = $pdo->prepare("
        SELECT 1 FROM requests
        WHERE id = :id AND status = 'pending' AND mentor_id IS NULL
    ");
    $check->execute([':id' => $request_id]);
    if (!$check->fetchColumn()) {
        die("Request not available or already accepted.");
    }

    $update = $pdo->prepare("
        UPDATE requests SET mentor_id = :mentor_id, status = 'accepted' WHERE id = :id
    ");
    $update->execute([':mentor_id' => $mentor_id, ':id' => $request_id]);

    $insert = $pdo->prepare("
        INSERT INTO chats (request_id) VALUES (:request_id)
    ");
    $insert->execute([':request_id' => $request_id]);

    // Obtener ID del chat usando lastInsertId()
    $chat_id = (int) $pdo->lastInsertId();

    header("Location: ../Topchat_mentor.php?chat_id={$chat_id}");
    exit;
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
