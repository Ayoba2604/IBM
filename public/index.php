<?php
declare(strict_types=1);

require_once dirname(__DIR__) . "/app/bootstrap.php";

header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
header(
    "Content-Security-Policy: default-src 'self'; " .
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
    "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; " .
    "script-src 'self'; img-src 'self' data:; connect-src 'self'; base-uri 'self'; form-action 'self'",
);
if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}
if (is_logged_in()) {
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: no-cache");
}

$currentPage = $_GET["page"] ?? (is_logged_in() ? "dashboard" : "home");
$currentPage = is_string($currentPage) ? $currentPage : "home";
$pageMeta = [
    "dashboard" => ["title" => "Visão geral", "section" => "Painel"],
    "works" => ["title" => "Trabalhos", "section" => "Acadêmico"],
    "work" => ["title" => "Detalhes do trabalho", "section" => "Trabalhos"],
    "work_new" => ["title" => "Novo trabalho", "section" => "Trabalhos"],
    "bancas" => ["title" => "Bancas", "section" => "Acadêmico"],
    "banca" => ["title" => "Detalhes da banca", "section" => "Bancas"],
    "banca_new" => ["title" => "Agendar banca", "section" => "Bancas"],
    "calendar" => ["title" => "Calendário", "section" => "Acadêmico"],
    "invitations" => ["title" => "Meus convites", "section" => "Avaliações"],
    "invite" => ["title" => "Convite", "section" => "Avaliações"],
    "notifications" => ["title" => "Notificações", "section" => "Conta"],
    "public_search" => ["title" => "Consulta pública", "section" => "Acervo"],
    "admin" => ["title" => "Administração", "section" => "Sistema"],
    "home" => ["title" => "Início", "section" => "SIG-Banca"],
    "login" => ["title" => "Entrar", "section" => "Acesso"],
];

function rows(string $sql, array $params = []): array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function one(string $sql, array $params = []): ?array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetch() ?: null;
}

function placeholders(array $values): string
{
    return implode(",", array_fill(0, max(1, count($values)), "?"));
}

function normalize_time(mixed $value): ?string
{
    $value = trim((string) $value);
    if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)(?::([0-5]\d))?$/', $value, $matches)) {
        return null;
    }
    return $matches[1] . ":" . $matches[2] . ":" . ($matches[3] ?? "00");
}

function valid_date(mixed $value): ?string
{
    $value = trim((string) $value);
    $parsed = DateTimeImmutable::createFromFormat("!Y-m-d", $value);
    return $parsed && $parsed->format("Y-m-d") === $value ? $value : null;
}

function post_ids(string $field): array
{
    $raw = $_POST[$field] ?? [];
    if (!is_array($raw)) {
        $raw = [$raw];
    }
    $ids = array_map("intval", $raw);
    $ids = array_values(array_unique(array_filter($ids, static fn (int $id): bool => $id > 0)));
    return $ids;
}

function login_blocked(): bool
{
    global $config;
    $attempts = $_SESSION["login_attempts"] ?? null;
    if (!is_array($attempts)) {
        return false;
    }
    $last = (int) ($attempts["last"] ?? 0);
    if ($last > 0 && time() - $last > (int) $config["login_lockout_seconds"]) {
        unset($_SESSION["login_attempts"]);
        return false;
    }
    return (int) ($attempts["count"] ?? 0) >= (int) $config["max_login_attempts"];
}

function register_login_failure(): void
{
    $attempts = $_SESSION["login_attempts"] ?? ["count" => 0, "last" => 0];
    $_SESSION["login_attempts"] = [
        "count" => (int) ($attempts["count"] ?? 0) + 1,
        "last" => time(),
    ];
}

function active(string $page, string $label, string $icon): string
{
    global $currentPage;
    $isActive = $currentPage === $page;
    return '<a class="nav-link' . ($isActive ? " active" : "") . '" href="?page=' . e($page) . '"' .
        ($isActive ? ' aria-current="page"' : "") . '><i class="bi bi-' . e($icon) . '" aria-hidden="true"></i><span>' .
        e($label) . "</span></a>";
}

function layout_start(string $title, array $options = []): void
{
    global $config, $currentPage, $pageMeta;
    $user = current_user();
    $mode = $options["mode"] ?? ($user ? "app" : "public");
    $GLOBALS["layoutMode"] = $mode;
    $GLOBALS["layoutOptions"] = $options;
    $bodyClass = $options["body_class"] ?? ($mode === "auth" ? "auth-body" : "app-body");
    $meta = $pageMeta[$currentPage] ?? ["title" => $title, "section" => "SIG-Banca"];
    $unread = 0;
    $recentNotifications = [];
    if ($mode === "app" && $user) {
        $unread = unread_notifications((int) $user["id"]);
        $recentNotifications = rows(
            "SELECT * FROM notificacoes WHERE usuario_id=? ORDER BY created_at DESC LIMIT 5",
            [(int) $user["id"]],
        );
    }
    ?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1f3a">
    <meta name="description" content="SIG-Banca — sistema integrado de gestão de bancas de TCC da Fatec Bauru.">
    <title><?= e($title) ?> · <?= e($config["name"]) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e(asset("img/favicon.svg")) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(asset("css/style.css")) ?>" rel="stylesheet">
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
<?php if ($mode === "app" && $user): ?>
    <button class="sidebar-scrim" type="button" data-sidebar-close aria-label="Fechar menu" tabindex="-1"></button>
    <aside class="sidebar" id="sidebar" aria-label="Menu principal">
        <a class="brand" href="?page=dashboard">
            <span class="brand-mark" aria-hidden="true">SB</span>
            <span class="brand-text"><strong>SIG-Banca</strong><small><?= e($config["institution"]) ?></small></span>
        </a>
        <nav class="side-nav">
            <p class="nav-label">Painel</p>
            <?= active("dashboard", "Visão geral", "grid-1x2") ?>
            <?= active("works", "Trabalhos", "journal-text") ?>
            <?= active("bancas", "Bancas", "calendar-event") ?>
            <?= active("calendar", "Calendário", "calendar3") ?>
            <p class="nav-label">Avaliações</p>
            <?php if ($user["perfil"] === "avaliador"): ?>
                <?= active("invitations", "Meus convites", "envelope-paper") ?>
            <?php endif; ?>
            <?= active("public_search", "Consulta pública", "search") ?>
            <?php if ($user["perfil"] === "administrador"): ?>
                <p class="nav-label">Sistema</p>
                <?= active("admin", "Administração", "sliders2") ?>
            <?php endif; ?>
        </nav>
        <div class="sidebar-foot">
            <a class="user-chip" href="?page=notifications">
                <span class="avatar" aria-hidden="true"><?= e(initials($user["nome"])) ?></span>
                <span class="user-chip-text"><strong><?= e($user["nome"]) ?></strong><small><?= e(role_label($user["perfil"])) ?></small></span>
            </a>
            <form method="post" class="logout-form">
                <input type="hidden" name="action" value="logout">
                <?= csrf_field() ?>
                <button class="logout" type="submit"><i class="bi bi-box-arrow-left" aria-hidden="true"></i> Sair da conta</button>
            </form>
        </div>
    </aside>
    <div class="app">
        <header class="topbar">
            <div class="topbar-left">
                <button class="icon-btn menu-btn" type="button" data-sidebar-toggle aria-controls="sidebar" aria-expanded="false" aria-label="Abrir menu">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
                <div class="topbar-title">
                    <span class="eyebrow"><?= e($meta["section"]) ?></span>
                    <h1><?= e($title) ?></h1>
                </div>
            </div>
            <div class="topbar-actions">
                <details class="notif">
                    <summary class="icon-btn notif-trigger" aria-label="Notificações (<?= (int) $unread ?> não lidas)">
                        <i class="bi bi-bell" aria-hidden="true"></i>
                        <?php if ($unread > 0): ?><span class="dot"><?= $unread > 9 ? "9+" : (int) $unread ?></span><?php endif; ?>
                    </summary>
                    <div class="notif-panel">
                        <div class="notif-head"><strong>Notificações</strong><span><?= (int) $unread ?> não lidas</span></div>
                        <?php if ($recentNotifications): ?>
                            <ul>
                                <?php foreach ($recentNotifications as $notification): ?>
                                    <li class="<?= $notification["lida_em"] ? "" : "unread" ?>">
                                        <a href="<?= e($notification["link_interno"] ?: "?page=notifications") ?>">
                                            <strong><?= e($notification["titulo"]) ?></strong>
                                            <span><?= e($notification["mensagem"]) ?></span>
                                            <small><?= e(date_br($notification["created_at"], true)) ?></small>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="notif-empty">Nenhuma notificação por aqui.</p>
                        <?php endif; ?>
                        <a class="notif-all" href="?page=notifications">Ver todas</a>
                    </div>
                </details>
                <div class="user-pill" title="<?= e($user["nome"]) ?>">
                    <span class="avatar small" aria-hidden="true"><?= e(initials($user["nome"])) ?></span>
                    <span><strong><?= e($user["nome"]) ?></strong><small><?= e(role_label($user["perfil"])) ?></small></span>
                </div>
            </div>
        </header>
        <main class="content" id="conteudo">
<?php elseif ($mode === "public"): ?>
    <header class="public-header">
        <a class="brand" href="index.php">
            <span class="brand-mark" aria-hidden="true">SB</span>
            <span class="brand-text"><strong>SIG-Banca</strong><small><?= e($config["institution"]) ?></small></span>
        </a>
        <nav class="public-nav" aria-label="Navegação pública">
            <a href="index.php">Início</a>
            <a href="?page=public_search">Consulta pública</a>
            <?php if (is_logged_in()): ?>
                <a class="btn primary small" href="?page=dashboard">Painel</a>
            <?php else: ?>
                <a class="btn primary small" href="?page=login">Entrar</a>
            <?php endif; ?>
        </nav>
    </header>
    <main class="public-main" id="conteudo">
<?php else: ?>
    <main class="auth-shell" id="conteudo">
<?php endif; ?>

<?php foreach (["success", "error", "info", "warning"] as $flashType): ?>
    <?php foreach (flash($flashType) as $message): ?>
        <?php
        $flashIcon = match ($flashType) {
            "success" => "check-circle",
            "error" => "exclamation-octagon",
            "warning" => "exclamation-triangle",
            default => "info-circle",
        };
        ?>
        <div class="alert <?= e($flashType) ?>" role="status" data-dismissible>
            <i class="bi bi-<?= e($flashIcon) ?>" aria-hidden="true"></i>
            <span><?= e($message) ?></span>
            <button type="button" class="alert-close" data-dismiss aria-label="Fechar aviso">&times;</button>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>
<?php
}

function layout_end(): void
{
    global $config;
    $mode = $GLOBALS["layoutMode"] ?? "public";
    ?>
<?php if ($mode === "app"): ?>
        </main>
        <footer class="app-footer">
            <span>© <?= date("Y") ?> <?= e($config["name"]) ?> · <?= e($config["institution"]) ?></span>
            <span class="app-footer-links"><a href="?page=public_search">Acervo</a><a href="?page=notifications">Notificações</a></span>
        </footer>
    </div>
<?php elseif ($mode === "public"): ?>
    </main>
    <footer class="public-footer">
        <div class="public-footer-brand">
            <span class="brand-mark" aria-hidden="true">SB</span>
            <div><strong><?= e($config["name"]) ?></strong><small>Sistema integrado de gestão de bancas de TCC · <?= e($config["institution"]) ?></small></div>
        </div>
        <nav aria-label="Links do rodapé">
            <a href="index.php">Início</a>
            <a href="?page=public_search">Consulta pública</a>
            <a href="?page=login">Acesso restrito</a>
        </nav>
        <small class="public-footer-copy">© <?= date("Y") ?> Fatec Bauru. Uso acadêmico.</small>
    </footer>
<?php else: ?>
    </main>
<?php endif; ?>
<script src="<?= e(asset("js/app.js")) ?>"></script>
</body>
</html>
<?php
}

$action = is_string($_POST["action"] ?? null) ? $_POST["action"] : "";
$pdo = null;
$uploadedFilePath = null;

try {
    if ($action === "login") {
        verify_csrf();
        if (login_blocked()) {
            throw new RuntimeException("Muitas tentativas de acesso. Aguarde alguns minutos e tente novamente.");
        }
        $email = post_string("email");
        $password = is_string($_POST["password"] ?? null) ? $_POST["password"] : "";
        $record = one(
            "SELECT * FROM usuarios WHERE email = ? AND ativo = 1",
            [$email],
        );
        if (!$record || !password_verify($password, $record["senha_hash"])) {
            register_login_failure();
            throw new RuntimeException("E-mail ou senha inválidos.");
        }
        unset($_SESSION["login_attempts"]);
        session_regenerate_id(true);
        $_SESSION["user"] = array_intersect_key($record, array_flip(["id", "nome", "email", "perfil", "curso_id"]));
        csrf_rotate();
        db_set_actor((int) $record["id"]);
        audit((int) $record["id"], "login", "sessao");
        redirect("index.php?page=dashboard");
    }

    if ($action === "logout") {
        verify_csrf();
        $userId = (int) (current_user()["id"] ?? 0);
        if ($userId) {
            audit($userId, "logout", "sessao");
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), "", [
                "expires" => time() - 42000,
                "path" => $params["path"],
                "domain" => $params["domain"],
                "secure" => $params["secure"],
                "httponly" => $params["httponly"],
                "samesite" => $params["samesite"] ?? "Lax",
            ]);
        }
        session_destroy();
        redirect("index.php?page=login");
    }

    if ($action === "create_work") {
        verify_csrf();
        require_role(["orientador", "administrador"]);
        $user = current_user();
        $title = post_string("titulo");
        $summary = post_string("resumo");
        $courseId = (int) ($_POST["curso_id"] ?? 0);
        $year = (int) ($_POST["ano"] ?? 0);
        $semester = (int) ($_POST["semestre"] ?? 0);
        $students = post_ids("alunos");

        if (mb_strlen($title) < 5 || mb_strlen($title) > 255) {
            throw new RuntimeException("O título deve ter entre 5 e 255 caracteres.");
        }
        if (mb_strlen($summary) > 5000) {
            throw new RuntimeException("O resumo deve ter no máximo 5.000 caracteres.");
        }
        if (!one("SELECT id FROM cursos WHERE id=? AND ativo=1", [$courseId])) {
            throw new RuntimeException("Selecione um curso válido.");
        }
        if ($year < 2000 || $year > 2200 || !in_array($semester, [1, 2], true)) {
            throw new RuntimeException("Informe um ano letivo e um semestre válidos.");
        }
        if (!$students) {
            throw new RuntimeException("Vincule pelo menos um aluno ao trabalho.");
        }
        if ($user["perfil"] === "administrador") {
            $owner = (int) ($_POST["orientador_id"] ?? 0);
            if (!one("SELECT id FROM usuarios WHERE id=? AND perfil IN ('orientador','administrador') AND ativo=1", [$owner])) {
                throw new RuntimeException("Selecione um orientador ativo.");
            }
        } else {
            $owner = (int) $user["id"];
        }
        $validStudents = (int) one(
            "SELECT COUNT(*) AS total FROM usuarios WHERE id IN (" . placeholders($students) . ") AND perfil='aluno' AND ativo=1",
            $students,
        )["total"];
        if ($validStudents !== count($students)) {
            throw new RuntimeException("Um ou mais alunos selecionados não estão disponíveis.");
        }

        $pdo = db();
        $pdo->beginTransaction();
        $pdo->prepare(
            "INSERT INTO trabalhos (titulo,resumo,curso_id,orientador_id,ano_letivo,semestre_letivo,status)
             VALUES (?,?,?,?,?,?, 'RASCUNHO')",
        )->execute([$title, $summary ?: null, $courseId, $owner, $year, $semester]);
        $workId = (int) $pdo->lastInsertId();
        $linkStudent = $pdo->prepare("INSERT INTO alunos_trabalhos (trabalho_id,aluno_id) VALUES (?,?)");
        foreach ($students as $studentId) {
            $linkStudent->execute([$workId, $studentId]);
        }
        audit((int) $user["id"], "criou", "trabalho", $workId, $title);
        $pdo->commit();
        flash("success", "Trabalho cadastrado com sucesso. Agende a banca para iniciar o fluxo.");
        redirect("index.php?page=work&id=" . $workId);
    }

    if ($action === "add_course") {
        verify_csrf();
        require_role(["administrador"]);
        $name = post_string("nome");
        $acronym = mb_strtoupper(post_string("sigla"));
        if (mb_strlen($name) < 2 || mb_strlen($name) > 150) {
            throw new RuntimeException("Informe um nome de curso entre 2 e 150 caracteres.");
        }
        if (!preg_match('/^[A-Z0-9\-]{2,20}$/', $acronym)) {
            throw new RuntimeException("A sigla deve ter de 2 a 20 caracteres, usando letras, números ou hífen.");
        }
        if (one("SELECT id FROM cursos WHERE sigla=? OR nome=?", [$acronym, $name])) {
            throw new RuntimeException("Já existe um curso com esta sigla ou nome.");
        }
        $pdo = db();
        $pdo->prepare("INSERT INTO cursos (nome,sigla) VALUES (?,?)")->execute([$name, $acronym]);
        audit((int) current_user()["id"], "criou", "curso", (int) $pdo->lastInsertId(), $name);
        flash("success", "Curso cadastrado.");
        redirect("index.php?page=admin");
    }

    if ($action === "create_banca") {
        verify_csrf();
        require_role(["orientador", "administrador"]);
        $user = current_user();
        $work = work_by_id((int) ($_POST["trabalho_id"] ?? 0));
        if (!$work || !can_access_work($work)) {
            throw new RuntimeException("Trabalho não encontrado ou sem permissão.");
        }
        if ($work["status"] !== "RASCUNHO") {
            throw new RuntimeException("Só é possível agendar banca para um trabalho em rascunho.");
        }
        $date = valid_date(post_string("data_banca"));
        $start = normalize_time(post_string("hora_inicio"));
        $end = normalize_time(post_string("hora_fim"));
        if (!$date || !$start || !$end) {
            throw new RuntimeException("Informe uma data e horários válidos.");
        }
        if ($end <= $start) {
            throw new RuntimeException("O horário final deve ser posterior ao inicial.");
        }
        $startsAt = new DateTimeImmutable($date . " " . $start);
        if ($startsAt <= new DateTimeImmutable("now")) {
            throw new RuntimeException("A banca deve ser agendada para uma data e hora futuras.");
        }
        $mode = post_string("modalidade");
        if (!in_array($mode, ["presencial", "online", "hibrida"], true)) {
            throw new RuntimeException("Modalidade inválida.");
        }
        $room = post_string("local") ?: null;
        $meetingLink = post_string("link_online") ?: null;
        if (in_array($mode, ["presencial", "hibrida"], true) && !$room) {
            throw new RuntimeException("Informe a sala ou o local da banca.");
        }
        if (in_array($mode, ["online", "hibrida"], true)) {
            $scheme = strtolower((string) parse_url((string) $meetingLink, PHP_URL_SCHEME));
            if (!$meetingLink || filter_var($meetingLink, FILTER_VALIDATE_URL) === false || !in_array($scheme, ["http", "https"], true)) {
                throw new RuntimeException("Informe um link http(s) válido para a banca online.");
            }
        }
        $evaluators = post_ids("avaliadores");
        if (!$evaluators) {
            throw new RuntimeException("Selecione pelo menos um avaliador.");
        }
        $evaluatorRecords = rows(
            "SELECT id,nome,email FROM usuarios WHERE id IN (" . placeholders($evaluators) . ") AND perfil='avaliador' AND ativo=1",
            $evaluators,
        );
        if (count($evaluatorRecords) !== count($evaluators)) {
            throw new RuntimeException("Um ou mais avaliadores selecionados não estão disponíveis.");
        }

        $conflict = one(
            "SELECT b.id FROM bancas b JOIN banca_avaliadores ba ON ba.banca_id=b.id
             WHERE ba.avaliador_id IN (" . placeholders($evaluators) . ") AND b.data_banca=?
               AND b.hora_inicio < ? AND b.hora_fim > ? LIMIT 1",
            array_merge($evaluators, [$date, $end, $start]),
        );
        if ($conflict) {
            throw new RuntimeException("Um dos avaliadores já tem banca nesse intervalo.");
        }
        $advisorConflict = one(
            "SELECT b.id FROM bancas b JOIN trabalhos t ON t.id=b.trabalho_id
             WHERE t.orientador_id=? AND b.data_banca=? AND b.hora_inicio < ? AND b.hora_fim > ? LIMIT 1",
            [(int) $work["orientador_id"], $date, $end, $start],
        );
        if ($advisorConflict) {
            throw new RuntimeException("O orientador já tem banca nesse intervalo.");
        }

        $pdo = db();
        $pdo->beginTransaction();
        $pdo->prepare(
            "INSERT INTO bancas (trabalho_id,data_banca,hora_inicio,hora_fim,modalidade,local,link_online,status,criado_por)
             VALUES (?,?,?,?,?,?,?, 'AGUARDANDO_CONFIRMACOES', ?)",
        )->execute([$work["id"], $date, $start, $end, $mode, $room, $meetingLink, $user["id"]]);
        $bancaId = (int) $pdo->lastInsertId();
        $addEvaluator = $pdo->prepare("INSERT INTO banca_avaliadores (banca_id,avaliador_id,funcao) VALUES (?,?,?)");
        $addInvite = $pdo->prepare(
            "INSERT INTO convites (banca_avaliador_id,token_hash,expira_em,enviado_em)
             VALUES (?,?,DATE_ADD(NOW(), INTERVAL 14 DAY),NOW())",
        );
        $inviteLinks = [];
        foreach ($evaluators as $evaluatorId) {
            $addEvaluator->execute([$bancaId, $evaluatorId, "Avaliador(a)"]);
            $relationId = (int) $pdo->lastInsertId();
            [$token, $tokenHash] = TokenService::create();
            $addInvite->execute([$relationId, $tokenHash]);
            $inviteLinks[$evaluatorId] = base_url("index.php?page=invite&token=" . rawurlencode($token));
            notify(
                $evaluatorId,
                "convite",
                "Novo convite de banca",
                "Você foi convidado(a) para avaliar “{$work["titulo"]}” em " . date_br($date) . " às " . time_br($start) . ".",
                "index.php?page=invitations",
            );
        }
        $pdo->prepare("UPDATE trabalhos SET status='AGUARDANDO_CONFIRMACOES' WHERE id=?")->execute([$work["id"]]);
        audit((int) $user["id"], "criou", "banca", $bancaId, $work["titulo"]);
        $pdo->commit();

        foreach ($evaluatorRecords as $evaluator) {
            $url = $inviteLinks[(int) $evaluator["id"]] ?? base_url("index.php?page=invitations");
            try {
                MailService::invitation(
                    $evaluator["email"],
                    "Convite para banca · SIG-Banca",
                    "Olá, {$evaluator["nome"]}. Você foi convidado(a) para avaliar “{$work["titulo"]}” em " .
                    date_br($date) . " às " . time_br($start) . ". Confirme sua participação em: {$url}",
                );
            } catch (Throwable $mailError) {
                error_log("[SIG-Banca] Falha ao registrar convite: " . $mailError->getMessage());
            }
        }
        flash("success", "Banca agendada e convites enviados aos avaliadores.");
        redirect("index.php?page=banca&id=" . $bancaId);
    }

    if ($action === "respond_invite") {
        verify_csrf();
        $answer = post_string("answer");
        if (!in_array($answer, ["aceito", "recusado"], true)) {
            throw new RuntimeException("Resposta de convite inválida.");
        }
        $pdo = db();
        $token = post_string("token");
        if ($token !== "") {
            $invite = one(
                "SELECT c.*, ba.id AS rel_id, ba.avaliador_id, ba.banca_id, b.trabalho_id
                 FROM convites c
                 JOIN banca_avaliadores ba ON ba.id=c.banca_avaliador_id
                 JOIN bancas b ON b.id=ba.banca_id
                 WHERE c.token_hash=?",
                [TokenService::hash($token)],
            );
            if (is_logged_in() && (int) current_user()["id"] !== (int) ($invite["avaliador_id"] ?? 0)) {
                throw new RuntimeException("Este convite pertence a outro avaliador.");
            }
        } else {
            require_role(["avaliador"]);
            $invite = one(
                "SELECT c.*, ba.id AS rel_id, ba.avaliador_id, ba.banca_id, b.trabalho_id
                 FROM convites c
                 JOIN banca_avaliadores ba ON ba.id=c.banca_avaliador_id
                 JOIN bancas b ON b.id=ba.banca_id
                 WHERE c.id=? AND ba.avaliador_id=?",
                [(int) ($_POST["convite_id"] ?? 0), (int) current_user()["id"]],
            );
        }
        if (!$invite) {
            throw new RuntimeException("Convite inexistente.");
        }
        if ($invite["status"] !== "pendente" || $invite["respondido_em"] !== null) {
            throw new RuntimeException("Este convite já foi respondido.");
        }
        if ($invite["expira_em"] !== null && strtotime((string) $invite["expira_em"]) < time()) {
            $pdo->prepare("UPDATE convites SET status='expirado' WHERE id=? AND status='pendente'")->execute([$invite["id"]]);
            throw new RuntimeException("Este convite expirou.");
        }

        $pdo->beginTransaction();
        $respond = $pdo->prepare(
            "UPDATE convites SET status=?, respondido_em=NOW() WHERE id=? AND status='pendente' AND respondido_em IS NULL",
        );
        $respond->execute([$answer, $invite["id"]]);
        if ($respond->rowCount() !== 1) {
            throw new RuntimeException("Este convite já foi respondido.");
        }
        $updateEvaluator = $pdo->prepare(
            "UPDATE banca_avaliadores SET status_resposta=?, respondido_em=NOW() WHERE id=? AND status_resposta='pendente'",
        );
        $updateEvaluator->execute([$answer, $invite["rel_id"]]);
        if ($updateEvaluator->rowCount() !== 1) {
            throw new RuntimeException("A resposta do avaliador já foi registrada.");
        }

        $banca = one(
            "SELECT b.*, t.titulo, t.orientador_id FROM bancas b JOIN trabalhos t ON t.id=b.trabalho_id WHERE b.id=?",
            [$invite["banca_id"]],
        );
        $counts = one(
            "SELECT
                SUM(status_resposta='aceito') AS aceitos,
                SUM(status_resposta='recusado') AS recusados,
                SUM(status_resposta='pendente') AS pendentes
             FROM banca_avaliadores WHERE banca_id=?",
            [$invite["banca_id"]],
        );
        $pending = (int) ($counts["pendentes"] ?? 0);
        $refused = (int) ($counts["recusados"] ?? 0);
        if ($answer === "aceito" && $pending === 0 && $refused === 0 && $banca) {
            $pdo->prepare("UPDATE bancas SET status='CONFIRMADA' WHERE id=? AND status='AGUARDANDO_CONFIRMACOES'")->execute([$invite["banca_id"]]);
            $pdo->prepare("UPDATE trabalhos SET status='CONFIRMADA' WHERE id=? AND status='AGUARDANDO_CONFIRMACOES'")->execute([$invite["trabalho_id"]]);
            notify(
                (int) $banca["orientador_id"],
                "banca",
                "Banca confirmada",
                "Todos os avaliadores aceitaram a banca de “{$banca["titulo"]}”.",
                "index.php?page=banca&id=" . (int) $banca["id"],
            );
            foreach (rows("SELECT aluno_id FROM alunos_trabalhos WHERE trabalho_id=?", [$invite["trabalho_id"]]) as $student) {
                notify(
                    (int) $student["aluno_id"],
                    "banca",
                    "Banca confirmada",
                    "A banca de “{$banca["titulo"]}” está confirmada para " . date_br($banca["data_banca"]) . ".",
                    "index.php?page=work&id=" . (int) $invite["trabalho_id"],
                );
            }
        } elseif ($answer === "recusado" && $banca) {
            notify(
                (int) $banca["orientador_id"],
                "convite",
                "Avaliador recusou o convite",
                "É necessário substituir um avaliador da banca de “{$banca["titulo"]}”.",
                "index.php?page=banca&id=" . (int) $banca["id"],
            );
        }
        audit(
            is_logged_in() ? (int) current_user()["id"] : (int) $invite["avaliador_id"],
            "respondeu_convite_" . $answer,
            "convite",
            (int) $invite["id"],
        );
        $pdo->commit();
        flash("success", $answer === "aceito" ? "Convite aceito. Obrigado!" : "Convite recusado. O orientador será avisado.");
        if ($token !== "") {
            redirect("index.php?page=invite&respondido=1");
        }
        redirect("index.php?page=invitations");
    }

    if ($action === "resend_invite") {
        verify_csrf();
        require_role(["orientador", "administrador"]);
        $relation = one(
            "SELECT ba.*, b.id AS banca_id, b.trabalho_id, b.data_banca, b.hora_inicio, b.hora_fim,
                    b.status AS banca_status, t.titulo, t.orientador_id,
                    u.nome AS avaliador_nome, u.email AS avaliador_email
             FROM banca_avaliadores ba
             JOIN bancas b ON b.id=ba.banca_id
             JOIN trabalhos t ON t.id=b.trabalho_id
             JOIN usuarios u ON u.id=ba.avaliador_id
             WHERE ba.id=?",
            [(int) ($_POST["banca_avaliador_id"] ?? 0)],
        );
        if (!$relation || !can_access_work($relation)) {
            throw new RuntimeException("Convite não encontrado ou sem permissão.");
        }
        if ($relation["banca_status"] !== "AGUARDANDO_CONFIRMACOES") {
            throw new RuntimeException("Só é possível reenviar convites de uma banca aguardando confirmações.");
        }
        if ($relation["status_resposta"] === "aceito") {
            throw new RuntimeException("Este avaliador já aceitou o convite.");
        }

        $pdo = db();
        $pdo->beginTransaction();
        $url = InviteService::issue((int) $relation["id"]);
        $pdo->prepare(
            "UPDATE banca_avaliadores SET status_resposta='pendente', respondido_em=NULL WHERE id=?",
        )->execute([$relation["id"]]);
        audit((int) current_user()["id"], "reenviou_convite", "banca", (int) $relation["banca_id"]);
        $pdo->commit();

        InviteService::send(
            ["id" => (int) $relation["avaliador_id"], "nome" => $relation["avaliador_nome"], "email" => $relation["avaliador_email"]],
            $relation,
            $relation["titulo"],
            $url,
        );
        flash("success", "Convite reenviado. Novo prazo de resposta: " . InviteService::VALIDADE_DIAS . " dias.");
        redirect("index.php?page=banca&id=" . (int) $relation["banca_id"]);
    }

    if ($action === "replace_evaluator") {
        verify_csrf();
        require_role(["orientador", "administrador"]);
        $relation = one(
            "SELECT ba.*, b.id AS banca_id, b.trabalho_id, b.data_banca, b.hora_inicio, b.hora_fim,
                    b.status AS banca_status, t.titulo, t.orientador_id,
                    u.nome AS avaliador_nome, u.email AS avaliador_email
             FROM banca_avaliadores ba
             JOIN bancas b ON b.id=ba.banca_id
             JOIN trabalhos t ON t.id=b.trabalho_id
             JOIN usuarios u ON u.id=ba.avaliador_id
             WHERE ba.id=?",
            [(int) ($_POST["banca_avaliador_id"] ?? 0)],
        );
        if (!$relation || !can_access_work($relation)) {
            throw new RuntimeException("Avaliador não encontrado ou sem permissão.");
        }
        if ($relation["banca_status"] !== "AGUARDANDO_CONFIRMACOES") {
            throw new RuntimeException("A comissão só pode ser alterada enquanto a banca aguarda confirmações.");
        }
        $newId = (int) ($_POST["novo_avaliador_id"] ?? 0);
        $replacement = one(
            "SELECT id,nome,email FROM usuarios WHERE id=? AND perfil='avaliador' AND ativo=1",
            [$newId],
        );
        if (!$replacement || $newId === (int) $relation["avaliador_id"]) {
            throw new RuntimeException("Selecione um avaliador ativo diferente do atual.");
        }
        if (one("SELECT id FROM banca_avaliadores WHERE banca_id=? AND avaliador_id=?", [$relation["banca_id"], $newId])) {
            throw new RuntimeException("Este avaliador já faz parte da comissão.");
        }
        $conflict = one(
            "SELECT b.id FROM bancas b JOIN banca_avaliadores ba ON ba.banca_id=b.id
             WHERE ba.avaliador_id=? AND b.id<>? AND b.data_banca=?
               AND b.hora_inicio < ? AND b.hora_fim > ? LIMIT 1",
            [$newId, $relation["banca_id"], $relation["data_banca"], $relation["hora_fim"], $relation["hora_inicio"]],
        );
        if ($conflict) {
            throw new RuntimeException("O novo avaliador já tem banca nesse intervalo.");
        }

        $pdo = db();
        $pdo->beginTransaction();
        $url = InviteService::issue((int) $relation["id"]);
        $pdo->prepare(
            "UPDATE banca_avaliadores
             SET avaliador_id=?, status_resposta='pendente', respondido_em=NULL
             WHERE id=?",
        )->execute([$newId, $relation["id"]]);
        audit(
            (int) current_user()["id"],
            "substituiu_avaliador",
            "banca",
            (int) $relation["banca_id"],
            $relation["avaliador_nome"] . " -> " . $replacement["nome"],
        );
        $pdo->commit();

        InviteService::send($replacement, $relation, $relation["titulo"], $url);
        flash("success", "Avaliador substituído. O novo convite foi enviado.");
        redirect("index.php?page=banca&id=" . (int) $relation["banca_id"]);
    }

    if ($action === "defense") {
        verify_csrf();
        require_role(["orientador", "administrador"]);
        $banca = banca_by_id((int) ($_POST["banca_id"] ?? 0));
        if (!$banca || !can_access_work($banca)) {
            throw new RuntimeException("Banca não encontrada ou sem permissão.");
        }
        if ($banca["status"] !== "CONFIRMADA" || $banca["trabalho_status"] !== "CONFIRMADA") {
            throw new RuntimeException("A banca precisa estar confirmada antes de registrar a realização.");
        }
        $pdo = db();
        $pdo->beginTransaction();
        $updateBanca = $pdo->prepare("UPDATE bancas SET status='REALIZADA', realizada_em=NOW() WHERE id=? AND status='CONFIRMADA'");
        $updateBanca->execute([$banca["id"]]);
        if ($updateBanca->rowCount() !== 1) {
            throw new RuntimeException("A situação da banca mudou. Atualize a página e tente novamente.");
        }
        // Keep the banca timeline aligned with the work: "Defesa" is done and the
        // documentation stage is now current. The trigger only allows the path
        // CONFIRMADA -> REALIZADA -> AGUARDANDO_DOCUMENTOS.
        $pdo->prepare("UPDATE bancas SET status='AGUARDANDO_DOCUMENTOS' WHERE id=? AND status='REALIZADA'")
            ->execute([$banca["id"]]);
        $updateWork = $pdo->prepare("UPDATE trabalhos SET status='AGUARDANDO_DOCUMENTOS' WHERE id=? AND status='CONFIRMADA'");
        $updateWork->execute([$banca["trabalho_id"]]);
        if ($updateWork->rowCount() !== 1) {
            throw new RuntimeException("A situação do trabalho mudou. Atualize a página e tente novamente.");
        }
        foreach (rows("SELECT aluno_id FROM alunos_trabalhos WHERE trabalho_id=?", [$banca["trabalho_id"]]) as $student) {
            notify(
                (int) $student["aluno_id"],
                "banca",
                "Defesa realizada",
                "A defesa de “{$banca["titulo"]}” foi registrada. A etapa de documentação está aberta.",
                "index.php?page=work&id=" . (int) $banca["trabalho_id"],
            );
        }
        audit((int) current_user()["id"], "registrou_realizacao", "banca", (int) $banca["id"]);
        $pdo->commit();
        flash("success", "Defesa registrada. O trabalho entrou na etapa de documentação.");
        redirect("index.php?page=banca&id=" . $banca["id"]);
    }

    if ($action === "correction") {
        verify_csrf();
        require_role(["orientador", "administrador"]);
        $work = work_by_id((int) ($_POST["trabalho_id"] ?? 0));
        if (!$work || !can_access_work($work)) {
            throw new RuntimeException("Trabalho não encontrado ou sem permissão.");
        }
        if (!in_array($work["status"], ["AGUARDANDO_DOCUMENTOS", "AGUARDANDO_CORRECOES"], true)) {
            throw new RuntimeException("As correções só podem ser formalizadas após a realização da banca.");
        }
        $baseDate = valid_date(post_string("data_liberacao"));
        $description = post_string("descricao");
        if (!$baseDate || $description === "") {
            throw new RuntimeException("Informe uma data-base válida e a descrição das correções.");
        }
        if ($baseDate > date("Y-m-d")) {
            throw new RuntimeException("A data-base das correções não pode estar no futuro.");
        }
        $bancaId = (int) ($_POST["banca_id"] ?? 0);
        $banca = $bancaId ? one("SELECT * FROM bancas WHERE id=? AND trabalho_id=?", [$bancaId, $work["id"]]) : null;
        if (!$banca) {
            throw new RuntimeException("A banca informada não pertence a este trabalho.");
        }
        $pdo = db();
        $deadline = PrazoService::deadline($baseDate);
        $pdo->beginTransaction();
        $pdo->prepare(
            "INSERT INTO correcoes (trabalho_id,banca_id,descricao,data_liberacao,prazo_final,registrado_por)
             VALUES (?,?,?,?,?,?)",
        )->execute([$work["id"], $bancaId, $description, $baseDate, $deadline, current_user()["id"]]);

        // Corrections stay open until the student uploads the final version, so
        // the "Correções" step is actually visible while the 15-day deadline runs.
        $pdo->prepare(
            "UPDATE bancas SET status='AGUARDANDO_CORRECOES' WHERE id=? AND status IN ('REALIZADA','AGUARDANDO_DOCUMENTOS','AGUARDANDO_CORRECOES')",
        )->execute([$bancaId]);
        $pdo->prepare(
            "UPDATE trabalhos SET status='AGUARDANDO_CORRECOES' WHERE id=? AND status IN ('AGUARDANDO_DOCUMENTOS','AGUARDANDO_CORRECOES')",
        )->execute([$work["id"]]);
        foreach (rows("SELECT aluno_id FROM alunos_trabalhos WHERE trabalho_id=?", [$work["id"]]) as $student) {
            notify(
                (int) $student["aluno_id"],
                "prazo",
                "Correções liberadas",
                "A versão definitiva vence em " . date_br($deadline) . ".",
                "index.php?page=work&id=" . (int) $work["id"],
            );
        }
        audit((int) current_user()["id"], "liberou_correcoes", "trabalho", (int) $work["id"]);
        $pdo->commit();
        flash("success", "Correções registradas. Prazo final: " . date_br($deadline) . ".");
        redirect("index.php?page=work&id=" . $work["id"]);
    }

    if ($action === "upload") {
        verify_csrf();
        require_login();
        $work = work_by_id((int) ($_POST["trabalho_id"] ?? 0));
        if (!$work || !can_access_work($work)) {
            throw new RuntimeException("Trabalho não encontrado ou sem permissão.");
        }
        $user = current_user();
        $type = post_string("tipo");
        $allowedByRole = $user["perfil"] === "aluno"
            ? ["versao_final"]
            : (in_array($user["perfil"], ["orientador", "administrador"], true)
                ? ["ata", "autorizacao_ric", "outro"]
                : []);
        if (!in_array($type, $allowedByRole, true)) {
            throw new RuntimeException("Seu perfil não pode enviar este tipo de documento.");
        }
        if ($type === "versao_final" && !in_array($work["status"], ["AGUARDANDO_CORRECOES", "AGUARDANDO_VERSAO_FINAL", "AJUSTES_SOLICITADOS"], true)) {
            throw new RuntimeException("O trabalho ainda não está aguardando uma versão definitiva.");
        }
        $bancaId = (int) ($_POST["banca_id"] ?? 0);
        if ($bancaId && !one("SELECT id FROM bancas WHERE id=? AND trabalho_id=?", [$bancaId, $work["id"]])) {
            throw new RuntimeException("A banca informada não pertence a este trabalho.");
        }
        $saved = UploadService::save($_FILES["arquivo"] ?? []);
        $uploadedFilePath = $config["root"] . "/" . $saved["path"];

        $pdo = db();
        $pdo->beginTransaction();
        $lock = $pdo->prepare("SELECT status FROM trabalhos WHERE id=? FOR UPDATE");
        $lock->execute([$work["id"]]);
        $lockedStatus = (string) $lock->fetchColumn();
        if ($type === "versao_final" && !in_array($lockedStatus, ["AGUARDANDO_CORRECOES", "AGUARDANDO_VERSAO_FINAL", "AJUSTES_SOLICITADOS"], true)) {
            throw new RuntimeException("A situação do trabalho mudou antes do envio. Atualize a página.");
        }
        $pdo->prepare(
            "INSERT INTO documentos (trabalho_id,banca_id,tipo,nome_original,nome_armazenado,caminho_armazenado,mime_type,tamanho_bytes,enviado_por)
             VALUES (?,?,?,?,?,?,?,?,?)",
        )->execute([
            $work["id"],
            $bancaId ?: null,
            $type,
            $saved["original"],
            $saved["stored"],
            $saved["path"],
            $saved["mime"],
            $saved["size"],
            $user["id"],
        ]);
        $documentId = (int) $pdo->lastInsertId();
        if ($type === "versao_final") {
            $nextVersion = (int) one(
                "SELECT COALESCE(MAX(numero_versao),0)+1 AS next_version FROM versoes WHERE trabalho_id=?",
                [$work["id"]],
            )["next_version"];
            $pdo->prepare(
                "INSERT INTO versoes (trabalho_id,documento_id,numero_versao,enviado_por,status)
                 VALUES (?,?,?,?, 'em_analise')",
            )->execute([$work["id"], $documentId, $nextVersion, $user["id"]]);
            // The version fulfils the open corrections, so the work passes
            // through "Versão final" before entering approval.
            if ($lockedStatus === "AGUARDANDO_CORRECOES") {
                $pdo->prepare("UPDATE trabalhos SET status='AGUARDANDO_VERSAO_FINAL' WHERE id=? AND status='AGUARDANDO_CORRECOES'")
                    ->execute([$work["id"]]);
                $pdo->prepare("UPDATE bancas SET status='AGUARDANDO_VERSAO_FINAL' WHERE trabalho_id=? AND status='AGUARDANDO_CORRECOES'")
                    ->execute([$work["id"]]);
            }
            $pdo->prepare("UPDATE trabalhos SET status='AGUARDANDO_APROVACAO' WHERE id=?")->execute([$work["id"]]);
            $pdo->prepare(
                "UPDATE bancas SET status='AGUARDANDO_APROVACAO'
                 WHERE trabalho_id=? AND status IN ('AGUARDANDO_VERSAO_FINAL','AJUSTES_SOLICITADOS')",
            )->execute([$work["id"]]);
            $pdo->prepare("UPDATE correcoes SET status='entregue' WHERE trabalho_id=? AND status='aberta'")->execute([$work["id"]]);
            notify(
                (int) $work["orientador_id"],
                "versao",
                "Versão final enviada",
                "Há uma nova versão de “{$work["titulo"]}” aguardando avaliação.",
                "index.php?page=work&id=" . (int) $work["id"],
            );
        }
        audit((int) $user["id"], "enviou_documento", "documento", $documentId, $type);
        $pdo->commit();
        $uploadedFilePath = null;
        flash("success", "Documento enviado com segurança.");
        redirect("index.php?page=work&id=" . $work["id"]);
    }

    if ($action === "review_version") {
        verify_csrf();
        require_role(["orientador", "administrador"]);
        $decision = post_string("decision");
        if (!in_array($decision, ["approve", "adjust"], true)) {
            throw new RuntimeException("Decisão de avaliação inválida.");
        }
        $version = one(
            "SELECT v.*, t.orientador_id, t.titulo, t.status AS trabalho_status
             FROM versoes v JOIN trabalhos t ON t.id=v.trabalho_id WHERE v.id=?",
            [(int) ($_POST["versao_id"] ?? 0)],
        );
        if (!$version || !can_access_work($version)) {
            throw new RuntimeException("Versão não encontrada ou sem permissão.");
        }
        if ($version["status"] !== "em_analise" || $version["trabalho_status"] !== "AGUARDANDO_APROVACAO") {
            throw new RuntimeException("Esta versão não está aguardando avaliação.");
        }
        $observation = post_string("observacao");
        if ($decision === "adjust" && $observation === "") {
            throw new RuntimeException("Informe o motivo dos ajustes solicitados.");
        }
        $pdo = db();
        $pdo->beginTransaction();
        $newStatus = $decision === "approve" ? "aprovada" : "ajustes_solicitados";
        $workStatus = $decision === "approve" ? "CONCLUIDA" : "AJUSTES_SOLICITADOS";
        $review = $pdo->prepare(
            "UPDATE versoes SET status=?, avaliado_por=?, avaliado_em=NOW(), observacao_orientador=?
             WHERE id=? AND status='em_analise'",
        );
        $review->execute([$newStatus, current_user()["id"], $observation ?: null, $version["id"]]);
        if ($review->rowCount() !== 1) {
            throw new RuntimeException("Esta versão já foi avaliada.");
        }
        $pdo->prepare("UPDATE trabalhos SET status=? WHERE id=?")->execute([$workStatus, $version["trabalho_id"]]);
        $pdo->prepare(
            "UPDATE bancas SET status=? WHERE trabalho_id=? AND status='AGUARDANDO_APROVACAO'",
        )->execute([$workStatus, $version["trabalho_id"]]);
        foreach (rows("SELECT aluno_id FROM alunos_trabalhos WHERE trabalho_id=?", [$version["trabalho_id"]]) as $student) {
            notify(
                (int) $student["aluno_id"],
                "versao",
                $decision === "approve" ? "Versão aprovada" : "Ajustes solicitados",
                $decision === "approve"
                    ? "Seu trabalho “{$version["titulo"]}” foi concluído."
                    : $observation,
                "index.php?page=work&id=" . (int) $version["trabalho_id"],
            );
        }
        audit(
            (int) current_user()["id"],
            $decision === "approve" ? "aprovou_versao" : "solicitou_ajustes",
            "versao",
            (int) $version["id"],
        );
        $pdo->commit();
        flash("success", $decision === "approve" ? "Versão aprovada e trabalho concluído." : "Ajustes solicitados ao aluno.");
        redirect("index.php?page=work&id=" . $version["trabalho_id"]);
    }

    if ($action === "validate_document") {
        verify_csrf();
        require_role(["orientador", "administrador"]);
        $decision = post_string("decision");
        if (!in_array($decision, ["aprovado", "correcao"], true)) {
            throw new RuntimeException("Decisão de validação inválida.");
        }
        $document = one(
            "SELECT d.*, t.orientador_id, t.titulo FROM documentos d JOIN trabalhos t ON t.id=d.trabalho_id WHERE d.id=?",
            [(int) ($_POST["documento_id"] ?? 0)],
        );
        if (
            !$document ||
            !can_access_work([
                "id" => (int) $document["trabalho_id"],
                "orientador_id" => (int) $document["orientador_id"],
            ])
        ) {
            throw new RuntimeException("Documento não encontrado ou sem permissão.");
        }
        if (!in_array($document["status_validacao"], ["pendente", "correcao"], true)) {
            throw new RuntimeException("Este documento já foi validado.");
        }
        if ($document["status_validacao"] === "correcao" && $decision === "correcao") {
            throw new RuntimeException("Este documento já está aguardando ajustes.");
        }
        $observation = post_string("observacao");
        if ($decision === "correcao" && $observation === "") {
            throw new RuntimeException("Descreva o ajuste necessário no documento.");
        }
        $pdo = db();
        $pdo->beginTransaction();
        $update = $pdo->prepare(
            "UPDATE documentos
             SET status_validacao=?, validado_por=?, validado_em=NOW(), observacao=?
             WHERE id=? AND status_validacao IN ('pendente','correcao')",
        );
        $update->execute([$decision, current_user()["id"], $observation ?: null, $document["id"]]);
        if ($update->rowCount() !== 1) {
            throw new RuntimeException("A situação do documento mudou. Atualize a página.");
        }
        if ((int) $document["enviado_por"] !== (int) current_user()["id"]) {
            notify(
                (int) $document["enviado_por"],
                "documento",
                $decision === "aprovado" ? "Documento aprovado" : "Ajuste solicitado em documento",
                $decision === "aprovado"
                    ? "O documento “{$document["nome_original"]}” foi aprovado."
                    : "O documento “{$document["nome_original"]}” precisa de ajustes: {$observation}",
                "index.php?page=work&id=" . (int) $document["trabalho_id"],
            );
        }
        audit((int) current_user()["id"], "validou_documento", "documento", (int) $document["id"], $decision);
        $pdo->commit();
        flash("success", $decision === "aprovado" ? "Documento aprovado." : "Ajuste solicitado ao responsável.");
        redirect("index.php?page=work&id=" . $document["trabalho_id"]);
    }

    if ($action === "toggle_public") {
        verify_csrf();
        require_role(["orientador", "administrador"]);
        $work = work_by_id((int) ($_POST["trabalho_id"] ?? 0));
        if (!$work || !can_access_work($work)) {
            throw new RuntimeException("Trabalho não encontrado ou sem permissão.");
        }
        $pdo = db();
        $newValue = (int) $work["publico"] === 1 ? 0 : 1;
        $pdo->prepare("UPDATE trabalhos SET publico=? WHERE id=?")->execute([$newValue, $work["id"]]);
        audit(
            (int) current_user()["id"],
            $newValue ? "publicou_trabalho" : "despublicou_trabalho",
            "trabalho",
            (int) $work["id"],
        );
        flash("success", $newValue ? "Trabalho publicado na consulta pública." : "Trabalho removido da consulta pública.");
        redirect("index.php?page=work&id=" . $work["id"]);
    }

    if ($action === "mark_notifications_read") {
        verify_csrf();
        require_login();
        $pdo = db();
        $pdo->prepare("UPDATE notificacoes SET lida_em=NOW() WHERE usuario_id=? AND lida_em IS NULL")
            ->execute([current_user()["id"]]);
        flash("info", "Notificações marcadas como lidas.");
        redirect("index.php?page=notifications");
    }
} catch (Throwable $error) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($uploadedFilePath !== null && is_file($uploadedFilePath)) {
        @unlink($uploadedFilePath);
    }
    if (!($error instanceof RuntimeException) || $error instanceof PDOException) {
        error_log("[SIG-Banca] " . $error);
    }
    if ($error instanceof PDOException) {
        flash("error", "Não foi possível concluir a operação. Revise os dados e tente novamente.");
    } elseif ($error instanceof RuntimeException) {
        flash("error", $error->getMessage());
    } else {
        flash("error", $config["debug"]
            ? $error->getMessage()
            : "Ocorreu um erro inesperado. Tente novamente.");
    }
    $target = "index.php?page=" . urlencode($currentPage);
    if ($currentPage === "invite" && post_string("token") !== "") {
        $target .= "&token=" . rawurlencode(post_string("token"));
    } elseif (!empty($_GET["id"])) {
        $target .= "&id=" . (int) $_GET["id"];
    }
    redirect($target);
}

if (!in_array($currentPage, ["home", "login", "public_search", "invite"], true)) {
    require_login();
}

/* Authenticated file download. Stored files live outside the public folder. */
if ($currentPage === "download") {
    $document = one("SELECT * FROM documentos WHERE id=?", [(int) ($_GET["id"] ?? 0)]);
    if (!$document) {
        abort(404, "Documento não encontrado.");
    }
    $work = work_by_id((int) $document["trabalho_id"]);
    if (!$work || !can_access_work($work)) {
        abort(403, "Você não tem acesso a este documento.");
    }
    $base = realpath($config["storage"] . "/uploads");
    $file = realpath($config["root"] . "/" . $document["caminho_armazenado"]);
    if (!$base || !$file || !str_starts_with($file, $base . DIRECTORY_SEPARATOR) || !is_file($file)) {
        abort(404, "O arquivo solicitado não está disponível.");
    }
    $filename = preg_replace('/[^\pL\pN._ -]+/u', "_", $document["nome_original"]) ?: "documento.pdf";
    header("Content-Type: application/pdf");
    header("Content-Length: " . (string) filesize($file));
    header("Content-Disposition: inline; filename=\"" . $filename . "\"; filename*=UTF-8''" . rawurlencode($filename));
    header("X-Content-Type-Options: nosniff");
    readfile($file);
    exit();
}

require __DIR__ . "/pages.php";
