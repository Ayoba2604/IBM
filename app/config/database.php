<?php
declare(strict_types=1);

/**
 * Shared PDO connection. The connection carries the authenticated actor id so
 * MySQL triggers can attribute audit rows to the current user.
 */
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
    $password = getenv("DB_PASSWORD");
    $password = $password === false ? "" : $password;

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ],
    );

    // Keep NOW()/CURDATE() aligned with the application timezone even when the
    // database server uses UTC.
    $offset = (new DateTimeImmutable("now"))->format("P");
    $pdo->prepare("SET time_zone = ?")->execute([$offset]);
    $pdo->prepare("SET @sig_usuario_id = ?")->execute([
        $_SESSION["user"]["id"] ?? null,
    ]);

    return $pdo;
}

/**
 * Refresh the connection-scoped actor id (for example right after login).
 */
function db_set_actor(?int $userId): void
{
    db()->prepare("SET @sig_usuario_id = ?")->execute([$userId]);
}
