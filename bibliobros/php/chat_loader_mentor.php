<?php
require_once __DIR__ . '/../auth_guard.php';
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION['user_id'];
$subject_id = isset($_GET['subject_id']) ? (int) $_GET['subject_id'] : 0;

try {
  if ($subject_id > 0) {
    // ───── FILTRADO POR ASIGNATURA (Topsubject_mentor.php) ─────
    $stmt = $pdo->prepare("
      SELECT c.id AS chat_id,
             c.active,
             u.fullname AS mentee_name,
             s.name AS subject
        FROM chats c
        JOIN requests r ON r.id = c.request_id
        JOIN users u ON u.id = r.mentee_id
        JOIN subjects s ON s.id = r.subject_id
       WHERE r.mentor_id = :uid
         AND r.subject_id = :sid
         AND c.active = TRUE
       ORDER BY c.created_at DESC
    ");
    $stmt->execute([':uid' => $user_id, ':sid' => $subject_id]);
    $activeChats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
      SELECT c.id AS chat_id,
             c.active,
             u.fullname AS mentee_name,
             s.name AS subject,
             DATE_FORMAT(c.created_at, '%d %b %Y %H:%i') AS closed_at
        FROM chats c
        JOIN requests r ON r.id = c.request_id
        JOIN users u ON u.id = r.mentee_id
        JOIN subjects s ON s.id = r.subject_id
       WHERE r.mentor_id = :uid
         AND r.subject_id = :sid
         AND c.active = FALSE
       ORDER BY c.created_at DESC
    ");
    $stmt->execute([':uid' => $user_id, ':sid' => $subject_id]);
    $closedChats = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    // ───── MODO GENERAL (Topchat_mentor.php) ─────
    $stmt = $pdo->prepare("
      SELECT c.id AS chat_id,
             c.active,
             u.fullname AS mentee_name,
             s.name AS subject,
             NULL AS closed_at
        FROM chats c
        JOIN requests r ON r.id = c.request_id
        JOIN users u ON u.id = r.mentee_id
        JOIN subjects s ON s.id = r.subject_id
       WHERE r.mentor_id = :uid
       ORDER BY c.created_at DESC
    ");
    $stmt->execute([':uid' => $user_id]);
    $allChats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Separar activos de cerrados
    $activeChats = array_filter($allChats, fn($c) => $c['active']);
    $closedChats = array_filter($allChats, fn($c) => !$c['active']);
  }

  echo json_encode([
    'requests' => [],
    'active_chats' => array_values($activeChats),
    'closed_chats' => array_values($closedChats)
  ]);

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
