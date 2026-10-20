const fs = require('fs');
const path = require('path');
const cdp = require('./cdp');

const BASE = 'http://localhost/IBM/public/index.php';
const ids = JSON.parse(fs.readFileSync(path.join(__dirname, 'ids.json'), 'utf8'));
const fixtures = path.resolve(__dirname, 'fixtures');

const SEL = {
  upload: `[...document.querySelectorAll('section.card')].find(s => s.textContent.includes('Enviar documento'))`,
  deadline: `document.querySelector('.deadline') ? document.querySelector('.deadline').closest('section.card') : null`,
  docs: `[...document.querySelectorAll('section.card')].find(s => s.textContent.includes('Ata de defesa') && s.textContent.includes('Autorização RIC-CPS'))`,
  versions: `[...document.querySelectorAll('section.card')].find(s => s.textContent.includes('Versões finais'))`,
  stepper: `[...document.querySelectorAll('section.card')].find(s => s.textContent.includes('Etapa atual'))`,
  commission: `[...document.querySelectorAll('section.card')].find(s => s.textContent.includes('Avaliadores e convites'))`,
};

(async () => {
  const client = await cdp.Client.connect();
  await cdp.init(client);

  await cdp.login(client, 'ricardo@fatec.sp.gov.br');
  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);

  await cdp.setValue(client, { 'select[name="tipo"]': 'ata' });
  await cdp.setFile(client, 'input[name="arquivo"]', path.join(fixtures, 'Ata_Defesa_TCC.pdf'));
  await cdp.shotElement(client, '12_upload_ata', SEL.upload);

  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.shotElement(client, '14_documentos_aprovados', SEL.docs);
  await cdp.shotElement(client, '16_prazo_15_dias', SEL.deadline);
  await cdp.shotElement(client, '21b_versoes_aprovadas', SEL.versions);
  await cdp.shotElement(client, '21c_documentos_final', SEL.docs);
  await cdp.shotElement(client, '21d_stepper_concluido', SEL.stepper);

  // Banca commission card (confirmed/aceito)
  await cdp.nav(client, `${BASE}?page=banca&id=${ids.banca}`);
  await cdp.shotElement(client, '10b_comissao_confirmada', SEL.commission);

  process.exit(0);
})().catch((e) => { console.error('FAILED', e); process.exit(1); });
