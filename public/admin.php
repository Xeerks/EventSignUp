<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../app/db.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SESSION['is_admin'] ?? false) !== true) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
}

function mask_email(string $email): string {
    $parts = explode('@', $email, 2);
    if (count($parts) !== 2) return '***';
    $name = $parts[0];
    $domain = $parts[1];
    $prefix = substr($name, 0, 2);
    return $prefix . '***@' . $domain;
}

$q = trim($_GET['q'] ?? '');

$pdo = db();

if ($q !== '') {
    $stmt = $pdo->prepare("
        SELECT first_name, last_name, email, is_18
        FROM personaldata
        WHERE email LIKE :q OR first_name LIKE :q OR last_name LIKE :q
        ORDER BY last_name ASC
        LIMIT 200
    ");
    $stmt->execute([':q' => "%$q%"]);
    $rows = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("
        SELECT first_name, last_name, email, is_18
        FROM personaldata
        ORDER BY last_name ASC
        LIMIT 200
    ");
    $rows = $stmt->fetchAll();
}

$out = [];
foreach ($rows as $r) {
    $out[] = [
        'first_name'    => $r['first_name'],
        'last_name'     => $r['last_name'],
        'email'         => $r['email'],                 // potrzebne do usuwania
        'email_masked'  => mask_email((string)$r['email']),
        'is_18'         => (int)$r['is_18'],
    ];
}

echo json_encode(['ok' => true, 'rows' => $out], JSON_UNESCAPED_UNICODE);
