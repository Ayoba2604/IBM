const fs = require('fs');
const path = require('path');
const cdp = require('./cdp');

const BASE = 'http://localhost/IBM/public/index.php';
const ids = JSON.parse(fs.readFileSync(path.join(__dirname, 'ids.json'), 'utf8'));
const fixtures = path.resolve(__dirname, 'fixtures');

async function submitAction(client, action) {
  const ok = await cdp.evaluate(client, `(() => {
    const f = [...document.querySelectorAll('form')].find(f => f.querySelector('input[name="action"][value="${action}"]'));
    if (!f) return false; f.submit(); return true;
  })()`);
  await cdp.sleep(1600);
  return ok;
}

(async () => {
  const client = await cdp.Client.connect();
  await cdp.init(client);

  // Student sends the final version (15-day deadline running)
  await cdp.login(client, 'lucas@fatec.sp.gov.br');
  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.shot(client, '17_prazo_aluno', { scroll: '.deadline' });

  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.setValue(client, { 'select[name="tipo"]': 'versao_final' });
  await cdp.setFile(client, 'input[name="arquivo"]', path.join(fixtures, 'Versao_Final_Corrigida.pdf'));
  await cdp.shot(client, '18_envio_versao_final', { scroll: '.form.compact' });
  const up = await submitAction(client, 'upload');
  console.log('upload versao final:', up);
  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.shot(client, '19_versao_em_analise');

  // Advisor approves the final version
  await cdp.login(client, 'ricardo@fatec.sp.gov.br');
  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.shot(client, '20_aprovacao_orientador', { scroll: '.form.compact' });
  const approve = await cdp.evaluate(client, `(() => {
    const f = [...document.querySelectorAll('form')].find(f => f.querySelector('input[name="versao_id"]'));
    if (!f) return false;
    f.submit(); return true;
  })()`);
  console.log('approve:', approve);
  await cdp.sleep(1600);
  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.shot(client, '21_trabalho_concluido');
  await cdp.shot(client, '21b_versoes_aprovadas', { scroll: '.version-list' });

  // Public consultation (anonymous)
  await cdp.logout(client);
  await cdp.nav(client, `${BASE}?page=public_search`);
  await cdp.setValue(client, { 'input[name="q"]': 'acessibilidade' });
  await cdp.submitForm(client, 'form');
  await cdp.sleep(900);
  await cdp.shot(client, '22_consulta_publica');

  // Calendar
  await cdp.login(client, 'ricardo@fatec.sp.gov.br');
  await cdp.nav(client, `${BASE}?page=calendar`);
  await cdp.shot(client, '23_calendario');

  // Notifications
  await cdp.nav(client, `${BASE}?page=notifications`);
  await cdp.shot(client, '24_notificacoes');

  // Administration / audit trail
  await cdp.login(client, 'coord@fatec.sp.gov.br');
  await cdp.nav(client, `${BASE}?page=admin`);
  await cdp.shot(client, '25_admin_auditoria');

  process.exit(0);
})().catch((e) => { console.error('FAILED', e); process.exit(1); });
