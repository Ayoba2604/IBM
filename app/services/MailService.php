<?php
declare(strict_types=1);
class MailService
{
    public static function invitation(
        string $to,
        string $subject,
        string $body,
    ): void {
        global $config;
        $dir = $config["root"] . "/storage/mail-outbox";
        if (!is_dir($dir)) {
            mkdir($dir, 0770, true);
        }
        file_put_contents(
            $dir .
                "/" .
                date("YmdHis") .
                "-" .
                bin2hex(random_bytes(4)) .
                ".txt",
            "Para: {$to}\nAssunto: {$subject}\n\n{$body}",
        );
    }
}
