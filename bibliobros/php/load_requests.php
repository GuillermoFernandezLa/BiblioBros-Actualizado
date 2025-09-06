<?php
// php/load_requests.php – Cargar solicitudes pendientes visibles por cualquier mentor de una asignatura

require_once __DIR__ . '/../auth_guard.php';
header('Content-Type: application/json');

$mentor_id = (int) $_SESSION['user_id'];
$subject_id = isset($_GET['subject_id']) ? (int) $_GET['subject_id'] : 0;

if ($subject_id <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid subject']);
  exit;
}

// Verificamos que el mentor esté vinculado a esa asignatura
$stmt = $pdo->prepare("
  SELECT 1 FROM mentor_subject
   WHERE user_id = :uid AND subject_id = :sid
   LIMIT 1
");
$stmt->execute(['uid' => $mentor_id, 'sid' => $subject_id]);
if (!$stmt->fetch()) {
  http_response_code(403);
  echo json_encode(['error' => 'Access denied to this subject']);
  exit;
}

try {
  $stmt = $pdo->prepare("
    SELECT r.id,
           u.fullname AS mentee,
           r.message,
           DATE_FORMAT(r.created_at, '%d %b %Y %H:%i') AS created_at
      FROM requests r
      JOIN users u ON u.id = r.mentee_id
     WHERE r.subject_id = :sid
       AND r.status = 'pending'
       AND r.mentor_id IS NULL
     ORDER BY r.created_at DESC
  ");
  $stmt->execute([':sid' => $subject_id]);
  echo json_encode($stmt->fetchAll());

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error' => $e->getMessage()]);
}
