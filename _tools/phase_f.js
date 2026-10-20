const fs = require('fs');
const path = require('path');
const cdp = require('./cdp');

const BASE = 'http://localhost/IBM/public/index.php';

(async () => {
  const client = await cdp.Client.connect();
  await cdp.init(client);

  // Public consultation after conclusion
  await cdp.logout(client);
  await cdp.nav(client, `${BASE}?page=public_search&q=acessibilidade`);
  await cdp.shot(client, '22_consulta_publica');

  // Admin audit trail, scrolled view
  await cdp.login(client, 'coord@fatec.sp.gov.br');
  await cdp.nav(client, `${BASE}?page=admin`);
  await cdp.evaluate(client, `(() => { const el = document.querySelector('.audit-list'); if (el) window.scrollTo(0, el.getBoundingClientRect().top + window.scrollY - 180); return true; })()`);
  await cdp.sleep(600);
  await cdp.shot(client, '25b_auditoria');

  process.exit(0);
})().catch((e) => { console.error('FAILED', e); process.exit(1); });
