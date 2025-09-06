<?php
require_once __DIR__ . '/../auth_guard.php';
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
  http_response_code(401);
  echo json_encode(['error' => 'User not authenticated']);
  exit;
}

try {
  $stmt = $pdo->prepare("
    SELECT c.id AS chat_id,
           c.active,
           r.mentee_id,
           r.mentor_id,
           s.name AS subject,
           DATE_FORMAT(c.created_at, '%d %b %Y %H:%i') AS closed_at,
           u1.fullname AS mentee_name,
           u2.fullname AS mentor_name
      FROM chats c
      JOIN requests r ON r.id = c.request_id
      JOIN subjects s ON s.id = r.subject_id
      JOIN users u1 ON u1.id = r.mentee_id
      JOIN users u2 ON u2.id = r.mentor_id
     WHERE r.mentee_id = :uid OR r.mentor_id = :uid
     ORDER BY c.created_at DESC
  ");
  $stmt->execute([':uid' => $user_id]);
  $allChats = $stmt->fetchAll(PDO::FETCH_ASSOC);

  $activeChats = array_filter($allChats, fn($c) => $c['active']);
  $closedChats = array_filter($allChats, fn($c) => !$c['active']);

  echo json_encode([
    'active_chats' => array_values($activeChats),
    'closed_chats' => array_values($closedChats)
  ]);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
