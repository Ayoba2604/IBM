<?php
declare(strict_types=1);

const ROLES = ["aluno", "orientador", "avaliador", "administrador"];
const ROLE_LABELS = [
    "aluno" => "Aluno(a)",
    "orientador" => "Orientador(a)",
    "avaliador" => "Avaliador(a)",
    "administrador" => "Administração",
];
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
const STATUS_BADGES = [
    "RASCUNHO" => "neutral",
    "AGUARDANDO_CONFIRMACOES" => "pending",
    "CONFIRMADA" => "success",
    "REALIZADA" => "info",
    "AGUARDANDO_DOCUMENTOS" => "pending",
    "AGUARDANDO_CORRECOES" => "warning",
    "AGUARDANDO_VERSAO_FINAL" => "pending",
    "AGUARDANDO_APROVACAO" => "info",
    "AJUSTES_SOLICITADOS" => "warning",
    "CONCLUIDA" => "success",
];
const WORK_STEPS = [
    "RASCUNHO" => "Elaboração",
    "AGUARDANDO_CONFIRMACOES" => "Agendamento",
    "CONFIRMADA" => "Confirmação",
    "REALIZADA" => "Defesa",
    "AGUARDANDO_DOCUMENTOS" => "Documentação",
    "AGUARDANDO_CORRECOES" => "Correções",
    "AGUARDANDO_VERSAO_FINAL" => "Versão final",
    "AGUARDANDO_APROVACAO" => "Aprovação",
    "CONCLUIDA" => "Concluído",
];
const MODALITY_LABELS = [
    "presencial" => "Presencial",
    "online" => "Online",
    "hibrida" => "Híbrida",
];
const DOCUMENT_LABELS = [
    "ata" => "Ata de defesa",
    "autorizacao_ric" => "Autorização RIC-CPS",
    "trabalho" => "Trabalho",
    "versao_final" => "Versão final",
    "outro" => "Outro documento",
];
const VERSION_LABELS = [
    "enviada" => "Enviada",
    "em_analise" => "Em análise",
    "aprovada" => "Aprovada",
    "ajustes_solicitados" => "Ajustes solicitados",
];

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

/**
 * Read a scalar value from the request without triggering "Array to string"
 * warnings when a malicious client sends nested fields.
 */
function request_string(array $source, string $key, string $default = ""): string
{
    $value = $source[$key] ?? null;
    if (!is_string($value) && !is_int($value) && !is_float($value)) {
        return $default;
    }
    return trim((string) $value);
}

function post_string(string $key, string $default = ""): string
{
    return request_string($_POST, $key, $default);
}

function query_string(string $key, string $default = ""): string
{
    return request_string($_GET, $key, $default);
}

function base_url(string $path = ""): string
{
    global $config;
    return rtrim($config["url"], "/") . ($path ? "/" . ltrim($path, "/") : "");
}

function asset(string $path): string
{
    $relative = "assets/" . ltrim($path, "/");
    $absolute = dirname(__DIR__, 2) . "/public/" . $relative;
    $version = is_file($absolute) ? (string) filemtime($absolute) : "1";
    return base_url($relative) . "?v=" . $version;
}

function redirect(string $path): never
{
    header("Location: " . (str_starts_with($path, "http") ? $path : base_url($path)));
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
    if (!in_array(current_user()["perfil"] ?? "", $roles, true)) {
        abort(403, "Seu perfil não tem permissão para acessar esta área.");
    }
}

function csrf_token(): string
{
    if (empty($_SESSION["csrf"]) || !is_string($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf"];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_rotate(): void
{
    $_SESSION["csrf"] = bin2hex(random_bytes(32));
}

function verify_csrf(): void
{
    $expected = $_SESSION["csrf"] ?? "";
    $provided = $_POST["csrf"] ?? "";
    if (
        !is_string($expected) ||
        !is_string($provided) ||
        $expected === "" ||
        !hash_equals($expected, $provided)
    ) {
        abort(400, "Sua sessão expirou. Atualize a página e tente novamente.");
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
    $statement = db()->prepare(
        "INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, detalhes, ip)
         VALUES (?, ?, ?, ?, ?, ?)",
    );
    $statement->execute([
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
    $statement = db()->prepare(
        "INSERT INTO notificacoes (usuario_id,tipo,titulo,mensagem,link_interno)
         VALUES (?,?,?,?,?)",
    );
    $statement->execute([$userId, $type, $title, $message, $link]);
}

function unread_notifications(int $userId): int
{
    $statement = db()->prepare(
        "SELECT COUNT(*) FROM notificacoes WHERE usuario_id=? AND lida_em IS NULL",
    );
    $statement->execute([$userId]);
    return (int) $statement->fetchColumn();
}

function status_label(string $status): string
{
    return STATUS[$status] ?? $status;
}

function status_class(string $status): string
{
    return STATUS_BADGES[$status] ?? "neutral";
}

function role_label(?string $role): string
{
    return ROLE_LABELS[$role ?? ""] ?? ucfirst((string) $role);
}

function modality_label(?string $modality): string
{
    return MODALITY_LABELS[$modality ?? ""] ?? ucfirst((string) $modality);
}

function document_label(?string $type): string
{
    return DOCUMENT_LABELS[$type ?? ""] ?? ucfirst(str_replace("_", " ", (string) $type));
}

function version_label(?string $status): string
{
    return VERSION_LABELS[$status ?? ""] ?? ucfirst(str_replace("_", " ", (string) $status));
}

function status_steps(): array
{
    return WORK_STEPS;
}

function status_step_index(string $status): int
{
    // Each status points to the step the work is CURRENTLY IN, not to the
    // milestone that produced it. "AGUARDANDO_CONFIRMACOES" means the banca is
    // already scheduled and the group is confirming, so the cursor must be on
    // "Confirmação", not on "Agendamento". The same shift applies to CONFIRMADA
    // (defense pending) and REALIZADA (documentation pending).
    return match ($status) {
        "RASCUNHO" => 0,                 // Elaboração
        "AGUARDANDO_CONFIRMACOES" => 2,  // Agendamento concluído; falta confirmar
        "CONFIRMADA" => 3,               // Confirmação concluída; falta a defesa
        "REALIZADA" => 4,                // Defesa concluída; falta documentação
        "AGUARDANDO_DOCUMENTOS" => 4,    // Documentação
        "AGUARDANDO_CORRECOES" => 5,     // Correções
        "AGUARDANDO_VERSAO_FINAL" => 6,  // Versão final
        "AJUSTES_SOLICITADOS" => 6,      // Ajustes reabrem a versão final
        "AGUARDANDO_APROVACAO" => 7,     // Aprovação
        "CONCLUIDA" => count(WORK_STEPS), // tudo cumprido: nenhuma etapa "em andamento"
        default => 0,
    };
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $parts = array_values(array_filter($parts, static fn (string $part): bool => $part !== ""));
    if (!$parts) {
        return "?";
    }
    $first = mb_substr($parts[0], 0, 1);
    $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : "";
    return mb_strtoupper($first . $last);
}

function date_br(?string $date, bool $time = false): string
{
    if (!$date) {
        return "—";
    }
    try {
        $parsed = new DateTimeImmutable($date);
    } catch (Throwable) {
        return "—";
    }
    return $parsed->format($time ? "d/m/Y H:i" : "d/m/Y");
}

function time_br(?string $time): string
{
    return $time ? substr($time, 0, 5) : "—";
}

function date_long_pt(?string $date): string
{
    if (!$date) {
        return "—";
    }
    $months = [
        1 => "janeiro",
        2 => "fevereiro",
        3 => "março",
        4 => "abril",
        5 => "maio",
        6 => "junho",
        7 => "julho",
        8 => "agosto",
        9 => "setembro",
        10 => "outubro",
        11 => "novembro",
        12 => "dezembro",
    ];
    try {
        $parsed = new DateTimeImmutable($date);
    } catch (Throwable) {
        return "—";
    }
    return $parsed->format("j") . " de " . $months[(int) $parsed->format("n")] . " de " . $parsed->format("Y");
}

function format_bytes(int $bytes): string
{
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 1, ",", ".") . " MB";
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 0, ",", ".") . " KB";
    }
    return $bytes . " B";
}

/**
 * Restrict listed works/bancas according to the authenticated profile.
 * Returns [SQL condition (with ? placeholders), parameters].
 */
function access_scope(string $alias = "t"): array
{
    $user = current_user();
    if (!$user || $user["perfil"] === "administrador") {
        return ["1=1", []];
    }
    return match ($user["perfil"]) {
        "orientador" => ["{$alias}.orientador_id = ?", [(int) $user["id"]]],
        "aluno" => [
            "{$alias}.id IN (SELECT trabalho_id FROM alunos_trabalhos WHERE aluno_id = ?)",
            [(int) $user["id"]],
        ],
        "avaliador" => [
            "{$alias}.id IN (
                SELECT b.trabalho_id FROM bancas b
                JOIN banca_avaliadores ba ON ba.banca_id = b.id
                WHERE ba.avaliador_id = ?
            )",
            [(int) $user["id"]],
        ],
        default => ["1=0", []],
    };
}

function can_access_work(array $work): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }
    // Bancas and versions carry the work reference in trabalho_id; works carry id.
    $workId = isset($work["trabalho_id"])
        ? (int) $work["trabalho_id"]
        : (int) ($work["id"] ?? 0);
    if ($workId <= 0) {
        return false;
    }
    if (
        $user["perfil"] === "administrador" ||
        (int) ($work["orientador_id"] ?? 0) === (int) $user["id"]
    ) {
        return true;
    }
    if ($user["perfil"] === "aluno") {
        $statement = db()->prepare(
            "SELECT 1 FROM alunos_trabalhos WHERE trabalho_id=? AND aluno_id=?",
        );
        $statement->execute([$workId, (int) $user["id"]]);
        return (bool) $statement->fetchColumn();
    }
    if ($user["perfil"] === "avaliador") {
        $statement = db()->prepare(
            "SELECT 1 FROM bancas b
             JOIN banca_avaliadores ba ON ba.banca_id=b.id
             WHERE b.trabalho_id=? AND ba.avaliador_id=? LIMIT 1",
        );
        $statement->execute([$workId, (int) $user["id"]]);
        return (bool) $statement->fetchColumn();
    }
    return false;
}

function work_by_id(int $id): ?array
{
    $statement = db()->prepare(
        "SELECT t.*, c.nome curso_nome, c.sigla curso_sigla, u.nome orientador_nome
         FROM trabalhos t
         JOIN cursos c ON c.id=t.curso_id
         JOIN usuarios u ON u.id=t.orientador_id
         WHERE t.id=?",
    );
    $statement->execute([$id]);
    return $statement->fetch() ?: null;
}

function banca_by_id(int $id): ?array
{
    $statement = db()->prepare(
        "SELECT b.*, t.titulo, t.id trabalho_id, t.orientador_id, t.status trabalho_status
         FROM bancas b JOIN trabalhos t ON t.id=b.trabalho_id
         WHERE b.id=?",
    );
    $statement->execute([$id]);
    return $statement->fetch() ?: null;
}

/**
 * Render a complete, self-contained error page. Used before the main layout is
 * available (permission checks during routing).
 */
function abort(int $status, string $message): never
{
    http_response_code($status);
    $title = match ($status) {
        400 => "Solicitação inválida",
        403 => "Acesso não autorizado",
        404 => "Página não encontrada",
        419 => "Sessão expirada",
        default => "Não foi possível continuar",
    };
    $home = is_logged_in() ? "index.php?page=dashboard" : "index.php";
    ?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · SIG-Banca</title>
    <link rel="stylesheet" href="<?= e(asset("css/style.css")) ?>">
</head>
<body class="error-body">
    <main class="error-card">
        <span class="error-code"><?= (int) $status ?></span>
        <h1><?= e($title) ?></h1>
        <p><?= e($message) ?></p>
        <a class="btn primary" href="<?= e(base_url($home)) ?>">Voltar ao início</a>
    </main>
</body>
</html>
    <?php
    exit();
}
