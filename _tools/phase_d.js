const fs = require('fs');
const path = require('path');
const cdp = require('./cdp');

const BASE = 'http://localhost/IBM/public/index.php';
const ids = JSON.parse(fs.readFileSync(path.join(__dirname, 'ids.json'), 'utf8'));
const fixtures = path.resolve(__dirname, 'fixtures');

function latestToken() {
  const dir = path.resolve(__dirname, '..', 'storage', 'mail-outbox');
  const files = fs.readdirSync(dir).filter((f) => f.endsWith('.txt')).sort().reverse();
  for (const f of files) {
    const txt = fs.readFileSync(path.join(dir, f), 'utf8');
    const m = txt.match(/token=([a-f0-9]{64})/);
    if (m) return m[1];
  }
  throw new Error('token not found');
}

const SEL = {
  upload: `[...document.querySelectorAll('section.card')].find(s => s.textContent.includes('Enviar documento'))`,
  deadline: `document.querySelector('.deadline') ? document.querySelector('.deadline').closest('section.card') : null`,
  docs: `[...document.querySelectorAll('section.card')].find(s => s.textContent.includes('Ata de defesa') && s.textContent.includes('Autorização RIC-CPS'))`,
  versions: `[...document.querySelectorAll('section.card')].find(s => s.textContent.includes('Versões finais'))`,
  approval: `[...document.querySelectorAll('section.card')].find(s => s.textContent.includes('Avaliar versão'))`,
};

(async () => {
  const client = await cdp.Client.connect();
  await cdp.init(client);

  await cdp.logout(client);
  // Public token invite (valid state)
  await cdp.nav(client, `${BASE}?page=invite&token=${latestToken()}`);
  await cdp.shot(client, '07_convite_token');

  await cdp.login(client, 'ricardo@fatec.sp.gov.br');
  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);

  // Upload card with a chosen file
  await cdp.setValue(client, { 'select[name="tipo"]': 'ata' });
  await cdp.setFile(client, 'input[name="arquivo"]', path.join(fixtures, 'Ata_Defesa_TCC.pdf'));
  await cdp.shotElement(client, '12_upload_ata', SEL.upload);

  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.shotElement(client, '14_documentos_aprovados', SEL.docs);
  await cdp.shotElement(client, '16_prazo_15_dias', SEL.deadline);

  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.shotElement(client, '20_aprovacao_orientador', SEL.approval);

  // Approve final version (decision is a button value -> inject hidden field)
  const approve = await cdp.evaluate(client, `(() => {
    const f = [...document.querySelectorAll('form')].find(f => f.querySelector('input[name="versao_id"]'));
    if (!f) return false;
    const d = document.createElement('input'); d.type='hidden'; d.name='decision'; d.value='approve'; f.appendChild(d);
    f.submit(); return true;
  })()`);
  console.log('approve:', approve);
  await cdp.sleep(1800);

  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.shot(client, '21_trabalho_concluido');
  await cdp.shotElement(client, '21b_versoes_aprovadas', SEL.versions);
  await cdp.shotElement(client, '21c_documentos_final', SEL.docs);

  // Concluded stepper card
  const stepper = `[...document.querySelectorAll('section.card')].find(s => s.textContent.includes('Etapa atual')) || document.querySelector('.stepper-card')`;
  await cdp.shotElement(client, '21d_stepper_concluido', stepper);

  process.exit(0);
})().catch((e) => { console.error('FAILED', e); process.exit(1); });
