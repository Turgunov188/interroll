import html, json, os, re, shutil, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from i18n_apply import translate
import dynamic_patches
import site_tokens
import i18n_apply

SRC = sys.argv[1]
OUT = sys.argv[2]
TOOLS = os.path.dirname(os.path.abspath(__file__))

PAGES = [
    ('Variant-Birlashgan', 'index.html', 'Bosh sahifa'),
    ('Katalog-G', 'catalog.html', 'Katalog'),
    ('Mahsulot-G', 'product.html', 'Mahsulot — konfigurator'),
    ('Qoplash-G', 'recoating.html', 'Qayta qoplash'),
    ('Zavod-G', 'factory.html', 'Zavod haqida'),
    ('Aloqa-G', 'contact.html', 'Aloqa'),
    ('Usta-G', 'part-finder.html', 'Detal tanlash ustasi'),
    ('Kuzatish-G', 'order-tracking.html', 'Buyurtmani kuzatish'),
    ('Portfolio-G', 'projects.html', 'Bajarilgan ishlar'),
    ('Video-G', 'video.html', 'Video'),
    ('Blog-G', 'blog.html', 'Blog'),
    ('Maqola-G', 'blog-shore-a-hardness.html', 'Blog — maqola'),
    ('Maqola-G', 'blog-post.html', 'Blog — maqola (bazadan)'),
]
ROUTE = {'index.html': '/', 'blog-shore-a-hardness.html': '/blog/shore-a-hardness', 'blog-post.html': '/blog'}  # URLs in English: /catalog, /product/…
def route_of(out):
    return ROUTE.get(out, '/' + out[:-5])
LINK = {}
for _src, _out, _ in PAGES:
    LINK.setdefault(_src + '.dc.html', route_of(_out))   # first page of a design wins (Maqola-G → the Shore A article)
LEAD_TYPE = {'index.html': 'quote', 'catalog.html': 'quote', 'product.html': 'quote',
             'recoating.html': 'recoating', 'part-finder.html': 'wizard', 'order-tracking.html': 'none'}

FONT_CSS = """@font-face{font-family:'Archivo';font-weight:500;font-display:swap;src:url(/fonts/site/archivo-latin-500-normal.woff2) format('woff2')}
@font-face{font-family:'Archivo';font-weight:700;font-display:swap;src:url(/fonts/site/archivo-latin-700-normal.woff2) format('woff2')}
@font-face{font-family:'Archivo';font-weight:800;font-display:swap;src:url(/fonts/site/archivo-latin-800-normal.woff2) format('woff2')}
@font-face{font-family:'Archivo';font-weight:500;font-display:swap;src:url(/fonts/site/archivo-latin-ext-500-normal.woff2) format('woff2');unicode-range:U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF,U+0304,U+0308,U+0329,U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF}
@font-face{font-family:'Archivo';font-weight:700;font-display:swap;src:url(/fonts/site/archivo-latin-ext-700-normal.woff2) format('woff2');unicode-range:U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF,U+0304,U+0308,U+0329,U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF}
@font-face{font-family:'Archivo';font-weight:800;font-display:swap;src:url(/fonts/site/archivo-latin-ext-800-normal.woff2) format('woff2');unicode-range:U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF,U+0304,U+0308,U+0329,U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF}
@font-face{font-family:'JetBrains Mono';font-weight:400;font-display:swap;src:url(/fonts/site/jetbrains-mono-latin-400-normal.woff2) format('woff2')}
@font-face{font-family:'JetBrains Mono';font-weight:600;font-display:swap;src:url(/fonts/site/jetbrains-mono-latin-600-normal.woff2) format('woff2')}
@font-face{font-family:'JetBrains Mono';font-weight:400;font-display:swap;src:url(/fonts/site/jetbrains-mono-latin-ext-400-normal.woff2) format('woff2');unicode-range:U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF,U+0304,U+0308,U+0329,U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF}
@font-face{font-family:'JetBrains Mono';font-weight:600;font-display:swap;src:url(/fonts/site/jetbrains-mono-latin-ext-600-normal.woff2) format('woff2');unicode-range:U+0100-02BA,U+02BD-02C5,U+02C7-02CC,U+02CE-02D7,U+02DD-02FF,U+0304,U+0308,U+0329,U+1D00-1DBF,U+1E00-1E9F,U+1EF2-1EFF,U+2020,U+20A0-20AB,U+20AD-20C0,U+2113,U+2C60-2C7F,U+A720-A7FF}
@font-face{font-family:'JetBrains Mono';font-weight:400;font-display:swap;src:url(/fonts/site/jetbrains-mono-cyrillic-400-normal.woff2) format('woff2');unicode-range:U+0301,U+0400-045F,U+0490-0491,U+04B0-04B1,U+2116}
@font-face{font-family:'JetBrains Mono';font-weight:600;font-display:swap;src:url(/fonts/site/jetbrains-mono-cyrillic-600-normal.woff2) format('woff2');unicode-range:U+0301,U+0400-045F,U+0490-0491,U+04B0-04B1,U+2116}
"""

LANG_ORDER = ['uz', 'ru', 'en', 'tr']
PREFIX = {'ru': '', 'uz': '/uz', 'en': '/en', 'tr': '/tr'}   # Russian is the main language (no prefix)
TITLES = {
    'index.html': ('Inter Roll — poliuretan va rezina detallar zavodi', 'Inter Roll — завод полиуретановых и резиновых деталей', 'Inter Roll — polyurethane and rubber parts factory', 'Inter Roll — poliüretan ve kauçuk parça fabrikası'),
    'catalog.html': ('Inter Roll — Katalog', 'Inter Roll — Каталог', 'Inter Roll — Catalog', 'Inter Roll — Katalog'),
    'product.html': ('__PAGE_TITLE__ — Inter Roll',) * 4,
    'recoating.html': ('Inter Roll — Valiklarni qayta qoplash', 'Inter Roll — Восстановление покрытия валов', 'Inter Roll — Roller recoating', 'Inter Roll — Merdane yeniden kaplama'),
    'factory.html': ('Inter Roll — Zavod haqida', 'Inter Roll — О заводе', 'Inter Roll — About the factory', 'Inter Roll — Fabrika hakkında'),
    'contact.html': ('Inter Roll — Aloqa', 'Inter Roll — Контакты', 'Inter Roll — Contact', 'Inter Roll — İletişim'),
    'part-finder.html': ('Inter Roll — Detal tanlash ustasi', 'Inter Roll — Мастер подбора детали', 'Inter Roll — Part selection wizard', 'Inter Roll — Parça seçim sihirbazı'),
    'order-tracking.html': ('Inter Roll — Buyurtmani kuzatish', 'Inter Roll — Отслеживание заказа', 'Inter Roll — Order tracking', 'Inter Roll — Sipariş takibi'),
    'projects.html': ('Inter Roll — Bajarilgan ishlar', 'Inter Roll — Выполненные работы', 'Inter Roll — Completed projects', 'Inter Roll — Tamamlanan işler'),
    'video.html': ('Inter Roll — Video', 'Inter Roll — Видео', 'Inter Roll — Video', 'Inter Roll — Video'),
    'blog.html': ('Inter Roll — Blog', 'Inter Roll — Блог', 'Inter Roll — Blog', 'Inter Roll — Blog'),
    # __PAGE_TITLE__: filled by the server (product / article name)
    'blog-post.html': ('__PAGE_TITLE__ — Inter Roll',) * 4,
    'blog-shore-a-hardness.html': ('Shore A qattiqlikni qanday tanlash kerak — Inter Roll', 'Как выбрать твёрдость по Шору A — Inter Roll', 'How to choose Shore A hardness — Inter Roll', 'Shore A sertliği nasıl seçilir — Inter Roll'),
}


def url_for(route, lang):
    p = PREFIX[lang]
    return (p or '/') if route == '/' else p + route


def switcher(lang, route, wrap, suffix=''):
    links = []
    for L in LANG_ORDER:
        style = 'font-weight: 600; border-bottom: 2px solid {{accent}}' if L == lang else 'color: #5B636D'
        links.append('<a href="%s" hreflang="%s" lang="%s" style="%s">%s</a>' % (url_for(route, L) + suffix, L, L, style, L.upper()))
    s = ''.join(links)
    return '<span class="mono" style="display: flex; gap: 12px; font-size: 14px">%s</span>' % s if wrap else s


def add_switcher(s, lang, route, suffix=''):
    pair = re.compile(r'<a href="[^"]*" style="[^"]*">UZ</a><a href="[^"]*" style="[^"]*">RU</a>')
    if pair.search(s):
        return pair.sub(lambda m: switcher(lang, route, False, suffix), s, count=1)
    i = s.index('<header')
    for anchor in ('</nav>', '>← Blog</a>'):
        j = s.find(anchor, i)
        if j > 0:
            j += len(anchor)
            return s[:j] + switcher(lang, route, True, suffix) + s[j:]
    raise SystemExit('no place for language switcher')


TABLE_TAGS = ['table', 'thead', 'tbody', 'tr', 'th', 'td']


def table_safe(s):
    for t in TABLE_TAGS:
        s = re.sub(r'<(/?)' + t + r'(?=[\s>/])', r'<\1x-' + t, s)
    return s


def fix_links(s, lang):
    def rep(m):
        target = m.group(2)
        base, _, frag = target.partition('#')
        if base in LINK:
            return m.group(1) + url_for(LINK[base], lang) + ('#' + frag if frag else '') + m.group(3)
        if base.endswith('.html') and not base.startswith('http'):
            return m.group(1) + url_for(route_of(base), lang) + ('#' + frag if frag else '') + m.group(3)
        return m.group(0)
    return re.sub(r'(href=")([^"]+?)(")', rep, s)


# ---------------- one main menu on every page (home + all sections, the rest under "More")
NAV_MAIN = ['/', '/catalog', '/recoating', '/projects', '/blog', '/factory', '/contact']
NAV_MORE = ['/video', '/part-finder']
# Pages switched off for now: not built, and every link to them is dropped. Remove from here to bring one back
# (and add it to config/site.php 'pages').
DISABLED = {'order-tracking.html'}
NAV_LABEL = {   # uz, ru, en, tr
    '/': ('Bosh sahifa', 'Главная', 'Home', 'Ana sayfa'),
    '/catalog': ('Mahsulotlar', 'Продукция', 'Products', 'Ürünler'),
    '/recoating': ('Qayta qoplash', 'Восстановление', 'Recoating', 'Yeniden kaplama'),
    '/projects': ('Ishlar', 'Работы', 'Projects', 'Projeler'),
    '/blog': ('Blog', 'Блог', 'Blog', 'Blog'),
    '/factory': ('Zavod', 'Завод', 'Factory', 'Fabrika'),
    '/contact': ('Aloqa', 'Контакты', 'Contact', 'İletişim'),
    '/video': ('Video', 'Видео', 'Video', 'Video'),
    '/part-finder': ('Detal tanlash ustasi', 'Мастер подбора', 'Part finder', 'Parça seçici'),
    '/order-tracking': ('Buyurtmani kuzatish', 'Отслеживание заказа', 'Order tracking', 'Sipariş takibi'),
    'more': ('Yana', 'Ещё', 'More', 'Daha fazla'),
    'aria': ('Asosiy menyu', 'Главное меню', 'Main menu', 'Ana menü'),
}
NAV_CSS = """.ir-nav{display:flex;flex-wrap:wrap;align-items:center;gap:6px 22px;font-size:13px;text-transform:uppercase}
.ir-nav a,.ir-more>button{text-decoration:none;padding:4px 0;border-bottom:2px solid transparent;white-space:nowrap}
.ir-nav a:hover,.ir-more>button:hover{border-bottom-color:#111418}
.ir-more{position:relative}
.ir-more>button{font:inherit;text-transform:inherit;letter-spacing:inherit;color:inherit;background:none;border-top:0;border-left:0;border-right:0;cursor:pointer;display:flex;align-items:center;gap:6px}
.ir-more>button svg{transition:transform .15s}
.ir-more:hover>button svg,.ir-more:focus-within>button svg{transform:rotate(180deg)}
.ir-menu{display:none;position:absolute;right:0;top:100%;padding-top:10px;z-index:50}
.ir-menu>div{background:#fff;border:1px solid #111418;box-shadow:4px 4px 0 #111418;min-width:240px;display:flex;flex-direction:column}
.ir-menu a{padding:12px 16px;border-bottom:1px solid #E3E6EA}
.ir-menu a:last-child{border-bottom:0}
.ir-menu a:hover{background:#F2F3F5;border-bottom-color:#E3E6EA}
.ir-more:hover .ir-menu,.ir-more:focus-within .ir-menu{display:block}
"""


def nav_active(out):
    route = route_of(out)
    if out == 'product.html':
        return '/catalog'
    if route.startswith('/blog'):
        return '/blog'
    return route


def main_nav(lang, out):
    i = LANG_ORDER.index(lang)
    act = nav_active(out)
    on = ' style="border-bottom-color: {{accent}}" aria-current="page"'
    def link(r):
        return '<a href="%s"%s>%s</a>' % (url_for(r, lang), on if r == act else '', html.escape(NAV_LABEL[r][i]))
    more_on = act in NAV_MORE
    caret = '<svg aria-hidden="true" width="10" height="6" viewBox="0 0 10 6" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 1l4 4 4-4"/></svg>'
    more = ('<div class="ir-more"><button type="button" aria-haspopup="true"%s>%s%s</button>'
            '<div class="ir-menu"><div>%s</div></div></div>') % (
        ' style="border-bottom-color: {{accent}}"' if more_on else '', html.escape(NAV_LABEL['more'][i]), caret,
        ''.join(link(r) for r in NAV_MORE))
    return '<nav aria-label="%s" class="mono ir-nav">%s%s</nav>' % (
        NAV_LABEL['aria'][i], ''.join(link(r) for r in NAV_MAIN), more)


def drop_disabled_links(body, lang):
    for out in DISABLED:
        href = re.escape(url_for(route_of(out), lang))
        body = re.sub(r'<a href="%s(?:#[^"]*)?"[^>]*>.*?</a>' % href, '', body, flags=re.S)
    return body


def unify_nav(body, lang, out):
    nav = main_nav(lang, out)
    hs = body.index('<header')
    he = body.index('</header>', hs)
    head = body[hs:he]
    n = 0
    head, n = re.subn(r'<nav aria-label="[^"]*" class="mono"[^>]*>.*?</nav>', lambda m: nav, head, count=1, flags=re.S)
    if not n:   # blog article: "← Blog" back link instead of a menu
        head, n = re.subn(r'<a href="[^"]*/blog" class="mono"[^>]*>← [^<]*</a>', lambda m: nav, head, count=1)
    assert n, 'no main menu in ' + out
    # order tracking had its own name as an extra header link — it is in the menu now
    head = re.sub(r'<a href="[^"]*/order-tracking" class="mono" style="font-size: 14px; text-transform: uppercase; border-bottom[^"]*">[^<]*</a>', '', head)
    # the logo always leads to the home page
    head = re.sub(r'^(<header.*?<a href=")[^"]*(")', lambda m: m.group(1) + url_for('/', lang) + m.group(2), head, count=1, flags=re.S)
    return body[:hs] + head + body[he:]


def build_page(src, out, title, runtime, lang, stats):
    s = open(os.path.join(SRC, src + '.dc.html'), encoding='utf-8').read()
    route = route_of(out)
    # Product pages live at /product/{slug}: the server fills __SUFFIX__ with "/slug".
    suffix = '__SUFFIX__' if out in ('product.html', 'blog-post.html') else ''
    s = add_switcher(s, lang, route, suffix)
    s = dynamic_patches.apply(src, s, out)
    s = site_tokens.apply(src, s)
    if lang != 'uz':
        s = translate(s, src, lang, stats)
    ptitle = TITLES[out][LANG_ORDER.index(lang)]
    alternates = ''.join('<link rel="alternate" hreflang="%s" href="__BASE__%s%s">\n' % (L, url_for(route, L), suffix) for L in LANG_ORDER)
    alternates += '<link rel="alternate" hreflang="x-default" href="__BASE__%s%s">' % (url_for(route, 'ru'), suffix)
    body = s[s.index('<x-dc>') + len('<x-dc>'): s.index('</x-dc>')]
    hm = re.search(r'<helmet>(.*?)</helmet>', body, re.S)
    helmet = hm.group(1) if hm else ''
    body = body[:hm.start()] + body[hm.end():] if hm else body
    helmet = re.sub(r'<link[^>]*fonts\.(googleapis|gstatic)\.com[^>]*>\s*', '', helmet)
    sm = re.search(r'<script type="text/x-dc" data-dc-script data-props=\'(.*?)\'>(.*?)</script>', s, re.S)
    props = json.loads(html.unescape(sm.group(1)))
    code = sm.group(2)
    body = site_tokens.to_placeholders(fix_links(table_safe(body), lang), script=False)
    code = site_tokens.to_placeholders(code, script=True)
    body = drop_disabled_links(unify_nav(body, lang, out), lang)
    mobile = src.startswith('M-')
    extra = NAV_CSS
    doc = f"""<!doctype html>
<html lang="{lang}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="__CSRF_TOKEN__">
<meta name="ir-lead-type" content="{LEAD_TYPE.get(out, 'contact')}">
<title>{html.escape(ptitle)}</title>
<link rel="canonical" href="__BASE__{url_for(route, lang)}{suffix}">
<script type="application/json" id="ir-data">__IR_DATA__</script>
{dynamic_patches.HEAD_EXTRA.get(src, '')}
{alternates}
<style>
{FONT_CSS}
</style>
{helmet}
<style>{extra}</style>
</head>
<body>
<div id="app"></div>
<template id="dc-template">{body}</template>
<script>
{runtime}
</script>
<script>
{code}
window.__mountDC(Component, {json.dumps(props, ensure_ascii=False)});
</script>
</body>
</html>
"""
    os.makedirs(os.path.join(OUT, 'resources', 'site', lang), exist_ok=True)
    open(os.path.join(OUT, 'resources', 'site', lang, out), 'w', encoding='utf-8').write(doc)


def main():
    # fonts are copied only when the font packages are present (a/, j/ from fontsource); otherwise public/fonts/site stays
    fonts = all(os.path.isdir(os.path.join(TOOLS, d)) for d in ('a', 'j'))
    for d in ('resources/site',) + (('public/fonts/site',) if fonts else ()):
        p = os.path.join(OUT, d)
        if os.path.exists(p):
            shutil.rmtree(p)
        os.makedirs(p)
    for d, pat in ([('a/package/files', 'archivo-latin'), ('j/package/files', 'jetbrains-mono-')] if fonts else []):
        for f in os.listdir(os.path.join(TOOLS, d)):
            if f.endswith('-normal.woff2') and re.search(r'(latin|latin-ext|cyrillic)-(400|500|600|700|800)-normal', f) and f.startswith(pat.split('-latin')[0]):
                if re.match(r'^(archivo-(latin|latin-ext)-(500|700|800)|jetbrains-mono-(latin|latin-ext|cyrillic)-(400|600))-normal\.woff2$', f):
                    shutil.copy(os.path.join(TOOLS, d, f), os.path.join(OUT, 'public', 'fonts', 'site', f))
    runtime = open(os.path.join(TOOLS, 'runtime-laravel.js'), encoding='utf-8').read()
    for lang in LANG_ORDER:
        stats = set()
        for src, out, title in PAGES:
            if out in DISABLED:
                continue
            build_page(src, out, title, runtime, lang, stats)
        print('built', lang, len(PAGES), 'pages,', len(stats), 'strings translated')
    if i18n_apply.UNMATCHED:
        print('note: strings with site tokens kept as written (no design translation):')
        for t in sorted(i18n_apply.UNMATCHED):
            print('   ', t)


main()
