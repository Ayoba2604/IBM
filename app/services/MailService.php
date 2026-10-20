<?php
declare(strict_types=1);

class MailService
{
    /**
     * Development transport: writes each message to storage/mail-outbox.
     * Replace this method with PHPMailer/SMTP for real delivery.
     */
    public static function invitation(
        string $to,
        string $subject,
        string $body,
    ): void {
        global $config;
        $directory = $config["storage"] . "/mail-outbox";
        if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new RuntimeException("Não foi possível preparar a caixa de saída de e-mails.");
        }
        $to = str_replace(["\r", "\n"], "", $to);
        $subject = str_replace(["\r", "\n"], " ", $subject);
        $filename = date("YmdHis") . "-" . bin2hex(random_bytes(4)) . ".txt";
        $written = file_put_contents(
            $directory . "/" . $filename,
            "Para: {$to}\nAssunto: {$subject}\nData: " . date("d/m/Y H:i:s") . "\n\n{$body}\n",
            LOCK_EX,
        );
        if ($written === false) {
            throw new RuntimeException("Não foi possível registrar o e-mail de convite.");
        }
        @chmod($directory . "/" . $filename, 0640);
    }
}
