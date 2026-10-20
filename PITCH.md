# Pitch — SIG-Banca

## Em uma frase

O SIG-Banca organiza o ciclo de uma banca de TCC — do agendamento à entrega da versão final — em um único fluxo digital, com prazos, documentos e responsabilidades acompanháveis.

## O desafio

O processo de defesa costuma envolver várias pessoas, datas, convites, documentos e etapas de revisão. Quando essas informações ficam espalhadas por e-mails, planilhas e conversas, acompanhar pendências e prazos se torna mais difícil para estudantes, orientadores, avaliadores e coordenação.

## A proposta

O SIG-Banca centraliza esse acompanhamento em uma aplicação web. Cada perfil acessa as funções adequadas ao seu papel, enquanto os registros do trabalho, da banca e das entregas permanecem relacionados no mesmo sistema.

## Como funciona

1. O orientador ou a coordenação cadastra o trabalho e vincula os estudantes.
2. A banca é agendada e os avaliadores recebem convites para aceitar ou recusar.
3. Após a defesa, são registrados documentos e, quando aplicável, as correções com prazo de 15 dias.
4. O estudante envia a versão final em PDF.
5. O orientador avalia a versão, aprova o trabalho ou solicita ajustes.

## Para quem

- **Estudantes:** acompanham a etapa do trabalho, os documentos e o prazo da versão final.
- **Orientadores:** organizam bancas, formalizam correções e avaliam as entregas.
- **Avaliadores:** consultam e respondem aos convites de banca.
- **Coordenação:** administra cursos e acompanha registros e fluxos institucionais.

## Valor para a instituição

- Mais clareza sobre a etapa atual e as próximas ações de cada trabalho.
- Convites, documentos, prazos e avaliações organizados em um só lugar.
- Menos dependência de controles paralelos para acompanhar as defesas.
- Histórico auditável das mudanças de estado dos trabalhos e das bancas.
- Regras importantes de integridade e transição também verificadas pelo banco de dados.

## O que já existe

O projeto conta com uma aplicação web em PHP e MySQL, controle de acesso por perfil, agendamento de bancas, convites por token, consulta pública, calendário, envio de PDFs, controle de versões, notificações internas e trilha de auditoria. As regras de prazo e de mudança de estado são apoiadas por serviços da aplicação e restrições e triggers no MySQL.

## Situação atual e próximos passos

O SIG-Banca está em estágio de MVP/protótipo funcional para validação local no WAMP. Antes de uma implantação institucional, os próximos passos recomendados são validar os fluxos com usuários, executar testes funcionais completos, definir a hospedagem e as rotinas de backup, e configurar o envio real de e-mails. No ambiente atual, os convites são registrados em uma caixa de saída local para desenvolvimento.

## Fala de abertura — aproximadamente 30 segundos

> O SIG-Banca transforma o processo de defesa de TCC em um fluxo mais fácil de acompanhar. Em vez de distribuir agendamentos, convites, documentos e prazos por diferentes canais, estudantes, orientadores, avaliadores e coordenação trabalham com as mesmas informações, cada um conforme seu papel. O sistema acompanha o trabalho desde o agendamento da banca até a avaliação da versão final, mantendo prazos e histórico em um só lugar.

## Demonstração sugerida

Apresente um trabalho, agende uma banca, mostre a resposta de um avaliador ao convite e percorra as etapas de registro da defesa, liberação das correções e avaliação da versão final.
