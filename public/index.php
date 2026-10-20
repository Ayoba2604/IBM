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
                <?= active('dashboard', 'Visão geral', 'grid-1x2') ?>
                <?= active('bancas', 'Bancas', 'calendar-event') ?>
                <?= active('works', 'Trabalhos', 'journal-text') ?>
                <?php if ($u['perfil'] === 'avaliador'): ?>
                    <?= active('invitations', 'Convites', 'envelope') ?>
                <?php endif; ?>
                <?= active('calendar', 'Calendário', 'calendar3') ?>
                <?= active('public_search', 'Consulta pública', 'search') ?>
                <?php if ($u['perfil'] === 'administrador'): ?>
                    <?= active('admin', 'Administração', 'sliders') ?>
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
            <nav><a href="?page=public_search">Consulta pública</a><a class="btn primary" href="?page=login">Entrar</a></nav>
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
$pdo = null;
$uploadedFilePath = null;
try {
    if ($action === 'login') {
        verify_csrf();
        $pdo = db();
        $record = one('SELECT * FROM usuarios WHERE email = ? AND ativo = 1', [trim($_POST['email'] ?? '')]);
        if (!$record || !password_verify($_POST['password'] ?? '', $record['senha_hash'])) {
            throw new RuntimeException('E-mail ou senha inválidos.');
        }
        session_regenerate_id(true);
        $_SESSION['user'] = array_intersect_key($record, array_flip(['id', 'nome', 'email', 'perfil', 'curso_id']));
        redirect('index.php?page=dashboard');
    }
    if ($action === 'logout') {
        verify_csrf();
        require_login();
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
        $pdo = db();
        $pdo->prepare('INSERT INTO cursos(nome,sigla) VALUES(?,?)')->execute([trim($_POST['nome']), strtoupper(trim($_POST['sigla']))]);
        flash('success', 'Curso cadastrado.');
        redirect('index.php?page=admin');
    }

    if ($action === 'create_banca') {
        verify_csrf();
        require_role(['orientador', 'administrador']);
        $user = current_user();
        $work = work_by_id((int) ($_POST['trabalho_id'] ?? 0));
        if (!$work || !can_access_work($work)) {
            throw new RuntimeException('Trabalho não encontrado ou sem permissão.');
        }
        if ($work['status'] !== 'RASCUNHO') {
            throw new RuntimeException('Só é possível agendar banca para trabalho em rascunho.');
        }
        $date = $_POST['data_banca'] ?? '';
        $start = $_POST['hora_inicio'] ?? '';
        $end = $_POST['hora_fim'] ?? '';
        $mode = $_POST['modalidade'] ?? '';
        $evaluators = array_values(array_unique(array_filter(array_map('intval', $_POST['avaliadores'] ?? []))));
        if (!$date || $date < date('Y-m-d') || !$start || !$end || $start >= $end || !$evaluators) {
            throw new RuntimeException('Informe data futura, intervalo válido e pelo menos um avaliador.');
        }
        if (!in_array($mode, ['presencial', 'online', 'hibrida'], true)) {
            throw new RuntimeException('Modalidade inválida.');
        }
        $room = trim($_POST['local'] ?? '') ?: null;
        $meetingLink = trim($_POST['link_online'] ?? '') ?: null;
        if (in_array($mode, ['presencial', 'hibrida'], true) && !$room) {
            throw new RuntimeException('Informe a sala ou local da banca.');
        }
        if (in_array($mode, ['online', 'hibrida'], true) && !$meetingLink) {
            throw new RuntimeException('Informe o link da banca online.');
        }
        $pdo = db();
        $placeholders = implode(',', array_fill(0, count($evaluators), '?'));
        $conflict = one(
            "SELECT b.id FROM bancas b JOIN banca_avaliadores ba ON ba.banca_id=b.id
             WHERE ba.avaliador_id IN ({$placeholders}) AND b.data_banca=?
             AND b.hora_inicio < ? AND b.hora_fim > ? LIMIT 1",
            array_merge($evaluators, [$date, $end, $start]),
        );
        if ($conflict) {
            throw new RuntimeException('Um dos avaliadores já tem banca nesse intervalo.');
        }
        $advisorConflict = one(
            'SELECT b.id FROM bancas b JOIN trabalhos t ON t.id=b.trabalho_id
             WHERE t.orientador_id=? AND b.data_banca=? AND b.hora_inicio < ? AND b.hora_fim > ? LIMIT 1',
            [$work['orientador_id'], $date, $end, $start],
        );
        if ($advisorConflict) {
            throw new RuntimeException('O orientador já tem banca nesse intervalo.');
        }

        $pdo->beginTransaction();
        $pdo->prepare(
            'INSERT INTO bancas (trabalho_id,data_banca,hora_inicio,hora_fim,modalidade,local,link_online,status,criado_por)
             VALUES (?,?,?,?,?,?,?,?,?)',
        )->execute([$work['id'], $date, $start, $end, $mode, $room, $meetingLink, 'AGUARDANDO_CONFIRMACOES', $user['id']]);
        $bancaId = (int) $pdo->lastInsertId();
        $addEvaluator = $pdo->prepare('INSERT INTO banca_avaliadores (banca_id,avaliador_id,funcao) VALUES (?,?,?)');
        $addInvite = $pdo->prepare(
            'INSERT INTO convites (banca_avaliador_id,token_hash,expira_em,enviado_em)
             VALUES (?,?,DATE_ADD(NOW(), INTERVAL 14 DAY),NOW())',
        );
        foreach ($evaluators as $evaluatorId) {
            $addEvaluator->execute([$bancaId, $evaluatorId, 'Avaliador(a)']);
            $relationId = (int) $pdo->lastInsertId();
            [$token, $tokenHash] = TokenService::create();
            $addInvite->execute([$relationId, $tokenHash]);
            $evaluator = one('SELECT nome,email FROM usuarios WHERE id=? AND perfil=? AND ativo=1', [$evaluatorId, 'avaliador']);
            $inviteUrl = base_url('index.php?page=invite&token=' . rawurlencode($token));
            MailService::invitation(
                $evaluator['email'],
                'Convite para banca SIG-Banca',
                "Olá, {$evaluator['nome']}. Você foi convidado para avaliar “{$work['titulo']}” em " . date_br($date) . " às {$start}. Acesse: {$inviteUrl}",
            );
            notify((int) $evaluatorId, 'convite', 'Novo convite de banca', 'Você recebeu um convite para ' . $work['titulo'], 'index.php?page=invitations');
        }
        $pdo->prepare('UPDATE trabalhos SET status=? WHERE id=?')->execute(['AGUARDANDO_CONFIRMACOES', $work['id']]);
        audit((int) $user['id'], 'criou', 'banca', $bancaId);
        $pdo->commit();
        flash('success', 'Banca agendada e convites registrados. Os e-mails de teste estão em storage/mail-outbox.');
        redirect('index.php?page=banca&id=' . $bancaId);
    }

    if ($action === 'respond_invite') {
        verify_csrf();
        $answer = $_POST['answer'] ?? '';
        if (!in_array($answer, ['aceito', 'recusado'], true)) {
            throw new RuntimeException('Resposta de convite inválida.');
        }
        $pdo = db();
        $token = trim($_POST['token'] ?? '');
        if ($token !== '') {
            $invite = one(
                'SELECT c.*,ba.id AS rel_id,ba.avaliador_id,ba.banca_id,b.trabalho_id
                 FROM convites c JOIN banca_avaliadores ba ON ba.id=c.banca_avaliador_id
                 JOIN bancas b ON b.id=ba.banca_id WHERE c.token_hash=?',
                [TokenService::hash($token)],
            );
            if (is_logged_in() && (int) current_user()['id'] !== (int) ($invite['avaliador_id'] ?? 0)) {
                throw new RuntimeException('Este convite pertence a outro avaliador.');
            }
        } else {
            require_role(['avaliador']);
            $invite = one(
                'SELECT c.*,ba.id AS rel_id,ba.avaliador_id,ba.banca_id,b.trabalho_id
                 FROM convites c JOIN banca_avaliadores ba ON ba.id=c.banca_avaliador_id
                 JOIN bancas b ON b.id=ba.banca_id WHERE c.id=? AND ba.avaliador_id=?',
                [(int) ($_POST['convite_id'] ?? 0), current_user()['id']],
            );
        }
        if (!$invite || $invite['status'] !== 'pendente' || $invite['respondido_em'] !== null) {
            throw new RuntimeException('Convite inexistente ou já respondido.');
        }
        if ($invite['expira_em'] !== null && strtotime($invite['expira_em']) < time()) {
            $pdo->prepare("UPDATE convites SET status='expirado' WHERE id=? AND status='pendente'")->execute([$invite['id']]);
            throw new RuntimeException('Este convite expirou.');
        }
        $pdo->beginTransaction();
        $respond = $pdo->prepare(
            'UPDATE convites SET status=?,respondido_em=NOW() WHERE id=? AND status=? AND respondido_em IS NULL',
        );
        $respond->execute([$answer, $invite['id'], 'pendente']);
        if ($respond->rowCount() !== 1) {
            throw new RuntimeException('Este convite já foi respondido.');
        }
        $updateEvaluator = $pdo->prepare(
            'UPDATE banca_avaliadores SET status_resposta=?,respondido_em=NOW() WHERE id=? AND status_resposta=?',
        );
        $updateEvaluator->execute([$answer, $invite['rel_id'], 'pendente']);
        if ($updateEvaluator->rowCount() !== 1) {
            throw new RuntimeException('A resposta do avaliador já foi registrada.');
        }
        $pending = (int) one(
            "SELECT COUNT(*) AS total FROM banca_avaliadores WHERE banca_id=? AND status_resposta<>'aceito'",
            [$invite['banca_id']],
        )['total'];
        if ($answer === 'aceito' && $pending === 0) {
            $pdo->prepare("UPDATE bancas SET status='CONFIRMADA' WHERE id=? AND status='AGUARDANDO_CONFIRMACOES'")->execute([$invite['banca_id']]);
            $pdo->prepare("UPDATE trabalhos SET status='CONFIRMADA' WHERE id=? AND status='AGUARDANDO_CONFIRMACOES'")->execute([$invite['trabalho_id']]);
        }
        audit(is_logged_in() ? (int) current_user()['id'] : (int) $invite['avaliador_id'], 'respondeu_convite_' . $answer, 'convite', (int) $invite['id']);
        $pdo->commit();
        flash('success', $answer === 'aceito' ? 'Convite aceito.' : 'Convite recusado.');
        if ($token !== '') {
            redirect('index.php?page=invite&respondido=1');
        }
        redirect('index.php?page=invitations');
    }

    if ($action === 'defense') {
        verify_csrf();
        require_role(['orientador', 'administrador']);
        $pdo = db();
        $banca = one(
            'SELECT b.*,t.orientador_id,t.status AS trabalho_status FROM bancas b JOIN trabalhos t ON t.id=b.trabalho_id WHERE b.id=?',
            [(int) ($_POST['banca_id'] ?? 0)],
        );
        if (!$banca || !can_access_work(['id' => $banca['trabalho_id'], 'orientador_id' => $banca['orientador_id']])) {
            throw new RuntimeException('Banca não encontrada ou sem permissão.');
        }
        if ($banca['status'] !== 'CONFIRMADA' || $banca['trabalho_status'] !== 'CONFIRMADA') {
            throw new RuntimeException('A banca precisa estar confirmada antes de registrar a realização.');
        }
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE bancas SET status='REALIZADA',realizada_em=NOW() WHERE id=?")->execute([$banca['id']]);
        $pdo->prepare("UPDATE trabalhos SET status='AGUARDANDO_DOCUMENTOS' WHERE id=?")->execute([$banca['trabalho_id']]);
        audit((int) current_user()['id'], 'registrou_realizacao', 'banca', (int) $banca['id']);
        $pdo->commit();
        flash('success', 'Defesa registrada. O trabalho entrou na etapa de documentação.');
        redirect('index.php?page=banca&id=' . $banca['id']);
    }

    if ($action === 'correction') {
        verify_csrf();
        require_role(['orientador', 'administrador']);
        $work = work_by_id((int) ($_POST['trabalho_id'] ?? 0));
        if (!$work || !can_access_work($work)) {
            throw new RuntimeException('Trabalho não encontrado ou sem permissão.');
        }
        if (!in_array($work['status'], ['AGUARDANDO_DOCUMENTOS', 'AGUARDANDO_CORRECOES'], true)) {
            throw new RuntimeException('As correções só podem ser formalizadas após a realização da banca.');
        }
        $baseDate = $_POST['data_liberacao'] ?? '';
        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $baseDate);
        if (!$parsedDate || $parsedDate->format('Y-m-d') !== $baseDate || trim($_POST['descricao'] ?? '') === '') {
            throw new RuntimeException('Informe uma data-base válida e a descrição das correções.');
        }
        $bancaId = (int) ($_POST['banca_id'] ?? 0);
        $banca = $bancaId ? one('SELECT * FROM bancas WHERE id=? AND trabalho_id=?', [$bancaId, $work['id']]) : null;
        if (!$banca) {
            throw new RuntimeException('A banca informada não pertence a este trabalho.');
        }
        $pdo = db();
        $deadline = PrazoService::deadline($baseDate);
        $pdo->beginTransaction();
        $pdo->prepare(
            'INSERT INTO correcoes (trabalho_id,banca_id,descricao,data_liberacao,prazo_final,registrado_por)
             VALUES (?,?,?,?,?,?)',
        )->execute([$work['id'], $bancaId, trim($_POST['descricao']), $baseDate, $deadline, current_user()['id']]);
        if ($banca['status'] === 'REALIZADA') {
            $pdo->prepare("UPDATE bancas SET status='AGUARDANDO_CORRECOES' WHERE id=?")->execute([$bancaId]);
        }
        if ($banca['status'] === 'AGUARDANDO_CORRECOES') {
            $pdo->prepare("UPDATE bancas SET status='AGUARDANDO_VERSAO_FINAL' WHERE id=?")->execute([$bancaId]);
        }
        $pdo->prepare("UPDATE trabalhos SET status='AGUARDANDO_VERSAO_FINAL' WHERE id=?")->execute([$work['id']]);
        $students = rows('SELECT aluno_id FROM alunos_trabalhos WHERE trabalho_id=?', [$work['id']]);
        foreach ($students as $student) {
            notify((int) $student['aluno_id'], 'prazo', 'Correções liberadas', 'A versão definitiva vence em ' . date_br($deadline) . '.', 'index.php?page=work&id=' . $work['id']);
        }
        audit((int) current_user()['id'], 'liberou_correcoes', 'trabalho', (int) $work['id']);
        $pdo->commit();
        flash('success', 'Correções registradas. Prazo final: ' . date_br($deadline) . '.');
        redirect('index.php?page=work&id=' . $work['id']);
    }

    if ($action === 'upload') {
        verify_csrf();
        require_login();
        $work = work_by_id((int) ($_POST['trabalho_id'] ?? 0));
        if (!$work || !can_access_work($work)) {
            throw new RuntimeException('Trabalho não encontrado ou sem permissão.');
        }
        $user = current_user();
        $type = $_POST['tipo'] ?? '';
        $allowedByRole = $user['perfil'] === 'aluno'
            ? ['versao_final']
            : (in_array($user['perfil'], ['orientador', 'administrador'], true) ? ['ata', 'autorizacao_ric', 'outro'] : []);
        if (!in_array($type, $allowedByRole, true)) {
            throw new RuntimeException('Seu perfil não pode enviar este tipo de documento.');
        }
        if ($type === 'versao_final' && !in_array($work['status'], ['AGUARDANDO_VERSAO_FINAL', 'AJUSTES_SOLICITADOS'], true)) {
            throw new RuntimeException('O trabalho ainda não está aguardando uma versão definitiva.');
        }
        $bancaId = (int) ($_POST['banca_id'] ?? 0);
        if ($bancaId && !one('SELECT id FROM bancas WHERE id=? AND trabalho_id=?', [$bancaId, $work['id']])) {
            throw new RuntimeException('A banca informada não pertence a este trabalho.');
        }
        $saved = UploadService::save($_FILES['arquivo'] ?? []);
        $uploadedFilePath = $config['root'] . '/' . $saved['path'];
        $pdo = db();
        $pdo->beginTransaction();
        $pdo->prepare('SELECT id FROM trabalhos WHERE id=? FOR UPDATE')->execute([$work['id']]);
        $pdo->prepare(
            'INSERT INTO documentos (trabalho_id,banca_id,tipo,nome_original,nome_armazenado,caminho_armazenado,mime_type,tamanho_bytes,enviado_por)
             VALUES (?,?,?,?,?,?,?,?,?)',
        )->execute([$work['id'], $bancaId ?: null, $type, $saved['original'], $saved['stored'], $saved['path'], $saved['mime'], $saved['size'], $user['id']]);
        $documentId = (int) $pdo->lastInsertId();
        if ($type === 'versao_final') {
            $nextVersion = (int) one('SELECT COALESCE(MAX(numero_versao),0)+1 AS next_version FROM versoes WHERE trabalho_id=?', [$work['id']])['next_version'];
            $pdo->prepare('INSERT INTO versoes (trabalho_id,documento_id,numero_versao,enviado_por,status) VALUES (?,?,?,?,?)')
                ->execute([$work['id'], $documentId, $nextVersion, $user['id'], 'em_analise']);
            $pdo->prepare("UPDATE trabalhos SET status='AGUARDANDO_APROVACAO' WHERE id=?")->execute([$work['id']]);
            $pdo->prepare("UPDATE bancas SET status='AGUARDANDO_APROVACAO' WHERE trabalho_id=? AND status IN ('AGUARDANDO_VERSAO_FINAL','AJUSTES_SOLICITADOS')")->execute([$work['id']]);
            $pdo->prepare("UPDATE correcoes SET status='entregue' WHERE trabalho_id=? AND status='aberta'")->execute([$work['id']]);
            notify((int) $work['orientador_id'], 'versao', 'Versão final enviada', 'Há uma nova versão para avaliação.', 'index.php?page=work&id=' . $work['id']);
        }
        audit((int) $user['id'], 'enviou_documento', 'documento', $documentId, $type);
        $pdo->commit();
        $uploadedFilePath = null;
        flash('success', 'Documento enviado com segurança.');
        redirect('index.php?page=work&id=' . $work['id']);
    }

    if ($action === 'review_version') {
        verify_csrf();
        require_role(['orientador', 'administrador']);
        $decision = $_POST['decision'] ?? '';
        if (!in_array($decision, ['approve', 'adjust'], true)) {
            throw new RuntimeException('Decisão de avaliação inválida.');
        }
        $version = one(
            'SELECT v.*,t.orientador_id,t.status AS trabalho_status FROM versoes v JOIN trabalhos t ON t.id=v.trabalho_id WHERE v.id=?',
            [(int) ($_POST['versao_id'] ?? 0)],
        );
        if (!$version || !can_access_work(['id' => $version['trabalho_id'], 'orientador_id' => $version['orientador_id']])) {
            throw new RuntimeException('Versão não encontrada ou sem permissão.');
        }
        if ($version['status'] !== 'em_analise' || $version['trabalho_status'] !== 'AGUARDANDO_APROVACAO') {
            throw new RuntimeException('Esta versão não está aguardando avaliação.');
        }
        $observation = trim($_POST['observacao'] ?? '');
        if ($decision === 'adjust' && $observation === '') {
            throw new RuntimeException('Informe o motivo dos ajustes solicitados.');
        }
        $pdo = db();
        $pdo->beginTransaction();
        $newStatus = $decision === 'approve' ? 'aprovada' : 'ajustes_solicitados';
        $workStatus = $decision === 'approve' ? 'CONCLUIDA' : 'AJUSTES_SOLICITADOS';
        $pdo->prepare('UPDATE versoes SET status=?,avaliado_por=?,avaliado_em=NOW(),observacao_orientador=? WHERE id=?')
            ->execute([$newStatus, current_user()['id'], $observation ?: null, $version['id']]);
        $pdo->prepare('UPDATE trabalhos SET status=? WHERE id=?')->execute([$workStatus, $version['trabalho_id']]);
        $pdo->prepare('UPDATE bancas SET status=? WHERE trabalho_id=? AND status=?')->execute([$workStatus, $version['trabalho_id'], 'AGUARDANDO_APROVACAO']);
        $students = rows('SELECT aluno_id FROM alunos_trabalhos WHERE trabalho_id=?', [$version['trabalho_id']]);
        foreach ($students as $student) {
            notify((int) $student['aluno_id'], 'versao', $decision === 'approve' ? 'Versão aprovada' : 'Ajustes solicitados', $decision === 'approve' ? 'Seu trabalho foi concluído.' : $observation, 'index.php?page=work&id=' . $version['trabalho_id']);
        }
        audit((int) current_user()['id'], $decision === 'approve' ? 'aprovou_versao' : 'solicitou_ajustes', 'versao', (int) $version['id']);
        $pdo->commit();
        flash('success', $decision === 'approve' ? 'Versão aprovada e trabalho concluído.' : 'Ajustes solicitados ao aluno.');
        redirect('index.php?page=work&id=' . $version['trabalho_id']);
    }
} catch (Throwable $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($uploadedFilePath !== null && is_file($uploadedFilePath)) {
        unlink($uploadedFilePath);
    }
    if ($e instanceof PDOException) {
        error_log((string) $e);
        flash('error', 'Não foi possível concluir a operação. Revise os dados e tente novamente.');
    } else {
        flash('error', $e->getMessage());
    }
    redirect('index.php?page=' . urlencode($_GET['page'] ?? 'login'));
}
if (!in_array($currentPage, ['home', 'login', 'public_search', 'invite'], true)) {
    require_login();
}
if($currentPage==='home'){layout_start('Gestão acadêmica');?><section class="hero"><div><span class="eyebrow">SISTEMA INTEGRADO DE GESTÃO</span><h1>Da banca à versão final,<br><em>tudo em um só lugar.</em></h1><p>Organize bancas, documentos e prazos para toda a comunidade acadêmica.</p><a class="btn primary" href="?page=login">Acessar o sistema</a></div><div class="hero-card"><p>Próxima banca</p><h2>Plataforma de Gestão de Bancas</h2><hr><b>19:00 · Híbrida</b></div></section><?php
layout_end();
exit();
}
if ($currentPage === "login") {
    layout_start(
        "Entrar",
    ); ?><section class="login-wrap"><div class="login-aside"><h1>O processo acadêmico merece fluidez.</h1></div><form class="login-card" method="post"><h2>Bem-vindo(a)</h2><input type="hidden" name="action" value="login"><?= csrf_field() ?><label>E-mail<input required type="email" name="email"></label><label>Senha<input required type="password" name="password"></label><button class="btn primary full">Entrar</button><small>Demonstração: senha password</small></form></section><?php
layout_end();
exit();

}
$u = current_user();
if ($currentPage === "dashboard") {

    $b = rows(
        "SELECT b.*,t.titulo FROM bancas b JOIN trabalhos t ON t.id=b.trabalho_id ORDER BY b.data_banca LIMIT 4",
    );
    layout_start("Visão geral");
    ?><div class="dashboard-top"><p>Acompanhe as etapas que precisam da sua atenção.</p><a class="btn primary" href="?page=work_new">Novo trabalho</a></div><section class="metrics"><article><span><?= count(
    $b,
    ) ?></span><p>Próximas bancas</p></article><article><span>15</span><p>Dias de prazo</p></article><article><span><?= count(
    rows("SELECT id FROM trabalhos WHERE status='CONCLUIDA'"),
    ) ?></span><p>Concluídos</p></article><article><span><?= count(
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
