<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$config = require __DIR__ . '/../app/config.php';
$adminPass = $config['admin']['password'] ?? '';

$pass = (string)($_POST['password'] ?? '');

if ($adminPass !== '' && hash_equals($adminPass, $pass)) {
    $_SESSION['is_admin'] = true;
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(401);
echo json_encode(['ok' => false, 'error' => 'Feil passord.']);
