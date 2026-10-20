USE sig_banca;

-- Align existing installations with the constrained schema in schema.sql.
ALTER TABLE trabalhos
    MODIFY status ENUM(
        'RASCUNHO', 'AGUARDANDO_CONFIRMACOES', 'CONFIRMADA', 'REALIZADA',
        'AGUARDANDO_DOCUMENTOS', 'AGUARDANDO_CORRECOES', 'AGUARDANDO_VERSAO_FINAL',
        'AGUARDANDO_APROVACAO', 'AJUSTES_SOLICITADOS', 'CONCLUIDA'
    ) NOT NULL DEFAULT 'RASCUNHO',
    ADD CONSTRAINT chk_trabalho_titulo CHECK (CHAR_LENGTH(TRIM(titulo)) >= 5),
    ADD CONSTRAINT chk_trabalho_ano CHECK (ano_letivo BETWEEN 2000 AND 2200),
    ADD CONSTRAINT chk_trabalho_semestre CHECK (semestre_letivo IN (1, 2)),
    ADD INDEX idx_trabalho_orientador_status (orientador_id, status);

ALTER TABLE bancas
    MODIFY status ENUM(
        'AGUARDANDO_CONFIRMACOES', 'CONFIRMADA', 'REALIZADA', 'AGUARDANDO_DOCUMENTOS',
        'AGUARDANDO_CORRECOES', 'AGUARDANDO_VERSAO_FINAL', 'AGUARDANDO_APROVACAO',
        'AJUSTES_SOLICITADOS', 'CONCLUIDA'
    ) NOT NULL DEFAULT 'AGUARDANDO_CONFIRMACOES',
    ADD CONSTRAINT chk_banca_horario CHECK (hora_fim > hora_inicio),
    ADD CONSTRAINT chk_banca_local CHECK (modalidade NOT IN ('presencial','hibrida') OR NULLIF(TRIM(local),'') IS NOT NULL),
    ADD CONSTRAINT chk_banca_link CHECK (modalidade NOT IN ('online','hibrida') OR NULLIF(TRIM(link_online),'') IS NOT NULL),
    ADD INDEX idx_banca_trabalho_data (trabalho_id, data_banca);

ALTER TABLE correcoes
    ADD CONSTRAINT chk_cor_prazo CHECK (prazo_final = DATE_ADD(data_liberacao, INTERVAL 15 DAY)),
    ADD CONSTRAINT chk_cor_descricao CHECK (CHAR_LENGTH(TRIM(descricao)) > 0);

ALTER TABLE documentos
    ADD CONSTRAINT chk_doc_tamanho CHECK (tamanho_bytes > 0),
    ADD CONSTRAINT chk_doc_nome CHECK (CHAR_LENGTH(TRIM(nome_original)) > 0);

ALTER TABLE cursos
    ADD CONSTRAINT chk_curso_nome CHECK (CHAR_LENGTH(TRIM(nome)) >= 2),
    ADD CONSTRAINT chk_curso_sigla CHECK (CHAR_LENGTH(TRIM(sigla)) BETWEEN 2 AND 20);

ALTER TABLE usuarios
    ADD CONSTRAINT chk_usuario_nome CHECK (CHAR_LENGTH(TRIM(nome)) >= 2),
    ADD CONSTRAINT chk_usuario_email CHECK (email LIKE '%_@_%._%');

ALTER TABLE alunos_trabalhos
    ADD INDEX idx_at_aluno (aluno_id, trabalho_id);

ALTER TABLE banca_avaliadores
    ADD INDEX idx_ba_avaliador_status (avaliador_id, status_resposta);
