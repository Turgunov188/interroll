/* Inter Roll — mini runtime: renders a Design Component template without any framework. */
(function () {
  'use strict';
  var TABLE_MAP = { 'x-table': 'table', 'x-thead': 'thead', 'x-tbody': 'tbody', 'x-tr': 'tr', 'x-th': 'th', 'x-td': 'td' };
  var SVG_NS = 'http://www.w3.org/2000/svg';
  var HOLE = /\{\{\s*([^}]+?)\s*\}\}/g;
  var WHOLE = /^\s*\{\{\s*([^}]+?)\s*\}\}\s*$/;

  function lookup(path, scope) {
    path = path.trim();
    if (path === 'true') return true;
    if (path === 'false') return false;
    if (path === 'null') return null;
    if (/^-?\d+(\.\d+)?$/.test(path)) return parseFloat(path);
    if (/^'.*'$|^".*"$/.test(path)) return path.slice(1, -1);
    var parts = path.split('.');
    var v = scope;
    for (var i = 0; i < parts.length; i++) {
      if (v == null) return undefined;
      v = v[parts[i]];
    }
    return v;
  }
  function interp(str, scope) {
    return str.replace(HOLE, function (_, p) { var v = lookup(p, scope); return v == null ? '' : String(v); });
  }
  function evalAttr(val, scope) {
    var m = WHOLE.exec(val);
    if (m) return lookup(m[1], scope);
    if (val.indexOf('{{') >= 0) return interp(val, scope);
    return val;
  }

  function eventName(attr, el) {
    var n = attr.slice(2);
    if (n === 'change') {
      var tag = el.localName, type = (el.getAttribute && el.getAttribute('type')) || '';
      if (tag === 'textarea' || (tag === 'input' && ['checkbox', 'radio', 'file'].indexOf(type) < 0)) return 'input';
      return 'change';
    }
    return n;
  }

  function createFrom(tpl) {
    var name = tpl.localName;
    if (tpl.namespaceURI === SVG_NS) return document.createElementNS(SVG_NS, tpl.localName);
    return document.createElement(TABLE_MAP[name] || name);
  }

  function renderNodes(tplParent, scope, out) {
    var kids = tplParent.childNodes;
    for (var i = 0; i < kids.length; i++) renderNode(kids[i], scope, out);
    return out;
  }

  function renderNode(t, scope, out) {
    if (t.nodeType === 3) {
      var s = t.nodeValue;
      out.push(document.createTextNode(s.indexOf('{{') >= 0 ? interp(s, scope) : s));
      return;
    }
    if (t.nodeType !== 1) return;
    var name = t.localName;
    if (name === 'sc-for') {
      var list = evalAttr(t.getAttribute('list') || '', scope) || [];
      var as = t.getAttribute('as') || 'item';
      for (var k = 0; k < list.length; k++) {
        var sub = Object.create(scope);
        sub[as] = list[k];
        sub.$index = k;
        renderNodes(t, sub, out);
      }
      return;
    }
    if (name === 'sc-if') {
      if (evalAttr(t.getAttribute('value') || '', scope)) renderNodes(t, scope, out);
      return;
    }
    // <sc-html value="{{html}}">: trusted HTML from the server (sanitized there), e.g. a blog post body.
    if (name === 'sc-html') {
      var holder = document.createElement('template');
      holder.innerHTML = String(evalAttr(t.getAttribute('value') || '', scope) || '');
      var nodes = holder.content.childNodes;
      for (var h = 0; h < nodes.length; h++) out.push(nodes[h].cloneNode(true));
      return;
    }
    var el = createFrom(t);
    var handlers = {};
    var props = {};
    for (var a = 0; a < t.attributes.length; a++) {
      var at = t.attributes[a];
      var an = at.name;
      if (an.indexOf('hint-') === 0) continue;
      if (an.length > 2 && an.slice(0, 2) === 'on') {
        var fn = evalAttr(at.value, scope);
        if (typeof fn === 'function') handlers[eventName(an, t)] = fn;
        continue;
      }
      var v = evalAttr(at.value, scope);
      if (an === 'checked') { props.checked = !!v; if (v) el.setAttribute('checked', ''); continue; }
      if (an === 'value' && (el.localName === 'input' || el.localName === 'textarea' || el.localName === 'select')) { props.value = v == null ? '' : String(v); el.setAttribute('value', props.value); continue; }
      if (v === false || v == null) continue;
      if (v === true) v = '';
      if (typeof v === 'object' || typeof v === 'function') continue;
      if (at.namespaceURI) el.setAttributeNS(at.namespaceURI, an, String(v));
      else el.setAttribute(an, String(v));
    }
    el.__h = handlers;
    el.__p = props;
    var children = renderNodes(t.content || t, scope, []);
    for (var c = 0; c < children.length; c++) el.appendChild(children[c]);
    out.push(el);
  }

  function bindHandlers(el) {
    if (!el.__bound) el.__bound = {};
    var h = el.__h || {};
    Object.keys(h).forEach(function (ev) {
      if (!el.__bound[ev]) {
        el.__bound[ev] = true;
        el.addEventListener(ev, function (e) { var f = el.__h && el.__h[ev]; if (f) f(e); });
      }
    });
  }
  function applyProps(el) {
    var p = el.__p || {};
    if ('checked' in p && el.checked !== p.checked) el.checked = p.checked;
    if ('value' in p && el.value !== p.value) {
      if (el.localName === 'select') { el.value = p.value; }
      else if (document.activeElement !== el || el.type === 'range') el.value = p.value;
    }
  }
  function same(a, b) {
    if (a.nodeType !== b.nodeType) return false;
    if (a.nodeType === 3) return true;
    return a.localName === b.localName && a.namespaceURI === b.namespaceURI;
  }
  function patchEl(oldEl, newEl) {
    var oa = oldEl.attributes, na = newEl.attributes, i;
    var isCanvas = oldEl.localName === 'canvas';
    for (i = oa.length - 1; i >= 0; i--) {
      var nm = oa[i].name;
      // canvas width/height are set by the animation code, not the template — keep them,
      // otherwise the bitmap resets to 300x150 and gets stretched (blurry, oversized mesh)
      if (isCanvas && (nm === 'width' || nm === 'height')) continue;
      if (!newEl.hasAttribute(nm)) oldEl.removeAttribute(nm);
    }
    for (i = 0; i < na.length; i++) { if (oldEl.getAttribute(na[i].name) !== na[i].value) oldEl.setAttribute(na[i].name, na[i].value); }
    oldEl.__h = newEl.__h; oldEl.__p = newEl.__p;
    bindHandlers(oldEl);
    if (oldEl.localName !== 'canvas') patchChildren(oldEl, Array.prototype.slice.call(newEl.childNodes));
    applyProps(oldEl);
  }
  function prepare(node) {
    if (node.nodeType !== 1) return;
    bindHandlers(node);
    for (var c = node.firstChild; c; c = c.nextSibling) prepare(c);
    applyProps(node);
  }
  function patchChildren(parent, list) {
    var i = 0;
    for (; i < list.length; i++) {
      var cur = parent.childNodes[i];
      var nw = list[i];
      if (!cur) { prepare(nw); parent.appendChild(nw); continue; }
      if (same(cur, nw)) {
        if (cur.nodeType === 3) { if (cur.nodeValue !== nw.nodeValue) cur.nodeValue = nw.nodeValue; }
        else patchEl(cur, nw);
      } else { prepare(nw); parent.replaceChild(nw, cur); }
    }
    while (parent.childNodes.length > list.length) parent.removeChild(parent.lastChild);
  }

  function DCLogic(props) { this.props = props || {}; this.state = {}; }
  DCLogic.prototype.setState = function (patch, cb) {
    var p = typeof patch === 'function' ? patch(this.state, this.props) : patch;
    if (p) this.state = Object.assign({}, this.state, p);
    if (this.__schedule) this.__schedule();
    if (cb) cb();
  };
  DCLogic.prototype.forceUpdate = function () { if (this.__schedule) this.__schedule(); };
  window.DCLogic = DCLogic;

  function demoToast(msg) {
    var t = document.createElement('div');
    t.setAttribute('role', 'status');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;left:50%;bottom:28px;transform:translateX(-50%);z-index:9999;background:#111418;color:#fff;border:1px solid #111418;box-shadow:5px 5px 0 #F5B700;padding:14px 20px;font:600 14px "JetBrains Mono",monospace;transition:opacity .4s';
    document.body.appendChild(t);
    setTimeout(function () { t.style.opacity = '0'; }, 2400);
    setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 2900);
  }
  // ---- Forms → POST /leads (Laravel). Inputs have no name attributes, so fields are
  // recognised by type / placeholder / label text. Component state (cart, configurator,
  // wizard answers) is attached as JSON payload so managers see what was chosen.
  function meta(n) { var m = document.querySelector('meta[name="' + n + '"]'); return m ? m.getAttribute('content') : ''; }
  var LANG = ((document.documentElement.lang || 'ru').slice(0, 2).toLowerCase());
  if (['uz', 'ru', 'en', 'tr'].indexOf(LANG) < 0) LANG = 'ru';
  var T = {
    ru: { ok: '✓ Заявка отправлена. Мы свяжемся с вами.', phone: 'Укажите номер телефона', err: 'Не удалось отправить. Позвоните нам.', anon: 'С сайта' },
    uz: { ok: "✓ So'rov yuborildi. Tez orada bog'lanamiz.", phone: 'Telefon raqamini kiriting', err: "Yuborib bo'lmadi. Iltimos, qo'ng'iroq qiling.", anon: 'Saytdan' },
    en: { ok: '✓ Request sent. We will contact you shortly.', phone: 'Please enter your phone number', err: 'Could not send. Please call us.', anon: 'Website' },
    tr: { ok: '✓ Talebiniz gönderildi. En kısa sürede size ulaşacağız.', phone: 'Lütfen telefon numaranızı girin', err: 'Gönderilemedi. Lütfen bizi arayın.', anon: 'Web sitesi' }
  }[LANG];

  function safeState() {
    try {
      var st = window.__dc && window.__dc.state ? window.__dc.state : null;
      if (!st) return null;
      var json = JSON.stringify(st, function (k, v) {
        if (typeof v === 'function') return undefined;
        if (v && typeof v === 'object' && (v.nodeType || v === window)) return undefined;
        return v;
      });
      return json && json.length < 20000 ? json : null;
    } catch (err) { return null; }
  }

  function collect(f) {
    var fd = new FormData(), name = '', phone = '', company = '', email = '', msg = [];
    // A form for one product (data-lead-product) turns its sized fields (data-lead-option) into an item.
    var product = f.getAttribute('data-lead-product'), item = product ? { code: product, qty: 1, options: {} } : null;
    var els = f.querySelectorAll('input, textarea, select');
    for (var i = 0; i < els.length; i++) {
      var el = els[i], type = (el.getAttribute('type') || '').toLowerCase();
      if (type === 'file') { for (var j = 0; j < el.files.length; j++) fd.append('attachments[]', el.files[j]); continue; }
      // Structured data the page prepares for the CRM, e.g. <input type="hidden" data-lead="items" value="[...]">
      if (el.hasAttribute('data-lead')) { if (el.value) fd.append(el.getAttribute('data-lead'), el.value); continue; }
      if (item && el.hasAttribute('data-lead-option')) {
        var key = el.getAttribute('data-lead-option'), val = String(el.value || '').trim();
        if (key === 'qty') item.qty = Math.max(1, parseInt(val.replace(/[^0-9]/g, ''), 10) || 1);
        else if (val) item.options[key] = val;
        continue;
      }
      if (type === 'submit' || type === 'button' || type === 'hidden') continue;
      if ((type === 'checkbox' || type === 'radio') && !el.checked) continue;
      var v = String(el.value || '').trim();
      if (!v) continue;
      var ph = (el.getAttribute('placeholder') || '').toLowerCase();
      var lab = el.closest('label') ? el.closest('label').textContent.toLowerCase() : '';
      if (type === 'tel' || /telefon|телефон|phone|\+998/.test(ph)) { phone = phone || v; continue; }
      if (type === 'email' || /e-?mail|e-?posta|почта/.test(ph + ' ' + lab)) { email = email || v; continue; }
      if (/^(ism|ismingiz|имя|ваше имя|name|your name|ad\b|adınız)/.test(ph)) { name = name || v; continue; }
      if (/korxona|kompaniya|компани|организац|предприят|company|şirket/.test(ph + ' ' + lab)) { company = company || v; continue; }
      var title = el.closest('label') ? el.closest('label').firstChild && String(el.closest('label').firstChild.textContent || '').trim() : '';
      msg.push({ title: title || el.getAttribute('placeholder') || '', value: v });
    }
    fd.append('name', name || company || T.anon);
    fd.append('phone', phone);
    if (company) fd.append('company', company);
    if (email) fd.append('email', email);
    // One free-text field goes as is; several are labelled so the manager knows which is which.
    if (msg.length) fd.append('message', msg.length === 1 ? msg[0].value : msg.map(function (m) { return (m.title ? m.title + ': ' : '') + m.value; }).join('\n'));
    if (item && !fd.has('items')) fd.append('items', JSON.stringify([item]));
    fd.append('type', meta('ir-lead-type') || 'quote');
    fd.append('locale', LANG);
    fd.append('source', location.pathname + location.search);
    var st = safeState();
    if (st) fd.append('payload', st);
    return { fd: fd, phone: phone };
  }

  document.addEventListener('submit', function (e) {
    e.preventDefault();
    var f = e.target;
    var hasOwnUi = !!(f.__h && f.__h.submit);
    if (meta('ir-lead-type') === 'none' || f.hasAttribute('data-no-lead')) {
      if (!hasOwnUi) demoToast(T.ok);
      return;
    }
    var data = collect(f);
    if (!data.phone) { demoToast(T.phone); return; }
    fetch('/leads', {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': meta('csrf-token'), 'Accept': 'application/json' },
      body: data.fd,
      credentials: 'same-origin'
    }).then(function (r) {
      if (!r.ok) throw new Error('HTTP ' + r.status);
      return r.json().catch(function () { return {}; });
    }).then(function (j) {
      // Pages that show the request number ({{leadNo}}) read it from here.
      if (j && j.number) { window.__irLead = j.number; if (window.__dc && window.__dc.setState) window.__dc.setState({}); }
      if (!hasOwnUi) demoToast(T.ok);
      try { f.reset(); } catch (err) { }
    }).catch(function () { demoToast(T.err); });
  }, true);
  document.addEventListener('click', function (e) {
    var a = e.target && e.target.closest ? e.target.closest('a[href="#"]') : null;
    if (a) e.preventDefault();
  });

  // Data injected by Laravel (catalog, product). Static demo: placeholder stays → null.
  window.__IR = (function () {
    var el = document.getElementById('ir-data');
    if (!el) return null;
    try { return JSON.parse(el.textContent); } catch (err) { return null; }
  })();

  window.__mountDC = function (Component, defaults) {
    var tpl = document.getElementById('dc-template');
    var root = document.getElementById('app');
    var props = {};
    Object.keys(defaults || {}).forEach(function (k) { if (k.charAt(0) !== '$' && defaults[k] && 'default' in defaults[k]) props[k] = defaults[k].default; });
    var inst = new Component(props);
    if (!inst.state) inst.state = {};
    var pending = false;
    function render() {
      pending = false;
      var vals = inst.renderVals ? inst.renderVals() : {};
      var nodes = renderNodes(tpl.content, vals, []);
      patchChildren(root, nodes);
    }
    inst.__schedule = function () {
      if (pending) return;
      pending = true;
      (window.requestAnimationFrame || setTimeout)(render);
    };
    render();
    if (inst.componentDidMount) inst.componentDidMount();
    window.addEventListener('pagehide', function () { if (inst.componentWillUnmount) inst.componentWillUnmount(); });
    window.__dc = inst;
  };
})();
