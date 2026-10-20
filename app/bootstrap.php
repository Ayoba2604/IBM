<?php
declare(strict_types=1);

$envFile = dirname(__DIR__) . "/.env";
if (is_file($envFile)) {
    foreach (
        file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
        as $line
    ) {
        if ($line[0] !== "#" && str_contains($line, "=")) {
            [$key, $value] = explode("=", $line, 2);
            putenv(trim($key) . "=" . trim($value));
        }
    }
}
$config = require __DIR__ . "/config/app.php";
date_default_timezone_set($config["timezone"]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name("sig_banca_session");
    session_set_cookie_params([
        "httponly" => true,
        "samesite" => "Lax",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    ]);
    session_start();
}
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/helpers/functions.php";
require_once __DIR__ . "/services/TokenService.php";
require_once __DIR__ . "/services/PrazoService.php";
require_once __DIR__ . "/services/UploadService.php";
require_once __DIR__ . "/services/MailService.php";
