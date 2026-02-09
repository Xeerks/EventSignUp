<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$first   = trim($_POST['first_name'] ?? '');
$last    = trim($_POST['last_name'] ?? '');
$email   = trim($_POST['email'] ?? '');

$consent = isset($_POST['consent']);
$is18    = isset($_POST['is_18']);

if ($first === '' || $last === '') {
    exit('Ugyldig navn.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('Ugyldig e-postadresse.');
}
if (!$is18) {
    exit('Du må bekrefte at du er 18+.');
}
if (!$consent) {
    exit('Du må godta personvernerklæringen.');
}

try {
    $pdo = db();

    // valgfritt: blokkere duplikat e-post
    $check = $pdo->prepare("SELECT 1 FROM personaldata WHERE email = :email LIMIT 1");
    $check->execute([':email' => $email]);
    if ($check->fetchColumn()) {
        exit('Denne e-posten er allerede registrert.');
    }

    $stmt = $pdo->prepare("
        INSERT INTO personaldata (first_name, last_name, email, is_18)
        VALUES (:first_name, :last_name, :email, :is_18)
    ");

    $stmt->execute([
        ':first_name' => $first,
        ':last_name'  => $last,
        ':email'      => $email,
        ':is_18'      => 1,
    ]);

    echo 'Takk! Påmeldingen er registrert.';
} catch (Throwable $e) {
    // ikke vis detaljer til bruker
    exit('Databasefeil.');
}
