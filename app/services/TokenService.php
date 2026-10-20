<?php
declare(strict_types=1);
class TokenService
{
    public static function create(): array
    {
        $token = bin2hex(random_bytes(32));
        return [$token, hash("sha256", $token)];
    }
    public static function hash(string $token): string
    {
        return hash("sha256", $token);
    }
}
