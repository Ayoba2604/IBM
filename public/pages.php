<?php
declare(strict_types=1);

$u = current_user();

if ($currentPage === "home") {
    $stats = ["works" => 0, "bancas" => 0, "courses" => 0, "concluded" => 0];
    try {
        $stats["works"] = (int) one("SELECT COUNT(*) AS total FROM trabalhos WHERE publico=1")["total"];
        $stats["bancas"] = (int) one("SELECT COUNT(*) AS total FROM bancas WHERE data_banca >= CURDATE()")["total"];
        $stats["courses"] = (int) one("SELECT COUNT(*) AS total FROM cursos WHERE ativo=1")["total"];
        $stats["concluded"] = (int) one("SELECT COUNT(*) AS total FROM trabalhos WHERE publico=1 AND status='CONCLUIDA'")["total"];
    } catch (Throwable $error) {
        error_log("[SIG-Banca] Home stats: " . $error->getMessage());
    }
    layout_start("SIG-Banca", ["mode" => "public"]);
    ?>
    <section class="hero">
        <div class="hero-copy">
            <span class="eyebrow light">Fatec Bauru · Gestão acadêmica</span>
            <h1>Da banca à versão final, <em>tudo em um só lugar.</em></h1>
            <p class="hero-lead">Organize defesas, convites, documentos e prazos de TCC em um fluxo claro para alunos, orientadores, avaliadores e coordenação.</p>
            <div class="hero-actions">
                <a class="btn primary large" href="?page=login">Acessar o sistema <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                <a class="btn ghost-on-dark large" href="?page=public_search">Consultar acervo</a>
            </div>
            <ul class="hero-checks">
                <li><i class="bi bi-shield-check" aria-hidden="true"></i> Acesso por perfil</li>
                <li><i class="bi bi-clock-history" aria-hidden="true"></i> Prazos de 15 dias</li>
                <li><i class="bi bi-file-earmark-lock" aria-hidden="true"></i> Documentos protegidos</li>
            </ul>
        </div>
        <div class="hero-panel">
            <div class="hero-panel-head">
                <span class="status-dot"></span>
                <span>Fluxo da banca</span>
                <span class="hero-panel-tag">ao vivo</span>
            </div>
            <ol class="hero-flow">
                <li><span>01</span><div><strong>Agendamento</strong><small>Data, horário e avaliadores</small></div></li>
                <li><span>02</span><div><strong>Convites</strong><small>Confirmação com token seguro</small></div></li>
                <li><span>03</span><div><strong>Defesa</strong><small>Registro e documentação</small></div></li>
                <li><span>04</span><div><strong>Versão final</strong><small>Correções com prazo e aprovação</small></div></li>
            </ol>
            <div class="hero-panel-foot">
                <span>Documentos em <code>storage/</code></span>
                <span class="badge success">Seguro</span>
            </div>
        </div>
    </section>

    <section class="public-stats" aria-label="Números do sistema">
        <div><strong><?= $stats["works"] ?></strong><span>Trabalhos publicados</span></div>
        <div><strong><?= $stats["bancas"] ?></strong><span>Bancas futuras</span></div>
        <div><strong><?= $stats["courses"] ?></strong><span>Cursos ativos</span></div>
        <div><strong><?= $stats["concluded"] ?></strong><span>Trabalhos concluídos</span></div>
    </section>

    <section class="feature-section">
        <div class="section-head">
            <span class="eyebrow">Como funciona</span>
            <h2>Um processo acadêmico sem planilhas paralelas</h2>
            <p>Cada etapa tem responsável, estado e histórico. A coordenação acompanha tudo em tempo real.</p>
        </div>
        <div class="feature-grid">
            <article class="feature-card">
                <span class="feature-icon"><i class="bi bi-calendar2-week" aria-hidden="true"></i></span>
                <h3>Agendamento inteligente</h3>
                <p>Conflitos de horário de avaliadores e orientadores são verificados antes de confirmar a defesa.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon"><i class="bi bi-envelope-paper" aria-hidden="true"></i></span>
                <h3>Convites com token</h3>
                <p>O avaliador aceita ou recusa pelo link recebido, sem expor dados internos do sistema.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i></span>
                <h3>Documentos fora da web</h3>
                <p>PDFs ficam em armazenamento protegido e só são entregues a quem tem permissão.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon"><i class="bi bi-stopwatch" aria-hidden="true"></i></span>
                <h3>Prazo de 15 dias</h3>
                <p>Correções geram prazo automático, notificações e acompanhamento de atrasos.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon"><i class="bi bi-diagram-3" aria-hidden="true"></i></span>
                <h3>Estados validados no banco</h3>
                <p>Triggers impedem saltos de etapa, alterações indevidas e exclusão da auditoria.</p>
            </article>
            <article class="feature-card">
                <span class="feature-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
                <h3>Quatro perfis integrados</h3>
                <p>Aluno, orientador, avaliador e administração veem exatamente o que precisam.</p>
            </article>
        </div>
    </section>

    <section class="cta-band">
        <div>
            <span class="eyebrow light">Pronto para começar?</span>
            <h2>Leve sua banca do rascunho à conclusão.</h2>
            <p>Entre com uma das contas demonstrativas e percorra o fluxo completo.</p>
        </div>
        <a class="btn gold large" href="?page=login">Entrar agora <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
    </section>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "login") {
    if (is_logged_in()) {
        redirect("index.php?page=dashboard");
    }
    layout_start("Entrar", ["mode" => "auth"]);
    ?>
    <div class="auth-grid">
        <section class="auth-aside">
            <a class="brand on-dark" href="index.php">
                <span class="brand-mark" aria-hidden="true">SB</span>
                <span class="brand-text"><strong>SIG-Banca</strong><small>Fatec Bauru</small></span>
            </a>
            <div class="auth-copy">
                <span class="eyebrow light">Sistema integrado de gestão</span>
                <h1>O processo acadêmico merece fluidez.</h1>
                <p>Acompanhe bancas, documentos e prazos com a segurança de um fluxo validado pelo próprio banco de dados.</p>
                <ul class="auth-list">
                    <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Visão por perfil e permissões verificadas</li>
                    <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Convites e respostas com token</li>
                    <li><i class="bi bi-check2-circle" aria-hidden="true"></i> Auditoria imutável das mudanças de estado</li>
                </ul>
            </div>
            <p class="auth-footnote">© <?= date("Y") ?> Fatec Bauru · Uso acadêmico</p>
        </section>

        <section class="auth-card">
            <div class="auth-card-head">
                <span class="eyebrow">Acesso restrito</span>
                <h2>Bem-vindo(a) de volta</h2>
                <p>Entre com suas credenciais institucionais.</p>
            </div>
            <form method="post" class="auth-form">
                <input type="hidden" name="action" value="login">
                <?= csrf_field() ?>
                <label class="field">
                    <span>E-mail</span>
                    <span class="input-icon"><i class="bi bi-envelope" aria-hidden="true"></i><input required type="email" name="email" autocomplete="username" placeholder="nome@fatec.sp.gov.br"></span>
                </label>
                <label class="field">
                    <span>Senha</span>
                    <span class="input-icon"><i class="bi bi-lock" aria-hidden="true"></i><input required type="password" name="password" autocomplete="current-password" placeholder="Sua senha" data-password><button type="button" class="password-toggle" data-password-toggle aria-label="Mostrar senha"><i class="bi bi-eye" aria-hidden="true"></i></button></span>
                </label>
                <button class="btn primary large full" type="submit">Entrar no painel <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
            </form>
            <details class="demo-box">
                <summary>Contas demonstrativas</summary>
                <p>Senha para todas: <code>password</code></p>
                <ul>
                    <li><span>Coordenação</span><code>coord@fatec.sp.gov.br</code></li>
                    <li><span>Orientador</span><code>ricardo@fatec.sp.gov.br</code></li>
                    <li><span>Avaliador</span><code>marina@fatec.sp.gov.br</code></li>
                    <li><span>Aluno</span><code>lucas@fatec.sp.gov.br</code></li>
                </ul>
            </details>
            <a class="auth-back" href="index.php"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar ao site</a>
        </section>
    </div>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "dashboard") {
    [$scopeSql, $scopeParams] = access_scope("t");
    $upcoming = rows(
        "SELECT b.*, t.titulo, t.id AS trabalho_id, c.sigla
         FROM bancas b
         JOIN trabalhos t ON t.id=b.trabalho_id
         JOIN cursos c ON c.id=t.curso_id
         WHERE {$scopeSql} AND b.data_banca >= CURDATE()
         ORDER BY b.data_banca, b.hora_inicio LIMIT 4",
        $scopeParams,
    );
    $totalWorks = (int) one("SELECT COUNT(*) AS total FROM trabalhos t WHERE {$scopeSql}", $scopeParams)["total"];
    $concludedWorks = (int) one(
        "SELECT COUNT(*) AS total FROM trabalhos t WHERE {$scopeSql} AND t.status='CONCLUIDA'",
        $scopeParams,
    )["total"];
    $deadlines = rows(
        "SELECT c.*, t.titulo, t.id AS trabalho_id, DATEDIFF(c.prazo_final, CURDATE()) AS dias
         FROM correcoes c JOIN trabalhos t ON t.id=c.trabalho_id
         WHERE {$scopeSql} AND c.status IN ('aberta','entregue')
         ORDER BY c.prazo_final LIMIT 4",
        $scopeParams,
    );
    $recentWorks = rows(
        "SELECT t.*, c.sigla, u.nome AS orientador,
                (SELECT COUNT(*) FROM alunos_trabalhos at WHERE at.trabalho_id=t.id) AS alunos
         FROM trabalhos t
         JOIN cursos c ON c.id=t.curso_id
         JOIN usuarios u ON u.id=t.orientador_id
         WHERE {$scopeSql}
         ORDER BY t.updated_at DESC LIMIT 5",
        $scopeParams,
    );
    $pendingInvites = 0;
    if ($u["perfil"] === "avaliador") {
        $pendingInvites = (int) one(
            "SELECT COUNT(*) AS total FROM banca_avaliadores WHERE avaliador_id=? AND status_resposta='pendente'",
            [$u["id"]],
        )["total"];
    }
    $nextBanca = $upcoming[0] ?? null;
    $hour = (int) date("G");
    $greeting = $hour < 12 ? "Bom dia" : ($hour < 18 ? "Boa tarde" : "Boa noite");
    $firstName = explode(" ", trim((string) $u["nome"]))[0];
    $newWorkAllowed = in_array($u["perfil"], ["orientador", "administrador"], true);
    layout_start("Visão geral");
    ?>
    <section class="welcome-card">
        <div class="welcome-copy">
            <span class="eyebrow light"><?= e(date_long_pt(date("Y-m-d"))) ?></span>
            <h2><?= e($greeting) ?>, <?= e($firstName) ?>.</h2>
            <p>
                <?php if ($u["perfil"] === "avaliador"): ?>
                    <?= $pendingInvites > 0 ? "Você tem {$pendingInvites} convite(s) aguardando resposta." : "Nenhum convite pendente no momento." ?>
                <?php elseif ($u["perfil"] === "aluno"): ?>
                    Acompanhe aqui as etapas do seu trabalho e os prazos em aberto.
                <?php else: ?>
                    Acompanhe as bancas, prazos e pendências que precisam da sua atenção.
                <?php endif; ?>
            </p>
            <div class="welcome-actions">
                <?php if ($newWorkAllowed): ?>
                    <a class="btn gold" href="?page=work_new"><i class="bi bi-plus-lg" aria-hidden="true"></i> Novo trabalho</a>
                <?php endif; ?>
                <?php if ($u["perfil"] === "avaliador"): ?>
                    <a class="btn gold" href="?page=invitations"><i class="bi bi-envelope-paper" aria-hidden="true"></i> Ver convites</a>
                <?php endif; ?>
                <a class="btn ghost-on-dark" href="?page=calendar">Abrir calendário</a>
            </div>
        </div>
        <?php if ($nextBanca): ?>
            <aside class="next-banca">
                <span class="next-label">Próxima banca</span>
                <strong class="next-title"><?= e($nextBanca["titulo"]) ?></strong>
                <div class="next-meta">
                    <span><i class="bi bi-calendar3" aria-hidden="true"></i> <?= e(date_br($nextBanca["data_banca"])) ?></span>
                    <span><i class="bi bi-clock" aria-hidden="true"></i> <?= e(time_br($nextBanca["hora_inicio"])) ?></span>
                    <span><i class="bi bi-camera-video" aria-hidden="true"></i> <?= e(modality_label($nextBanca["modalidade"])) ?></span>
                </div>
                <a class="btn ghost-dark small" href="?page=banca&id=<?= (int) $nextBanca["id"] ?>">Ver detalhes <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </aside>
        <?php else: ?>
            <aside class="next-banca empty-next">
                <span class="next-label">Próxima banca</span>
                <strong class="next-title">Nenhuma banca futura</strong>
                <p>Quando houver uma defesa agendada, ela aparece aqui.</p>
            </aside>
        <?php endif; ?>
    </section>

    <section class="metric-grid" aria-label="Indicadores">
        <article class="metric-card">
            <span class="metric-icon blue"><i class="bi bi-journal-text" aria-hidden="true"></i></span>
            <div><span class="metric-value"><?= $totalWorks ?></span><span class="metric-label">Trabalhos no seu escopo</span></div>
        </article>
        <article class="metric-card">
            <span class="metric-icon gold"><i class="bi bi-calendar-event" aria-hidden="true"></i></span>
            <div><span class="metric-value"><?= count($upcoming) ?></span><span class="metric-label">Próximas bancas</span></div>
        </article>
        <article class="metric-card">
            <span class="metric-icon green"><i class="bi bi-patch-check" aria-hidden="true"></i></span>
            <div><span class="metric-value"><?= $concludedWorks ?></span><span class="metric-label">Trabalhos concluídos</span></div>
        </article>
        <article class="metric-card">
            <span class="metric-icon slate"><i class="bi bi-stopwatch" aria-hidden="true"></i></span>
            <div><span class="metric-value"><?= count($deadlines) ?></span><span class="metric-label">Prazos em acompanhamento</span></div>
        </article>
    </section>

    <div class="dashboard-grid">
        <section class="card span-2">
            <div class="card-head">
                <div><span class="eyebrow">Agenda</span><h2>Próximas bancas</h2></div>
                <a class="card-link" href="?page=bancas">Ver todas <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <?php if ($upcoming): ?>
                <div class="timeline">
                    <?php foreach ($upcoming as $banca): ?>
                        <a class="event" href="?page=banca&id=<?= (int) $banca["id"] ?>">
                            <time class="event-date" datetime="<?= e($banca["data_banca"]) ?>">
                                <b><?= date("d", strtotime($banca["data_banca"])) ?></b>
                                <small><?= strtoupper(date("M", strtotime($banca["data_banca"]))) ?></small>
                            </time>
                            <div class="event-body">
                                <strong><?= e($banca["titulo"]) ?></strong>
                                <span><?= e($banca["sigla"]) ?> · <?= e(time_br($banca["hora_inicio"])) ?> · <?= e(modality_label($banca["modalidade"])) ?><?= $banca["local"] ? " · " . e($banca["local"]) : "" ?></span>
                            </div>
                            <span class="badge <?= e(status_class($banca["status"])) ?>"><?= e(status_label($banca["status"])) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state"><i class="bi bi-calendar-x" aria-hidden="true"></i><p>Nenhuma banca agendada no seu escopo.</p></div>
            <?php endif; ?>
        </section>

        <section class="card">
            <div class="card-head">
                <div><span class="eyebrow">Prazos</span><h2>Versões finais</h2></div>
            </div>
            <?php if ($deadlines): ?>
                <ul class="deadline-list">
                    <?php foreach ($deadlines as $deadline): ?>
                        <?php $late = (int) $deadline["dias"] < 0; ?>
                        <li class="<?= $late ? "late" : "" ?>">
                            <a href="?page=work&id=<?= (int) $deadline["trabalho_id"] ?>">
                                <strong><?= e($deadline["titulo"]) ?></strong>
                                <span>
                                    <?= $late
                                        ? abs((int) $deadline["dias"]) . " dia(s) em atraso"
                                        : (int) $deadline["dias"] . " dia(s) restante(s)" ?>
                                </span>
                                <small>Prazo: <?= e(date_br($deadline["prazo_final"])) ?></small>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="empty-state small"><i class="bi bi-check2-circle" aria-hidden="true"></i><p>Nenhum prazo em aberto.</p></div>
            <?php endif; ?>
        </section>

        <section class="card span-2">
            <div class="card-head">
                <div><span class="eyebrow">Atualizações</span><h2>Trabalhos recentes</h2></div>
                <a class="card-link" href="?page=works">Abrir lista <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <?php if ($recentWorks): ?>
                <div class="table-wrap compact">
                    <table class="responsive">
                        <thead><tr><th>Trabalho</th><th>Curso</th><th>Equipe</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        <?php foreach ($recentWorks as $work): ?>
                            <tr>
                                <td data-label="Trabalho"><strong><?= e($work["titulo"]) ?></strong><small>Orientação: <?= e($work["orientador"]) ?></small></td>
                                <td data-label="Curso"><?= e($work["sigla"]) ?> · <?= (int) $work["ano_letivo"] ?>/<?= (int) $work["semestre_letivo"] ?></td>
                                <td data-label="Equipe"><?= (int) $work["alunos"] ?> aluno(s)</td>
                                <td data-label="Status"><span class="badge <?= e(status_class($work["status"])) ?>"><?= e(status_label($work["status"])) ?></span></td>
                                <td class="cell-action" data-label="Abrir"><a class="icon-link" href="?page=work&id=<?= (int) $work["id"] ?>" aria-label="Abrir trabalho"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state"><i class="bi bi-journal-plus" aria-hidden="true"></i><p>Nenhum trabalho cadastrado ainda.</p></div>
            <?php endif; ?>
        </section>
    </div>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "works") {
    [$scopeSql, $scopeParams] = access_scope("t");
    $query = query_string("q");
    $statusFilter = query_string("status");
    $where = [$scopeSql];
    $params = $scopeParams;
    if ($query !== "") {
        $where[] = "(t.titulo LIKE ? OR u.nome LIKE ? OR EXISTS(
            SELECT 1 FROM alunos_trabalhos at JOIN usuarios al ON al.id=at.aluno_id
            WHERE at.trabalho_id=t.id AND al.nome LIKE ?
        ))";
        $like = "%" . $query . "%";
        array_push($params, $like, $like, $like);
    }
    if (isset(STATUS[$statusFilter])) {
        $where[] = "t.status=?";
        $params[] = $statusFilter;
    }
    $works = rows(
        "SELECT t.*, c.sigla, c.nome AS curso, u.nome AS orientador,
                GROUP_CONCAT(DISTINCT a.nome SEPARATOR ', ') AS alunos
         FROM trabalhos t
         JOIN cursos c ON c.id=t.curso_id
         JOIN usuarios u ON u.id=t.orientador_id
         LEFT JOIN alunos_trabalhos at ON at.trabalho_id=t.id
         LEFT JOIN usuarios a ON a.id=at.aluno_id
         WHERE " . implode(" AND ", $where) . "
         GROUP BY t.id
         ORDER BY t.updated_at DESC",
        $params,
    );
    layout_start("Trabalhos");
    ?>
    <div class="page-head">
        <div class="page-head-text">
            <span class="eyebrow">Acervo acadêmico</span>
            <h2>Todos os trabalhos</h2>
            <p>Cadastros, versões e situação de cada TCC no seu escopo de acesso.</p>
        </div>
        <?php if (in_array($u["perfil"], ["orientador", "administrador"], true)): ?>
            <a class="btn primary" href="?page=work_new"><i class="bi bi-plus-lg" aria-hidden="true"></i> Novo trabalho</a>
        <?php endif; ?>
    </div>
    <form class="toolbar" method="get">
        <input type="hidden" name="page" value="works">
        <label class="toolbar-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input name="q" value="<?= e($query) ?>" placeholder="Buscar por título, aluno ou orientador">
        </label>
        <select name="status" aria-label="Filtrar por status">
            <option value="">Todos os status</option>
            <?php foreach (STATUS as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $statusFilter === $key ? "selected" : "" ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn secondary" type="submit">Filtrar</button>
        <?php if ($query !== "" || $statusFilter !== ""): ?>
            <a class="btn ghost" href="?page=works">Limpar</a>
        <?php endif; ?>
    </form>
    <section class="card table-wrap">
        <?php if ($works): ?>
            <table class="responsive">
                <thead><tr><th>Trabalho</th><th>Participantes</th><th>Curso / período</th><th>Status</th><th class="cell-action"></th></tr></thead>
                <tbody>
                <?php foreach ($works as $work): ?>
                    <tr>
                        <td data-label="Trabalho"><strong><?= e($work["titulo"]) ?></strong><small>Orientação: <?= e($work["orientador"]) ?></small></td>
                        <td data-label="Participantes"><?= e($work["alunos"] ?? "—") ?></td>
                        <td data-label="Curso / período"><?= e($work["sigla"]) ?> · <?= (int) $work["ano_letivo"] ?>/<?= (int) $work["semestre_letivo"] ?></td>
                        <td data-label="Status"><span class="badge <?= e(status_class($work["status"])) ?>"><?= e(status_label($work["status"])) ?></span></td>
                        <td class="cell-action" data-label="Abrir"><a class="icon-link" href="?page=work&id=<?= (int) $work["id"] ?>" aria-label="Abrir trabalho"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state"><i class="bi bi-search" aria-hidden="true"></i><p>Nenhum trabalho encontrado com estes filtros.</p></div>
        <?php endif; ?>
    </section>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "work_new") {
    require_role(["orientador", "administrador"]);
    $courses = rows("SELECT * FROM cursos WHERE ativo=1 ORDER BY nome");
    $students = rows("SELECT id,nome,email,matricula FROM usuarios WHERE perfil='aluno' AND ativo=1 ORDER BY nome");
    $advisors = rows("SELECT id,nome FROM usuarios WHERE perfil IN ('orientador','administrador') AND ativo=1 ORDER BY nome");
    layout_start("Novo trabalho");
    ?>
    <div class="page-head">
        <div class="page-head-text">
            <span class="eyebrow">Cadastro</span>
            <h2>Novo trabalho de TCC</h2>
            <p>Comece pelos dados do TCC e pelo vínculo dos estudantes. O agendamento da banca vem em seguida.</p>
        </div>
        <a class="btn ghost" href="?page=works"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar</a>
    </div>
    <form class="card form" method="post">
        <input type="hidden" name="action" value="create_work">
        <?= csrf_field() ?>
        <div class="form-section">
            <div class="form-section-head"><h3>Dados do TCC</h3><p>As informações aparecem no detalhe do trabalho e na consulta pública.</p></div>
            <div class="form-grid">
                <label class="field span-2"><span>Título do trabalho *</span><input required minlength="5" maxlength="255" name="titulo" placeholder="Ex.: Plataforma de gestão de bancas de TCC"></label>
                <label class="field"><span>Curso *</span><select required name="curso_id"><option value="">Selecione</option><?php foreach ($courses as $course): ?><option value="<?= (int) $course["id"] ?>"><?= e($course["nome"]) ?></option><?php endforeach; ?></select></label>
                <?php if ($u["perfil"] === "administrador"): ?>
                    <label class="field"><span>Orientador *</span><select required name="orientador_id"><?php foreach ($advisors as $advisor): ?><option value="<?= (int) $advisor["id"] ?>"><?= e($advisor["nome"]) ?></option><?php endforeach; ?></select></label>
                <?php endif; ?>
                <label class="field"><span>Ano letivo *</span><input required type="number" min="2000" max="2200" name="ano" value="<?= date("Y") ?>"></label>
                <label class="field"><span>Semestre *</span><select name="semestre"><option value="1">1º semestre</option><option value="2" selected>2º semestre</option></select></label>
                <label class="field span-2"><span>Resumo</span><textarea name="resumo" rows="4" maxlength="5000" placeholder="Breve descrição do objetivo do trabalho"></textarea></label>
            </div>
        </div>
        <div class="form-section">
            <div class="form-section-head">
                <h3>Alunos vinculados *</h3>
                <p>Selecione os estudantes que desenvolverão o trabalho.</p>
            </div>
            <?php if ($students): ?>
                <label class="toolbar-search small-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" placeholder="Filtrar alunos" data-check-filter="#student-list">
                </label>
                <fieldset class="check-fieldset" id="student-list">
                    <legend class="sr-only">Alunos</legend>
                    <div class="check-grid">
                        <?php foreach ($students as $student): ?>
                            <label class="check">
                                <input type="checkbox" name="alunos[]" value="<?= (int) $student["id"] ?>">
                                <span class="check-body"><strong><?= e($student["nome"]) ?></strong><small><?= e($student["email"]) ?><?= $student["matricula"] ? " · " . e($student["matricula"]) : "" ?></small></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            <?php else: ?>
                <div class="empty-state small"><i class="bi bi-people" aria-hidden="true"></i><p>Nenhum aluno ativo cadastrado.</p></div>
            <?php endif; ?>
        </div>
        <div class="form-actions">
            <a class="btn ghost" href="?page=works">Cancelar</a>
            <button class="btn primary" type="submit">Salvar trabalho <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
        </div>
    </form>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "bancas") {
    [$scopeSql, $scopeParams] = access_scope("t");
    $bancas = rows(
        "SELECT b.*, t.titulo, t.id AS trabalho_id, c.sigla,
                (SELECT COUNT(*) FROM banca_avaliadores ba WHERE ba.banca_id=b.id) AS avaliadores
         FROM bancas b
         JOIN trabalhos t ON t.id=b.trabalho_id
         JOIN cursos c ON c.id=t.curso_id
         WHERE {$scopeSql}
         ORDER BY b.data_banca DESC, b.hora_inicio DESC",
        $scopeParams,
    );
    $upcoming = array_values(array_filter($bancas, static fn (array $b): bool => $b["data_banca"] >= date("Y-m-d")));
    $past = array_values(array_filter($bancas, static fn (array $b): bool => $b["data_banca"] < date("Y-m-d")));
    layout_start("Bancas");
    ?>
    <div class="page-head">
        <div class="page-head-text">
            <span class="eyebrow">Defesas</span>
            <h2>Agenda de bancas</h2>
            <p>Datas, horários, modalidades e status de confirmação das defesas.</p>
        </div>
        <?php if (in_array($u["perfil"], ["orientador", "administrador"], true)): ?>
            <a class="btn primary" href="?page=banca_new"><i class="bi bi-plus-lg" aria-hidden="true"></i> Agendar banca</a>
        <?php endif; ?>
    </div>
    <section class="card">
        <div class="card-head"><div><span class="eyebrow">Próximas</span><h2>Defesas agendadas</h2></div><span class="count-pill"><?= count($upcoming) ?></span></div>
        <?php if ($upcoming): ?>
            <div class="timeline">
                <?php foreach ($upcoming as $banca): ?>
                    <a class="event" href="?page=banca&id=<?= (int) $banca["id"] ?>">
                        <time class="event-date" datetime="<?= e($banca["data_banca"]) ?>"><b><?= date("d", strtotime($banca["data_banca"])) ?></b><small><?= strtoupper(date("M", strtotime($banca["data_banca"]))) ?></small></time>
                        <div class="event-body">
                            <strong><?= e($banca["titulo"]) ?></strong>
                            <span><?= e($banca["sigla"]) ?> · <?= e(time_br($banca["hora_inicio"])) ?>–<?= e(time_br($banca["hora_fim"])) ?> · <?= e(modality_label($banca["modalidade"])) ?><?= $banca["local"] ? " · " . e($banca["local"]) : "" ?></span>
                            <small><i class="bi bi-people" aria-hidden="true"></i> <?= (int) $banca["avaliadores"] ?> avaliador(es)</small>
                        </div>
                        <span class="badge <?= e(status_class($banca["status"])) ?>"><?= e(status_label($banca["status"])) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><i class="bi bi-calendar-x" aria-hidden="true"></i><p>Nenhuma banca futura encontrada.</p></div>
        <?php endif; ?>
    </section>
    <?php if ($past): ?>
        <section class="card">
            <div class="card-head"><div><span class="eyebrow">Histórico</span><h2>Bancas realizadas</h2></div><span class="count-pill muted"><?= count($past) ?></span></div>
            <div class="timeline muted-timeline">
                <?php foreach ($past as $banca): ?>
                    <a class="event" href="?page=banca&id=<?= (int) $banca["id"] ?>">
                        <time class="event-date" datetime="<?= e($banca["data_banca"]) ?>"><b><?= date("d", strtotime($banca["data_banca"])) ?></b><small><?= strtoupper(date("M", strtotime($banca["data_banca"]))) ?></small></time>
                        <div class="event-body"><strong><?= e($banca["titulo"]) ?></strong><span><?= e(date_br($banca["data_banca"])) ?> · <?= e(modality_label($banca["modalidade"])) ?></span></div>
                        <span class="badge <?= e(status_class($banca["status"])) ?>"><?= e(status_label($banca["status"])) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "calendar") {
    [$scopeSql, $scopeParams] = access_scope("t");
    $monthParam = query_string("mes", date("Y-m"));
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthParam)) {
        $monthParam = date("Y-m");
    }
    $monthStart = new DateTimeImmutable($monthParam . "-01");
    $monthEnd = $monthStart->modify("last day of this month");
    $gridStart = $monthStart->modify("monday this week");
    $gridEnd = $monthEnd->modify("sunday this week");
    $calendarBancas = rows(
        "SELECT b.*, t.titulo, t.id AS trabalho_id
         FROM bancas b JOIN trabalhos t ON t.id=b.trabalho_id
         WHERE {$scopeSql} AND b.data_banca BETWEEN ? AND ?
         ORDER BY b.hora_inicio",
        array_merge($scopeParams, [$gridStart->format("Y-m-d"), $gridEnd->format("Y-m-d")]),
    );
    $byDate = [];
    foreach ($calendarBancas as $banca) {
        $byDate[$banca["data_banca"]][] = $banca;
    }
    $monthNames = ["", "janeiro", "fevereiro", "março", "abril", "maio", "junho", "julho", "agosto", "setembro", "outubro", "novembro", "dezembro"];
    $monthLabel = $monthNames[(int) $monthStart->format("n")] . " de " . $monthStart->format("Y");
    $prevMonth = $monthStart->modify("-1 month")->format("Y-m");
    $nextMonth = $monthStart->modify("+1 month")->format("Y-m");
    $today = date("Y-m-d");
    layout_start("Calendário");
    ?>
    <div class="page-head">
        <div class="page-head-text">
            <span class="eyebrow">Planejamento</span>
            <h2>Calendário de bancas</h2>
            <p>Visualize as defesas por dia e abra os detalhes com um clique.</p>
        </div>
        <div class="calendar-nav">
            <a class="icon-btn" href="?page=calendar&mes=<?= e($prevMonth) ?>" aria-label="Mês anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
            <a class="btn ghost" href="?page=calendar">Hoje</a>
            <a class="icon-btn" href="?page=calendar&mes=<?= e($nextMonth) ?>" aria-label="Próximo mês"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
        </div>
    </div>
    <section class="card calendar-card">
        <div class="calendar-head">
            <h2><?= e(ucfirst($monthLabel)) ?></h2>
            <span class="count-pill"><?= count($calendarBancas) ?> banca(s) no mês</span>
        </div>
        <div class="calendar-scroll">
            <div class="calendar-grid">
                <?php foreach (["Seg", "Ter", "Qua", "Qui", "Sex", "Sáb", "Dom"] as $weekday): ?>
                    <div class="calendar-weekday"><?= $weekday ?></div>
                <?php endforeach; ?>
                <?php for ($day = $gridStart; $day <= $gridEnd; $day = $day->modify("+1 day")): ?>
                    <?php
                    $key = $day->format("Y-m-d");
                    $events = $byDate[$key] ?? [];
                    $classes = ["calendar-day"];
                    if ($day->format("m") !== $monthStart->format("m")) {
                        $classes[] = "outside";
                    }
                    if ($key === $today) {
                        $classes[] = "today";
                    }
                    if ($events) {
                        $classes[] = "has-events";
                    }
                    ?>
                    <div class="<?= implode(" ", $classes) ?>">
                        <span class="calendar-number"><?= $day->format("j") ?></span>
                        <?php foreach (array_slice($events, 0, 3) as $event): ?>
                            <a class="calendar-event" href="?page=banca&id=<?= (int) $event["id"] ?>" title="<?= e($event["titulo"]) ?>">
                                <b><?= e(time_br($event["hora_inicio"])) ?></b><span><?= e($event["titulo"]) ?></span>
                            </a>
                        <?php endforeach; ?>
                        <?php if (count($events) > 3): ?>
                            <a class="calendar-more" href="?page=bancas">+<?= count($events) - 3 ?> banca(s)</a>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "banca_new") {
    require_role(["orientador", "administrador"]);
    [$scopeSql, $scopeParams] = access_scope("t");
    $workId = (int) ($_GET["work_id"] ?? 0);
    $works = rows(
        "SELECT t.id,t.titulo,c.sigla FROM trabalhos t JOIN cursos c ON c.id=t.curso_id
         WHERE {$scopeSql} AND t.status='RASCUNHO' ORDER BY t.titulo",
        $scopeParams,
    );
    $evaluators = rows("SELECT id,nome,email FROM usuarios WHERE perfil='avaliador' AND ativo=1 ORDER BY nome");
    layout_start("Agendar banca");
    ?>
    <div class="page-head">
        <div class="page-head-text">
            <span class="eyebrow">Nova defesa</span>
            <h2>Agendar banca</h2>
            <p>Defina data, horário e avaliadores. Os convites são registrados com token de 14 dias.</p>
        </div>
        <a class="btn ghost" href="?page=bancas"><i class="bi bi-arrow-left" aria-hidden="true"></i> Voltar</a>
    </div>
    <?php if (!$works): ?>
        <section class="card">
            <div class="empty-state"><i class="bi bi-journal-x" aria-hidden="true"></i><p>Nenhum trabalho em rascunho disponível para agendamento.</p><a class="btn primary" href="?page=work_new">Cadastrar trabalho</a></div>
        </section>
    <?php else: ?>
        <form class="card form" method="post">
            <input type="hidden" name="action" value="create_banca">
            <?= csrf_field() ?>
            <div class="form-section">
                <div class="form-section-head"><h3>Dados da defesa</h3><p>A banca será criada aguardando a confirmação dos avaliadores.</p></div>
                <div class="form-grid">
                    <label class="field span-2"><span>Trabalho *</span><select required name="trabalho_id"><option value="">Selecione o trabalho</option><?php foreach ($works as $work): ?><option value="<?= (int) $work["id"] ?>" <?= $workId === (int) $work["id"] ? "selected" : "" ?>><?= e($work["titulo"]) ?> — <?= e($work["sigla"]) ?></option><?php endforeach; ?></select></label>
                    <label class="field"><span>Data *</span><input required type="date" name="data_banca" min="<?= date("Y-m-d") ?>"></label>
                    <div class="field-row">
                        <label class="field"><span>Início *</span><input required type="time" name="hora_inicio" value="19:00"></label>
                        <label class="field"><span>Fim *</span><input required type="time" name="hora_fim" value="20:00"></label>
                    </div>
                    <label class="field"><span>Modalidade *</span><select name="modalidade" data-modality><option value="presencial">Presencial</option><option value="online">Online</option><option value="hibrida">Híbrida</option></select></label>
                    <label class="field"><span>Local / sala</span><input name="local" placeholder="Ex.: Sala 12 — Bloco B" data-room></label>
                    <label class="field span-2"><span>Link da sala online</span><input type="url" name="link_online" placeholder="https://meet.google.com/..." data-link></label>
                </div>
            </div>
            <div class="form-section">
                <div class="form-section-head"><h3>Avaliadores convidados *</h3><p>Conflitos de agenda são verificados automaticamente.</p></div>
                <?php if ($evaluators): ?>
                    <label class="toolbar-search small-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" placeholder="Filtrar avaliadores" data-check-filter="#evaluator-list"></label>
                    <fieldset class="check-fieldset" id="evaluator-list">
                        <legend class="sr-only">Avaliadores</legend>
                        <div class="check-grid">
                            <?php foreach ($evaluators as $evaluator): ?>
                                <label class="check">
                                    <input type="checkbox" name="avaliadores[]" value="<?= (int) $evaluator["id"] ?>">
                                    <span class="check-body"><strong><?= e($evaluator["nome"]) ?></strong><small><?= e($evaluator["email"]) ?></small></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                <?php else: ?>
                    <div class="empty-state small"><i class="bi bi-person-x" aria-hidden="true"></i><p>Nenhum avaliador ativo cadastrado.</p></div>
                <?php endif; ?>
            </div>
            <div class="form-actions">
                <a class="btn ghost" href="?page=bancas">Cancelar</a>
                <button class="btn primary" type="submit">Agendar e enviar convites <i class="bi bi-send" aria-hidden="true"></i></button>
            </div>
        </form>
    <?php endif; ?>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "work") {
    $work = work_by_id((int) ($_GET["id"] ?? 0));
    if (!$work || !can_access_work($work)) {
        abort(404, "Trabalho não encontrado ou você não tem acesso a ele.");
    }
    $students = rows(
        "SELECT u.nome,u.email,u.matricula FROM alunos_trabalhos at JOIN usuarios u ON u.id=at.aluno_id WHERE at.trabalho_id=? ORDER BY u.nome",
        [$work["id"]],
    );
    $bancas = rows("SELECT * FROM bancas WHERE trabalho_id=? ORDER BY data_banca DESC", [$work["id"]]);
    $documents = rows(
        "SELECT d.*, u.nome AS enviado_nome FROM documentos d JOIN usuarios u ON u.id=d.enviado_por WHERE d.trabalho_id=? ORDER BY d.enviado_em DESC",
        [$work["id"]],
    );
    $corrections = rows("SELECT * FROM correcoes WHERE trabalho_id=? ORDER BY created_at DESC", [$work["id"]]);
    $versions = rows(
        "SELECT v.*, d.nome_original, d.id AS documento_id FROM versoes v JOIN documentos d ON d.id=v.documento_id WHERE v.trabalho_id=? ORDER BY v.numero_versao DESC",
        [$work["id"]],
    );
    $latestCorrection = $corrections[0] ?? null;
    $canManage = in_array($u["perfil"], ["orientador", "administrador"], true);
    $steps = status_steps();
    $stepIndex = status_step_index($work["status"]);
    layout_start("Detalhes do trabalho");
    ?>
    <div class="detail-hero">
        <div class="detail-hero-main">
            <div class="detail-eyebrow"><span class="eyebrow"><?= e($work["curso_nome"]) ?></span><span class="dot-sep">·</span><span><?= (int) $work["ano_letivo"] ?>/<?= (int) $work["semestre_letivo"] ?></span></div>
            <h2><?= e($work["titulo"]) ?></h2>
            <p><?= e($work["resumo"] ?: "Sem resumo cadastrado.") ?></p>
            <div class="detail-flags">
                <span class="badge <?= e(status_class($work["status"])) ?>"><?= e(status_label($work["status"])) ?></span>
                <span class="badge <?= $work["publico"] ? "info" : "neutral" ?>"><i class="bi bi-<?= $work["publico"] ? "globe2" : "eye-slash" ?>" aria-hidden="true"></i> <?= $work["publico"] ? "Público" : "Privado" ?></span>
                <span class="badge neutral"><i class="bi bi-person-check" aria-hidden="true"></i> <?= e($work["orientador_nome"]) ?></span>
            </div>
        </div>
        <div class="detail-hero-actions">
            <?php if ($canManage && !$bancas): ?>
                <a class="btn primary" href="?page=banca_new&work_id=<?= (int) $work["id"] ?>"><i class="bi bi-calendar-plus" aria-hidden="true"></i> Agendar banca</a>
            <?php endif; ?>
            <?php if ($canManage): ?>
                <form method="post">
                    <input type="hidden" name="action" value="toggle_public">
                    <?= csrf_field() ?>
                    <input type="hidden" name="trabalho_id" value="<?= (int) $work["id"] ?>">
                    <button class="btn ghost" type="submit"><i class="bi bi-<?= $work["publico"] ? "eye-slash" : "globe2" ?>" aria-hidden="true"></i> <?= $work["publico"] ? "Ocultar do acervo" : "Publicar no acervo" ?></button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <section class="card stepper-card">
        <div class="card-head"><div><span class="eyebrow">Fluxo</span><h2>Etapa atual: <?= e(status_label($work["status"])) ?></h2></div></div>
        <ol class="stepper">
            <?php $index = 0; foreach ($steps as $statusKey => $stepLabel): ?>
                <?php
                $state = $index < $stepIndex ? "done" : ($index === $stepIndex ? "current" : "");
                ?>
                <li class="step <?= $state ?>" <?= $index === $stepIndex ? 'aria-current="step"' : "" ?>>
                    <span class="step-dot"><?= $index < $stepIndex ? '<i class="bi bi-check-lg" aria-hidden="true"></i>' : $index + 1 ?></span>
                    <span class="step-label"><?= e($stepLabel) ?></span>
                </li>
            <?php $index++; endforeach; ?>
        </ol>
    </section>

    <div class="grid-2">
        <section class="card">
            <div class="card-head"><div><span class="eyebrow">Equipe</span><h2>Orientação e alunos</h2></div></div>
            <dl class="info-list">
                <div><dt>Orientador(a)</dt><dd><span class="avatar tiny" aria-hidden="true"><?= e(initials($work["orientador_nome"])) ?></span><?= e($work["orientador_nome"]) ?></dd></div>
                <div><dt>Alunos</dt><dd>
                    <?php if ($students): ?>
                        <ul class="people-list"><?php foreach ($students as $student): ?><li><span class="avatar tiny" aria-hidden="true"><?= e(initials($student["nome"])) ?></span><span><strong><?= e($student["nome"]) ?></strong><small><?= e($student["email"]) ?><?= $student["matricula"] ? " · " . e($student["matricula"]) : "" ?></small></span></li><?php endforeach; ?></ul>
                    <?php else: ?>—<?php endif; ?>
                </dd></div>
                <div><dt>Curso</dt><dd><?= e($work["curso_nome"]) ?> (<?= e($work["curso_sigla"]) ?>)</dd></div>
                <div><dt>Período</dt><dd><?= (int) $work["ano_letivo"] ?>/<?= (int) $work["semestre_letivo"] ?></dd></div>
            </dl>
        </section>

        <section class="card">
            <div class="card-head"><div><span class="eyebrow">Prazos</span><h2>Versão definitiva</h2></div></div>
            <?php if ($latestCorrection): ?>
                <?php
                $remaining = PrazoService::daysRemaining($latestCorrection["prazo_final"]);
                $percent = max(0, min(100, (int) round(($remaining / PrazoService::DIAS) * 100)));
                ?>
                <div class="deadline <?= $remaining < 0 ? "overdue" : "" ?>">
                    <div class="deadline-top"><b><?= e(date_br($latestCorrection["prazo_final"])) ?></b><span><?= $remaining < 0 ? abs($remaining) . " dia(s) em atraso" : $remaining . " dia(s) restante(s)" ?></span></div>
                    <div class="progress-track" role="presentation"><span class="progress-bar" style="--value: <?= $percent ?>%"></span></div>
                    <p><?= e($latestCorrection["descricao"]) ?></p>
                    <span class="badge <?= $latestCorrection["status"] === "entregue" ? "success" : "warning" ?>"><?= e(ucfirst($latestCorrection["status"])) ?></span>
                </div>
            <?php else: ?>
                <div class="empty-state small"><i class="bi bi-hourglass" aria-hidden="true"></i><p>As correções ainda não foram formalizadas.</p></div>
            <?php endif; ?>
        </section>
    </div>

    <div class="grid-2">
        <section class="card">
            <div class="card-head"><div><span class="eyebrow">Defesas</span><h2>Bancas do trabalho</h2></div><?php if ($canManage && !$bancas): ?><a class="card-link" href="?page=banca_new&work_id=<?= (int) $work["id"] ?>">Agendar</a><?php endif; ?></div>
            <?php if ($bancas): ?>
                <div class="timeline">
                    <?php foreach ($bancas as $banca): ?>
                        <a class="event" href="?page=banca&id=<?= (int) $banca["id"] ?>">
                            <time class="event-date" datetime="<?= e($banca["data_banca"]) ?>"><b><?= date("d", strtotime($banca["data_banca"])) ?></b><small><?= strtoupper(date("M", strtotime($banca["data_banca"]))) ?></small></time>
                            <div class="event-body"><strong><?= e(date_br($banca["data_banca"])) ?> · <?= e(time_br($banca["hora_inicio"])) ?></strong><span><?= e(modality_label($banca["modalidade"])) ?><?= $banca["local"] ? " · " . e($banca["local"]) : "" ?></span></div>
                            <span class="badge <?= e(status_class($banca["status"])) ?>"><?= e(status_label($banca["status"])) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state small"><i class="bi bi-calendar-x" aria-hidden="true"></i><p>Nenhuma banca agendada.</p></div>
            <?php endif; ?>
        </section>

        <section class="card">
            <div class="card-head"><div><span class="eyebrow">Entregas</span><h2>Versões finais</h2></div></div>
            <?php if ($versions): ?>
                <div class="version-list">
                    <?php foreach ($versions as $version): ?>
                        <div class="version">
                            <span class="version-tag">V<?= (int) $version["numero_versao"] ?></span>
                            <div class="version-body"><strong><?= e($version["nome_original"]) ?></strong><small><?= e(date_br($version["enviado_em"], true)) ?> · <?= e(version_label($version["status"])) ?></small></div>
                            <span class="badge <?= $version["status"] === "aprovada" ? "success" : ($version["status"] === "ajustes_solicitados" ? "warning" : "info") ?>"><?= e(version_label($version["status"])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state small"><i class="bi bi-file-earmark" aria-hidden="true"></i><p>Nenhuma versão final enviada.</p></div>
            <?php endif; ?>
        </section>
    </div>

    <section class="card">
        <div class="card-head"><div><span class="eyebrow">Arquivos</span><h2>Documentos</h2></div><span class="count-pill muted"><?= count($documents) ?></span></div>
        <?php if ($documents): ?>
            <div class="table-wrap">
                <table class="responsive">
                    <thead><tr><th>Tipo</th><th>Arquivo</th><th>Enviado por</th><th>Data</th><th>Validação</th><th class="cell-action"></th></tr></thead>
                    <tbody>
                    <?php foreach ($documents as $document): ?>
                        <tr>
                            <td data-label="Tipo"><span class="doc-type"><?= e(document_label($document["tipo"])) ?></span></td>
                            <td data-label="Arquivo"><a href="?page=download&id=<?= (int) $document["id"] ?>" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> <?= e($document["nome_original"]) ?></a><small><?= e(format_bytes((int) $document["tamanho_bytes"])) ?></small></td>
                            <td data-label="Enviado por"><?= e($document["enviado_nome"]) ?></td>
                            <td data-label="Data"><?= e(date_br($document["enviado_em"], true)) ?></td>
                            <td data-label="Validação">
                                <span class="badge <?= $document["status_validacao"] === "aprovado" ? "success" : ($document["status_validacao"] === "correcao" ? "warning" : "neutral") ?>"><?= e(ucfirst($document["status_validacao"])) ?></span>
                                <?php if ($document["observacao"]): ?><small><?= e($document["observacao"]) ?></small><?php endif; ?>
                            </td>
                            <td class="cell-action" data-label="Ações">
                                <?php if ($canManage && in_array($document["status_validacao"], ["pendente", "correcao"], true)): ?>
                                    <details class="inline-menu">
                                        <summary class="icon-btn" aria-label="Validar documento"><i class="bi bi-check2-square" aria-hidden="true"></i></summary>
                                        <form method="post" class="inline-menu-panel">
                                            <input type="hidden" name="action" value="validate_document">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="documento_id" value="<?= (int) $document["id"] ?>">
                                            <label class="field"><span>Observação</span><textarea name="observacao" rows="2"></textarea></label>
                                            <div class="inline-menu-actions">
                                                <button class="btn success small" name="decision" value="aprovado">Aprovar</button>
                                                <?php if ($document["status_validacao"] === "pendente"): ?>
                                                    <button class="btn warning small" name="decision" value="correcao">Solicitar ajuste</button>
                                                <?php endif; ?>
                                            </div>
                                        </form>
                                    </details>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state"><i class="bi bi-folder2-open" aria-hidden="true"></i><p>Nenhum documento enviado.</p></div>
        <?php endif; ?>
    </section>

    <?php if (($u["perfil"] === "aluno" && in_array($work["status"], ["AGUARDANDO_CORRECOES", "AGUARDANDO_VERSAO_FINAL", "AJUSTES_SOLICITADOS"], true)) || $canManage): ?>
        <section class="card form compact">
            <div class="card-head"><div><span class="eyebrow">Upload</span><h2>Enviar documento</h2></div></div>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                <?= csrf_field() ?>
                <input type="hidden" name="trabalho_id" value="<?= (int) $work["id"] ?>">
                <div class="form-grid">
                    <label class="field"><span>Tipo de documento</span>
                        <select name="tipo">
                            <?php if ($u["perfil"] === "aluno"): ?>
                                <option value="versao_final">Versão definitiva (PDF)</option>
                            <?php else: ?>
                                <option value="ata">Ata de defesa</option>
                                <option value="autorizacao_ric">Autorização RIC-CPS</option>
                                <option value="outro">Outro documento</option>
                            <?php endif; ?>
                        </select>
                    </label>
                    <?php if ($canManage && $bancas): ?>
                        <label class="field"><span>Banca relacionada (opcional)</span>
                            <select name="banca_id">
                                <option value="0">Nenhuma</option>
                                <?php foreach ($bancas as $banca): ?><option value="<?= (int) $banca["id"] ?>"><?= e(date_br($banca["data_banca"])) ?> · <?= e(modality_label($banca["modalidade"])) ?></option><?php endforeach; ?>
                            </select>
                        </label>
                    <?php endif; ?>
                    <label class="field span-2 file-field">
                        <span>Arquivo PDF *</span>
                        <input required type="file" name="arquivo" accept="application/pdf" data-file-input>
                        <small>Limite de <?= e(format_bytes((int) $config["upload_max_bytes"])) ?>. O arquivo fica fora da pasta pública.</small>
                    </label>
                </div>
                <div class="form-actions"><button class="btn primary" type="submit"><i class="bi bi-upload" aria-hidden="true"></i> Enviar arquivo</button></div>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canManage): ?>
        <?php if (in_array($work["status"], ["AGUARDANDO_DOCUMENTOS", "AGUARDANDO_CORRECOES"], true)): ?>
            <section class="card form compact">
                <div class="card-head"><div><span class="eyebrow">Correções</span><h2>Formalizar correções</h2></div><span class="badge warning">Prazo de 15 dias</span></div>
                <form method="post">
                    <input type="hidden" name="action" value="correction">
                    <?= csrf_field() ?>
                    <input type="hidden" name="trabalho_id" value="<?= (int) $work["id"] ?>">
                    <input type="hidden" name="banca_id" value="<?= (int) ($bancas[0]["id"] ?? 0) ?>">
                    <div class="form-grid">
                        <label class="field"><span>Data-base *</span><input required type="date" name="data_liberacao" value="<?= date("Y-m-d") ?>" max="<?= date("Y-m-d") ?>"></label>
                        <label class="field span-2"><span>Correções indicadas *</span><textarea required name="descricao" rows="3" placeholder="Descreva os ajustes solicitados pela banca"></textarea></label>
                    </div>
                    <div class="form-actions"><button class="btn primary" type="submit"><i class="bi bi-stopwatch" aria-hidden="true"></i> Definir prazo de 15 dias</button></div>
                </form>
            </section>
        <?php endif; ?>

        <?php foreach ($versions as $version): ?>
            <?php if (in_array($version["status"], ["enviada", "em_analise"], true)): ?>
                <section class="card form compact">
                    <div class="card-head"><div><span class="eyebrow">Avaliação</span><h2>Avaliar versão <?= (int) $version["numero_versao"] ?></h2></div><a class="card-link" href="?page=download&id=<?= (int) $version["documento_id"] ?>" target="_blank" rel="noopener">Abrir PDF</a></div>
                    <form method="post">
                        <input type="hidden" name="action" value="review_version">
                        <?= csrf_field() ?>
                        <input type="hidden" name="versao_id" value="<?= (int) $version["id"] ?>">
                        <label class="field"><span>Observação</span><textarea name="observacao" rows="2" placeholder="Obrigatória ao solicitar ajustes"></textarea></label>
                        <div class="form-actions">
                            <button class="btn warning" name="decision" value="adjust" data-confirm="Enviar solicitação de ajustes ao aluno?">Solicitar ajustes</button>
                            <button class="btn primary" name="decision" value="approve">Aprovar versão</button>
                        </div>
                    </form>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "banca") {
    $banca = banca_by_id((int) ($_GET["id"] ?? 0));
    if (!$banca || !can_access_work($banca)) {
        abort(404, "Banca não encontrada ou você não tem acesso a ela.");
    }
    InviteService::markExpired((int) $banca["id"]);
    $evaluators = rows(
        "SELECT ba.*, u.nome, u.email, c.status AS convite_status, c.expira_em
         FROM banca_avaliadores ba
         JOIN usuarios u ON u.id=ba.avaliador_id
         LEFT JOIN convites c ON c.id = (
             SELECT MAX(c2.id) FROM convites c2
             WHERE c2.banca_avaliador_id=ba.id AND c2.status <> 'substituido'
         )
         WHERE ba.banca_id=? ORDER BY ba.id",
        [$banca["id"]],
    );
    $replaceable = array_values(array_filter(
        $evaluators,
        static fn (array $evaluator): bool => $evaluator["status_resposta"] !== "aceito",
    ));
    $availableEvaluators = rows(
        "SELECT id,nome,email FROM usuarios
         WHERE perfil='avaliador' AND ativo=1
           AND id NOT IN (SELECT avaliador_id FROM banca_avaliadores WHERE banca_id=?)
         ORDER BY nome",
        [$banca["id"]],
    );
    $canManage = in_array($u["perfil"], ["orientador", "administrador"], true);
    $stepIndex = status_step_index($banca["status"]);
    $steps = status_steps();
    layout_start("Detalhes da banca");
    ?>
    <div class="detail-hero">
        <div class="detail-hero-main">
            <div class="detail-eyebrow"><span class="eyebrow">Defesa de TCC</span><span class="dot-sep">·</span><span><?= e(date_br($banca["data_banca"])) ?></span><span class="dot-sep">·</span><span><?= e(time_br($banca["hora_inicio"])) ?>–<?= e(time_br($banca["hora_fim"])) ?></span></div>
            <h2><?= e($banca["titulo"]) ?></h2>
            <div class="detail-flags">
                <span class="badge <?= e(status_class($banca["status"])) ?>"><?= e(status_label($banca["status"])) ?></span>
                <?php if ($banca["realizada_em"]): ?><span class="badge success"><i class="bi bi-check2-circle" aria-hidden="true"></i> Defesa realizada em <?= e(date_br($banca["realizada_em"], true)) ?></span><?php endif; ?>
                <span class="badge info"><i class="bi bi-camera-video" aria-hidden="true"></i> <?= e(modality_label($banca["modalidade"])) ?></span>
                <?php if ($banca["local"]): ?><span class="badge neutral"><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= e($banca["local"]) ?></span><?php endif; ?>
            </div>
        </div>
        <div class="detail-hero-actions">
            <a class="btn ghost" href="?page=work&id=<?= (int) $banca["trabalho_id"] ?>"><i class="bi bi-journal-text" aria-hidden="true"></i> Abrir trabalho</a>
            <?php if ($banca["link_online"]): ?>
                <a class="btn primary" href="<?= e($banca["link_online"]) ?>" target="_blank" rel="noopener noreferrer"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Sala online</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-card"><span class="meta-icon"><i class="bi bi-calendar3" aria-hidden="true"></i></span><div><small>Data</small><strong><?= e(date_long_pt($banca["data_banca"])) ?></strong></div></div>
        <div class="meta-card"><span class="meta-icon"><i class="bi bi-clock" aria-hidden="true"></i></span><div><small>Horário</small><strong><?= e(time_br($banca["hora_inicio"])) ?> às <?= e(time_br($banca["hora_fim"])) ?></strong></div></div>
        <div class="meta-card"><span class="meta-icon"><i class="bi bi-camera-video" aria-hidden="true"></i></span><div><small>Modalidade</small><strong><?= e(modality_label($banca["modalidade"])) ?></strong></div></div>
        <div class="meta-card"><span class="meta-icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span><div><small>Local</small><strong><?= e($banca["local"] ?: "Ambiente online") ?></strong></div></div>
    </div>

    <section class="card stepper-card">
        <div class="card-head"><div><span class="eyebrow">Fluxo</span><h2>Situação da banca</h2></div></div>
        <ol class="stepper">
            <?php $index = 0; foreach ($steps as $statusKey => $stepLabel): ?>
                <?php $state = $index < $stepIndex ? "done" : ($index === $stepIndex ? "current" : ""); ?>
                <li class="step <?= $state ?>" <?= $index === $stepIndex ? 'aria-current="step"' : "" ?>>
                    <span class="step-dot"><?= $index < $stepIndex ? '<i class="bi bi-check-lg" aria-hidden="true"></i>' : $index + 1 ?></span>
                    <span class="step-label"><?= e($stepLabel) ?></span>
                </li>
            <?php $index++; endforeach; ?>
        </ol>
    </section>

    <section class="card">
        <div class="card-head"><div><span class="eyebrow">Comissão</span><h2>Avaliadores e convites</h2></div><span class="count-pill"><?= count($evaluators) ?></span></div>
        <?php if ($evaluators): ?>
            <div class="people-grid">
                <?php foreach ($evaluators as $evaluator): ?>
                    <?php
                    $inviteExpired = $evaluator["expira_em"] && strtotime((string) $evaluator["expira_em"]) < time();
                    $response = $evaluator["status_resposta"];
                    ?>
                    <article class="person-card">
                        <span class="avatar"><?= e(initials($evaluator["nome"])) ?></span>
                        <div class="person-body">
                            <strong><?= e($evaluator["nome"]) ?></strong>
                            <small><?= e($evaluator["email"]) ?></small>
                            <span class="person-role"><?= e($evaluator["funcao"] ?: "Avaliador(a)") ?></span>
                            <small class="person-note">
                                <?php if ($response === "aceito"): ?>Confirmação recebida
                                <?php elseif ($response === "recusado"): ?>Recusou — reenvie ou substitua
                                <?php elseif ($inviteExpired): ?>Convite expirado — reenvie ou substitua
                                <?php elseif ($evaluator["expira_em"]): ?>Convite válido até <?= e(date_br($evaluator["expira_em"])) ?>
                                <?php else: ?>Convite pendente<?php endif; ?>
                            </small>
                        </div>
                        <div class="person-side">
                            <span class="badge <?= $response === "aceito" ? "success" : ($response === "recusado" ? "warning" : "pending") ?>"><?= e(ucfirst($response)) ?></span>
                            <?php if ($canManage && $banca["status"] === "AGUARDANDO_CONFIRMACOES" && $response !== "aceito"): ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="resend_invite">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="banca_avaliador_id" value="<?= (int) $evaluator["id"] ?>">
                                    <button class="btn ghost small" type="submit"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Reenviar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state small"><i class="bi bi-people" aria-hidden="true"></i><p>Nenhum avaliador vinculado.</p></div>
        <?php endif; ?>
    </section>

    <?php if ($canManage && $banca["status"] === "AGUARDANDO_CONFIRMACOES" && $replaceable && $availableEvaluators): ?>
        <section class="card form compact">
            <div class="card-head"><div><span class="eyebrow">Comissão</span><h2>Substituir avaliador</h2></div></div>
            <p class="muted">Use quando um convidado recusar ou não responder. O convite anterior é cancelado e um novo prazo de 14 dias começa.</p>
            <form method="post">
                <input type="hidden" name="action" value="replace_evaluator">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <label class="field"><span>Avaliador a substituir</span>
                        <select required name="banca_avaliador_id">
                            <?php foreach ($replaceable as $candidate): ?>
                                <option value="<?= (int) $candidate["id"] ?>"><?= e($candidate["nome"]) ?> — <?= e(ucfirst($candidate["status_resposta"])) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="field"><span>Novo avaliador</span>
                        <select required name="novo_avaliador_id">
                            <?php foreach ($availableEvaluators as $candidate): ?>
                                <option value="<?= (int) $candidate["id"] ?>"><?= e($candidate["nome"]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="form-actions"><button class="btn primary" type="submit"><i class="bi bi-person-gear" aria-hidden="true"></i> Substituir e enviar convite</button></div>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canManage && $banca["status"] === "CONFIRMADA"): ?>
        <section class="card form compact">
            <div class="card-head"><div><span class="eyebrow">Encerramento</span><h2>Registrar realização</h2></div></div>
            <p class="muted">Esta ação registra a defesa como realizada e inicia a etapa de documentação e correções.</p>
            <form method="post">
                <input type="hidden" name="action" value="defense">
                <?= csrf_field() ?>
                <input type="hidden" name="banca_id" value="<?= (int) $banca["id"] ?>">
                <div class="form-actions"><button class="btn primary" data-confirm="Confirmar que a defesa foi realizada?">Marcar como realizada</button></div>
            </form>
        </section>
    <?php endif; ?>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "invitations") {
    require_role(["avaliador"]);
    $items = rows(
        "SELECT c.id AS convite_id, c.status AS convite_status, c.expira_em,
                b.*, t.titulo, ba.status_resposta
         FROM convites c
         JOIN banca_avaliadores ba ON ba.id=c.banca_avaliador_id
         JOIN bancas b ON b.id=ba.banca_id
         JOIN trabalhos t ON t.id=b.trabalho_id
         WHERE ba.avaliador_id=? AND c.status <> 'substituido'
         ORDER BY b.data_banca DESC, c.created_at DESC",
        [$u["id"]],
    );
    $pending = array_values(array_filter($items, static fn (array $item): bool => $item["convite_status"] === "pendente" && (!$item["expira_em"] || strtotime($item["expira_em"]) >= time())));
    layout_start("Meus convites");
    ?>
    <div class="page-head">
        <div class="page-head-text">
            <span class="eyebrow">Comissões avaliadoras</span>
            <h2>Convites recebidos</h2>
            <p><?= count($pending) ?> convite(s) aguardando sua resposta. Confirme apenas as bancas em que poderá atuar.</p>
        </div>
    </div>
    <?php if (!$items): ?>
        <section class="card"><div class="empty-state"><i class="bi bi-envelope-open" aria-hidden="true"></i><p>Nenhum convite recebido até o momento.</p></div></section>
    <?php else: ?>
        <div class="invite-grid">
            <?php foreach ($items as $item): ?>
                <?php
                $expired = $item["expira_em"] && strtotime($item["expira_em"]) < time();
                $open = $item["convite_status"] === "pendente" && !$expired;
                ?>
                <article class="invite-card status-<?= e($item["status_resposta"]) ?>">
                    <div class="invite-top">
                        <span class="badge <?= e(status_class($item["status"])) ?>"><?= e(status_label($item["status"])) ?></span>
                        <span class="invite-date"><i class="bi bi-calendar3" aria-hidden="true"></i> <?= e(date_br($item["data_banca"])) ?></span>
                    </div>
                    <h3><?= e($item["titulo"]) ?></h3>
                    <dl class="invite-meta">
                        <div><dt>Horário</dt><dd><?= e(time_br($item["hora_inicio"])) ?>–<?= e(time_br($item["hora_fim"])) ?></dd></div>
                        <div><dt>Modalidade</dt><dd><?= e(modality_label($item["modalidade"])) ?></dd></div>
                        <div><dt>Local</dt><dd><?= e($item["local"] ?: "Online") ?></dd></div>
                    </dl>
                    <div class="invite-footer">
                        <?php if ($open): ?>
                            <span class="invite-state pending"><i class="bi bi-hourglass-split" aria-hidden="true"></i> Resposta pendente<?= $item["expira_em"] ? " · até " . e(date_br($item["expira_em"])) : "" ?></span>
                            <form class="invite-actions" method="post">
                                <input type="hidden" name="action" value="respond_invite">
                                <?= csrf_field() ?>
                                <input type="hidden" name="convite_id" value="<?= (int) $item["convite_id"] ?>">
                                <button class="btn warning" name="answer" value="recusado" data-confirm="Recusar este convite? O orientador será avisado.">Recusar</button>
                                <button class="btn primary" name="answer" value="aceito">Aceitar</button>
                            </form>
                        <?php elseif ($item["status_resposta"] === "aceito"): ?>
                            <span class="invite-state accepted"><i class="bi bi-check-circle" aria-hidden="true"></i> Convite aceito</span>
                            <a class="btn ghost small" href="?page=banca&id=<?= (int) $item["id"] ?>">Ver banca</a>
                        <?php elseif ($item["status_resposta"] === "recusado"): ?>
                            <span class="invite-state refused"><i class="bi bi-x-circle" aria-hidden="true"></i> Convite recusado</span>
                        <?php else: ?>
                            <span class="invite-state expired"><i class="bi bi-clock-history" aria-hidden="true"></i> Convite expirado</span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "invite") {
    $token = query_string("token");
    if (isset($_GET["respondido"])) {
        layout_start("Convite respondido", ["mode" => "public"]);
        ?>
        <section class="result-panel">
            <span class="result-icon success"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
            <span class="eyebrow">Resposta registrada</span>
            <h2>Obrigado por responder ao convite.</h2>
            <p>O orientador responsável já foi informado sobre a sua decisão.</p>
            <a class="btn primary" href="index.php">Voltar ao início</a>
        </section>
        <?php
        layout_end();
        exit();
    }
    $invite = $token !== ""
        ? one(
            "SELECT c.status, c.expira_em, ba.avaliador_id, b.data_banca, b.hora_inicio, b.hora_fim, b.modalidade, b.local, b.id AS banca_id, t.titulo, u.nome AS avaliador_nome
             FROM convites c
             JOIN banca_avaliadores ba ON ba.id=c.banca_avaliador_id
             JOIN bancas b ON b.id=ba.banca_id
             JOIN trabalhos t ON t.id=b.trabalho_id
             JOIN usuarios u ON u.id=ba.avaliador_id
             WHERE c.token_hash=?",
            [TokenService::hash($token)],
        )
        : null;
    $valid = $invite && $invite["status"] === "pendente" && (!$invite["expira_em"] || strtotime((string) $invite["expira_em"]) >= time());
    if ($valid && is_logged_in() && (int) current_user()["id"] !== (int) $invite["avaliador_id"]) {
        $valid = false;
        $wrongAccount = true;
    }
    layout_start("Convite para banca", ["mode" => "public"]);
    ?>
    <section class="result-panel invite-result">
        <?php if ($valid): ?>
            <span class="eyebrow">Convite SIG-Banca</span>
            <span class="result-icon info"><i class="bi bi-envelope-paper" aria-hidden="true"></i></span>
            <h2><?= e($invite["titulo"]) ?></h2>
            <p>Olá, <?= e($invite["avaliador_nome"]) ?>. Você foi convidado(a) para compor a banca avaliadora deste trabalho.</p>
            <dl class="invite-meta wide">
                <div><dt>Data</dt><dd><?= e(date_long_pt($invite["data_banca"])) ?></dd></div>
                <div><dt>Horário</dt><dd><?= e(time_br($invite["hora_inicio"])) ?>–<?= e(time_br($invite["hora_fim"])) ?></dd></div>
                <div><dt>Modalidade</dt><dd><?= e(modality_label($invite["modalidade"])) ?></dd></div>
                <div><dt>Local</dt><dd><?= e($invite["local"] ?: "Online") ?></dd></div>
            </dl>
            <?php if (!is_logged_in()): ?><p class="muted small-note">Você não precisa estar logado para responder a este convite.</p><?php endif; ?>
            <form class="invite-actions center" method="post">
                <input type="hidden" name="action" value="respond_invite">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <button class="btn warning" name="answer" value="recusado" data-confirm="Recusar este convite?">Recusar convite</button>
                <button class="btn primary" name="answer" value="aceito">Aceitar convite</button>
            </form>
        <?php else: ?>
            <span class="eyebrow">Convite indisponível</span>
            <span class="result-icon warning"><i class="bi bi-link-45deg" aria-hidden="true"></i></span>
            <h2>Este link não é válido.</h2>
            <p><?= isset($wrongAccount) ? "Este convite pertence a outra conta. Entre com o e-mail que recebeu o convite." : "O convite pode ter expirado ou já ter sido respondido." ?></p>
            <div class="result-actions"><a class="btn ghost" href="index.php">Ir para o início</a><a class="btn primary" href="?page=login">Entrar</a></div>
        <?php endif; ?>
    </section>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "notifications") {
    require_login();
    $notifications = rows(
        "SELECT * FROM notificacoes WHERE usuario_id=? ORDER BY created_at DESC LIMIT 100",
        [$u["id"]],
    );
    layout_start("Notificações");
    ?>
    <div class="page-head">
        <div class="page-head-text">
            <span class="eyebrow">Central de avisos</span>
            <h2>Suas notificações</h2>
            <p>Avisos de convites, bancas, versões e prazos relacionados aos seus trabalhos.</p>
        </div>
        <?php if ($notifications): ?>
            <form method="post">
                <input type="hidden" name="action" value="mark_notifications_read">
                <?= csrf_field() ?>
                <button class="btn ghost" type="submit"><i class="bi bi-check2-all" aria-hidden="true"></i> Marcar todas como lidas</button>
            </form>
        <?php endif; ?>
    </div>
    <section class="card">
        <?php if ($notifications): ?>
            <ul class="notification-list">
                <?php foreach ($notifications as $notification): ?>
                    <li class="<?= $notification["lida_em"] ? "" : "unread" ?>">
                        <span class="notification-icon type-<?= e($notification["tipo"]) ?>"><i class="bi bi-<?= match ($notification["tipo"]) {
                            "convite" => "envelope-paper",
                            "prazo" => "stopwatch",
                            "versao" => "file-earmark-check",
                            "documento" => "file-earmark-pdf",
                            "banca" => "calendar-event",
                            default => "bell",
                        } ?>" aria-hidden="true"></i></span>
                        <div class="notification-body">
                            <strong><?= e($notification["titulo"]) ?><?= $notification["lida_em"] ? "" : '<span class="unread-dot" title="Não lida"></span>' ?></strong>
                            <p><?= e($notification["mensagem"]) ?></p>
                            <small><?= e(date_br($notification["created_at"], true)) ?></small>
                        </div>
                        <?php if ($notification["link_interno"]): ?>
                            <a class="icon-link" href="<?= e($notification["link_interno"]) ?>" aria-label="Abrir notificação"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="empty-state"><i class="bi bi-bell-slash" aria-hidden="true"></i><p>Nenhuma notificação recebida.</p></div>
        <?php endif; ?>
    </section>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "public_search") {
    $query = query_string("q");
    $courseId = (int) ($_GET["curso"] ?? 0);
    $where = ["t.publico=1"];
    $params = [];
    if ($query !== "") {
        $where[] = "(t.titulo LIKE ? OR u.nome LIKE ? OR EXISTS(
            SELECT 1 FROM alunos_trabalhos at JOIN usuarios al ON al.id=at.aluno_id
            WHERE at.trabalho_id=t.id AND al.nome LIKE ?
        ))";
        $like = "%" . $query . "%";
        array_push($params, $like, $like, $like);
    }
    if ($courseId > 0) {
        $where[] = "t.curso_id=?";
        $params[] = $courseId;
    }
    $results = rows(
        "SELECT t.*, c.nome AS curso, c.sigla, u.nome AS orientador,
                (SELECT GROUP_CONCAT(a.nome SEPARATOR ', ')
                 FROM alunos_trabalhos at JOIN usuarios a ON a.id=at.aluno_id
                 WHERE at.trabalho_id=t.id) AS alunos
         FROM trabalhos t
         JOIN cursos c ON c.id=t.curso_id
         JOIN usuarios u ON u.id=t.orientador_id
         WHERE " . implode(" AND ", $where) . "
         ORDER BY t.updated_at DESC LIMIT 30",
        $params,
    );
    $courses = rows("SELECT * FROM cursos WHERE ativo=1 ORDER BY nome");
    layout_start("Consulta pública", ["mode" => "public"]);
    ?>
    <section class="search-hero">
        <span class="eyebrow light">Acervo acadêmico</span>
        <h2>Encontre trabalhos e bancas</h2>
        <p>Consulte as informações autorizadas para divulgação pela instituição.</p>
        <form class="search-form" method="get">
            <input type="hidden" name="page" value="public_search">
            <label class="search-input"><i class="bi bi-search" aria-hidden="true"></i><input name="q" value="<?= e($query) ?>" placeholder="Título, aluno ou orientador"></label>
            <select name="curso" aria-label="Curso">
                <option value="">Todos os cursos</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= (int) $course["id"] ?>" <?= $courseId === (int) $course["id"] ? "selected" : "" ?>><?= e($course["nome"]) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn gold" type="submit">Pesquisar</button>
        </form>
        <div class="search-summary"><?= count($results) ?> resultado(s)<?= $query !== "" ? " para “" . e($query) . "”" : "" ?></div>
    </section>
    <section class="result-grid">
        <?php foreach ($results as $result): ?>
            <article class="result-card">
                <div class="result-top">
                    <span class="badge <?= e(status_class($result["status"])) ?>"><?= e(status_label($result["status"])) ?></span>
                    <span class="result-course"><?= e($result["sigla"]) ?> · <?= (int) $result["ano_letivo"] ?>/<?= (int) $result["semestre_letivo"] ?></span>
                </div>
                <h3><?= e($result["titulo"]) ?></h3>
                <p><?= e(mb_strimwidth((string) $result["resumo"], 0, 190, "…")) ?></p>
                <footer>
                    <span><i class="bi bi-person" aria-hidden="true"></i> <?= e($result["alunos"] ?: "Aluno não informado") ?></span>
                    <span><i class="bi bi-person-check" aria-hidden="true"></i> <?= e($result["orientador"]) ?></span>
                </footer>
            </article>
        <?php endforeach; ?>
        <?php if (!$results): ?>
            <div class="empty-state card"><i class="bi bi-search" aria-hidden="true"></i><p>Nenhum trabalho encontrado com estes filtros.</p><a class="btn ghost" href="?page=public_search">Limpar busca</a></div>
        <?php endif; ?>
    </section>
    <?php
    layout_end();
    exit();
}

if ($currentPage === "admin") {
    require_role(["administrador"]);
    $courses = rows(
        "SELECT c.*, (SELECT COUNT(*) FROM trabalhos t WHERE t.curso_id=c.id) AS trabalhos
         FROM cursos c ORDER BY c.nome",
    );
    $users = rows(
        "SELECT nome,email,perfil,ativo,created_at FROM usuarios ORDER BY created_at DESC LIMIT 20",
    );
    $auditLog = rows(
        "SELECT a.*, u.nome AS usuario_nome FROM auditoria a LEFT JOIN usuarios u ON u.id=a.usuario_id ORDER BY a.created_at DESC, a.id DESC LIMIT 12",
    );
    $totals = [
        "users" => (int) one("SELECT COUNT(*) AS total FROM usuarios")["total"],
        "works" => (int) one("SELECT COUNT(*) AS total FROM trabalhos")["total"],
        "bancas" => (int) one("SELECT COUNT(*) AS total FROM bancas")["total"],
        "documents" => (int) one("SELECT COUNT(*) AS total FROM documentos")["total"],
    ];
    layout_start("Administração");
    ?>
    <div class="page-head">
        <div class="page-head-text">
            <span class="eyebrow">Coordenação</span>
            <h2>Administração do sistema</h2>
            <p>Cursos, usuários e trilha de auditoria institucional.</p>
        </div>
        <a class="btn ghost" href="?page=public_search"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> Consulta pública</a>
    </div>
    <section class="metric-grid">
        <article class="metric-card"><span class="metric-icon blue"><i class="bi bi-people" aria-hidden="true"></i></span><div><span class="metric-value"><?= $totals["users"] ?></span><span class="metric-label">Usuários</span></div></article>
        <article class="metric-card"><span class="metric-icon gold"><i class="bi bi-journal-text" aria-hidden="true"></i></span><div><span class="metric-value"><?= $totals["works"] ?></span><span class="metric-label">Trabalhos</span></div></article>
        <article class="metric-card"><span class="metric-icon green"><i class="bi bi-calendar-event" aria-hidden="true"></i></span><div><span class="metric-value"><?= $totals["bancas"] ?></span><span class="metric-label">Bancas</span></div></article>
        <article class="metric-card"><span class="metric-icon slate"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i></span><div><span class="metric-value"><?= $totals["documents"] ?></span><span class="metric-label">Documentos</span></div></article>
    </section>
    <div class="grid-2">
        <section class="card form compact">
            <div class="card-head"><div><span class="eyebrow">Cursos</span><h2>Novo curso</h2></div></div>
            <form method="post">
                <input type="hidden" name="action" value="add_course">
                <?= csrf_field() ?>
                <div class="form-grid">
                    <label class="field span-2"><span>Nome do curso *</span><input required name="nome" maxlength="150" placeholder="Ex.: Sistemas para Internet"></label>
                    <label class="field"><span>Sigla *</span><input required name="sigla" maxlength="20" placeholder="Ex.: SI"></label>
                </div>
                <div class="form-actions"><button class="btn primary" type="submit">Cadastrar curso</button></div>
            </form>
            <div class="card-head spaced"><div><span class="eyebrow">Catálogo</span><h2>Cursos cadastrados</h2></div></div>
            <div class="table-wrap">
                <table class="responsive">
                    <thead><tr><th>Curso</th><th>Sigla</th><th>Trabalhos</th><th>Estado</th></tr></thead>
                    <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td data-label="Curso"><strong><?= e($course["nome"]) ?></strong></td>
                            <td data-label="Sigla"><?= e($course["sigla"]) ?></td>
                            <td data-label="Trabalhos"><?= (int) $course["trabalhos"] ?></td>
                            <td data-label="Estado"><span class="badge <?= $course["ativo"] ? "success" : "neutral" ?>"><?= $course["ativo"] ? "Ativo" : "Inativo" ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <section class="card">
            <div class="card-head"><div><span class="eyebrow">Acessos</span><h2>Usuários recentes</h2></div></div>
            <div class="table-wrap">
                <table class="responsive">
                    <thead><tr><th>Nome</th><th>Perfil</th><th>Estado</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $person): ?>
                        <tr>
                            <td data-label="Nome"><strong><?= e($person["nome"]) ?></strong><small><?= e($person["email"]) ?></small></td>
                            <td data-label="Perfil"><?= e(role_label($person["perfil"])) ?></td>
                            <td data-label="Estado"><span class="badge <?= $person["ativo"] ? "success" : "neutral" ?>"><?= $person["ativo"] ? "Ativo" : "Inativo" ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-head spaced"><div><span class="eyebrow">Auditoria</span><h2>Últimos eventos</h2></div></div>
            <ul class="audit-list">
                <?php foreach ($auditLog as $entry): ?>
                    <li>
                        <span class="audit-dot"></span>
                        <div><strong><?= e(ucfirst(str_replace("_", " ", $entry["acao"]))) ?></strong><small><?= e(ucfirst(str_replace("_", " ", $entry["entidade"]))) ?><?= $entry["entidade_id"] ? " #" . (int) $entry["entidade_id"] : "" ?> · <?= e($entry["usuario_nome"] ?? "Sistema") ?> · <?= e(date_br($entry["created_at"], true)) ?></small></div>
                    </li>
                <?php endforeach; ?>
                <?php if (!$auditLog): ?><li class="muted">Nenhum evento registrado.</li><?php endif; ?>
            </ul>
        </section>
    </div>
    <?php
    layout_end();
    exit();
}

http_response_code(404);
layout_start("Página não encontrada", ["mode" => is_logged_in() ? "app" : "public"]);
?>
<section class="card">
    <div class="empty-state large">
        <i class="bi bi-compass" aria-hidden="true"></i>
        <h2>Página não encontrada</h2>
        <p>O endereço solicitado não existe ou você não tem permissão para acessá-lo.</p>
        <a class="btn primary" href="<?= is_logged_in() ? "?page=dashboard" : "index.php" ?>">Voltar ao início</a>
    </div>
</section>
<?php
layout_end();

