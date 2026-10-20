const fs = require('fs');
const path = require('path');
const cdp = require('./cdp');

const BASE = 'http://localhost/IBM/public/index.php';
const ids = {};

(async () => {
  const client = await cdp.Client.connect();
  await cdp.init(client);

  // 00. Public home
  await cdp.nav(client, BASE);
  await cdp.shot(client, '00_home');

  // 01. Login screen
  await cdp.nav(client, BASE + '?page=login');
  await cdp.shot(client, '01_login');

  // Login as advisor
  await cdp.setValue(client, { 'input[name="email"]': 'ricardo@fatec.sp.gov.br', 'input[name="password"]': 'password' });
  await cdp.submitForm(client, 'form');
  await cdp.sleep(800);
  console.log('after login:', await cdp.href(client));
  await cdp.shot(client, '02_dashboard_orientador');

  // 03. New work form (filled)
  await cdp.nav(client, BASE + '?page=work_new');
  await cdp.setValue(client, {
    'input[name="titulo"]': 'Acessibilidade digital para estudantes com dislexia',
    'select[name="curso_id"]': '1',
    'input[name="ano"]': '2026',
    'select[name="semestre"]': '2',
    'textarea[name="resumo"]': 'Aplicativo que adapta materiais didáticos para estudantes com dislexia, com tipografia ajustável, leitura em voz alta e acompanhamento pedagógico.',
    'input[name="alunos[]"][value="4"]': true,
    'input[name="alunos[]"][value="5"]': true,
  });
  await cdp.shot(client, '03_novo_trabalho');

  // Submit -> work detail (RASCUNHO)
  await cdp.submitForm(client, 'form.form');
  let url = await cdp.href(client);
  console.log('after create_work:', url);
  let m = url.match(/id=(\d+)/);
  if (!m) throw new Error('work id not found in ' + url);
  ids.work = Number(m[1]);
  await cdp.sleep(600);
  await cdp.shot(client, '04_trabalho_rascunho');

  // 05. Schedule banca form
  await cdp.nav(client, `${BASE}?page=banca_new&work_id=${ids.work}`);
  await cdp.setValue(client, {
    'select[name="trabalho_id"]': String(ids.work),
    'input[name="data_banca"]': '2026-10-20',
    'input[name="hora_inicio"]': '14:00',
    'input[name="hora_fim"]': '15:00',
    'select[name="modalidade"]': 'hibrida',
    'input[name="local"]': 'Sala 12 — Bloco B',
    'input[name="link_online"]': 'https://meet.google.com/sig-banca-demo',
    'input[name="avaliadores[]"][value="3"]': true,
  });
  await cdp.shot(client, '05_agendar_banca');

  // Submit -> banca detail
  await cdp.submitForm(client, 'form.form');
  url = await cdp.href(client);
  console.log('after create_banca:', url);
  m = url.match(/id=(\d+)/);
  if (!m) throw new Error('banca id not found in ' + url);
  ids.banca = Number(m[1]);
  await cdp.sleep(800);
  await cdp.shot(client, '06_banca_convites_pendentes');

  ids.email = 'ricardo@fatec.sp.gov.br';
  fs.writeFileSync(path.join(__dirname, 'ids.json'), JSON.stringify(ids, null, 2));
  console.log('IDS', ids);
  process.exit(0);
})().catch((e) => {
  console.error('FAILED', e);
  process.exit(1);
});
