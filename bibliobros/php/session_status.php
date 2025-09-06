<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['authenticated' => false]);
    exit;
}

// Safely retrieve role
$role = $_SESSION['role'] ?? null;

echo json_encode([
    'authenticated' => true,
    'user_id'       => $_SESSION['user_id'],
    'role'          => $role,
    'fullname'      => $_SESSION['fullname'] ?? ''
]);
