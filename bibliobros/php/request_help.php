<?php
// request_help.php — Handles mentee's question submission and redirects back
require_once __DIR__ . '/../auth_guard.php'; // inicia sesión y $pdo

$mentee_id = (int) $_SESSION['user_id'];
$subject_id = (int) ($_POST['subject_id'] ?? 0);
$message = trim($_POST['message'] ?? '');

if ($subject_id <= 0 || $message === '') {
    die("Missing data.");
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO requests (subject_id, mentee_id, message)
        VALUES (:subject_id, :mentee_id, :message)
    ");
    $stmt->execute([
        ':subject_id' => $subject_id,
        ':mentee_id'  => $mentee_id,
        ':message'    => $message
    ]);
} catch (PDOException $e) {
    die("Error inserting the request: " . htmlspecialchars($e->getMessage()));
}

header("Location: ../Topsubject_mentee.php?subject_id={$subject_id}&submitted=1");
exit;
