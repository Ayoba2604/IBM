# Como o SIG-Banca foi feito

Este documento explica a estrutura técnica e as principais decisões de implementação do SIG-Banca. O projeto é um MVP web para organizar o processo de bancas de TCC, desde o cadastro do trabalho até a avaliação da versão final.

## 1. Tecnologias utilizadas

- **PHP 8:** lógica do servidor, autenticação, regras do fluxo e renderização das páginas.
- **MySQL 8:** armazenamento relacional, integridade dos dados, restrições e triggers.
- **HTML e CSS:** estrutura das páginas e interface responsiva.
- **JavaScript puro:** pequenas interações no navegador, como confirmação de solicitação de ajustes.
- **WAMP:** ambiente local de desenvolvimento e execução no Windows.

Não foi usado um framework PHP. O projeto mantém a aplicação pequena e direta para que seja fácil de executar e estudar no ambiente local.

## 2. Organização das pastas

```text
SIG/
├── app/
│   ├── bootstrap.php          # Inicialização da aplicação e sessão
│   ├── config/                # Configuração geral e conexão PDO
│   ├── helpers/               # Autenticação, CSRF, consultas e utilitários
│   └── services/              # Tokens, prazos, uploads e convites
├── database/
│   ├── schema.sql             # Banco e tabelas
│   ├── seed.sql               # Dados demonstrativos
│   ├── triggers.sql           # Regras e auditoria no MySQL
│   └── migrations/            # Atualizações de estrutura existentes
├── public/
│   ├── index.php              # Entrada, rotas e processamento de formulários
│   ├── pages.php              # Consultas e páginas da aplicação
│   └── assets/                # CSS e JavaScript
├── storage/
│   ├── uploads/               # PDFs fora da pasta pública
│   └── mail-outbox/           # Convites gerados no modo local
├── .env                       # Configuração local do MySQL
└── README.md
```

O servidor web deve expor `public/`, não a raiz inteira do projeto. No WAMP deste ambiente, a aplicação é aberta por `http://www8/IBM/SIG/public`.

## 3. Como uma requisição percorre o sistema

1. O navegador acessa `public/index.php`.
2. `app/bootstrap.php` carrega as configurações, define o fuso horário, inicia a sessão e inclui os serviços e utilitários. A resposta declara UTF-8.
3. `index.php` identifica a página solicitada. Quando recebe um formulário, valida CSRF, autenticação, perfil e permissões antes de processar a ação.
4. Consultas e alterações usam o PDO criado por `app/config/database.php`, com consultas preparadas e conexão `utf8mb4`.
5. As páginas em `pages.php` consultam os dados e montam o HTML. O layout e os helpers compartilhados evitam repetir a estrutura básica da interface.
6. Após uma operação, o sistema registra uma mensagem de retorno e redireciona o usuário para a tela apropriada.

O roteamento e a apresentação são simples e ficam concentrados em poucos arquivos; não há uma camada de rotas ou templates fornecida por framework.

## 4. Perfis e controle de acesso

O campo `usuarios.perfil` define quatro perfis:

- **Aluno:** consulta os trabalhos aos quais está vinculado e envia a versão final quando o fluxo permite.
- **Orientador:** cadastra trabalhos, agenda bancas, registra correções, envia documentos e avalia versões.
- **Avaliador:** acessa os convites de banca e aceita ou recusa o convite.
- **Administrador:** acessa as funções administrativas e pode operar em nome da instituição.

Os helpers `require_login()` e `require_role()` protegem páginas e ações. A senha é verificada com `password_verify`; após autenticação, o identificador da sessão é regenerado. Formulários de alteração usam token CSRF.

## 5. Modelo de dados

O esquema principal possui 12 tabelas:

| Tabela | Responsabilidade |
| --- | --- |
| `cursos` | Cursos cadastrados na instituição |
| `usuarios` | Estudantes, orientadores, avaliadores e administradores |
| `trabalhos` | Dados, período letivo, orientador e estado do TCC |
| `alunos_trabalhos` | Vínculo entre trabalhos e estudantes |
| `bancas` | Data, horário, modalidade, local/link e estado da banca |
| `banca_avaliadores` | Avaliadores associados e resposta ao convite |
| `convites` | Token protegido por hash, validade e resposta |
| `documentos` | Metadados dos arquivos enviados |
| `correcoes` | Descrição, data de liberação e prazo final |
| `versoes` | Número e avaliação de cada versão final |
| `notificacoes` | Avisos internos associados a um usuário |
| `auditoria` | Histórico de ações e mudanças relevantes |

As tabelas usam InnoDB, chaves estrangeiras, índices, validações `CHECK` e valores `ENUM` para estados fechados. O banco e a conexão usam `utf8mb4`. Os valores internos dos estados e dos tipos são mantidos sem acentos para facilitar sua validação pelo código; a interface apresenta os rótulos em português.

## 6. Fluxo do trabalho

```text
Rascunho
  → agendamento da banca e convites
  → confirmações dos avaliadores
  → defesa realizada
  → documentos e/ou correções
  → envio da versão final
  → avaliação pelo orientador
  → concluído ou ajustes solicitados
```

As transições válidas são verificadas tanto nas ações PHP quanto nos triggers do banco. Assim, o banco também rejeita mudanças incompatíveis com o fluxo definido, mesmo se uma atualização tentar contornar a interface.

## 7. Serviços e regras de negócio

- **`TokenService`:** gera tokens aleatórios para convites e persiste somente o hash SHA-256; o token original vai no link de resposta.
- **`PrazoService`:** calcula a data final somando 15 dias à data de liberação das correções.
- **`UploadService`:** verifica o resultado do upload, tamanho e tipo MIME, gera um nome aleatório e guarda o PDF em `storage/uploads`, fora de `public/`.
- **`MailService`:** no modo local, grava o conteúdo do convite em `storage/mail-outbox`. Isso facilita conferir o e-mail durante o desenvolvimento sem enviar mensagens reais.
- **Helpers:** centralizam consultas simples, controle de sessão, mensagens temporárias, redirecionamento, autorização, auditoria e notificações.

Operações que alteram tabelas relacionadas usam transações PDO. O tratamento de erros tenta reverter a transação na mesma conexão e registra exceções do banco no log PHP.

## 8. Regras no MySQL e auditoria

`database/triggers.sql` complementa as validações da aplicação. Entre as regras estão verificar o perfil de usuários relacionados, rejeitar transições de estado não permitidas, validar documentos/versões e registrar mudanças de estado em `auditoria`.

A conexão define a variável MySQL `@sig_usuario_id` para que os triggers possam atribuir a auditoria ao usuário autenticado. A aplicação também registra ações de negócio específicas, como criação de banca, resposta a convite e avaliação de versão.

Para uma base SIG que já contenha dados, `database/migrations/001_integrity_mysql.sql` é a migração de integridade; depois dela, instalam-se os triggers. `seed.sql` é apenas para criar dados demonstrativos e não deve ser reimportado sobre dados que se deseja preservar.

## 9. Como configurar no WAMP

1. Importe `database/schema.sql` no MySQL para criar o banco e as tabelas.
2. Importe `database/triggers.sql` para instalar as regras complementares.
3. Opcionalmente, importe `database/seed.sql` em uma base nova para carregar registros de demonstração.
4. Ajuste as credenciais do banco em `.env` se forem diferentes das padrão do WAMP. A URL da aplicação é fixa no código para o host `www8` e não usa `APP_URL`.
5. Garanta que o PHP tenha PDO MySQL e Fileinfo habilitados e que `storage/uploads` e `storage/mail-outbox` possam ser gravadas pelo processo PHP.
6. Abra `http://www8/IBM/SIG/public`.

As contas incluídas no seed são demonstrativas e usam a senha `password`; não devem ser mantidas assim em um ambiente real.

## 10. Interface e idioma

As páginas declaram `lang="pt-BR"` e `charset="UTF-8"`. A aplicação envia `Content-Type: text/html; charset=UTF-8`, os arquivos textuais são UTF-8, e as páginas usam CSS responsivo. O JavaScript é pequeno e não é necessário para executar as operações principais.

## 11. Validação e limites conhecidos

A sintaxe dos arquivos PHP foi verificada com `php -l`, a conexão local com MySQL foi conferida e a página pública foi aberta pelo virtual host do WAMP. Isso confirma verificações técnicas básicas, mas não substitui uma bateria completa de testes ponta a ponta para todos os perfis e cenários de erro.

Antes de usar institucionalmente, ainda é necessário testar os fluxos com usuários, configurar envio real de e-mail, definir hospedagem e HTTPS, estabelecer backups e retenção de arquivos, revisar as contas de demonstração e executar testes funcionais e de segurança. O envio de e-mail atual é uma caixa de saída local, não uma integração SMTP de produção.
