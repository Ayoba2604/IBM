const fs = require('fs');
const path = require('path');
const cdp = require('./cdp');

const BASE = 'http://localhost/IBM/public/index.php';
const ids = { work: 2 };

(async () => {
  const client = await cdp.Client.connect();
  await cdp.init(client);

  await cdp.login(client, 'ricardo@fatec.sp.gov.br');
  await cdp.shot(client, '02_dashboard_orientador');

  await cdp.nav(client, `${BASE}?page=banca_new&work_id=${ids.work}`);
  await cdp.setValue(client, {
    'select[name="trabalho_id"]': String(ids.work),
    'input[name="data_banca"]': '2026-10-21',
    'input[name="hora_inicio"]': '14:00',
    'input[name="hora_fim"]': '15:00',
    'select[name="modalidade"]': 'hibrida',
    'input[name="local"]': 'Sala 12 — Bloco B',
    'input[name="link_online"]': 'https://meet.google.com/sig-banca-demo',
    'input[name="avaliadores[]"][value="3"]': true,
  });
  await cdp.shot(client, '05_agendar_banca');

  await cdp.submitForm(client, 'form.form');
  await cdp.sleep(1000);
  let url = await cdp.href(client);
  console.log('after create_banca:', url);
  const m = url.match(/id=(\d+)/);
  if (!m) {
    const body = await cdp.evaluate(client, `(document.body.innerText||'').slice(0,600)`);
    throw new Error('banca id not found in ' + url + ' | ' + body);
  }
  ids.banca = Number(m[1]);
  await cdp.shot(client, '06_banca_convites_pendentes');

  fs.writeFileSync(path.join(__dirname, 'ids.json'), JSON.stringify(ids, null, 2));
  console.log('IDS', ids);
  process.exit(0);
})().catch((e) => { console.error('FAILED', e); process.exit(1); });
