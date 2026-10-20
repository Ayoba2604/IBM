<?php
declare(strict_types=1);

/**
 * Load .env without overwriting variables already defined by the process
 * environment (useful for CLI tools and tests).
 */
$envFile = dirname(__DIR__) . "/.env";
if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === "" || str_starts_with($line, "#") || !str_contains($line, "=")) {
            continue;
        }
        [$key, $value] = explode("=", $line, 2);
        $key = trim($key);
        if (!preg_match('/^[A-Z_][A-Z0-9_]*$/i', $key) || getenv($key) !== false) {
            continue;
        }
        $value = trim($value);
        if (
            strlen($value) >= 2 &&
            (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))
        ) {
            $value = substr($value, 1, -1);
        }
        putenv($key . "=" . $value);
    }
}

$config = require __DIR__ . "/config/app.php";

date_default_timezone_set($config["timezone"]);
mb_internal_encoding("UTF-8");
setlocale(LC_TIME, "pt_BR", "Portuguese_Brazil", "pt_BR.utf8");

if (!is_dir($config["storage"] . "/logs")) {
    @mkdir($config["storage"] . "/logs", 0770, true);
}
ini_set("log_errors", "1");
ini_set("error_log", $config["storage"] . "/logs/app.log");
ini_set("display_errors", $config["debug"] ? "1" : "0");
error_reporting(E_ALL);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set("session.use_strict_mode", "1");
    ini_set("session.use_only_cookies", "1");
    session_name($config["session_name"]);
    session_set_cookie_params([
        "lifetime" => $config["session_lifetime"],
        "path" => "/",
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
require_once __DIR__ . "/services/InviteService.php";
