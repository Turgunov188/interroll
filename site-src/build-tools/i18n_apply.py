"""Translate a .dc.html source page (Uzbek) into ru / en / tr.

Only exact matches are replaced: whole text nodes (or the fragments around {{holes}}),
a few attributes, and whole JS string literals (or fragments around ${...}).
Every occurrence of the same literal in a page gets the same translation, so the
strings the page logic compares stay consistent.
"""
import html, json, os, re

I18N = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'i18n')
LANGS = ('ru', 'en', 'tr')

# Short JS literals that are labels on these pages (elsewhere the same words are ids).
JS_EXTRA = {
    'Portfolio-G': {'dona': ('шт.', 'pcs', 'adet'), 'tayyorlash': ('изготовление', 'production', 'üretim'),
                    'uzoqroq': ('дольше', 'longer', 'daha uzun'), 'tejash': ('экономия', 'savings', 'tasarruf'),
                    'rolik': ('роликов', 'rollers', 'makara'), 'muddat': ('срок', 'lead time', 'süre'),
                    'yuk': ('нагрузка', 'load', 'yük'), 'model': ('моделей', 'models', 'model')},
    'Mahsulot-G': {'dona': ('шт.', 'pcs', 'adet'), 'valik': ('вал', 'roller', 'merdane')},
    'Kuzatish-G': {'kutilmoqda': ('ожидается', 'pending', 'bekleniyor')},
    'Aloqa-G': {'soat': ('ч', 'h', 'sa'), 'daqiqa': ('мин', 'min', 'dk'), 'MFO': ('МФО', 'MFO', 'MFO'), 'OKED': ('ОКЭД', 'OKED', 'OKED'),
                ':00 da ochiladi · ariza qoldiring': (':00 — откроемся · оставьте заявку', ':00 — we open · leave a request', ':00 — açılıyoruz · talep bırakın')},
    'Qoplash-G': {'1 kun': ('1 день', '1 day', '1 gün'), 'Qayta qoplash jarayoni:': ('Процесс восстановления:', 'Recoating process:', 'Yeniden kaplama süreci:'),
                  'past': ('низкая', 'low', 'düşük'), 'yaxshi': ('хорошая', 'good', 'iyi')},
    'Usta-G': {'past': ('низкая', 'low', 'düşük'), 'yaxshi': ('хорошая', 'good', 'iyi')},
}


def load(lang):
    d = json.load(open(os.path.join(I18N, lang + '.json'), encoding='utf-8'))
    extra = {}
    i = LANGS.index(lang)
    for line in open(os.path.join(I18N, 'extra.tsv'), encoding='utf-8'):
        p = line.rstrip('\n').split('\t')
        if len(p) == 4:
            extra[p[0]] = p[1 + i]
    return d, extra


TOKEN = re.compile(r'⟦[a-z0-9_]+(?::img)?⟧')
# what a token stood for in the design: a phone mask or any [bracketed gap]
GAP = r'(?:\+998 \[__\] ___-__-__|\[[^\[\]\n]*\])'
_TOKENS_TSV = None
UNMATCHED = set()   # tokenized strings with no design counterpart in the dictionary, reported by build_i18n


def _tokens_tsv(lang):
    """Hand translations for strings whose gaps were reshaped by a token (i18n/tokens.tsv)."""
    global _TOKENS_TSV
    if _TOKENS_TSV is None:
        _TOKENS_TSV = {}
        path = os.path.join(I18N, 'tokens.tsv')
        if os.path.exists(path):
            for line in open(path, encoding='utf-8'):
                p = line.rstrip('\n').split('\t')
                if len(p) == 4 and not line.startswith('#'):
                    _TOKENS_TSV[p[0]] = p[1:]
    v = _TOKENS_TSV.get
    return lambda t: (v(t) or [None, None, None])[LANGS.index(lang)]


def _with_tokens(lookup, keys, lang, missing):
    """Translate a string that contains ⟦tokens⟧: find the design string it came from
    (tokens in place of gaps), translate that, and put the tokens back into the same gaps."""
    by_hand = _tokens_tsv(lang)

    def look(t):
        if '⟦' not in t:
            return lookup(t)
        hand = by_hand(t)
        if hand is not None:
            return hand
        toks = TOKEN.findall(t)
        if not re.sub(r'[\W\d_]+', '', TOKEN.sub('', t)):
            return None                      # only tokens, numbers and punctuation: nothing to translate
        parts = re.split('(' + TOKEN.pattern + ')', t)
        pat = re.compile(''.join(GAP if TOKEN.fullmatch(x) else re.escape(x) for x in parts if x))
        cands = [k for k in keys if pat.fullmatch(k)]
        if not cands:
            UNMATCHED.add(t)                 # the design string was not translated either (or reshaped: see tokens.tsv)
            return None
        if len(cands) == 1:
            v = lookup(cands[0])
            if v is not None:
                gaps = list(re.finditer(GAP, v))
                if len(gaps) == len(toks):
                    out, pos = [], 0
                    for g, tok in zip(gaps, toks):
                        out.append(v[pos:g.start()] + tok)
                        pos = g.end()
                    return ''.join(out) + v[pos:]
        missing.add(t)
        return None
    return look


def _swap(raw_part, lookup, escape):
    """raw_part is source text (already unescaped); keep surrounding whitespace."""
    m = re.match(r'^(\s*)(.*?)(\s*)$', raw_part, re.S)
    t = lookup(m.group(2))
    if t is None:
        return None
    return m.group(1) + escape(t) + m.group(3)


def translate(src, page, lang, stats=None):
    d, extra = load(lang)
    li = LANGS.index(lang)
    js_extra = {k: v[li] for k, v in JS_EXTRA.get(page, {}).items()}
    hit = set()

    def look_text(t):
        v = d.get(t, extra.get(t))
        if v is not None:
            hit.add(t)
        return v

    def look_js(t):
        v = d.get(t, js_extra.get(t))
        if v is not None:
            hit.add(t)
        return v

    missing = set()
    look_text = _with_tokens(look_text, list(d) + list(extra), lang, missing)
    look_js = _with_tokens(look_js, list(d) + list(js_extra), lang, missing)

    start = src.index('<x-dc>')
    end = src.index('</x-dc>')
    body = src[start:end]

    # keep helmet / style / script blocks untouched (svg <text> labels are translated)
    keep = []
    def stash(m):
        keep.append(m.group(0))
        return '<\x00%d\x00>' % (len(keep) - 1)
    body = re.sub(r'<helmet>.*?</helmet>|<style.*?</style>|<script.*?</script>', stash, body, flags=re.S)

    def text_node(m):
        raw = m.group(1)
        pieces = re.split(r'(\{\{[^}]*\}\})', raw)
        out = []
        for pc in pieces:
            if pc.startswith('{{') or not pc.strip():
                out.append(pc)
                continue
            r = _swap(html.unescape(pc), look_text, lambda s: html.escape(s, quote=False))
            out.append(pc if r is None else r)
        return '>' + ''.join(out) + '<'
    body = re.sub(r'>([^<>]+)<', text_node, body)

    def attr(m):
        val = m.group(2)
        if '{{' in val:
            return m.group(0)
        r = _swap(html.unescape(val), look_text, lambda s: html.escape(s, quote=True))
        return m.group(0) if r is None else '%s="%s"' % (m.group(1), r)
    body = re.sub(r'\b(placeholder|aria-label|title|alt)="([^"]*)"', attr, body)

    body = re.sub(r'<\x00(\d+)\x00>', lambda m: keep[int(m.group(1))], body)
    src = src[:start] + body + src[end:]

    # JS component code
    sm = re.search(r"(data-dc-script data-props='.*?'>)(.*?)(</script>)", src, re.S)
    code = sm.group(2)

    def js_lit(m):
        q = m.group(0)[0]
        inner = m.group(0)[1:-1]
        def esc(s):
            s = s.replace('\\', '\\\\')
            return s.replace(q, '\\' + q) if q != '`' else s.replace('`', '\\`').replace('${', '\\${')
        def unesc(s):
            return re.sub(r'\\(.)', r'\1', s)
        if q == '`':
            parts = re.split(r'(\$\{[^}]*\})', inner)
            out = []
            for pc in parts:
                if pc.startswith('${') or not pc.strip():
                    out.append(pc)
                    continue
                r = _swap(unesc(pc), look_js, esc)
                out.append(pc if r is None else r)
            return q + ''.join(out) + q
        r = _swap(unesc(inner), look_js, esc)
        return m.group(0) if r is None else q + r + q
    code = re.sub(r"'(?:[^'\\\n]|\\.)*'|\"(?:[^\"\\\n]|\\.)*\"|`[^`]*`", js_lit, code)
    src = src[:sm.start(2)] + code + src[sm.end(2):]
    if missing:
        raise SystemExit('i18n (%s, %s): no translation for strings with site tokens — add them to i18n/tokens.tsv:\n  %s'
                         % (page, lang, '\n  '.join(sorted(missing))))
    if stats is not None:
        stats.update(hit)
    return src
