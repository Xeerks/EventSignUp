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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Ugyldig e-post.']);
    exit;
}

try {
    $pdo = db();

    $stmt = $pdo->prepare("DELETE FROM personaldata WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);

    $logPath = __DIR__ . '/../logs/audit.log';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $time = date('Y-m-d H:i:s');

    $line = sprintf("[%s] DELETE email=%s ip=%s\n", $time, $email, $ip);

    $fh = fopen($logPath, 'ab');
    if ($fh) {
        flock($fh, LOCK_EX);
        fwrite($fh, $line);
        fflush($fh);
        flock($fh, LOCK_UN);
        fclose($fh);
    }

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Databasefeil.']);
}
