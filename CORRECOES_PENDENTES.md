# Revisão técnica — SIG-Banca

Revisão concluída em 20/10/2026. Os itens abaixo foram identificados na varredura e corrigidos nesta rodada. Não há pendências de código conhecidas; restam apenas etapas operacionais de implantação.

## Correções aplicadas

### URL, sessão e ambiente

- A URL base era fixa em `http://localhost/IBM/SIG/public`, endereço inexistente, e todos os redirecionamentos quebravam após o login. Agora é detectada a partir da requisição, com `APP_URL` opcional e validação do cabeçalho `Host`.
- O carregador de `.env` não sobrescreve variáveis já definidas, aceita aspas, ignora comentários e valida nomes de chave.
- Sessão com `use_strict_mode`, cookie `HttpOnly`/`SameSite=Lax`, expiração explícita, rotação de ID no login e de CSRF.
- `PDO` define timezone da conexão e `@sig_usuario_id` para os triggers de auditoria.
- Headers de segurança (`nosniff`, frame, referrer, permissions e CSP) e cache desativado para áreas autenticadas.

### Regras de negócio e controlador

- `create_work`: validação de título, curso, ano, semestre, orientador ativo e vínculo real de alunos.
- `create_banca`: validação de data/hora, modalidade, local/link, avaliadores ativos e conflito de agenda; convites e notificações em transação; envio de e-mails após o commit.
- `respond_invite`: proteção contra resposta duplicada/vencida, verificação de titularidade, atualização consistente de convite e vínculo e confirmação automática da banca.
- `correction`: removido o trecho morto que comparava o status antigo da banca; o avanço agora percorre as transições permitidas pelos triggers.
- `upload`: validação de PDF por MIME e assinatura, lock do trabalho na transação, versionamento e limpeza do arquivo em caso de erro.
- `validate_document`: nova ação para aprovar documento ou solicitar ajuste, com observação e notificação ao autor.
- `toggle_public`: nova ação para publicar/remover o trabalho da consulta pública.
- `review_version`: atualização condicional, registro de avaliador/data e notificações.
- `defense`: atualização condicional com verificação de concorrência.
- Erros de validação não geram mais stack trace no log; falhas de banco continuam registradas e recebem mensagem genérica.
- CSRF inválido passou de 419 para **400**: o Apache convertia o código não padrão em HTTP 500.

### Permissões e dados

- Avaliadores não conseguiam abrir trabalhos e bancas em que atuavam. O escopo de acesso agora considera vínculos de `banca_avaliadores` e `alunos_trabalhos`.
- Listagens e painel filtram trabalhos/bancas pelo perfil autenticado.
- `can_access_work` centraliza a checagem de administrador, orientador, aluno e avaliador.
- Corrigido `validate_document` usando o ID do trabalho, e não do documento, na checagem de acesso.

### Interface

- Menu lateral móvel inexistente: adicionado botão, overlay, fechamento por ESC, foco e `aria-expanded`.
- Layout reescrito com sidebar fixa, topbar, cartões, indicadores, linha do tempo, stepper de 9 etapas, estados vazios e rodapé.
- Nova tela de calendário mensal navegável.
- Tabelas viram cartões no mobile, com rótulos por célula.
- Página de convites sem duplicação, com aceite/recusa e estado do convite.
- Central de notificações e painel no cabeçalho.
- Formulários com campos dependentes de modalidade, filtro de alunos/avaliadores, confirmação de ações e exibição de senha.
- Design system completo (`style.css`), tipografia DM Sans + Fraunces, paleta institucional, foco visível, `prefers-reduced-motion` e estilos de impressão.
- Corrigidos acentos e encoding dos arquivos PHP (UTF-8 sem BOM).

### Infraestrutura

- `.htaccess` da raiz bloqueia `.env`, `app/`, `database/` e `storage/` quando o projeto inteiro fica exposto no `htdocs`.
- `.htaccess` público com `DirectoryIndex`, headers e rewrite.
- `.env.example` corrigido; `storage/logs` adicionado ao `.gitignore`.
- README atualizado com instalação, estrutura, regras e validação.

## Testes executados

- `php -l` em todos os arquivos PHP: sem erros.
- Suíte funcional em banco descartável `sig_banca_test` criado a partir do banco real: **47 verificações HTTP aprovadas** e **6 verificações diretas de escopo de acesso**, incluindo:
  - login e redirecionamento correto;
  - criação de trabalho e vínculo de dois alunos;
  - agendamento de banca e convite com token hasheado;
  - aceite pelo painel e por link sem login;
  - confirmação automática de banca e trabalho;
  - realização da defesa;
  - formalização de correções e prazo de 15 dias;
  - upload de PDF, download autenticado e validação;
  - aprovação da versão e conclusão;
  - notificações;
  - CSRF inválido, perfil sem permissão, data inválida e curso duplicado.
- Renderização de todas as telas nos quatro perfis sem warnings/notices.
- Inspeção visual em 1440 px e 390 px, incluindo menu móvel e tabelas em cartões.

## Pendências operacionais (fora do código)

1. Configurar o DocumentRoot para `public/` em produção.
2. Criar o `.env` de produção com credenciais próprias e `APP_DEBUG=false`.
3. Substituir o transporte de e-mail local (`storage/mail-outbox`) por SMTP real.
4. Garantir permissão de escrita em `storage/uploads`, `storage/mail-outbox` e `storage/logs`.
5. Revisar as contas e cursos de demonstração antes de publicar.
