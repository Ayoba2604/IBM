# SIG-Banca

MVP web para acompanhar bancas de TCC da Fatec Bauru, criado em PHP 8, MySQL, HTML, CSS e JavaScript puro.

Documentação: [pitch do projeto](PITCH.md) · [como o projeto foi feito](COMO_FOI_FEITO.md).

## Como executar no WAMP

1. Crie as tabelas importando `database/schema.sql` no phpMyAdmin.
2. Importe `database/triggers.sql` para instalar as validações e a auditoria no MySQL.
3. Importe `database/seed.sql` no banco `sig_banca` para carregar as contas fictícias.
4. Copie `.env.example` para `.env` e ajuste os dados do MySQL, se necessário. No WAMP padrão, `root` sem senha é suportado pelos valores de exemplo.
5. Abra o sistema em `http://www8/IBM/SIG/public`. A URL base usa o virtual host `www8` e não depende do `APP_URL` no `.env`.

Para atualizar um banco SIG existente, importe uma vez `database/migrations/001_integrity_mysql.sql` e depois `database/triggers.sql`. A migração adiciona estados tipados, restrições, verificações e índices sem apagar os registros existentes. Não reimporte o seed sobre um banco que já tenha os dados demo.

O projeto usa arquivos em UTF-8, páginas HTML declaradas em `pt-BR`, resposta HTTP com charset UTF-8, conexão PDO em `utf8mb4` e banco MySQL em `utf8mb4`.

O DocumentRoot recomendado é `SIG/public`. Se não puder alterá-lo, o link acima aponta diretamente para a pasta pública.

## Contas demonstrativas

Todas usam a senha `password`:

| Perfil | E-mail |
| --- | --- |
| Coordenação | coord@fatec.sp.gov.br |
| Orientador | ricardo@fatec.sp.gov.br |
| Avaliador | marina@fatec.sp.gov.br |
| Aluno | lucas@fatec.sp.gov.br |

## Funcionalidades entregues

- Login seguro com `password_verify`, sessão regenerada e controle por perfil.
- Painel inicial, consulta pública, listagem de trabalhos, cadastro de trabalho e vínculo de alunos.
- Estrutura relacional completa para bancas, avaliadores, convites por token, documentos, correções, versões, notificações e auditoria.
- Restrições CHECK e ENUM para períodos, estados, horários, modalidade, documentos e prazo de 15 dias corridos.
- Triggers MySQL para validar perfis vinculados, impedir transições inválidas e registrar mudanças de estado na auditoria.
- Auditoria de alterações de estado protegida contra edição e exclusão pela aplicação.
- Telas de agendamento, detalhes de banca e trabalho, calendário e área administrativa.
- Serviços preparados para tokens seguros, upload de PDF fora da pasta pública, prazo de 15 dias corridos e caixa de saída local de e-mails.
- Interface responsiva e acessível, sem frameworks pesados.

## Configuração de e-mail e arquivos

No desenvolvimento, convites podem ser registrados em `storage/mail-outbox`; essa pasta não entra no Git. Configure PHPMailer/SMTP por variáveis locais antes de enviar e-mail real. Uploads devem permanecer em `storage/uploads`, também fora de `public`. O PDO define `@sig_usuario_id` por conexão para que os triggers atribuam eventos ao usuário autenticado.

## Validação executada

Todos os arquivos PHP foram verificados com `php -l` usando PHP 8.2 do WAMP.
# IBM
