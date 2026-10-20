<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $host = getenv("DB_HOST") ?: "127.0.0.1";
    $port = getenv("DB_PORT") ?: "3306";
    $name = getenv("DB_DATABASE") ?: "sig_banca";
    $user = getenv("DB_USERNAME") ?: "root";
    $password = getenv("DB_PASSWORD") ?: "";
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    );

    // The triggers use this connection-scoped value to attribute database audit rows.
    $actorId = $_SESSION["user"]["id"] ?? null;
    $pdo->prepare("SET @sig_usuario_id = ?")->execute([$actorId]);

    return $pdo;
}
