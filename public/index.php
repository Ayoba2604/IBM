<?php
declare(strict_types=1);
require_once dirname(__DIR__) . "/app/bootstrap.php";
$currentPage = $_GET['page'] ?? (is_logged_in() ? 'dashboard' : 'home');
function rows(string $q, array $p = []): array
{
    $s = db()->prepare($q);
    $s->execute($p);
    return $s->fetchAll();
}
function one(string $q, array $p = []): ?array
{
    $s = db()->prepare($q);
    $s->execute($p);
    return $s->fetch() ?: null;
}
function active(string $p, string $l, string $i): string
{
    global $currentPage;
    return '<a class="nav-link ' .
        ($currentPage === $p ? "active" : "") .
        '" href="?page=' .
        $p .
        '"><i class="bi bi-' .
        $i .
        '"></i>' .
        $l .
        "</a>";
}
function layout_start(string $title): void
{
    $u = current_user();
    ?>
    <!doctype html>
    <html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | SIG-Banca</title>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
        <link href="assets/css/style.css" rel="stylesheet">
    </head>
    <body>
    <?php if ($u): ?>
        <aside class="sidebar">
            <a class="brand" href="?page=dashboard"><span>SB</span>SIG-Banca</a>
            <nav>
                <?= active('dashboard', 'Visao geral', 'grid-1x2') ?>
                <?= active('bancas', 'Bancas', 'calendar-event') ?>
                <?= active('works', 'Trabalhos', 'journal-text') ?>
                <?= active('calendar', 'Calendario', 'calendar3') ?>
                <?= active('public_search', 'Consulta publica', 'search') ?>
                <?php if ($u['perfil'] === 'administrador'): ?>
                    <?= active('admin', 'Administracao', 'sliders') ?>
                <?php endif; ?>
            </nav>
            <div class="sidebar-bottom">
                <small><?= e($u['perfil']) ?></small>
                <strong><?= e($u['nome']) ?></strong>
                <form method="post">
                    <input type="hidden" name="action" value="logout">
                    <?= csrf_field() ?>
                    <button class="logout">Sair</button>
                </form>
            </div>
        </aside>
        <main class="app">
            <header><div><span class="eyebrow">FATEC BAURU</span><h1><?= e($title) ?></h1></div><div class="header-user"><?= e($u['nome']) ?></div></header>
    <?php else: ?>
        <header class="public-header">
            <a class="brand" href="index.php"><span>SB</span>SIG-Banca</a>
            <nav><a href="?page=public_search">Consulta publica</a><a class="btn primary" href="?page=login">Entrar</a></nav>
        </header>
        <main class="public-main">
    <?php endif; ?>
    <?php foreach (['success', 'error'] as $type): ?>
        <?php foreach (flash($type) as $message): ?>
            <div class="alert <?= e($type) ?>"><?= e($message) ?></div>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <?php
}

function layout_end(): void
{
    ?>
        </main>
        <script src="assets/js/app.js"></script>
    </body>
    </html>
    <?php
}
$action = $_POST['action'] ?? '';
try {
    if ($action === 'login') {
        verify_csrf();
        $record = one('SELECT * FROM usuarios WHERE email = ? AND ativo = 1', [trim($_POST['email'] ?? '')]);
        if (!$record || !password_verify($_POST['password'] ?? '', $record['senha_hash'])) {
            throw new RuntimeException('E-mail ou senha invalidos.');
        }
        session_regenerate_id(true);
        $_SESSION['user'] = array_intersect_key($record, array_flip(['id', 'nome', 'email', 'perfil', 'curso_id']));
        redirect('index.php?page=dashboard');
    }
    if ($action === 'logout') {
        verify_csrf();
        $_SESSION = [];
        session_destroy();
        redirect('index.php?page=login');
    }
    if ($action === 'create_work') {
        verify_csrf();
        require_role(['orientador', 'administrador']);
        $user = current_user();
        $students = array_unique(array_map('intval', $_POST['alunos'] ?? []));
        if (!trim($_POST['titulo'] ?? '') || !$students) {
            throw new RuntimeException('Informe titulo e alunos.');
        }
        $owner = $user['perfil'] === 'administrador' ? (int) $_POST['orientador_id'] : (int) $user['id'];
        $pdo = db();
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO trabalhos(titulo,resumo,curso_id,orientador_id,ano_letivo,semestre_letivo,status) VALUES(?,?,?,?,?,?,?)')->execute([trim($_POST['titulo']), trim($_POST['resumo']), $_POST['curso_id'], $owner, $_POST['ano'], $_POST['semestre'], 'RASCUNHO']);
        $id = (int) $pdo->lastInsertId();
        $linkStudent = $pdo->prepare('INSERT INTO alunos_trabalhos(trabalho_id,aluno_id) VALUES(?,?)');
        foreach ($students as $student) {
            $linkStudent->execute([$id, $student]);
        }
        $pdo->commit();
        flash('success', 'Trabalho cadastrado.');
        redirect('index.php?page=work&id=' . $id);
    }
    if ($action === 'add_course') {
        verify_csrf();
        require_role(['administrador']);
        db()->prepare('INSERT INTO cursos(nome,sigla) VALUES(?,?)')->execute([trim($_POST['nome']), strtoupper(trim($_POST['sigla']))]);
        flash('success', 'Curso cadastrado.');
        redirect('index.php?page=admin');
    }
} catch (Throwable $e) {
    if (db()->inTransaction()) {
        db()->rollBack();
    }
    flash('error', $e->getMessage());
    redirect('index.php?page=' . urlencode($_GET['page'] ?? 'login'));
}
if (!in_array($currentPage, ['home', 'login', 'public_search'], true)) {
    require_login();
}
if($currentPage==='home'){layout_start('Gestao academica');?><section class="hero"><div><span class="eyebrow">SISTEMA INTEGRADO DE GESTAO</span><h1>Da banca a versao final,<br><em>tudo em um so lugar.</em></h1><p>Organize bancas, documentos e prazos para toda a comunidade academica.</p><a class="btn primary" href="?page=login">Acessar sistema</a></div><div class="hero-card"><p>Proxima banca</p><h2>Plataforma de Gestao de Bancas</h2><hr><b>19:00 - Hibrida</b></div></section><?php
layout_end();
exit();
}
if ($currentPage === "login") {
    layout_start(
        "Entrar",
    ); ?><section class="login-wrap"><div class="login-aside"><h1>O processo academico merece fluidez.</h1></div><form class="login-card" method="post"><h2>Bem-vindo(a)</h2><input type="hidden" name="action" value="login"><?= csrf_field() ?><label>E-mail<input required type="email" name="email"></label><label>Senha<input required type="password" name="password"></label><button class="btn primary full">Entrar</button><small>Demo: senha password</small></form></section><?php
layout_end();
exit();

}
$u = current_user();
if ($currentPage === "dashboard") {

    $b = rows(
        "SELECT b.*,t.titulo FROM bancas b JOIN trabalhos t ON t.id=b.trabalho_id ORDER BY b.data_banca LIMIT 4",
    );
    layout_start("Visao geral");
    ?><div class="dashboard-top"><p>Acompanhe as etapas que precisam da sua atencao.</p><a class="btn primary" href="?page=work_new">Novo trabalho</a></div><section class="metrics"><article><span><?= count(
    $b,
) ?></span><p>Proximas bancas</p></article><article><span>15</span><p>Dias de prazo</p></article><article><span><?= count(
    rows("SELECT id FROM trabalhos WHERE status='CONCLUIDA'"),
) ?></span><p>Concluidos</p></article><article><span><?= count(
    rows("SELECT id FROM trabalhos"),
) ?></span><p>Trabalhos</p></article></section><section class="panel"><?php foreach (
    $b
    as $x
): ?><a class="event" href="?page=banca&id=<?= $x["id"] ?>"><time><b><?= date(
    "d",
    strtotime($x["data_banca"]),
) ?></b></time><div><strong><?= e($x["titulo"]) ?></strong><span><?= e(
    $x["modalidade"],
) ?></span></div></a><?php endforeach; ?></section><?php
layout_end();
exit();

}
require __DIR__ . "/pages.php";
