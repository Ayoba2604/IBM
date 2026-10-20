// Minimal CDP driver (no external deps) for Edge headless screenshots.
const fs = require('fs');
const path = require('path');

const PORT = process.env.CDP_PORT || 9333;
const OUT = path.resolve(__dirname, '..', 'Apresentacao', 'prints');
fs.mkdirSync(OUT, { recursive: true });

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function waitForCdp() {
  for (let i = 0; i < 100; i++) {
    try {
      const r = await fetch(`http://127.0.0.1:${PORT}/json/list`);
      const list = await r.json();
      const page = list.find((t) => t.type === 'page');
      if (page && page.webSocketDebuggerUrl) return page.webSocketDebuggerUrl;
    } catch (e) {}
    await sleep(250);
  }
  throw new Error('CDP endpoint not reachable');
}

class Client {
  constructor(ws) {
    this.ws = ws;
    this.id = 0;
    this.pending = new Map();
    this.listeners = new Map();
  }
  static async connect() {
    const url = await waitForCdp();
    const ws = new WebSocket(url);
    await new Promise((res, rej) => {
      ws.onopen = res;
      ws.onerror = rej;
    });
    const c = new Client(ws);
    ws.onmessage = (ev) => {
      const msg = JSON.parse(ev.data);
      if (msg.id && c.pending.has(msg.id)) {
        const { resolve, reject } = c.pending.get(msg.id);
        c.pending.delete(msg.id);
        if (msg.error) reject(new Error(`${msg.error.message}`));
        else resolve(msg.result);
      } else if (msg.method) {
        const arr = c.listeners.get(msg.method) || [];
        arr.forEach((fn) => fn(msg.params));
      }
    };
    return c;
  }
  send(method, params = {}) {
    const id = ++this.id;
    return new Promise((resolve, reject) => {
      this.pending.set(id, { resolve, reject });
      this.ws.send(JSON.stringify({ id, method, params }));
      setTimeout(() => {
        if (this.pending.has(id)) {
          this.pending.delete(id);
          reject(new Error(`timeout ${method}`));
        }
      }, 30000);
    });
  }
  on(method, fn) {
    const arr = this.listeners.get(method) || [];
    arr.push(fn);
    this.listeners.set(method, arr);
  }
}

async function init(client) {
  await client.send('Page.enable');
  await client.send('Runtime.enable');
  await client.send('DOM.enable');
  await client.send('Emulation.setDeviceMetricsOverride', {
    width: 1440,
    height: 810,
    deviceScaleFactor: 1.5,
    mobile: false,
  });
  await client.send('Emulation.setEmulatedMedia', {
    features: [{ name: 'prefers-reduced-motion', value: 'reduce' }],
  });
}

async function evaluate(client, expression) {
  const res = await client.send('Runtime.evaluate', {
    expression,
    awaitPromise: true,
    returnByValue: true,
  });
  if (res.exceptionDetails) {
    throw new Error('JS error: ' + (res.exceptionDetails.text || '') + ' ' + JSON.stringify(res.exceptionDetails.exception || {}));
  }
  return res.result ? res.result.value : undefined;
}

async function nav(client, url) {
  await client.send('Page.navigate', { url });
  for (let i = 0; i < 120; i++) {
    await sleep(120);
    try {
      const ready = await evaluate(client, 'document.readyState');
      if (ready === 'complete') break;
    } catch (e) {}
  }
  // Wait for web fonts (icons/text) with a hard cap.
  try {
    await client.send('Runtime.evaluate', {
      expression: `(async () => { if (document.fonts && document.fonts.ready) { await Promise.race([document.fonts.ready, new Promise(r=>setTimeout(r,4000))]); } return true; })()`,
      awaitPromise: true,
      returnByValue: true,
    });
  } catch (e) {}
  await sleep(450);
}

async function shot(client, name, opts = {}) {
  const scroll = opts.scroll;
  if (scroll) {
    await evaluate(client, `(() => { const el = document.querySelector(${JSON.stringify(scroll)}); if (el) el.scrollIntoView({block:'start'}); else window.scrollTo(0, ${Number(opts.y) || 0}); return true; })()`);
    await sleep(600);
  }
  const { data } = await client.send('Page.captureScreenshot', { format: 'png', fromSurface: true });
  const file = path.join(OUT, name + '.png');
  fs.writeFileSync(file, Buffer.from(data, 'base64'));
  console.log('shot', name, fs.statSync(file).size);
  return file;
}

async function setValue(client, data) {
  const json = JSON.stringify(data);
  await evaluate(client, `(() => {
    const data = ${json};
    for (const [sel, val] of Object.entries(data)) {
      const el = document.querySelector(sel);
      if (!el) { console.warn('missing ' + sel); continue; }
      if (el.type === 'checkbox') { el.checked = !!val; }
      else { el.value = val; }
      el.dispatchEvent(new Event('input', { bubbles: true }));
      el.dispatchEvent(new Event('change', { bubbles: true }));
    }
    return true;
  })()`);
  await sleep(400);
}

async function setFile(client, selector, filePath) {
  const { root } = await client.send('DOM.getDocument', { depth: -1 });
  const { nodeId } = await client.send('DOM.querySelector', { nodeId: root.nodeId, selector });
  if (!nodeId) throw new Error('file input not found: ' + selector);
  await client.send('DOM.setFileInputFiles', { files: [filePath], nodeId });
  await sleep(500);
}

async function submitForm(client, selector) {
  await evaluate(client, `(() => { const f = document.querySelector(${JSON.stringify(selector)}); if (!f) return false; if (f.requestSubmit) f.requestSubmit(); else f.submit(); return true; })()`);
  await sleep(1200);
}

async function click(client, selector) {
  await evaluate(client, `(() => { const el = document.querySelector(${JSON.stringify(selector)}); if (!el) return false; el.click(); return true; })()`);
  await sleep(900);
}

async function href(client) {
  return evaluate(client, 'location.href');
}

async function logout(client) {
  await evaluate(client, `(() => { const f = document.querySelector('form.logout-form'); if (f) { f.submit(); return true; } return false; })()`);
  await sleep(1200);
}

async function login(client, email) {
  const BASE = 'http://localhost/IBM/public/index.php';
  await nav(client, BASE + '?page=login');
  const hasForm = await evaluate(client, `!!document.querySelector('input[name="email"]')`);
  if (!hasForm) {
    // Already authenticated; log out first so we start a clean session.
    await logout(client);
    await nav(client, BASE + '?page=login');
  }
  await setValue(client, { 'input[name="email"]': email, 'input[name="password"]': 'password' });
  await submitForm(client, 'form[method="post"]');
  await sleep(1000);
  const u = await href(client);
  if (!/page=dashboard/.test(u)) console.warn('login may have failed:', u);
  return u;
}

async function shotElement(client, name, findExpr) {
  const ok = await evaluate(client, `(() => { const el = ${findExpr}; if (!el) return false; el.scrollIntoView({ block: 'center' }); return true; })()`);
  if (!ok) throw new Error('element not found: ' + name);
  await sleep(650);
  const rect = await evaluate(client, `(() => { const el = ${findExpr}; const r = el.getBoundingClientRect(); return { x: r.x + window.scrollX, y: r.y + window.scrollY, width: r.width, height: r.height, vw: window.innerWidth }; })()`);
  const { data } = await client.send('Page.captureScreenshot', {
    format: 'png',
    fromSurface: true,
    captureBeyondViewport: true,
    clip: {
      x: Math.max(0, rect.x),
      y: Math.max(0, rect.y),
      width: rect.width,
      height: rect.height,
      scale: 1,
    },
  });
  const file = path.join(OUT, name + '.png');
  fs.writeFileSync(file, Buffer.from(data, 'base64'));
  console.log('shotEl', name, fs.statSync(file).size, `${Math.round(rect.width)}x${Math.round(rect.height)}`);
  return file;
}

module.exports = { Client, init, nav, evaluate, shot, shotElement, setValue, setFile, submitForm, click, href, logout, login, OUT, sleep };
