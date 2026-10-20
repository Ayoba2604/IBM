USE sig_banca;
INSERT INTO cursos (nome,sigla) VALUES ('Análise e Desenvolvimento de Sistemas','ADS'),('Gestão Empresarial','GE');
-- Todas as contas de demonstração usam a senha: password
INSERT INTO usuarios (nome,email,senha_hash,perfil,matricula,curso_id) VALUES
('Ana Coordenadora','coord@fatec.sp.gov.br','$2y$10$13mYwMpPdr7EsiZmTnhTgOPT2ryRc72Cyf7830YKmYgyxpTTW6ktW','administrador','ADM001',1),
('Prof. Ricardo Mendes','ricardo@fatec.sp.gov.br','$2y$10$13mYwMpPdr7EsiZmTnhTgOPT2ryRc72Cyf7830YKmYgyxpTTW6ktW','orientador','DOC001',1),
('Prof. Marina Souza','marina@fatec.sp.gov.br','$2y$10$13mYwMpPdr7EsiZmTnhTgOPT2ryRc72Cyf7830YKmYgyxpTTW6ktW','avaliador','DOC002',1),
('Lucas Almeida','lucas@fatec.sp.gov.br','$2y$10$13mYwMpPdr7EsiZmTnhTgOPT2ryRc72Cyf7830YKmYgyxpTTW6ktW','aluno','2024001',1),
('Beatriz Costa','beatriz@fatec.sp.gov.br','$2y$10$13mYwMpPdr7EsiZmTnhTgOPT2ryRc72Cyf7830YKmYgyxpTTW6ktW','aluno','2024002',1);
INSERT INTO trabalhos (titulo,resumo,curso_id,orientador_id,ano_letivo,semestre_letivo,status,publico) VALUES ('Plataforma de Gestão de Bancas','Sistema para organizar etapas de TCC.',1,2,2026,2,'CONFIRMADA',1);
INSERT INTO alunos_trabalhos (trabalho_id,aluno_id) VALUES (1,4),(1,5);
INSERT INTO bancas (trabalho_id,data_banca,hora_inicio,hora_fim,modalidade,local,link_online,status,criado_por) VALUES (1,CURDATE() + INTERVAL 10 DAY,'19:00','20:00','hibrida','Sala 12','https://meet.google.com/exemplo','CONFIRMADA',2);
INSERT INTO banca_avaliadores (banca_id,avaliador_id,funcao,status_resposta,respondido_em) VALUES (1,3,'Avaliadora','aceito',NOW());
