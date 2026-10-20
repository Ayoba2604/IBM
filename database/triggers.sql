USE sig_banca;
DELIMITER $$

DROP TRIGGER IF EXISTS trg_usuario_bu$$
CREATE TRIGGER trg_usuario_bu BEFORE UPDATE ON usuarios FOR EACH ROW
BEGIN
    IF NEW.perfil <> OLD.perfil AND EXISTS (
        SELECT 1 FROM trabalhos WHERE orientador_id = OLD.id
    ) AND NEW.perfil NOT IN ('orientador','administrador') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O perfil do usuário não pode mudar enquanto ele orienta trabalhos.';
    END IF;
    IF NEW.perfil <> OLD.perfil AND EXISTS (
        SELECT 1 FROM alunos_trabalhos WHERE aluno_id = OLD.id
    ) AND NEW.perfil <> 'aluno' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O perfil do usuário não pode mudar enquanto ele está vinculado como aluno.';
    END IF;
    IF NEW.perfil <> OLD.perfil AND EXISTS (
        SELECT 1 FROM banca_avaliadores WHERE avaliador_id = OLD.id
    ) AND NEW.perfil <> 'avaliador' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O perfil do usuário não pode mudar enquanto ele está vinculado como avaliador.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_trabalho_bi$$
CREATE TRIGGER trg_trabalho_bi BEFORE INSERT ON trabalhos FOR EACH ROW
BEGIN
    DECLARE v_perfil VARCHAR(30);
    SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.orientador_id AND ativo = 1;
    IF v_perfil IS NULL OR v_perfil NOT IN ('orientador','administrador') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O responsável pelo trabalho precisa ser orientador ativo.';
    END IF;
    IF CHAR_LENGTH(TRIM(NEW.titulo)) < 5 OR NEW.ano_letivo NOT BETWEEN 2000 AND 2200 OR NEW.semestre_letivo NOT IN (1,2) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Título ou período letivo inválido.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_trabalho_bu$$
CREATE TRIGGER trg_trabalho_bu BEFORE UPDATE ON trabalhos FOR EACH ROW
BEGIN
    DECLARE v_perfil VARCHAR(30);
    SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.orientador_id AND ativo = 1;
    IF v_perfil IS NULL OR v_perfil NOT IN ('orientador','administrador') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O responsável pelo trabalho precisa ser orientador ativo.';
    END IF;
    IF CHAR_LENGTH(TRIM(NEW.titulo)) < 5 OR NEW.ano_letivo NOT BETWEEN 2000 AND 2200 OR NEW.semestre_letivo NOT IN (1,2) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Título ou período letivo inválido.';
    END IF;
    IF NEW.status <> OLD.status AND NOT (
        (OLD.status = 'RASCUNHO' AND NEW.status = 'AGUARDANDO_CONFIRMACOES') OR
        (OLD.status = 'AGUARDANDO_CONFIRMACOES' AND NEW.status = 'CONFIRMADA') OR
        (OLD.status = 'CONFIRMADA' AND NEW.status IN ('REALIZADA','AGUARDANDO_DOCUMENTOS')) OR
        (OLD.status = 'REALIZADA' AND NEW.status = 'AGUARDANDO_DOCUMENTOS') OR
        (OLD.status = 'AGUARDANDO_DOCUMENTOS' AND NEW.status IN ('AGUARDANDO_CORRECOES','AGUARDANDO_VERSAO_FINAL')) OR
        (OLD.status = 'AGUARDANDO_CORRECOES' AND NEW.status = 'AGUARDANDO_VERSAO_FINAL') OR
        (OLD.status = 'AGUARDANDO_VERSAO_FINAL' AND NEW.status = 'AGUARDANDO_APROVACAO') OR
        (OLD.status = 'AJUSTES_SOLICITADOS' AND NEW.status = 'AGUARDANDO_APROVACAO') OR
        (OLD.status = 'AGUARDANDO_APROVACAO' AND NEW.status IN ('AJUSTES_SOLICITADOS','CONCLUIDA'))
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transição de estado do trabalho não permitida.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_trabalho_au$$
CREATE TRIGGER trg_trabalho_au AFTER UPDATE ON trabalhos FOR EACH ROW
BEGIN
    IF NEW.status <> OLD.status THEN
        INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, detalhes)
        VALUES (@sig_usuario_id, 'mudanca_status', 'trabalho', NEW.id,
                JSON_OBJECT('anterior', OLD.status, 'novo', NEW.status));
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_aluno_trabalho_bi$$
CREATE TRIGGER trg_aluno_trabalho_bi BEFORE INSERT ON alunos_trabalhos FOR EACH ROW
BEGIN
    DECLARE v_perfil VARCHAR(30);
    SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.aluno_id AND ativo = 1;
    IF v_perfil IS NULL OR v_perfil <> 'aluno' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O vínculo exige um usuário ativo com perfil de aluno.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_banca_bi$$
CREATE TRIGGER trg_banca_bi BEFORE INSERT ON bancas FOR EACH ROW
BEGIN
    DECLARE v_perfil VARCHAR(30);
    SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.criado_por AND ativo = 1;
    IF v_perfil IS NULL OR v_perfil NOT IN ('orientador','administrador') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A banca deve ser criada por orientador ou administrador ativo.';
    END IF;
    IF NEW.hora_fim <= NEW.hora_inicio THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O horário final deve ser posterior ao horário inicial.';
    END IF;
    IF NEW.modalidade IN ('presencial','hibrida') AND NULLIF(TRIM(NEW.local),'') IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Informe local ou sala para banca presencial/híbrida.';
    END IF;
    IF NEW.modalidade IN ('online','hibrida') AND NULLIF(TRIM(NEW.link_online),'') IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Informe link para banca online/híbrida.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_banca_bu$$
CREATE TRIGGER trg_banca_bu BEFORE UPDATE ON bancas FOR EACH ROW
BEGIN
    IF NEW.hora_fim <= NEW.hora_inicio THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O horário final deve ser posterior ao horário inicial.';
    END IF;
    IF NEW.modalidade IN ('presencial','hibrida') AND NULLIF(TRIM(NEW.local),'') IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Informe local ou sala para banca presencial/híbrida.';
    END IF;
    IF NEW.modalidade IN ('online','hibrida') AND NULLIF(TRIM(NEW.link_online),'') IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Informe link para banca online/híbrida.';
    END IF;
    IF NEW.status <> OLD.status AND NOT (
        (OLD.status = 'AGUARDANDO_CONFIRMACOES' AND NEW.status = 'CONFIRMADA') OR
        (OLD.status = 'CONFIRMADA' AND NEW.status = 'REALIZADA') OR
        (OLD.status = 'REALIZADA' AND NEW.status IN ('AGUARDANDO_DOCUMENTOS','AGUARDANDO_CORRECOES')) OR
        (OLD.status = 'AGUARDANDO_DOCUMENTOS' AND NEW.status IN ('AGUARDANDO_CORRECOES','AGUARDANDO_VERSAO_FINAL')) OR
        (OLD.status = 'AGUARDANDO_CORRECOES' AND NEW.status = 'AGUARDANDO_VERSAO_FINAL') OR
        (OLD.status = 'AGUARDANDO_VERSAO_FINAL' AND NEW.status = 'AGUARDANDO_APROVACAO') OR
        (OLD.status = 'AJUSTES_SOLICITADOS' AND NEW.status = 'AGUARDANDO_APROVACAO') OR
        (OLD.status = 'AGUARDANDO_APROVACAO' AND NEW.status IN ('AJUSTES_SOLICITADOS','CONCLUIDA'))
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transição de estado da banca não permitida.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_banca_au$$
CREATE TRIGGER trg_banca_au AFTER UPDATE ON bancas FOR EACH ROW
BEGIN
    IF NEW.status <> OLD.status THEN
        INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, detalhes)
        VALUES (@sig_usuario_id, 'mudanca_status', 'banca', NEW.id,
                JSON_OBJECT('anterior', OLD.status, 'novo', NEW.status));
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_banca_avaliador_bi$$
CREATE TRIGGER trg_banca_avaliador_bi BEFORE INSERT ON banca_avaliadores FOR EACH ROW
BEGIN
    DECLARE v_perfil VARCHAR(30);
    SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.avaliador_id AND ativo = 1;
    IF v_perfil IS NULL OR v_perfil <> 'avaliador' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A banca exige usuário ativo com perfil de avaliador.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_documento_bi$$
CREATE TRIGGER trg_documento_bi BEFORE INSERT ON documentos FOR EACH ROW
BEGIN
    DECLARE v_trabalho_id INT UNSIGNED;
    IF NEW.banca_id IS NOT NULL THEN
    SELECT MAX(trabalho_id) INTO v_trabalho_id FROM bancas WHERE id = NEW.banca_id;
        IF v_trabalho_id IS NULL OR v_trabalho_id <> NEW.trabalho_id THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O documento e a banca devem pertencer ao mesmo trabalho.';
        END IF;
    END IF;
    IF NEW.tamanho_bytes = 0 OR CHAR_LENGTH(TRIM(NEW.nome_original)) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nome e tamanho do documento são obrigatórios.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_versao_bi$$
CREATE TRIGGER trg_versao_bi BEFORE INSERT ON versoes FOR EACH ROW
BEGIN
    DECLARE v_trabalho_id INT UNSIGNED;
    DECLARE v_tipo VARCHAR(30);
    SELECT MAX(trabalho_id), MAX(tipo) INTO v_trabalho_id, v_tipo FROM documentos WHERE id = NEW.documento_id;
    IF v_trabalho_id IS NULL OR v_trabalho_id <> NEW.trabalho_id OR v_tipo <> 'versao_final' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A versão deve apontar para documento final do mesmo trabalho.';
    END IF;
    IF NEW.numero_versao = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O número da versão deve começar em 1.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_versao_bu$$
CREATE TRIGGER trg_versao_bu BEFORE UPDATE ON versoes FOR EACH ROW
BEGIN
    DECLARE v_perfil VARCHAR(30);
    IF NEW.status <> OLD.status AND NOT (
        (OLD.status = 'enviada' AND NEW.status = 'em_analise') OR
        (OLD.status = 'em_analise' AND NEW.status IN ('aprovada','ajustes_solicitados'))
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transição de estado da versão não permitida.';
    END IF;
    IF NEW.status IN ('aprovada','ajustes_solicitados') THEN
        SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.avaliado_por AND ativo = 1;
        IF v_perfil IS NULL OR v_perfil NOT IN ('orientador','administrador') OR NEW.avaliado_em IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A avaliação exige orientador ativo e data de avaliação.';
        END IF;
        IF NEW.status = 'ajustes_solicitados' AND CHAR_LENGTH(TRIM(COALESCE(NEW.observacao_orientador,''))) = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Registre o motivo dos ajustes solicitados.';
        END IF;
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_versao_au$$
CREATE TRIGGER trg_versao_au AFTER UPDATE ON versoes FOR EACH ROW
BEGIN
    IF NEW.status <> OLD.status THEN
        INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, detalhes)
        VALUES (@sig_usuario_id, 'mudanca_status', 'versao', NEW.id,
                JSON_OBJECT('anterior', OLD.status, 'novo', NEW.status));
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_documento_bu$$
CREATE TRIGGER trg_documento_bu BEFORE UPDATE ON documentos FOR EACH ROW
BEGIN
    DECLARE v_perfil VARCHAR(30);
    IF NEW.status_validacao <> OLD.status_validacao AND NOT (
        (OLD.status_validacao = 'pendente' AND NEW.status_validacao IN ('aprovado','correcao')) OR
        (OLD.status_validacao = 'correcao' AND NEW.status_validacao = 'aprovado')
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Transição de validação do documento não permitida.';
    END IF;
    IF NEW.status_validacao <> 'pendente' THEN
        SELECT MAX(perfil) INTO v_perfil FROM usuarios WHERE id = NEW.validado_por AND ativo = 1;
        IF v_perfil IS NULL OR v_perfil NOT IN ('orientador','administrador') OR NEW.validado_em IS NULL THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'A validação exige orientador ativo e data de validação.';
        END IF;
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_documento_au$$
CREATE TRIGGER trg_documento_au AFTER UPDATE ON documentos FOR EACH ROW
BEGIN
    IF NEW.status_validacao <> OLD.status_validacao THEN
        INSERT INTO auditoria (usuario_id, acao, entidade, entidade_id, detalhes)
        VALUES (@sig_usuario_id, 'validacao_documento', 'documento', NEW.id,
                JSON_OBJECT('anterior', OLD.status_validacao, 'novo', NEW.status_validacao));
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_correcao_bi$$
CREATE TRIGGER trg_correcao_bi BEFORE INSERT ON correcoes FOR EACH ROW
BEGIN
    IF NEW.prazo_final <> DATE_ADD(NEW.data_liberacao, INTERVAL 15 DAY) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O prazo inicial deve ser de 15 dias corridos após a data-base.';
    END IF;
END$$

DROP TRIGGER IF EXISTS trg_auditoria_bu$$
CREATE TRIGGER trg_auditoria_bu BEFORE UPDATE ON auditoria FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O registro de auditoria é imutável.';
END$$

DROP TRIGGER IF EXISTS trg_auditoria_bd$$
CREATE TRIGGER trg_auditoria_bd BEFORE DELETE ON auditoria FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O registro de auditoria não pode ser removido.';
END$$

DELIMITER ;
