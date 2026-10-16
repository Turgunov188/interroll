const { JSDOM, VirtualConsole } = require('jsdom');
const fs = require('fs');
const path = require('path');
const dir = process.argv[2];
const files = fs.readdirSync(dir).filter((f) => f.endsWith('.html') && f !== 'demo.html');
let bad = 0;
(async () => {
  for (const f of files) {
    const errors = [];
    const vc = new VirtualConsole();
    vc.on('jsdomError', (e) => errors.push(String(e.message || e).slice(0, 300)));
    vc.on('error', (e) => errors.push('console.error: ' + String(e).slice(0, 300)));
    const html = fs.readFileSync(path.join(dir, f), 'utf8');
    const dom = new JSDOM(html, {
      runScripts: 'dangerously', pretendToBeVisual: true, virtualConsole: vc, url: 'file://' + path.join(dir, f),
      beforeParse(w) {
        const ctxStub = new Proxy({}, { get: (t, k) => (k === 'createRadialGradient' || k === 'createLinearGradient') ? () => ({ addColorStop() {} }) : (typeof k === 'string' ? (t[k] !== undefined ? t[k] : () => {}) : undefined), set: (t, k, v) => { t[k] = v; return true; } });
        w.HTMLCanvasElement.prototype.getContext = function () { return ctxStub; };
        w.IntersectionObserver = class { constructor(cb) { this.cb = cb; } observe(el) { setTimeout(() => this.cb([{ isIntersecting: true, target: el }]), 10); } disconnect() {} };
        w.matchMedia = () => ({ matches: false, addListener() {}, removeListener() {} });
        w.HTMLFormElement.prototype.requestSubmit = function () { this.dispatchEvent(new w.Event('submit', { bubbles: true, cancelable: true })); };
      }
    });
    await new Promise((r) => setTimeout(r, 400));
    const w = dom.window, d = w.document;
    const app = d.getElementById('app');
    const n = app ? app.querySelectorAll('*').length : 0;
    const leftover = app ? (app.innerHTML.match(/\{\{[^}]*\}\}/g) || []).slice(0, 3) : [];
    // click every button once
    let clicks = 0;
    const btns = Array.from(app.querySelectorAll('button')).slice(0, 60);
    for (const b of btns) { try { b.dispatchEvent(new w.MouseEvent('click', { bubbles: true })); clicks++; } catch (e) { errors.push('click: ' + e.message); } }
    const ranges = Array.from(app.querySelectorAll('input[type=range]'));
    for (const r of ranges) { r.value = r.max || '50'; r.dispatchEvent(new w.Event('input', { bubbles: true })); }
    const search = app.querySelector('input[type=search]');
    if (search) { search.value = 'valik'; search.dispatchEvent(new w.Event('input', { bubbles: true })); }
    await new Promise((r) => setTimeout(r, 300));
    const n2 = app.querySelectorAll('*').length; fs.writeFileSync('/tmp/claude-0/-home-claude/5b42eb50-a236-517b-b824-7bac48acbc6a/scratchpad/out-i18n/txt-' + path.basename(dir) + '-' + f + '.txt', app.textContent + '\n' + Array.from(app.querySelectorAll('[placeholder],[aria-label],[title]')).map(e => (e.getAttribute('placeholder')||'') + ' ' + (e.getAttribute('aria-label')||'') + ' ' + (e.getAttribute('title')||'')).join('\n'));
    const ok = errors.length === 0 && n > 20 && leftover.length === 0;
    if (!ok) bad++;
    console.log((ok ? 'OK  ' : 'FAIL') + ' ' + f + ' nodes=' + n + '→' + n2 + ' buttons=' + clicks + ' ranges=' + ranges.length + (leftover.length ? ' LEFTOVER ' + leftover.join(' ') : '') + (errors.length ? '\n   ' + errors.slice(0, 4).join('\n   ') : ''));
    w.close();
  }
  console.log(bad ? bad + ' failing' : 'all pages ok');
})();
