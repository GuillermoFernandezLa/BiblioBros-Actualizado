<?php
session_start();
require_once __DIR__ . '/config.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: Toplogin.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Obtener chat_id desde POST o GET
$chatId = isset($_POST['chat_id']) ? (int)$_POST['chat_id'] : (isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : 0);

if ($chatId <= 0) {
    header('Location: Topdashboard.php');
    exit;
}

// Verificar que el usuario es parte del chat
$stmt = $pdo->prepare("
    SELECT 
        r.mentee_id,
        r.mentor_id,
        r.subject_id,
        c.active
    FROM chats c
    JOIN requests r ON r.id = c.request_id
    WHERE c.id = :chat_id
    LIMIT 1
");
$stmt->execute(['chat_id' => $chatId]);
$chat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$chat) {
    header('Location: Topdashboard.php');
    exit;
}

$isMentee = ($chat['mentee_id'] == $userId);
$isMentor = ($chat['mentor_id'] == $userId);

// Verificar que el usuario es parte del chat
if (!$isMentee && !$isMentor) {
    header('Location: Topdashboard.php');
    exit;
}

// Si el chat ya está cerrado
if (!$chat['active']) {
    if ($isMentee) {
        // Verificar si ya ha sido calificado
        $checkRating = $pdo->prepare("SELECT id FROM ratings WHERE chat_id = :chat_id LIMIT 1");
        $checkRating->execute(['chat_id' => $chatId]);
        $hasRating = $checkRating->fetch();
        
        if (!$hasRating) {
            // Guardar en sesión para evitar problemas de redirección
            $_SESSION['pending_rating'] = $chatId;
            // Redirigir a la página de rating - SIN la barra inicial
            header("Location: rating.php?chat_id={$chatId}");
            exit;
        } else {
            // Ya fue calificado, ir al subject
            header("Location: Topsubject_mentee.php?subject_id={$chat['subject_id']}");
            exit;
        }
    } else {
        // Es mentor, redirigir al subject
        header("Location: Topsubject_mentor.php?subject_id={$chat['subject_id']}");
        exit;
    }
}

// Cerrar el chat (marcar como inactivo)
$updateChat = $pdo->prepare("
    UPDATE chats 
    SET active = 0 
    WHERE id = :chat_id
");
$updateChat->execute(['chat_id' => $chatId]);

// NO actualizar el estado de la request todavía (se hará después del rating)

// Redirigir según el rol del usuario
if ($isMentee) {
    // Guardar en sesión para evitar problemas
    $_SESSION['pending_rating'] = $chatId;
    // Si es el mentee, llevarlo a la página de rating - SIN la barra inicial
    header("Location: rating.php?chat_id={$chatId}");
    exit;
} else {
    // Si es el mentor, llevarlo de vuelta al subject
    header("Location: Topsubject_mentor.php?subject_id={$chat['subject_id']}");
    exit;
}
?>