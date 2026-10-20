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

async function submitAction(client, action) {
  const ok = await cdp.evaluate(client, `(() => {
    const f = [...document.querySelectorAll('form')].find(f => f.querySelector('input[name="action"][value="${action}"]'));
    if (!f) return false; f.submit(); return true;
  })()`);
  await cdp.sleep(1500);
  return ok;
}

(async () => {
  const client = await cdp.Client.connect();
  await cdp.init(client);

  // Public invite page with token (the link sent by e-mail)
  const token = latestToken();
  await cdp.nav(client, `${BASE}?page=invite&token=${token}`);
  await cdp.shot(client, '07_convite_token');

  // Evaluator accepts via panel
  await cdp.login(client, 'marina@fatec.sp.gov.br');
  await cdp.nav(client, BASE + '?page=invitations');
  await cdp.shot(client, '08_meus_convites');
  const clicked = await cdp.evaluate(client, `(() => {
    const b = document.querySelector('.invite-card form button[value="aceito"]');
    if (!b) return false; b.click(); return true;
  })()`);
  console.log('accepted:', clicked);
  await cdp.sleep(1500);
  await cdp.shot(client, '09_convite_aceito');

  // Advisor: banca confirmed
  await cdp.login(client, 'ricardo@fatec.sp.gov.br');
  await cdp.nav(client, `${BASE}?page=banca&id=${ids.banca}`);
  await cdp.shot(client, '10_banca_confirmada');

  // Register defense
  const def = await submitAction(client, 'defense');
  console.log('defense:', def);
  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.shot(client, '11_defesa_realizada');

  // Upload Ata de Defesa
  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.setValue(client, { 'select[name="tipo"]': 'ata' });
  await cdp.setFile(client, 'input[name="arquivo"]', path.join(fixtures, 'Ata_Defesa_TCC.pdf'));
  await cdp.shot(client, '12_upload_ata');
  await submitAction(client, 'upload');
  await cdp.sleep(700);

  // Upload Autorização RIC-CPS
  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.setValue(client, { 'select[name="tipo"]': 'autorizacao_ric' });
  await cdp.setFile(client, 'input[name="arquivo"]', path.join(fixtures, 'Autorizacao_RIC-CPS.pdf'));
  await submitAction(client, 'upload');
  await cdp.sleep(700);

  await cdp.shot(client, '13_documentos_pendentes', { scroll: '.table-wrap' });

  // Validate both documents
  for (let i = 0; i < 2; i++) {
    const ok = await cdp.evaluate(client, `(() => {
      const forms = [...document.querySelectorAll('form')].filter(f => f.querySelector('input[name="action"][value="validate_document"]'));
      if (!forms.length) return false;
      const f = forms[0];
      const d = document.createElement('input'); d.type='hidden'; d.name='decision'; d.value='aprovado';
      f.appendChild(d);
      const obs = f.querySelector('textarea[name="observacao"]');
      if (obs) obs.value = 'Documento conferido e aprovado.';
      f.submit(); return true;
    })()`);
    await cdp.sleep(1500);
    console.log('validate', i, ok);
    if (!ok) break;
    await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  }
  await cdp.shot(client, '14_documentos_aprovados', { scroll: '.table-wrap' });

  // Formalize corrections (15 days)
  await cdp.nav(client, `${BASE}?page=work&id=${ids.work}`);
  await cdp.setValue(client, {
    'input[name="data_liberacao"]': '2026-10-20',
    'textarea[name="descricao"]': 'Ajustar as referências ao padrão ABNT, revisar a fundamentação sobre acessibilidade e incluir os resultados do teste com os estudantes.',
  });
  await cdp.shot(client, '15_formalizar_correcoes', { scroll: '.form.compact' });
  await submitAction(client, 'correction');
  await cdp.sleep(800);
  await cdp.shot(client, '16_prazo_15_dias', { scroll: '.deadline' });

  process.exit(0);
})().catch((e) => { console.error('FAILED', e); process.exit(1); });
