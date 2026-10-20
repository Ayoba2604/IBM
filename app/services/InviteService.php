<?php
declare(strict_types=1);

/**
 * Invitation lifecycle helpers: issuing/renewing tokens, expiring overdue
 * invites and dispatching the notification + development e-mail.
 */
class InviteService
{
    public const VALIDADE_DIAS = 14;

    public static function url(string $token): string
    {
        return base_url("index.php?page=invite&token=" . rawurlencode($token));
    }

    /**
     * Invalidate any open invitation for this evaluator/banca relation and
     * issue a fresh token, restarting the 14-day response window.
     */
    public static function issue(int $relationId): string
    {
        $pdo = db();
        $pdo->prepare(
            "UPDATE convites SET status='substituido'
             WHERE banca_avaliador_id=? AND status IN ('pendente','expirado')",
        )->execute([$relationId]);

        [$token, $hash] = TokenService::create();
        $pdo->prepare(
            "INSERT INTO convites (banca_avaliador_id,token_hash,expira_em,enviado_em)
             VALUES (?,?,DATE_ADD(NOW(), INTERVAL " . self::VALIDADE_DIAS . " DAY),NOW())",
        )->execute([$relationId, $hash]);

        return self::url($token);
    }

    /**
     * Persist the expiry of pending invites so the UI can offer a resend
     * instead of showing an actionless "Convite expirado" card.
     */
    public static function markExpired(int $bancaId): void
    {
        db()->prepare(
            "UPDATE convites c
             JOIN banca_avaliadores ba ON ba.id=c.banca_avaliador_id
             SET c.status='expirado'
             WHERE ba.banca_id=? AND c.status='pendente'
               AND c.expira_em IS NOT NULL AND c.expira_em < NOW()",
        )->execute([$bancaId]);
    }

    /**
     * @param array{id:int|string,nome:string,email:string} $evaluator
     * @param array{data_banca:string,hora_inicio:string} $banca
     */
    public static function send(array $evaluator, array $banca, string $titulo, string $url): void
    {
        $when = date_br((string) $banca["data_banca"]) . " às " . time_br((string) $banca["hora_inicio"]);
        notify(
            (int) $evaluator["id"],
            "convite",
            "Novo convite de banca",
            "Você foi convidado(a) para avaliar “{$titulo}” em {$when}.",
            "index.php?page=invitations",
        );
        try {
            MailService::invitation(
                (string) $evaluator["email"],
                "Convite para banca · SIG-Banca",
                "Olá, {$evaluator["nome"]}. Você foi convidado(a) para avaliar “{$titulo}” em {$when}. " .
                "Confirme sua participação em: {$url}",
            );
        } catch (Throwable $mailError) {
            error_log("[SIG-Banca] Falha ao registrar convite: " . $mailError->getMessage());
        }
    }
}
