<?php
/*
 * load_ratings.php
 *
 * API endpoint: returns all ratings (score + comment) for current mentor.
 * Authenticated via session. MySQL queries adapted from PostgreSQL.
 */

require_once __DIR__ . '/../auth_guard.php'; // opcional: inicia sesión y $pdo para MySQL

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}
$mentor_id = (int) $_SESSION['user_id'];

try {
    // Si no usas auth_guard.php, define la conexión MySQL manualmente:
    // $pdo = new PDO('mysql:host=localhost;dbname=bibliobros;charset=utf8mb4', 'usuario', 'clave');
    // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
    SELECT r.score, r.comment, u.fullname AS mentee_name
      FROM ratings r
      JOIN chats c ON c.id = r.chat_id
      JOIN requests req ON req.id = c.request_id
      JOIN users u ON u.id = req.mentee_id
     WHERE req.mentor_id = :mentor_id
     ORDER BY r.id DESC
");

    $stmt->execute(['mentor_id' => $mentor_id]);
    $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($ratings);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
