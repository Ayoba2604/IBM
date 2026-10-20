<?php
declare(strict_types=1);

if ($currentPage === "works") {

    $where =
        $u["perfil"] === "administrador"
            ? "1=1"
            : ($u["perfil"] === "orientador"
                ? "t.orientador_id=" . (int) $u["id"]
                : "t.id IN (SELECT trabalho_id FROM alunos_trabalhos WHERE aluno_id=" .
                    (int) $u["id"] .
                    ")");
    $works = rows(
        "SELECT t.*,c.sigla,u.nome orientador,GROUP_CONCAT(a.nome SEPARATOR ', ') alunos FROM trabalhos t JOIN cursos c ON c.id=t.curso_id JOIN usuarios u ON u.id=t.orientador_id LEFT JOIN alunos_trabalhos at ON at.trabalho_id=t.id LEFT JOIN usuarios a ON a.id=at.aluno_id WHERE {$where} GROUP BY t.id ORDER BY t.updated_at DESC",
    );
    layout_start("Trabalhos");
    ?>
    <div class="dashboard-top"><p>Cadastros, versões e situação de cada TCC.</p><?php if (
        in_array($u["perfil"], ["orientador", "administrador"], true)
    ): ?><a class="btn primary" href="?page=work_new"><i class="bi bi-plus-lg"></i> Novo trabalho</a><?php endif; ?></div>
    <section class="panel table-wrap"><table><thead><tr><th>Trabalho</th><th>Participantes</th><th>Curso</th><th>Status</th><th></th></tr></thead><tbody><?php foreach (
        $works
        as $w
    ): ?><tr><td><strong><?= e($w["titulo"]) ?></strong><small><?= e(
    $w["orientador"],
) ?></small></td><td><?= e($w["alunos"] ?? "—") ?></td><td><?= e(
    $w["sigla"],
) ?> · <?= $w["ano_letivo"] ?>/<?= $w[
    "semestre_letivo"
] ?></td><td><span class="badge <?= status_class($w["status"]) ?>"><?= e(
    status_label($w["status"]),
) ?></span></td><td><a class="icon-link" href="?page=work&id=<?= $w[
    "id"
] ?>"><i class="bi bi-arrow-up-right"></i></a></td></tr><?php endforeach; ?></tbody></table><?php if (
    !$works
): ?><div class="empty">Nenhum trabalho disponível.</div><?php endif; ?></section>
    <?php
    layout_end();
    exit();

}
if ($currentPage === "work_new") {

    require_role(["orientador", "administrador"]);
    $courses = rows("SELECT * FROM cursos WHERE ativo=1 ORDER BY nome");
    $students = rows(
        "SELECT * FROM usuarios WHERE perfil='aluno' AND ativo=1 ORDER BY nome",
    );
    $advisors = rows(
        "SELECT * FROM usuarios WHERE perfil='orientador' AND ativo=1 ORDER BY nome",
    );
    layout_start("Novo trabalho");
    ?>
    <form class="panel form" method="post"><input type="hidden" name="action" value="create_work"><?= csrf_field() ?><div class="panel-title"><div><h2>Dados do TCC</h2><p>Comece pelo cadastro e vínculo dos estudantes.</p></div></div><div class="form-grid"><label class="span-2">Título do trabalho<input required minlength="5" name="titulo"></label><label>Curso<select required name="curso_id"><option value="">Selecione</option><?php foreach (
    $courses
    as $c
): ?><option value="<?= $c["id"] ?>"><?= e(
    $c["nome"],
) ?></option><?php endforeach; ?></select></label><?php if (
    $u["perfil"] === "administrador"
): ?><label>Orientador<select name="orientador_id"><?php foreach (
    $advisors
    as $a
): ?><option value="<?= $a["id"] ?>"><?= e(
    $a["nome"],
) ?></option><?php endforeach; ?></select></label><?php endif; ?><label>Ano letivo<input required type="number" name="ano" value="<?= date(
    "Y",
) ?>"></label><label>Semestre<select name="semestre"><option value="1">1º semestre</option><option value="2" selected>2º semestre</option></select></label><label class="span-2">Resumo<textarea name="resumo" rows="4"></textarea></label><fieldset class="span-2"><legend>Alunos vinculados</legend><div class="check-grid"><?php foreach (
    $students
    as $s
): ?><label class="check"><input type="checkbox" name="alunos[]" value="<?= $s[
    "id"
] ?>"><span><?= e($s["nome"]) ?><small><?= e(
    $s["email"],
) ?></small></span></label><?php endforeach; ?></div></fieldset></div><div class="form-actions"><a class="btn ghost" href="?page=works">Cancelar</a><button class="btn primary">Salvar trabalho <i class="bi bi-arrow-right"></i></button></div></form>
    <?php
    layout_end();
    exit();

}
if ($currentPage === "bancas" || $currentPage === "calendar") {

    $where =
        $u["perfil"] === "administrador"
            ? "1=1"
            : ($u["perfil"] === "orientador"
                ? "t.orientador_id=" . (int) $u["id"]
                : "t.id IN (SELECT trabalho_id FROM alunos_trabalhos WHERE aluno_id=" .
                    (int) $u["id"] .
                    ")");
    $bancas = rows(
        "SELECT b.*,t.titulo,c.sigla FROM bancas b JOIN trabalhos t ON t.id=b.trabalho_id JOIN cursos c ON c.id=t.curso_id WHERE {$where} ORDER BY b.data_banca,b.hora_inicio",
    );
    layout_start(
        $currentPage === "calendar" ? "Calendário de bancas" : "Bancas",
    );
    ?>
    <div class="dashboard-top"><p>Agenda cronológica de defesas e confirmações.</p><?php if (
        in_array($u["perfil"], ["orientador", "administrador"], true)
    ): ?><a class="btn primary" href="?page=banca_new"><i class="bi bi-plus-lg"></i> Agendar banca</a><?php endif; ?></div><section class="panel timeline"><?php
foreach ($bancas as $b): ?><a class="event" href="?page=banca&id=<?= $b[
    "id"
] ?>"><time><b><?= date(
    "d",
    strtotime($b["data_banca"]),
) ?></b><small><?= strtoupper(
    date("M", strtotime($b["data_banca"])),
) ?></small></time><div><strong><?= e(
    $b["titulo"],
) ?></strong><span><?= date_br($b["data_banca"]) ?> · <?= e(
     substr($b["hora_inicio"], 0, 5),
 ) ?> · <?=
  e(ucfirst($b["modalidade"]));
  echo $b["local"] ? " · " . e($b["local"]) : "";
 ?></span></div><span class="badge <?= status_class($b["status"]) ?>"><?= e(
    status_label($b["status"]),
) ?></span></a><?php endforeach;
if (!$bancas): ?><div class="empty">Nenhuma banca encontrada.</div><?php endif;
?></section><?php
layout_end();
exit();

}
if ($currentPage === "banca_new") {

    require_role(["orientador", "administrador"]);
    $workId = (int) ($_GET["work_id"] ?? 0);
    $where =
        $u["perfil"] === "administrador"
            ? "1=1"
            : "orientador_id=" . (int) $u["id"];
    $works = rows("SELECT * FROM trabalhos WHERE {$where} ORDER BY titulo");
    $evaluators = rows(
        "SELECT * FROM usuarios WHERE perfil='avaliador' AND ativo=1 ORDER BY nome",
    );
    layout_start("Agendar banca");
    ?>
    <form class="panel form" method="post"><input type="hidden" name="action" value="create_banca"><?= csrf_field() ?><div class="panel-title"><div><h2>Nova defesa</h2><p>Defina os dados e envie os convites aos avaliadores.</p></div></div><div class="form-grid"><label class="span-2">Trabalho<select required name="trabalho_id"><option value="">Selecionar</option><?php foreach (
    $works
    as $w
): ?><option value="<?= $w["id"] ?>" <?= $workId === $w["id"]
    ? "selected"
    : "" ?>><?= e(
    $w["titulo"],
) ?></option><?php endforeach; ?></select></label><label>Data<input required type="date" name="data_banca" min="<?= date(
    "Y-m-d",
) ?>"></label><label>Início<input required type="time" name="hora_inicio"></label><label>Fim<input required type="time" name="hora_fim"></label><label>Modalidade<select name="modalidade"><option value="presencial">Presencial</option><option value="online">Online</option><option value="hibrida">Híbrida</option></select></label><label>Local / sala<input name="local"></label><label>Link online<input type="url" name="link_online"></label><fieldset class="span-2"><legend>Avaliadores convidados</legend><div class="check-grid"><?php foreach (
    $evaluators
    as $a
): ?><label class="check"><input type="checkbox" name="avaliadores[]" value="<?= $a[
    "id"
] ?>"><span><?= e($a["nome"]) ?><small><?= e(
    $a["email"],
) ?></small></span></label><?php endforeach; ?></div></fieldset></div><div class="form-actions"><a class="btn ghost" href="?page=bancas">Cancelar</a><button class="btn primary">Salvar e enviar convites</button></div></form><?php
layout_end();
exit();

}

if ($currentPage === "work") {

    $work = work_by_id((int) ($_GET["id"] ?? 0));
    if (!$work || !can_access_work($work)) {
        http_response_code(404);
        exit("Trabalho não encontrado.");
    }
    $students = rows(
        "SELECT u.nome FROM alunos_trabalhos at JOIN usuarios u ON u.id=at.aluno_id WHERE at.trabalho_id=?",
        [$work["id"]],
    );
    $bancas = rows(
        "SELECT * FROM bancas WHERE trabalho_id=? ORDER BY data_banca DESC",
        [$work["id"]],
    );
    $docs = rows(
        "SELECT d.*,u.nome enviado_nome FROM documentos d JOIN usuarios u ON u.id=d.enviado_por WHERE d.trabalho_id=? ORDER BY d.enviado_em DESC",
        [$work["id"]],
    );
    $corrections = rows(
        "SELECT * FROM correcoes WHERE trabalho_id=? ORDER BY created_at DESC",
        [$work["id"]],
    );
    $versions = rows(
        "SELECT v.*,d.nome_original FROM versoes v JOIN documentos d ON d.id=v.documento_id WHERE v.trabalho_id=? ORDER BY v.numero_versao DESC",
        [$work["id"]],
    );
    layout_start("Detalhes do trabalho");
    ?>
    <div class="work-head"><div><span class="eyebrow"><?= e(
        $work["curso_nome"],
    ) ?> · <?= $work["ano_letivo"] ?>/<?= $work[
    "semestre_letivo"
] ?></span><h2><?= e($work["titulo"]) ?></h2><p><?= e(
    $work["resumo"],
) ?></p><span class="badge <?= status_class($work["status"]) ?>"><?= e(
    status_label($work["status"]),
) ?></span></div><?php if (
    in_array($u["perfil"], ["orientador", "administrador"], true) &&
    !$bancas
): ?><a class="btn primary" href="?page=banca_new&work_id=<?= $work[
    "id"
] ?>">Agendar banca</a><?php endif; ?></div>
    <section class="grid-2"><article class="panel"><h2>Equipe</h2><dl><dt>Orientador</dt><dd><?= e(
        $work["orientador_nome"],
    ) ?></dd><dt>Alunos</dt><dd><?php foreach ($students as $s) {
    echo e($s["nome"]) . "<br>";
} ?></dd></dl></article><article class="panel"><h2>Prazo da versão definitiva</h2><?php if (
    $corrections
):

    $c = $corrections[0];
    $remaining = PrazoService::daysRemaining($c["prazo_final"]);
    ?><div class="deadline <?= $remaining < 0
    ? "overdue"
    : "" ?>"><b><?= date_br($c["prazo_final"]) ?></b><span><?= $remaining < 0
    ? abs($remaining) . " dias em atraso"
    : $remaining . " dias restantes" ?></span></div><p><?= e(
    $c["descricao"],
) ?></p><?php
else:
     ?><div class="empty">As correções ainda não foram formalizadas.</div><?php
endif; ?></article></section>
    <section class="grid-2"><article class="panel"><div class="panel-title"><h2>Bancas</h2></div><?php
    foreach ($bancas as $b): ?><a class="event" href="?page=banca&id=<?= $b[
    "id"
] ?>"><time><b><?= date(
    "d",
    strtotime($b["data_banca"]),
) ?></b><small><?= strtoupper(
    date("M", strtotime($b["data_banca"])),
) ?></small></time><div><strong><?= date_br($b["data_banca"]) ?> · <?= e(
     substr($b["hora_inicio"], 0, 5),
 ) ?></strong><span><?= e(ucfirst($b["modalidade"])) ?> · <?= e(
     $b["local"],
 ) ?></span></div></a><?php endforeach;
    if (
        !$bancas
    ): ?><div class="empty">Nenhuma banca agendada.</div><?php endif;
    ?></article><article class="panel"><div class="panel-title"><h2>Versões finais</h2></div><?php
foreach ($versions as $v): ?><div class="version"><span>V<?= $v[
    "numero_versao"
] ?></span><div><strong><?= e(
    $v["nome_original"],
) ?></strong><small><?= date_br($v["enviado_em"], true) ?> · <?= e(
     str_replace("_", " ", $v["status"]),
 ) ?></small></div></div><?php endforeach;
if (
    !$versions
): ?><div class="empty">Nenhuma versão final enviada.</div><?php endif;
?></article></section>
    <section class="panel"><div class="panel-title"><h2>Documentos</h2></div><?php if (
        $docs
    ): ?><table><thead><tr><th>Tipo</th><th>Arquivo</th><th>Enviado por</th><th>Estado</th></tr></thead><tbody><?php foreach (
    $docs
    as $d
): ?><tr><td><?= e(str_replace("_", " ", ucfirst($d["tipo"]))) ?></td><td><?= e(
    $d["nome_original"],
) ?></td><td><?= e(
    $d["enviado_nome"],
) ?></td><td><span class="badge secondary"><?= e(
    $d["status_validacao"],
) ?></span></td></tr><?php endforeach; ?></tbody></table><?php else: ?><div class="empty">Nenhum documento enviado.</div><?php endif; ?></section>
    <?php if (
        ($u["perfil"] === "aluno" &&
            in_array(
                $work["status"],
                ["AGUARDANDO_VERSAO_FINAL", "AJUSTES_SOLICITADOS"],
                true,
            )) ||
        in_array($u["perfil"], ["orientador", "administrador"], true)
    ): ?><section class="panel form compact"><h2>Enviar documento</h2><form method="post" enctype="multipart/form-data"><input type="hidden" name="action" value="upload"><?= csrf_field() ?><input type="hidden" name="trabalho_id" value="<?= $work[
    "id"
] ?>"><div class="form-grid"><label>Tipo<select name="tipo"><?php if (
    $u["perfil"] === "aluno"
): ?><option value="versao_final">Versão definitiva</option><?php else: ?><option value="ata">Ata de defesa</option><option value="autorizacao_ric">Autorização RIC-CPS</option><option value="outro">Outro</option><?php endif; ?></select></label><label>Arquivo PDF<input required type="file" name="arquivo" accept="application/pdf"></label></div><button class="btn primary">Enviar arquivo</button></form></section><?php endif; ?>
    <?php
    if (
        in_array($u["perfil"], ["orientador", "administrador"], true)
    ): ?><section class="panel form compact"><h2>Formalizar correções</h2><form method="post"><input type="hidden" name="action" value="correction"><?= csrf_field() ?><input type="hidden" name="trabalho_id" value="<?= $work[
    "id"
] ?>"><input type="hidden" name="banca_id" value="<?= $bancas[0]["id"] ??
    0 ?>"><div class="form-grid"><label>Data-base<input required type="date" name="data_liberacao" value="<?= date(
    "Y-m-d",
) ?>"></label><label class="span-2">Correções indicadas<textarea required name="descricao" rows="2"></textarea></label></div><button class="btn primary">Definir prazo de 15 dias</button></form></section><?php foreach (
    $versions
    as $v
):
    if (
        in_array($v["status"], ["enviada", "em_analise"], true)
    ): ?><section class="panel form compact"><h2>Avaliar versão <?= $v[
    "numero_versao"
] ?></h2><form method="post"><input type="hidden" name="action" value="review_version"><?= csrf_field() ?><input type="hidden" name="versao_id" value="<?= $v[
    "id"
] ?>"><label>Observação<textarea name="observacao" rows="2"></textarea></label><button class="btn primary" name="decision" value="approve">Aprovar versão</button><button class="btn warning" name="decision" value="adjust">Solicitar ajustes</button></form></section><?php endif;
endforeach;endif;
    layout_end();
    exit();

}

if ($currentPage === "banca") {

    $b = one(
        "SELECT b.*,t.titulo,t.id trabalho_id,t.orientador_id FROM bancas b JOIN trabalhos t ON t.id=b.trabalho_id WHERE b.id=?",
        [(int) ($_GET["id"] ?? 0)],
    );
    if (
        !$b ||
        !can_access_work([
            "id" => $b["trabalho_id"],
            "orientador_id" => $b["orientador_id"],
        ])
    ) {
        http_response_code(404);
        exit("Banca não encontrada.");
    }
    $evals = rows(
        "SELECT ba.*,u.nome,u.email,c.status convite_status FROM banca_avaliadores ba JOIN usuarios u ON u.id=ba.avaliador_id LEFT JOIN convites c ON c.banca_avaliador_id=ba.id WHERE ba.banca_id=? ORDER BY c.created_at DESC",
        [$b["id"]],
    );
    layout_start("Detalhes da banca");
    ?>
    <div class="work-head"><div><span class="eyebrow"><?= date_br(
        $b["data_banca"],
    ) ?> · <?= e(substr($b["hora_inicio"], 0, 5)) ?>–<?= e(
    substr($b["hora_fim"], 0, 5),
) ?></span><h2><?= e($b["titulo"]) ?></h2><p><?= e(
    ucfirst($b["modalidade"]),
) ?> · <?= e($b["local"]) ?></p><span class="badge <?= status_class(
    $b["status"],
) ?>"><?= e(
    status_label($b["status"]),
) ?></span></div></div><section class="panel"><div class="panel-title"><h2>Avaliadores e convites</h2></div><?php foreach (
    $evals
    as $ev
): ?><div class="event"><div class="avatar"><?= e(
    mb_strtoupper(mb_substr($ev["nome"], 0, 1)),
) ?></div><div><strong><?= e($ev["nome"]) ?></strong><span><?= e(
    $ev["email"],
) ?></span></div><span class="badge <?= $ev["status_resposta"] === "aceito"
    ? "success"
    : ($ev["status_resposta"] === "recusado"
        ? "warning"
        : "secondary") ?>"><?= e(
    ucfirst($ev["status_resposta"]),
) ?></span></div><?php endforeach; ?></section><?php
if (
    in_array($u["perfil"], ["orientador", "administrador"], true) &&
    $b["status"] === "CONFIRMADA"
): ?><section class="panel form compact"><h2>Registrar realização</h2><form method="post"><input type="hidden" name="action" value="defense"><?= csrf_field() ?><input type="hidden" name="banca_id" value="<?= $b[
    "id"
] ?>"><p>Esta ação registra a defesa como realizada e inicia a etapa documental.</p><button class="btn primary">Marcar como realizada</button></form></section><?php endif;
layout_end();
exit();

}

if ($currentPage === "public_search") {

    $q = trim($_GET["q"] ?? "");
    $course = (int) ($_GET["curso"] ?? 0);
    $params = [];
    $where = ["t.publico=1"];
    if ($q !== "") {
        $where[] =
            "(t.titulo LIKE ? OR u.nome LIKE ? OR EXISTS(SELECT 1 FROM alunos_trabalhos at JOIN usuarios al ON al.id=at.aluno_id WHERE at.trabalho_id=t.id AND al.nome LIKE ?))";
        $params[] = "%{$q}%";
        $params[] = "%{$q}%";
        $params[] = "%{$q}%";
    }
    if ($course) {
        $where[] = "t.curso_id=?";
        $params[] = $course;
    }
    $results = rows(
        "SELECT t.*,c.nome curso,u.nome orientador FROM trabalhos t JOIN cursos c ON c.id=t.curso_id JOIN usuarios u ON u.id=t.orientador_id WHERE " .
            implode(" AND ", $where) .
            " ORDER BY t.updated_at DESC LIMIT 30",
        $params,
    );
    $courses = rows("SELECT * FROM cursos WHERE ativo=1 ORDER BY nome");
    layout_start("Consulta pública");
    ?>
    <section class="search-hero"><span class="eyebrow">ACERVO ACADÊMICO</span><h2>Encontre trabalhos e bancas</h2><p>Consulte informações autorizadas para divulgação pela instituição.</p><form method="get"><input type="hidden" name="page" value="public_search"><input name="q" value="<?= e(
        $q,
    ) ?>" placeholder="Título, aluno ou orientador"><select name="curso"><option value="">Todos os cursos</option><?php foreach (
    $courses
    as $c
): ?><option value="<?= $c["id"] ?>" <?= $course === $c["id"]
    ? "selected"
    : "" ?>><?= e(
    $c["nome"],
) ?></option><?php endforeach; ?></select><button class="btn primary">Pesquisar</button></form></section><section class="result-grid"><?php
foreach (
    $results
    as $r
): ?><article class="result-card"><span class="badge <?= status_class(
    $r["status"],
) ?>"><?= e(status_label($r["status"])) ?></span><h3><?= e(
    $r["titulo"],
) ?></h3><p><?= e($r["resumo"]) ?></p><footer><?= e($r["curso"]) ?> · <?= e(
     $r["ano_letivo"],
 ) ?>/<?= e($r["semestre_letivo"]) ?><br>Orientação: <?= e(
    $r["orientador"],
) ?></footer></article><?php endforeach;
if (
    !$results
): ?><div class="empty">Nenhum trabalho encontrado com estes filtros.</div><?php endif;
?></section><?php
layout_end();
exit();

}
if ($currentPage === "admin") {

    require_role(["administrador"]);
    $courses = rows("SELECT * FROM cursos ORDER BY nome");
    $users = rows(
        "SELECT nome,email,perfil,ativo FROM usuarios ORDER BY created_at DESC LIMIT 12",
    );
    layout_start("Administração");
    ?>
    <section class="grid-2"><article class="panel form compact"><h2>Novo curso</h2><form method="post"><input type="hidden" name="action" value="add_course"><?= csrf_field() ?><label>Nome<input required name="nome"></label><label>Sigla<input required maxlength="20" name="sigla"></label><button class="btn primary">Cadastrar curso</button></form></article><article class="panel"><h2>Controles institucionais</h2><p>Os registros de alterações, cursos e fluxos ficam protegidos por perfil administrativo.</p><a href="?page=public_search">Abrir consulta pública</a></article></section><section class="panel table-wrap"><div class="panel-title"><h2>Usuários recentes</h2></div><table><thead><tr><th>Nome</th><th>E-mail</th><th>Perfil</th><th>Estado</th></tr></thead><tbody><?php foreach (
    $users
    as $person
): ?><tr><td><?= e($person["nome"]) ?></td><td><?= e(
    $person["email"],
) ?></td><td><?= e(ucfirst($person["perfil"])) ?></td><td><?= $person["ativo"]
    ? "Ativo"
    : "Inativo" ?></td></tr><?php endforeach; ?></tbody></table></section><?php
layout_end();
exit();

}
if ($currentPage === "invitations") {

    require_role(["avaliador"]);
    $items = rows(
        "SELECT b.*,t.titulo,ba.status_resposta FROM banca_avaliadores ba JOIN bancas b ON b.id=ba.banca_id JOIN trabalhos t ON t.id=b.trabalho_id WHERE ba.avaliador_id=? ORDER BY b.data_banca DESC",
        [$u["id"]],
    );
    layout_start("Meus convites");
    ?><section class="panel timeline"><?php
foreach ($items as $b): ?><div class="event"><time><b><?= date(
    "d",
    strtotime($b["data_banca"]),
) ?></b><small><?= strtoupper(
    date("M", strtotime($b["data_banca"])),
) ?></small></time><div><strong><?= e($b["titulo"]) ?></strong><span><?= e(
    ucfirst($b["modalidade"]),
) ?> · <?= e($b["status_resposta"]) ?></span></div></div><?php endforeach;
if (!$items): ?><div class="empty">Nenhum convite recebido.</div><?php endif;
?></section><?php
layout_end();
exit();

}

http_response_code(404);
layout_start("Página não encontrada");
?><div class="empty large">A página solicitada não existe.</div><?php layout_end();
