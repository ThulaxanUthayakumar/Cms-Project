<?php
require_once __DIR__ . '/../includes/config.php';

if (isLoggedIn()) {
    $pdo = getDB();
    $log = $pdo->prepare("INSERT INTO activity_log (user_id, action) VALUES (?, ?)");
    $log->execute([$_SESSION['user_id'], 'Logged out']);
}

$_SESSION = [];
session_destroy();

header('Location: ' . APP_URL . '/index.php');
exit;
