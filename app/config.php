<?php
declare(strict_types=1);

/**
 * Laster .env fra prosjektroten (../.env) og returnerer config-array.
 * .env skal være i .gitignore.
 */

function load_env(string $path): array {
    if (!is_file($path)) return [];

    $vars = [];
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;

        $pos = strpos($line, '=');
        if ($pos === false) continue;

        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));

        // fjern enkle/doble anførselstegn hvis de finnes
        if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
            (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
            $val = substr($val, 1, -1);
        }

        $vars[$key] = $val;
    }

    return $vars;
}

$env = load_env(__DIR__ . '/../.env');

$config = [
    'db' => [
        'host'    => $env['DB_HOST'] ?? '127.0.0.1',
        'name'    => $env['DB_NAME'] ?? 'eventsingup',
        'user'    => $env['DB_USER'] ?? 'root',
        'pass'    => $env['DB_PASS'] ?? '',
        'charset' => $env['DB_CHARSET'] ?? 'utf8mb4',
    ],
    'admin' => [
        // legg inn i .env: ADMIN_PASSWORD=....
        'password' => $env['ADMIN_PASSWORD'] ?? '',
    ],
];

return $config;
