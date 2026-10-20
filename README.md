# SIG-Banca

Sistema web para acompanhar bancas de TCC da Fatec Bauru, do agendamento à versão final. Feito em PHP 8, MySQL/MariaDB, HTML, CSS e JavaScript puro — sem frameworks de front-end.

## Requisitos

- PHP 8.1 ou superior com `pdo_mysql`, `mbstring`, `fileinfo` e `openssl`
- MySQL 8 ou MariaDB 10.4+
- Apache com `mod_rewrite` (XAMPP/WAMP funcionam)
- Navegador moderno

## Como executar no XAMPP

1. Coloque o projeto em `C:\xampp\htdocs\IBM`.
2. Importe [`database/schema.sql`](database/schema.sql) no phpMyAdmin.
3. Importe [`database/triggers.sql`](database/triggers.sql) para ativar validações e auditoria.
4. Importe [`database/seed.sql`](database/seed.sql) para carregar os dados de demonstração.
5. Abra **http://localhost/IBM/public**.
6. (Opcional) Copie `.env.example` para `.env` e ajuste banco, URL ou limites. Sem `.env`, o sistema usa `root` sem senha e detecta a URL automaticamente.

> Para atualizar uma instalação existente, importe uma vez [`database/migrations/001_integrity_mysql.sql`](database/migrations/001_integrity_mysql.sql) e depois [`database/triggers.sql`](database/triggers.sql). Não reimporte o seed sobre um banco que já tem os dados demo.

O ideal é apontar o DocumentRoot para a pasta `public/`. Se o projeto inteiro ficar exposto em `htdocs`, o [`.htaccess`](.htaccess) da raiz bloqueia `app/`, `database/`, `storage/` e `.env`.

## Contas demonstrativas

Todas usam a senha `password`:

| Perfil | E-mail |
| --- | --- |
| Coordenação | coord@fatec.sp.gov.br |
| Orientador | ricardo@fatec.sp.gov.br |
| Avaliador | marina@fatec.sp.gov.br |
| Aluno | lucas@fatec.sp.gov.br |

## Funcionalidades

- **Autenticação e perfis:** login com `password_verify`, sessão regenerada, bloqueio temporário após tentativas inválidas, CSRF em todas as ações e escopo de acesso por perfil.
- **Painel:** indicadores, próximas bancas, prazos em aberto e trabalhos recentes de acordo com o perfil.
- **Trabalhos:** cadastro com vínculo de alunos, catálogo público/privado, linha do tempo do fluxo e detalhes de equipe.
- **Bancas:** agendamento com validação de data/hora, modalidade, local/link e conflito de agenda de avaliadores e orientador.
- **Convites:** token de 64 caracteres armazenado apenas como hash, expiração de 14 dias, aceite/recusa por link ou pelo painel, notificações e confirmação automática da banca. Convites vencidos podem ser reenviados (novo prazo de 14 dias) e avaliadores podem ser substituídos sem recriar a banca.
- **Defesa e correções:** registro de realização, sincronização do status da banca com o trabalho, formalização das correções com prazo automático de 15 dias corridos e etapa de correções aberta até o envio da versão definitiva.
- **Documentos:** upload de PDF validado por MIME e assinatura, armazenamento fora de `public/`, download autenticado e validação pelo orientador.
- **Versões finais:** versionamento, avaliação com aprovação ou solicitação de ajustes e conclusão do trabalho.
- **Calendário:** visão mensal das bancas com navegação entre meses.
- **Consulta pública:** busca por título, aluno ou orientador, filtrando apenas trabalhos publicados.
- **Notificações:** central de avisos, marcação de leitura e painel no cabeçalho.
- **Administração:** cursos, usuários recentes e trilha de auditoria.
- **Interface:** responsiva, acessível, com menu móvel, tabelas em cartões, estados de foco e suporte a `prefers-reduced-motion`.

## Regras garantidas no banco

- `CHECK` para períodos, horários, modalidade, documentos e prazo de 15 dias corridos.
- Triggers validam perfis vinculados, impedem transições inválidas de estado e registram mudanças na auditoria.
- Auditoria é imutável: triggers bloqueiam `UPDATE` e `DELETE`.
- `@sig_usuario_id` é definido por conexão para atribuir eventos ao usuário autenticado.
- Documentos e versões só são aceitos quando pertencem ao trabalho e à banca corretos.

## Estrutura

```
app/
  bootstrap.php          carregamento de .env, sessão, headers e serviços
  config/app.php         configuração e detecção da URL base
  config/database.php    conexão PDO compartilhada
  helpers/functions.php  utilidades, permissões, CSRF, status e formatação
  services/              token, prazo, upload e e-mail
database/
  schema.sql             estrutura completa
  triggers.sql           validações e auditoria
  seed.sql               dados de demonstração
  migrations/            atualização de bancos existentes
public/
  index.php              controlador, ações e layout
  pages.php              telas
  assets/css/style.css   design system
  assets/js/app.js       interações
storage/
  uploads/               PDFs fora da pasta pública
  mail-outbox/           e-mails de desenvolvimento
  logs/                  log da aplicação
```

## E-mail

Em desenvolvimento, convites são gravados em `storage/mail-outbox` (não versionado). Para envio real, substitua a implementação de [`MailService`](app/services/MailService.php) por PHPMailer/SMTP usando as variáveis `SMTP_*`.

## Validação executada

A revisão foi validada com:

- `php -l` em todos os arquivos PHP (PHP 8.2.12).
- Teste funcional ponta a ponta em banco descartável, cobrindo 47 verificações da suíte HTTP e 6 verificações diretas de escopo de acesso: login, criação de trabalho, agendamento, convite por painel e por token, aceite, defesa, correções, upload, download, validação de documento, aprovação, conclusão, notificações, CSRF inválido, permissões e validações de formulário.
- Renderização de todas as telas nos quatro perfis, sem warnings/notices.
- Inspeção visual em desktop (1440 px) e mobile (390 px), incluindo menu lateral e tabelas responsivas.

## Observações

- A pasta `storage/` precisa ser gravável pelo Apache para uploads, e-mails e logs.
- O seed define a banca demo como confirmada; para percorrer o fluxo completo, crie um trabalho novo e agende a banca.
- Não há testes automatizados versionados; a suíte descrita acima foi executada manualmente durante a revisão em banco isolado.
