<?php
declare(strict_types=1);

return [
    "name" => getenv("APP_NAME") ?: "SIG-Banca",
    "url" => rtrim(getenv("APP_URL") ?: "http://localhost/IBM/SIG/public", "/"),
    "timezone" => getenv("APP_TIMEZONE") ?: "America/Sao_Paulo",
    "upload_max_bytes" => (int) (getenv("UPLOAD_MAX_BYTES") ?: 10485760),
    "allowed_mimes" => ["application/pdf" => "pdf"],
    "root" => dirname(__DIR__, 2),
];
