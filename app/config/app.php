<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

/**
 * Resolve the application base URL from the current request so the system works
 * both at http://localhost/IBM/public and at a dedicated virtual host, without
 * requiring APP_URL to be configured first.
 */
$detectBaseUrl = static function (): string {
    $host = trim((string) ($_SERVER["HTTP_HOST"] ?? ""));
    if ($host === "" || !preg_match('/^[A-Za-z0-9.\-:\[\]]+$/', $host)) {
        return "http://localhost/IBM/public";
    }
    $https =
        (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ||
        (string) ($_SERVER["SERVER_PORT"] ?? "") === "443";
    $script = str_replace("\\", "/", (string) ($_SERVER["SCRIPT_NAME"] ?? "/index.php"));
    $directory = rtrim(str_replace("\\", "/", dirname($script)), "/");
    if ($directory === "." || $directory === "/") {
        $directory = "";
    }

    return ($https ? "https" : "http") . "://" . $host . $directory;
};

$env = static function (string $key, string $default = "") use ($root): string {
    $value = getenv($key);

    return $value === false || $value === "" ? $default : (string) $value;
};

return [
    "name" => $env("APP_NAME", "SIG-Banca"),
    "short_name" => "SIG-Banca",
    "institution" => $env("APP_INSTITUTION", "Fatec Bauru"),
    "url" => rtrim($env("APP_URL", $detectBaseUrl()), "/"),
    "timezone" => $env("APP_TIMEZONE", "America/Sao_Paulo"),
    "upload_max_bytes" => (int) $env("UPLOAD_MAX_BYTES", "10485760"),
    "allowed_mimes" => ["application/pdf" => "pdf"],
    "debug" => filter_var($env("APP_DEBUG", "false"), FILTER_VALIDATE_BOOL),
    "session_name" => "sig_banca_session",
    "session_lifetime" => 60 * 60 * 8,
    "max_login_attempts" => 5,
    "login_lockout_seconds" => 300,
    "root" => $root,
    "storage" => $root . "/storage",
];
