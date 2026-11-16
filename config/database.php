<?php
// Configuración compartida por Apache y el servidor integrado de PHP.
foreach (file_exists(__DIR__ . '/../.env') ? file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES) : [] as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) continue;
    [$key, $value] = explode('=', $line, 2);
    if (getenv(trim($key)) === false) putenv(trim($key) . '=' . trim(trim($value), "\"'"));
}
return [
    'driver' => getenv('DB_CONNECTION') ?: 'mysql',
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'database' => getenv('DB_DATABASE') ?: 'quiz_game',
    'username' => getenv('DB_USERNAME') !== false ? getenv('DB_USERNAME') : 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
];
