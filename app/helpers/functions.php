<?php
declare(strict_types=1);

const ROLES = ["aluno", "orientador", "avaliador", "administrador"];
const STATUS = [
    "RASCUNHO" => "Rascunho",
    "AGUARDANDO_CONFIRMACOES" => "Aguardando confirmações",
    "CONFIRMADA" => "Confirmada",
    "REALIZADA" => "Realizada",
    "AGUARDANDO_DOCUMENTOS" => "Aguardando documentos",
    "AGUARDANDO_CORRECOES" => "Aguardando correções",
    "AGUARDANDO_VERSAO_FINAL" => "Aguardando versão final",
    "AGUARDANDO_APROVACAO" => "Aguardando aprovação",
    "AJUSTES_SOLICITADOS" => "Ajustes solicitados",
    "CONCLUIDA" => "Concluída",
];

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
function base_url(string $path = ""): string
{
    global $config;
    return $config["url"] . ($path ? "/" . ltrim($path, "/") : "");
}
function redirect(string $path): never
{
    header("Location: " . base_url($path));
    exit();
}
function current_user(): ?array
{
    return $_SESSION["user"] ?? null;
}
function is_logged_in(): bool
{
    return current_user() !== null;
}
function require_login(): void
{
    if (!is_logged_in()) {
        flash("error", "Faça login para acessar esta área.");
        redirect("index.php?page=login");
    }
}
function require_role(array $roles): void
{
    require_login();
    if (!in_array(current_user()["perfil"], $roles, true)) {
        http_response_code(403);
        exit("Acesso não autorizado.");
    }
}
function csrf_token(): string
{
    return $_SESSION["csrf"] ??= bin2hex(random_bytes(32));
}
function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function verify_csrf(): void
{
    if (!hash_equals($_SESSION["csrf"] ?? "", $_POST["csrf"] ?? "")) {
        http_response_code(419);
        exit("Solicitação expirada. Atualize a página e tente novamente.");
    }
}
function flash(string $type, ?string $message = null): mixed
{
    if ($message !== null) {
        $_SESSION["flash"][$type][] = $message;
        return null;
    }
    $items = $_SESSION["flash"][$type] ?? [];
    unset($_SESSION["flash"][$type]);
    return $items;
}
function audit(
    ?int $userId,
    string $action,
    string $entity,
    ?int $entityId = null,
    ?string $details = null,
): void {
    $s = db()->prepare(
        "INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, detalhes, ip) VALUES (?, ?, ?, ?, ?, ?)",
    );
    $s->execute([
        $userId,
        $action,
        $entity,
        $entityId,
        $details,
        $_SERVER["REMOTE_ADDR"] ?? null,
    ]);
}
function notify(
    int $userId,
    string $type,
    string $title,
    string $message,
    ?string $link = null,
): void {
    $s = db()->prepare(
        "INSERT INTO notificacoes (usuario_id,tipo,titulo,mensagem,link_interno) VALUES (?,?,?,?,?)",
    );
    $s->execute([$userId, $type, $title, $message, $link]);
}
function status_label(string $status): string
{
    return STATUS[$status] ?? $status;
}
function status_class(string $status): string
{
    return match ($status) {
        "CONCLUIDA", "CONFIRMADA" => "success",
        "REALIZADA" => "info",
        "AJUSTES_SOLICITADOS", "AGUARDANDO_CORRECOES" => "warning",
        default => "secondary",
    };
}
function modality_label(string $modality): string
{
    return match ($modality) {
        "presencial" => "Presencial",
        "online" => "On-line",
        "hibrida" => "Híbrida",
        default => $modality,
    };
}
function date_br(?string $date, bool $time = false): string
{
    if (!$date) {
        return "—";
    }
    return (new DateTimeImmutable($date))->format($time ? "d/m/Y H:i" : "d/m/Y");
}
function can_access_work(array $work): bool
{
    $u = current_user();
    if (!$u) {
        return false;
    }
    if (
        $u["perfil"] === "administrador" ||
        (int) $work["orientador_id"] === (int) $u["id"]
    ) {
        return true;
    }
    $s = db()->prepare(
        "SELECT 1 FROM alunos_trabalhos WHERE trabalho_id=? AND aluno_id=?",
    );
    $s->execute([$work["id"], $u["id"]]);
    return (bool) $s->fetchColumn();
}
function work_by_id(int $id): ?array
{
    $s = db()->prepare(
        "SELECT t.*, c.nome curso_nome, u.nome orientador_nome FROM trabalhos t JOIN cursos c ON c.id=t.curso_id JOIN usuarios u ON u.id=t.orientador_id WHERE t.id=?",
    );
    $s->execute([$id]);
    return $s->fetch() ?: null;
}
