# Correções pendentes — SIG-Banca

Documento baseado na varredura inicial do projeto em 20/10/2026. Os itens abaixo descrevem problemas identificados e o trabalho necessário para corrigi-los; ainda não são correções aplicadas.

## Prioridade alta — ações dos formulários sem implementação

As telas exibem formulários cujas ações não são tratadas pelo controlador em `public/index.php`. Ao enviar esses formulários, os dados não são processados nem persistidos.

### Ações a implementar

- [x] `create_banca` — criar banca e associar avaliadores convidados.
- [x] `upload` — validar permissão e arquivo PDF, salvar com `UploadService` e registrar em `documentos`; para versão final, criar também o registro correspondente em `versoes`.
- [x] `correction` — registrar a descrição e o prazo de 15 dias usando `PrazoService`, vinculando trabalho e banca.
- [x] `review_version` — validar decisão e permissão, atualizar status e registrar avaliador, data e observação.
- [x] `defense` — registrar a realização da banca e atualizar os estados relacionados conforme as transições permitidas pelos triggers.

### Arquivos envolvidos

- Formulários e telas: [`public/pages.php`](./public/pages.php)
- Processamento das ações: [`public/index.php`](./public/index.php)
- Serviços e regras existentes: [`app/services/UploadService.php`](./app/services/UploadService.php), [`app/services/PrazoService.php`](./app/services/PrazoService.php), [`app/services/TokenService.php`](./app/services/TokenService.php) e [`database/triggers.sql`](./database/triggers.sql)

### Critérios para considerar corrigido

- Cada ação deve validar CSRF, autenticação, perfil e acesso ao trabalho/banca antes de alterar dados.
- Dados relacionados devem ser persistidos em transação quando a operação envolver mais de uma tabela.
- Erros devem ser informados sem ocultar exceções do banco ou dos triggers.
- Após sucesso, redirecionar para a tela do registro e exibir mensagem de confirmação.
- Testar submissões válidas, inválidas, usuário sem permissão e falhas de persistência.

## Prioridade média — convites de avaliadores

Existe uma página de convites, mas ela não está disponível no menu. Além disso, a tela lista convites sem permitir que o avaliador aceite ou recuse.

### Trabalho necessário

- [x] Exibir o acesso à página de convites para o perfil `avaliador`.
- [x] Implementar ações protegidas por CSRF para aceitar e recusar convites.
- [x] Conferir que o convite pertence ao avaliador autenticado e que ainda está pendente/não expirou.
- [x] Atualizar os estados de `convites` e `banca_avaliadores` de forma consistente.
- [x] Validar a transição de estado da banca após as respostas, respeitando as regras do banco.

### Arquivos envolvidos

- Menu e roteamento: [`public/index.php`](./public/index.php)
- Página atual: [`public/pages.php`](./public/pages.php)
- Esquema e regras: [`database/schema.sql`](./database/schema.sql) e [`database/triggers.sql`](./database/triggers.sql)

## Prioridade média — tratamento de falhas de banco

O `catch` de [`public/index.php`](./public/index.php) chama `db()` novamente para verificar se há uma transação ativa. Se a conexão original tiver falhado, essa nova chamada pode gerar outra exceção e mascarar a causa inicial.

### Trabalho necessário

- [x] Controlar a transação pela mesma instância PDO usada na operação.
- [x] No tratamento de erro, fazer rollback somente se essa instância estiver disponível e em transação.
- [x] Preservar a mensagem original para registro/diagnóstico e apresentar ao usuário uma mensagem apropriada.
- [ ] Testar falha de conexão, erro SQL dentro de transação e erro de validação antes de iniciar transação.

## Aviso do ambiente — PHP 8.5 / Xdebug

Corrigido nesta rodada: a linha que carregava o Xdebug compilado para PHP 8.4 foi desativada somente no php.ini do PHP 8.5; o php.ini original foi preservado em php.ini.codex-backup.

## Verificações já realizadas

- Sintaxe PHP verificada com PHP 8.0, 8.1, 8.2 e 8.3: sem erros.
- Conexão local com MySQL 8.4.7: funcionando.
- Tabelas esperadas no banco local: 12 encontradas.
- Não foram localizados testes automatizados durante a varredura; criar testes funcionais para as ações acima faz parte da validação das correções.

## Estado desta rodada

- Implementados os cinco handlers, a tela/menu de convites e o tratamento de falhas descritos acima.
- `php -l` passou em todos os arquivos PHP usando PHP 8.2.29 e PHP 8.5.0; a página inicial também foi renderizada em PHP 8.5 sem erro.
- Os fluxos com submissão, permissões e falhas de persistência ainda precisam de teste funcional ponta a ponta; não foram simulados nesta rodada para preservar os dados reais do banco.
- Xdebug incompatível desativado no PHP 8.5; backup do php.ini original salvo ao lado do arquivo.
