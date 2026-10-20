# SIG-Banca

MVP web para acompanhar bancas de TCC da Fatec Bauru, criado em PHP 8, MySQL, HTML, CSS e JavaScript puro.

## Como executar no WAMP

1. Crie as tabelas importando `database/schema.sql` no phpMyAdmin.
2. Importe `database/triggers.sql` para instalar as validações e a auditoria no MySQL.
3. Importe `database/seed.sql` no banco `sig_banca` para carregar as contas fictícias.
4. Copie `.env.example` para `.env` e ajuste os dados do MySQL, se necessário. No WAMP padrão, `root` sem senha é suportado pelos valores de exemplo.
5. Ajuste `APP_URL` para `http://localhost/IBM/SIG/public` e abra esse endereço.

Para atualizar um banco SIG existente, importe uma vez `database/migrations/001_integrity_mysql.sql` e depois `database/triggers.sql`. A migração adiciona estados tipados, restrições, verificações e índices sem apagar os registros existentes. Não reimporte o seed sobre um banco que já tenha os dados demo.

O DocumentRoot recomendado e `SIG/public`. Se nao puder alteralo, o link acima usa a pasta publica explicitamente.

## Contas demonstrativas

Todas usam a senha `password`:

| Perfil | E-mail |
| --- | --- |
| Coordenacao | coord@fatec.sp.gov.br |
| Orientador | ricardo@fatec.sp.gov.br |
| Avaliador | marina@fatec.sp.gov.br |
| Aluno | lucas@fatec.sp.gov.br |

## Funcionalidades entregues

- Login seguro com `password_verify`, sessao regenerada e controle por perfil.
- Dashboard, consulta publica, listagem de trabalhos, cadastro de trabalho e vinculo de alunos.
- Estrutura relacional completa para bancas, avaliadores, convites por token, documentos, correcoes, versoes, notificacoes e auditoria.
- Restrições CHECK e ENUM para períodos, estados, horários, modalidade, documentos e prazo de 15 dias corridos.
- Triggers MySQL para validar perfis vinculados, impedir transições inválidas e registrar mudanças de estado na auditoria.
- Auditoria de alterações de estado protegida contra edição e exclusão pela aplicação.
- Tela de agendamento, detalhes de banca/trabalho, calendario e area administrativa.
- Servicos preparados para token seguro, upload PDF fora da pasta publica, prazo de 15 dias corridos e caixa de saida local de e-mails.
- Interface responsiva e acessivel, sem frameworks pesados.

## Configuracao de e-mail e arquivos

No desenvolvimento, convites podem ser registrados em `storage/mail-outbox`; essa pasta não entra no Git. Configure PHPMailer/SMTP por variáveis locais antes de enviar e-mail real. Uploads devem permanecer em `storage/uploads`, também fora de `public`. O PDO define `@sig_usuario_id` por conexão para que os triggers atribuam eventos ao usuário autenticado.

## Validacao executada

Todos os arquivos PHP foram verificados com `php -l` usando PHP 8.2 do WAMP.
# IBM
