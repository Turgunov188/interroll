"""Make the catalog and product templates read data injected by Laravel.

The server puts JSON into <script type="application/json" id="ir-data"> and the
runtime exposes it as window.__IR. When the page is opened without the server
(static demo) window.__IR is null and the original hard-coded content is used.

Every patch must match exactly once, so a design change that breaks a patch
fails the build instead of silently shipping static content.
"""


def _sub(s, old, new, count=1):
    n = s.count(old)
    if n != count:
        raise SystemExit('dynamic patch: expected %d match(es), found %d: %r' % (count, n, old[:80]))
    return s.replace(old, new)


def katalog(s):
    # --- logic
    s = _sub(s, "    const all = [\n", "    const IR = window.__IR || null;\n    const all = (IR && IR.products) || [\n")
    s = _sub(s, "const cats = [['all', 'Barchasi'], ", "const cats = IR ? [['all', 'Barchasi']].concat(IR.cats) : [['all', 'Barchasi'], ")
    s = _sub(s, "        icon: icons[p.cat],\n        matL: p.mat === 'pu' ? 'Poliuretan' : 'Rezina',",
             "        icon: icons[p.cat] || icons.maxsus,\n        matL: p.matL || (p.mat === 'pu' ? 'Poliuretan' : 'Rezina'),\n"
             "        url: p.url || '#',\n        hasImg: !!p.img,")
    s = _sub(s, "icon: icons[p.cat], inc:", "icon: icons[p.cat] || icons.maxsus, inc:")
    # --- industry filter («Отрасль»): from the database; a product matches if it has any checked industry
    s = _sub(s, "this.state = { cat: 'all', q: '', pu: true, rz: true, shore: 'all',", "this.state = { cat: 'all', q: '', pu: true, rz: true, shore: 'all', ind: [],")
    # a section can be opened by link: /catalog?cat=valik (home page list)
    s = _sub(s, "super(p); this.state = { cat: 'all',", "super(p); this.state = { cat: (function () { try { return new URLSearchParams(location.search).get('cat') || 'all'; } catch (e) { return 'all'; } })(),")
    s = _sub(s, "    const q = this.state.q.trim().toLowerCase();\n",
             "    const inds = (IR && IR.industries) || [['metallurgiya', 'Metallurgiya'], ['poligrafiya', 'Poligrafiya'], ['oziq-ovqat', 'Oziq-ovqat'], ['avtomobil', 'Avtomobil'], ['ombor', 'Ombor va logistika']];\n"
             "    const sel = this.state.ind;\n"
             "    const indOk = (p) => !sel.length || !p.inds || p.inds.some((x) => sel.indexOf(x) >= 0);\n"
             "    const q = this.state.q.trim().toLowerCase();\n")
    s = _sub(s, "const base = (p) => (this.state.pu || p.mat !== 'pu')", "const base = (p) => indOk(p) && (this.state.pu || p.mat !== 'pu')")
    s = _sub(s, "reset: () => bump({ cat: 'all', q: '', pu: true, rz: true, shore: 'all' }),",
             "reset: () => bump({ cat: 'all', q: '', pu: true, rz: true, shore: 'all', ind: [] }),\n"
             "      hasInds: inds.length > 0,\n"
             "      leadItems: JSON.stringify(Object.keys(this.state.cart).map((code) => ({ code, qty: this.state.cart[code] }))),\n"
             "      inds: inds.map(([k, l]) => ({ label: l, on: sel.indexOf(k) >= 0, toggle: () => bump({ ind: sel.indexOf(k) >= 0 ? sel.filter((x) => x !== k) : sel.concat([k]) }) })),")
    s = _wrap(s, '<fieldset style="border: 0; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px">\n<legend class="mono" style="font-size: 12px; text-transform: uppercase; color: #5B636D; margin-bottom: 10px">Soha</legend>',
              '</fieldset>', '<sc-if value="{{hasInds}}" hint-placeholder-val="{{ true }}">', '</sc-if>')
    s = _sub(s, '<label class="chk"><input type="checkbox"> Metallurgiya</label>\n<label class="chk"><input type="checkbox"> Poligrafiya</label>\n'
                '<label class="chk"><input type="checkbox"> Oziq-ovqat</label>\n<label class="chk"><input type="checkbox"> Avtomobil</label>\n'
                '<label class="chk"><input type="checkbox"> Ombor va logistika</label>',
             '<sc-for list="{{inds}}" as="in" hint-placeholder-count="5"><label class="chk"><input type="checkbox" checked="{{in.on}}" onChange="{{in.toggle}}"> {{in.label}}</label></sc-for>')

    s = _sub(s, '<form onSubmit="{{sendCart}}" style="padding: 22px; display: flex; flex-direction: column; gap: 14px">',
             '<form onSubmit="{{sendCart}}" style="padding: 22px; display: flex; flex-direction: column; gap: 14px">'
             '<input type="hidden" data-lead="items" value="{{leadItems}}">')

    # --- template: card links and photo
    s = _sub(s, '<a href="Mahsulot-G.dc.html" class="ph" style="height: 190px;', '<a href="{{p.url}}" class="ph" style="overflow: hidden; height: 190px;')
    s = _sub(s, '<a href="Mahsulot-G.dc.html" style="font-weight: 800', '<a href="{{p.url}}" style="font-weight: 800') if '<a href="Mahsulot-G.dc.html" style="font-weight: 800' in s else _card_name_link(s)
    s = _sub(s, '[FOTO]</span>\n', '[FOTO]</span>\n<sc-if value="{{p.hasImg}}" hint-placeholder-val="{{ false }}"><img src="{{p.img}}" alt="{{p.name}}" loading="lazy" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover"></sc-if>\n')
    return s


def _card_name_link(s):
    i = s.index('<sc-for list="{{items}}"')
    j = s.index('</sc-for>', i)
    block = s[i:j]
    if block.count('href="Mahsulot-G.dc.html"') != 1:
        raise SystemExit('dynamic patch: catalog card name link not found')
    return s[:i] + block.replace('href="Mahsulot-G.dc.html"', 'href="{{p.url}}"') + s[j:]


def _wrap(s, start_marker, end_marker, before, after):
    """Wrap the text from start_marker (inclusive) to end_marker (inclusive) in before/after."""
    i = s.index(start_marker)
    if s.count(start_marker) != 1:
        raise SystemExit('dynamic patch: start marker not unique: %r' % start_marker[:60])
    j = s.index(end_marker, i) + len(end_marker)
    return s[:i] + before + s[i:j] + after + s[j:]


import os as _os
SHAPES_JS = open(_os.path.join(_os.path.dirname(_os.path.abspath(__file__)), 'shapes.js'), encoding='utf-8').read()


def mahsulot(s):
    """Product page. Views follow the product's configurator and media:
    Chizma — live drawing for configurable products (roller or a shape from shapes.js), otherwise uploaded drawings;
    3D — uploaded .glb model (model-viewer), otherwise the live CSS 3D of the configurator;
    Foto — uploaded photos. Size controls: roller sliders, or the parameters sent by the server for shapes."""
    # ---------------- logic
    s = _sub(s, "class Component extends DCLogic {", SHAPES_JS + "\nclass Component extends DCLogic {")
    s = _sub(s, "this.state = { view: 'draw', mat: 'pu', shore: 90,",
             "this.state = { product: (window.__IR && window.__IR.product && window.__IR.product.code) || 'IR-V01', sp: {}, view: 'draw', "
             "mat: (window.__IR && window.__IR.product && window.__IR.product.mat === 'rz') ? 'nbr' : 'pu', "
             "shore: (window.__IR && window.__IR.product && window.__IR.product.hardness) || 90,")
    s = _sub(s, "    const st = this.state;\n",
             "    const st = this.state;\n"
             "    const IR = window.__IR || null;\n"
             "    const P = IR && IR.product;\n"
             "    const CFG = !P || !!P.configurator;\n"
             "    const ROLLER = !P || P.configurator === 'roller';\n"
             "    const SHAPE = P && P.configurator && P.configurator !== 'roller' && IRShapes.has(P.configurator) ? P.configurator : null;\n"
             "    const SP = SHAPE ? IRShapes.values(SHAPE, P.params, st.sp) : null;\n"
             "    const PARAM_STR = SHAPE ? P.params.map((d) => d.key + ' ' + SP[d.key]).join(' × ') + ' mm' : '';\n"
             "    const M = (P && P.media) || { photos: [], drawings: [], model: null, documents: [] };\n"
             "    const DOCS = (P && P.docs) || [];\n"
             "    const avail = [];\n"
             "    if (CFG || M.drawings.length) avail.push('draw');\n"
             "    if (CFG || M.model) avail.push('3d');\n"
             "    if (!P || M.photos.length || !avail.length) avail.push('foto');\n"
             "    const view = avail.indexOf(st.view) >= 0 ? st.view : avail[0];\n")
    s = _sub(s, "const summary = 'IR-V01 Poliuretan valik · ' +",
             "const summary = (P && !CFG) ? P.code + ' ' + P.name + ' · ' + st.qty + ' dona'"
             " : SHAPE ? P.code + ' ' + P.name + ' · ' + matNames[st.mat][1] + ' · ' + st.shore + ' Shore A · ' + PARAM_STR + ' · ' + st.qty + ' dona'"
             " : (P ? P.code + ' ' + P.name + ' · ' : 'IR-V01 Poliuretan valik · ') +")
    s = _sub(s, "views: [['draw', 'Chizma'], ['3d', '3D'], ['foto', 'Foto']].map(", "views: [['draw', 'Chizma'], ['3d', '3D'], ['foto', 'Foto']].filter(([k2]) => avail.indexOf(k2) >= 0).map(")
    s = _sub(s, "cursor:pointer;' + (st.view === k2 ?", "cursor:pointer;' + (view === k2 ?")
    s = _sub(s, "isDraw: st.view === 'draw', is3d: st.view === '3d', isFoto: st.view === 'foto',",
             "isDrawSvg: view === 'draw' && ROLLER, isDrawShape: view === 'draw' && !!SHAPE, isDrawMedia: view === 'draw' && !CFG,\n"
             "      is3dModel: view === '3d' && !!M.model, is3dCss: view === '3d' && !M.model && ROLLER, is3dShape: view === '3d' && !M.model && !!SHAPE,\n"
             "      isFoto: view === 'foto',")
    s = _sub(s, "tabs: [['spec', 'Xususiyatlar'], ['use', \"Qo'llanilishi\"], ['docs', 'Hujjatlar']].map(",
             "tabs: [['spec', 'Xususiyatlar'], ['use', \"Qo'llanilishi\"], ['docs', 'Hujjatlar']].filter(([k2]) => !P || (k2 === 'docs' ? DOCS.length : k2 === 'use' ? (P.uses || []).length : true)).map(")
    s = _sub(s, "      uses: [\n", "      uses: (P ? (P.uses || []) : null) || [\n")
    s = _sub(s, "      related: [\n", "      related: ((IR && IR.related) || [\n")
    s = _sub(s, "      ],\n      summary\n    };",
             "      ]).map((r) => Object.assign({ url: '#' }, r)),\n"
             "      configurable: CFG, notConfigurable: !CFG, isRoller: CFG && ROLLER, isShape: !!SHAPE,\n"
             "      leadItems: JSON.stringify([{ code: P ? P.code : 'IR-V01', qty: st.qty,"
             " options: !CFG ? {} : Object.assign({ mat: st.mat, shore: st.shore }, SHAPE ? SP : { D: D, L: L, T: T }) }]),\n"
             "      params: SHAPE ? P.params.map((d) => ({ label: d.label, unit: d.unit, min: d.min, max: d.max, step: d.step, value: SP[d.key],"
             " set: (e) => this.setState({ sp: Object.assign({}, st.sp, { [d.key]: +e.target.value }) }) })) : [],\n"
             "      chips: SHAPE ? IRShapes.chips(SHAPE, SP) : [],\n"
             "      shapeSpecs: SHAPE ? P.params.map((d) => ({ name: d.label, value: SP[d.key] + (d.unit ? ' ' + d.unit : '') })) : [],\n"
             "      shapeSvg: SHAPE ? IRShapes.draw(SHAPE, SP, { stamp: P.code + ' · ' + P.name.toUpperCase() + ' · ' + \"o'lchamlar mm\".toUpperCase(), note: matNames[st.mat][0] + ' · ' + st.shore + ' Sh A' }) : '',\n"
             "      shapeAlt: SHAPE ? P.name + ' · ' + PARAM_STR : '',\n"
             "      shapeFaces: SHAPE ? IRShapes.faces(SHAPE, SP, coatCol) : [],\n"
             "      shapeTilt: 'position:absolute;left:0;top:0;width:0;height:0;transform-style:preserve-3d;transition:transform .25s ease-out;transform:rotateX(' + ((SHAPE ? IRShapes.pitch(SHAPE) : -24) + st.vy * 40) + 'deg) rotateY(' + (st.vx * 70) + 'deg)',\n"
             "      pCode: P ? P.code : 'IR-V01',\n"
             "      pCat: P ? P.cat : 'Valiklar',\n"
             "      pCatUp: (P ? P.cat : 'Valiklar').toUpperCase(),\n"
             "      pName1: P ? P.name1 : 'Poliuretan',\n"
             "      pName2: P ? P.name2 : 'valik',\n"
             "      pDesc: P ? P.desc : \"Metall o'zak ustiga quyilgan poliuretan qoplamali valik. Ishqalanish, yog' va yuklamaga chidamli. Konveyer, press, prokat va poligrafiya liniyalari uchun buyurtma o'lchamida tayyorlanadi.\",\n"
             "      pMatL: P ? P.matL : '', pShore: P ? P.shore : '',\n"
             "      pSpecs: (P && P.specs) || [],\n"
             "      photos: M.photos,\n"
             "      hasPhotos: M.photos.length > 0,\n"
             "      photoPlaceholder: !!P && M.photos.length === 0,\n"
             "      staticPhotos: !P,\n"
             "      drawings: M.drawings,\n"
             "      model: M.model || { src: '', alt: '' },\n"
             "      docs: DOCS, staticDocs: !P,\n"
             "      passport: (DOCS[0] && DOCS[0].url) || '#', hasPassport: !P || DOCS.length > 0,\n"
             "      summary\n    };")

    # ---------------- template: header
    s = _sub(s, '>Katalog</a> / Valiklar / IR-V01</div>', '>Katalog</a> / {{pCat}} / {{pCode}}</div>')
    s = _sub(s, '>IR-V01 · VALIKLAR</span>', '>{{pCode}} · {{pCatUp}}</span>')
    s = _sub(s, '">Poliuretan<br><span style="background: {{accent}}; padding: 0 8px">valik</span></h1>',
             '">{{pName1}}<br><span style="background: {{accent}}; padding: 0 8px">{{pName2}}</span></h1>')
    s = _sub(s, ">Metall o'zak ustiga quyilgan poliuretan qoplamali valik. Ishqalanish, yog' va yuklamaga chidamli. Konveyer, press, prokat va poligrafiya liniyalari uchun buyurtma o'lchamida tayyorlanadi.</p>",
             '>{{pDesc}}</p>')

    # ---------------- template: views
    s = _wrap(s, '<span class="mono" style="padding: 0 16px; font-size: 12px; color: #5B636D">M 1:{{scaleTxt}}</span>', '</span>',
              '<sc-if value="{{isDrawSvg}}" hint-placeholder-val="{{ true }}">', '</sc-if>')
    s = _sub(s, '<sc-if value="{{isDraw}}" hint-placeholder-val="{{ true }}">', '<sc-if value="{{isDrawSvg}}" hint-placeholder-val="{{ true }}">')
    tile = 'display: flex; align-items: flex-end; padding: 12px; font-size: 12px; color: #3E4650'
    drawings = ('\n<sc-if value="{{isDrawMedia}}" hint-placeholder-val="{{ false }}">\n'
                '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1px; background: #111418">\n'
                '<sc-for list="{{drawings}}" as="dr" hint-placeholder-count="1">'
                '<a href="{{dr.src}}" target="_blank" rel="noopener" class="ph mono" style="position: relative; min-height: 380px; ' + tile + '">'
                '<sc-if value="{{dr.isImage}}" hint-placeholder-val="{{ true }}"><img src="{{dr.src}}" alt="{{dr.alt}}" loading="lazy" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; background: #fff"></sc-if>'
                '<span style="position: relative; background: #fff; border: 1px solid #111418; padding: 4px 8px">{{dr.label}}</span></a>'
                '</sc-for>\n</div>\n</sc-if>\n')
    i = s.index('<sc-if value="{{isDrawSvg}}" hint-placeholder-val="{{ true }}">\n<svg')
    j = s.index('</svg>\n</sc-if>\n', i) + len('</svg>\n</sc-if>\n')
    shape_draw = ('<sc-if value="{{isDrawShape}}" hint-placeholder-val="{{ false }}">\n'
                  '<div style="height: 380px; background: #fff; display: flex; align-items: center; justify-content: center">'
                  '<img src="{{shapeSvg}}" alt="{{shapeAlt}}" style="width: 100%; height: 100%; object-fit: contain"></div>\n</sc-if>\n')
    s = s[:j] + drawings.lstrip('\n') + shape_draw + s[j:]
    s = _sub(s, '<sc-if value="{{is3d}}" hint-placeholder-val="{{ false }}">', '<sc-if value="{{is3dCss}}" hint-placeholder-val="{{ false }}">')
    model = ('<sc-if value="{{is3dModel}}" hint-placeholder-val="{{ false }}">\n'
             '<div style="position: relative; background: #fff">'
             '<model-viewer src="{{model.src}}" alt="{{model.alt}}" camera-controls auto-rotate shadow-intensity="0.6" exposure="1" style="width: 100%; height: 380px; --poster-color: transparent"></model-viewer>'
             '<span class="mono" style="position: absolute; left: 12px; bottom: 10px; font-size: 11px; color: #3E4650">Kursor bilan aylantiring</span>'
             '</div>\n</sc-if>\n')
    i = s.index('<sc-if value="{{is3dCss}}"')
    j = s.index('</sc-if>\n', i) + len('</sc-if>\n')
    shape3d = ('<sc-if value="{{is3dShape}}" hint-placeholder-val="{{ false }}">\n'
               '<div onMouseMove="{{move3d}}" onMouseLeave="{{leave3d}}" style="height: 380px; position: relative; overflow: hidden; perspective: 1200px; background: radial-gradient(ellipse at 50% 45%, #FFFFFF 0%, #EEF0F2 75%)">'
               '<div style="position: absolute; left: 50%; top: 48%; width: 0; height: 0; transform-style: preserve-3d"><div style="{{shapeTilt}}">'
               '<div class="turn" style="position: absolute; left: 0; top: 0; width: 0; height: 0; transform-style: preserve-3d">'
               '<sc-for list="{{shapeFaces}}" as="f" hint-placeholder-count="40"><div style="{{f.style}}"></div></sc-for>'
               '</div></div></div>'
               '<span class="mono" style="position: absolute; left: 16px; bottom: 14px; font-size: 12px; color: #5B636D; text-transform: uppercase">Kursor bilan aylantiring</span>'
               '</div>\n</sc-if>\n')
    s = s[:j] + '\n' + model + shape3d + s[j:]

    grid = '<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1px; background: #111418">\n'
    i = s.index('<sc-if value="{{isFoto}}"')
    j = s.index('</sc-if>', i)
    block = s[i:j]
    if block.count(grid) != 1:
        raise SystemExit('dynamic patch: photo grid not found')
    photos = ('<sc-if value="{{hasPhotos}}" hint-placeholder-val="{{ false }}">' + grid +
              '<sc-for list="{{photos}}" as="ph" hint-placeholder-count="4"><img src="{{ph.src}}" alt="{{ph.alt}}" loading="lazy" style="width: 100%; height: 189px; object-fit: cover; display: block; background: #fff"></sc-for>\n'
              '</div>\n</sc-if>\n'
              '<sc-if value="{{photoPlaceholder}}" hint-placeholder-val="{{ false }}"><div class="ph mono" style="height: 380px; ' + tile + '">[FOTO]</div></sc-if>\n'
              '<sc-if value="{{staticPhotos}}" hint-placeholder-val="{{ true }}">\n')
    block = block.replace(grid, photos + grid, 1).rstrip('\n') + '\n</sc-if>\n'
    s = s[:i] + block + s[j:]

    # measurement strip under the views: roller sizes only
    s = _wrap(s, '<div class="mono" style="border-top: 1px solid #111418; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr));',
              '{{shore}} A</div></div>\n</div>', '<sc-if value="{{isRoller}}" hint-placeholder-val="{{ true }}">\n',
              '\n</sc-if>\n<sc-if value="{{isShape}}" hint-placeholder-val="{{ false }}">'
              '<div class="mono" style="border-top: 1px solid #111418; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); font-size: 12px; text-transform: uppercase">'
              '<sc-for list="{{chips}}" as="c" hint-placeholder-count="4"><div style="padding: 14px 16px; border-right: 1px solid #DDE1E5"><div style="color: #5B636D">{{c.label}}</div>'
              '<div style="font: 800 22px Archivo, sans-serif; margin-top: 4px">{{c.value}}</div></div></sc-for></div></sc-if>')

    # ---------------- template: configurator and request button
    s = _wrap(s, '<div class="mono" style="padding: 14px 20px; border-bottom: 1px solid #111418; font-size: 13px; text-transform: uppercase; display: flex; justify-content: space-between"><span>Konfigurator</span>',
              'onChange="{{setT}}">\n</label>\n</div>', '<sc-if value="{{configurable}}" hint-placeholder-val="{{ true }}">\n', '\n</sc-if>')
    sliders = '<div style="padding: 20px; display: flex; flex-direction: column; gap: 18px; border-bottom: 1px solid #DDE1E5">\n<label'
    s = _wrap(s, sliders, 'onChange="{{setT}}">\n</label>\n</div>', '<sc-if value="{{isRoller}}" hint-placeholder-val="{{ true }}">\n',
              '\n</sc-if>\n<sc-if value="{{isShape}}" hint-placeholder-val="{{ false }}">'
              '<div style="padding: 20px; display: flex; flex-direction: column; gap: 18px; border-bottom: 1px solid #DDE1E5">'
              '<sc-for list="{{params}}" as="pr" hint-placeholder-count="4"><label style="display: flex; flex-direction: column; gap: 6px">'
              '<span style="display: flex; justify-content: space-between"><span class="mono" style="font-size: 12px; text-transform: uppercase; color: #5B636D">{{pr.label}}</span><b>{{pr.value}} {{pr.unit}}</b></span>'
              '<input type="range" min="{{pr.min}}" max="{{pr.max}}" step="{{pr.step}}" value="{{pr.value}}" onChange="{{pr.set}}"></label></sc-for></div></sc-if>')
    btn = '<a href="#sorov" class="btn" style="margin: 0 20px 20px; background: #111418; color: #fff; border: 1px solid #111418; padding: 18px; font-weight: 700; text-align: center">'
    s = _sub(s, btn + "Shu o'lcham bilan so'rov yuborish →</a>",
             '<sc-if value="{{configurable}}" hint-placeholder-val="{{ true }}">' + btn + "Shu o'lcham bilan so'rov yuborish →</a></sc-if>"
             '<sc-if value="{{notConfigurable}}" hint-placeholder-val="{{ false }}">' + btn + "So'rov yuborish →</a></sc-if>")
    passport = '<a href="#" class="btn" style="border: 1px solid #111418; background: #fff; padding: 12px 16px; display: flex; gap: 8px; align-items: center"><svg'
    s = _wrap(s, passport, 'Texnik pasport (PDF)</a>', '<sc-if value="{{hasPassport}}" hint-placeholder-val="{{ true }}">', '</sc-if>')
    s = _sub(s, passport, passport.replace('href="#"', 'href="{{passport}}"'))

    # ---------------- template: specs table
    s = _wrap(s, '<tr><th>Material</th><td>{{matLong}}</td></tr>', "<tr><th>O'zak</th><td>Po'lat · yoki mijoz o'zagi</td></tr>",
              '<sc-if value="{{isRoller}}" hint-placeholder-val="{{ true }}">',
              '</sc-if>\n<sc-if value="{{isShape}}" hint-placeholder-val="{{ false }}"><tr><th>Material</th><td>{{matLong}}</td></tr><tr><th>Qattiqlik</th><td>{{shore}} Shore A</td></tr>'
              '<sc-for list="{{shapeSpecs}}" as="ss" hint-placeholder-count="0"><tr><th>{{ss.name}}</th><td>{{ss.value}}</td></tr></sc-for></sc-if>\n<sc-if value="{{notConfigurable}}" hint-placeholder-val="{{ false }}"><tr><th>Material</th><td>{{pMatL}}</td></tr><tr><th>Qattiqlik</th><td>{{pShore}}</td></tr></sc-if>')
    s = _sub(s, '<tr><th>Tayyorlash muddati</th><td>[__] ish kunidan</td></tr>\n</tbody>',
             '<tr><th>Tayyorlash muddati</th><td>[__] ish kunidan</td></tr>\n'
             '<sc-for list="{{pSpecs}}" as="sp" hint-placeholder-count="0"><tr><th>{{sp.name}}</th><td>{{sp.value}}</td></tr></sc-for>\n</tbody>')

    # ---------------- template: documents tab
    row = 'display: flex; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid #DDE1E5'
    s = _wrap(s, '<a href="#" style="' + row + '"><span>Texnik pasport — IR-V01</span>', 'DWG / PDF ↓</span></a>',
              '<sc-for list="{{docs}}" as="doc" hint-placeholder-count="0"><a href="{{doc.url}}" target="_blank" rel="noopener" style="' + row + '"><span>{{doc.title}}</span><span class="mono" style="font-size: 13px">{{doc.meta}}</span></a></sc-for>\n'
              '<sc-if value="{{staticDocs}}" hint-placeholder-val="{{ true }}">\n', '\n</sc-if>')

    # ---------------- request form: send the chosen product and sizes to the CRM
    s = _sub(s, '<form style="flex: 1 1 480px;', '<form data-product-form style="flex: 1 1 480px;')
    i = s.index('<form data-product-form')
    j = s.index('>', i) + 1
    s = s[:j] + '<input type="hidden" data-lead="items" value="{{leadItems}}">' + s[j:]

    # ---------------- related products
    s = _sub(s, '<a href="Mahsulot-G.dc.html" class="card"', '<a href="{{r.url}}" class="card"')
    return s


HEAD_EXTRA = {
    # Google <model-viewer> renders uploaded .glb models in the 3D view.
    'Mahsulot-G': '<script type="module" src="https://cdn.jsdelivr.net/npm/@google/model-viewer@3.5.0/dist/model-viewer.min.js"></script>\n'
                  '<style>@keyframes irturn{from{transform:rotateY(0deg)}to{transform:rotateY(360deg)}}.turn{animation:irturn 14s linear infinite}'
                  '@media (prefers-reduced-motion: reduce){.turn{animation:none}}</style>',
}


def qoplash(s):
    """Recoating request: diameter, length, quantity and the chosen coating go to the CRM as one item."""
    s = _sub(s, '<form style="flex: 1 1 520px;', '<form data-lead-product="IR-K01" style="flex: 1 1 520px;')
    s = _sub(s, '<input type="text" placeholder="Ø">', '<input type="text" inputmode="decimal" placeholder="Ø" data-lead-option="D">')
    s = _sub(s, '<input type="text" placeholder="L">', '<input type="text" inputmode="decimal" placeholder="L" data-lead-option="L">')
    s = _sub(s, '<input type="text" placeholder="dona">', '<input type="text" inputmode="numeric" placeholder="dona" data-lead-option="qty">')
    s = _sub(s, 'matName: matInfo.name,', 'matName: matInfo.name, leadMat: this.state.mat,')
    i = s.index('<form data-lead-product="IR-K01"')
    j = s.index('>', i) + 1
    return s[:j] + '<input type="hidden" data-lead-option="mat" value="{{leadMat}}">' + s[j:]


def portfolio(s):
    """«Ishlar»: cases and the industry filter come from the projects table (ProjectsPageData)."""
    s = _sub(s, "    const all = [\n      { f: 'metal', ind: 'Metallurgiya', client: '[Mijoz korxona]'",
             "    const IR = window.__IR || null;\n    const all = (IR && IR.projects) || [\n      { f: 'metal', ind: 'Metallurgiya', client: '[Mijoz korxona]'")
    s = _sub(s, "const fl = [['all', 'Barchasi'], ", "const fl = IR ? [['all', 'Barchasi']].concat(IR.industries || []) : [['all', 'Barchasi'], ")
    s = _sub(s, "const vis = all.map((c, i) => Object.assign({ i }, c))", "const vis = all.map((c, i) => Object.assign({ i, noImg: !c.img }, c))")
    s = _sub(s, "const C = st.sel >= 0 ? all[st.sel] : all[0];", "const C = (st.sel >= 0 ? all[st.sel] : all[0]) || { kpis: [] };")
    s = _sub(s, "kpis: [{ v: C.k1, l: C.k1l }, { v: C.k2, l: C.k2l }, { v: '[__] kun', l: 'buyurtma muddati' }]",
             "kpis: C.kpis || [{ v: C.k1, l: C.k1l }, { v: C.k2, l: C.k2l }, { v: '[__] kun', l: 'buyurtma muddati' }]")
    s = _sub(s, "      open: st.sel >= 0, close: () => this.setState({ sel: -1 }),",
             "      open: st.sel >= 0, close: () => this.setState({ sel: -1 }), empty: all.length === 0,")
    # card photo
    s = _sub(s, '<span class="ph zoom mono" style="height: 240px; display: flex; align-items: flex-end; padding: 14px; font-size: 12px; color: #3E4650">[{{c.photo}}]</span>',
             '<sc-if value="{{c.img}}" hint-placeholder-val="{{ false }}"><img class="zoom" src="{{c.img}}" alt="{{c.t}}" loading="lazy" style="display: block; width: 100%; height: 240px; object-fit: cover"></sc-if>'
             '<sc-if value="{{c.noImg}}" hint-placeholder-val="{{ true }}"><span class="ph zoom mono" style="height: 240px; display: flex; align-items: flex-end; padding: 14px; font-size: 12px; color: #3E4650">[{{c.photo}}]</span></sc-if>')
    # before / after in the case window
    for label, key in (('[OLDIN]', 'before'), ('[KEYIN]', 'after')):
        i = s.index('">' + label + '</div>')
        j = s.rindex('<div class="mono" style="height: 240px;', 0, i)
        s = s[:j] + '<div class="mono" style="position: relative; overflow: hidden; height: 240px;' + s[j + len('<div class="mono" style="height: 240px;'):]
        s = _sub(s, '">' + label + '</div>', '">' + label + '<sc-if value="{{C.' + key + '}}" hint-placeholder-val="{{ false }}"><img src="{{C.' + key + '}}" alt="" loading="lazy" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover"></sc-if></div>')
    # customer quote
    s = _sub(s, '<span style="font-weight: 700; font-size: 18px; max-width: 560px">“[Mijozning haqiqiy fikri — tasdiqlangandan keyin joylanadi]”</span>',
             '<span style="font-weight: 700; font-size: 18px; max-width: 560px"><sc-if value="{{C.quote}}" hint-placeholder-val="{{ false }}">“{{C.quote}}”'
             '<span class="mono" style="display: block; margin-top: 8px; font-size: 12px; font-weight: 400; color: #5B636D">{{C.quoteBy}}</span></sc-if></span>')
    # nothing published yet
    s = _sub(s, '<sc-for list="{{cases}}" as="c" hint-placeholder-count="6">',
             '<sc-if value="{{empty}}" hint-placeholder-val="{{ false }}"><p style="margin: 0; color: #3E4650; font-size: 17px">Tez orada bu yerda bajarilgan ishlar paydo bo\'ladi.</p></sc-if>\n'
             '<sc-for list="{{cases}}" as="c" hint-placeholder-count="6">')
    return s


def blog(s):
    """Blog list: featured article, category filter and cards from the posts table (BlogPageData)."""
    s = _sub(s, "    const all = [\n      ['ekspl', 'Ekspluatatsiya',",
             "    const IR = window.__IR || null;\n"
             "    const all = (IR && IR.posts) ? IR.posts.map((p) => [p.k, p.cat, p.t, p.d, p.min, p.photo, p.url, p.img]) : [\n      ['ekspl', 'Ekspluatatsiya',")
    s = _sub(s, "const cl = [['all', 'Barchasi'], ", "const cl = IR ? [['all', 'Barchasi']].concat(IR.cats || []) : [['all', 'Barchasi'], ")
    s = _sub(s, "posts: vis.map(([k, cat, t, d, min, img], i) => ({ cat, t, d, min, img, style:",
             "feat: (IR && IR.featured) || {}, hasFeat: !!(IR && IR.featured), staticFeat: !IR,\n"
             "      featNoImg: !(IR && IR.featured && IR.featured.img), empty: !!IR && !IR.featured,\n"
             "      posts: vis.map(([k, cat, t, d, min, img, url, cover], i) => ({ cat, t, d, min, img, url: url || '#', cover: cover || '', noCover: !cover, style:")
    # featured article on top
    i = s.index('<a href="Maqola-G.dc.html" class="card" style="background: #fff; border: 1px solid #111418; display: flex; flex-wrap: wrap">')
    j = s.index('</a>', i) + len('</a>')
    static = s[i:j]
    ph = '<span class="ph mono" style="flex: 1 1 520px; min-height: 340px; display: flex; align-items: flex-end; padding: 16px; font-size: 12px; color: #3E4650; border-right: 1px solid #111418">'
    dyn = static.replace('href="Maqola-G.dc.html"', 'href="{{feat.url}}"')
    dyn = _sub(dyn, ph + "[Durometr bilan qattiqlik o'lchash]</span>",
               '<sc-if value="{{feat.img}}" hint-placeholder-val="{{ false }}"><img src="{{feat.img}}" alt="{{feat.t}}" style="flex: 1 1 520px; min-height: 340px; max-width: 100%; object-fit: cover; border-right: 1px solid #111418"></sc-if>'
               '<sc-if value="{{featNoImg}}" hint-placeholder-val="{{ true }}">' + ph + '[{{feat.photo}}]</span></sc-if>')
    dyn = _sub(dyn, 'padding: 3px 8px">Tanlov</span>', 'padding: 3px 8px">{{feat.cat}}</span>')
    dyn = _sub(dyn, "padding: 4px 0\">6 daqiqa o'qish</span>", "padding: 4px 0\">{{feat.min}} daqiqa o'qish</span>")
    dyn = _sub(dyn, '>Shore A qattiqlikni qanday tanlash kerak?</b>', '>{{feat.t}}</b>')
    k = dyn.index('<span style="color: #3E4650; font-size: 17px; line-height: 1.6">')
    m = dyn.index('</span>', k)
    dyn = dyn[:k] + '<span style="color: #3E4650; font-size: 17px; line-height: 1.6">{{feat.d}}' + dyn[m:]
    s = s[:i] + '<sc-if value="{{staticFeat}}" hint-placeholder-val="{{ true }}">' + static + '</sc-if>\n'\
        '<sc-if value="{{hasFeat}}" hint-placeholder-val="{{ false }}">' + dyn + '</sc-if>\n'\
        '<sc-if value="{{empty}}" hint-placeholder-val="{{ false }}"><p style="margin: 0; color: #3E4650; font-size: 17px">Tez orada bu yerda maqolalar paydo bo\'ladi.</p></sc-if>' + s[j:]
    # cards
    s = _sub(s, '<a href="Maqola-G.dc.html" class="card" style="{{p.style}}">', '<a href="{{p.url}}" class="card" style="{{p.style}}">')
    s = _sub(s, '<span class="ph mono" style="height: 200px; display: flex; align-items: flex-end; padding: 12px; font-size: 11px; color: #3E4650; border-bottom: 1px solid #111418">[{{p.img}}]</span>',
             '<sc-if value="{{p.cover}}" hint-placeholder-val="{{ false }}"><img src="{{p.cover}}" alt="{{p.t}}" loading="lazy" style="display: block; width: 100%; height: 200px; object-fit: cover; border-bottom: 1px solid #111418"></sc-if>'
             '<sc-if value="{{p.noCover}}" hint-placeholder-val="{{ true }}"><span class="ph mono" style="height: 200px; display: flex; align-items: flex-end; padding: 12px; font-size: 11px; color: #3E4650; border-bottom: 1px solid #111418">[{{p.img}}]</span></sc-if>')
    return s


def maqola(s, generic=False):
    """Article page. Header (category, minutes, author, title, lead, cover) comes from the post when
    the server sends one. generic=True (blog-post page): the body is the post's HTML and the table of
    contents is built from its headings; otherwise the designed interactive article stays."""
    s = _sub(s, "    const secs = [", "    const P = (window.__IR && window.__IR.post) || null;\n    const secs = (P && P.toc && P.toc.length) ? P.toc.map((x) => [x.href, x.t]) : " + ("[] || [" if generic else "["))
    s = _sub(s, "    return {\n      accent,\n      prog: st.prog.toFixed(1),",
             "    return {\n      accent, post: P || {}, dyn: !!P, stat: !P, postImg: !!(P && P.img), hasToc: secs.length > 0,\n      prog: st.prog.toFixed(1),")
    head_static = ('<span class="mono" style="font-size: 12px; text-transform: uppercase; display: flex; gap: 12px"><span style="background: {{accent}}; border: 1px solid #111418; padding: 3px 8px">Tanlov</span>'
                   '<span style="padding: 4px 0">6 daqiqa · [Muallif — bosh muhandis]</span></span>\n'
                   '<h1 style="margin: 0; font-weight: 800; font-size: 60px; line-height: 0.98; letter-spacing: -0.03em">Shore A qattiqlikni qanday tanlash kerak?</h1>\n')
    i = s.index(head_static)
    j = s.index('</p>', i) + len('</p>')
    static = s[i:j]
    dyn = ('<span class="mono" style="font-size: 12px; text-transform: uppercase; display: flex; gap: 12px"><span style="background: {{accent}}; border: 1px solid #111418; padding: 3px 8px">{{post.cat}}</span>'
           '<span style="padding: 4px 0">{{post.min}} daqiqa · {{post.author}}</span></span>\n'
           '<h1 style="margin: 0; font-weight: 800; font-size: 60px; line-height: 0.98; letter-spacing: -0.03em">{{post.t}}</h1>\n'
           '<p style="margin: 0; color: #3E4650; font-size: 20px; line-height: 1.6">{{post.d}}</p>')
    s = s[:i] + '<sc-if value="{{stat}}" hint-placeholder-val="{{ true }}">' + static + '</sc-if><sc-if value="{{dyn}}" hint-placeholder-val="{{ false }}">' + dyn + '</sc-if>' + s[j:]
    cover = "<div class=\"ph mono\" style=\"height: 380px; border: 1px solid #111418; display: flex; align-items: flex-end; padding: 16px; font-size: 12px; color: #3E4650\">[Durometr bilan poliuretan valik qattiqligini o'lchash]</div>"
    s = _sub(s, cover, '<sc-if value="{{stat}}" hint-placeholder-val="{{ true }}">' + cover + '</sc-if>'
             '<sc-if value="{{postImg}}" hint-placeholder-val="{{ false }}"><figure style="margin: 0; border: 1px solid #111418; background: #fff">'
             '<img src="{{post.img}}" alt="{{post.t}}" style="display: block; width: 100%; max-height: 520px; object-fit: cover">'
             '<figcaption class="mono" style="padding: 10px 14px; font-size: 12px; color: #5B636D; border-top: 1px solid #111418">{{post.caption}}</figcaption></figure></sc-if>')
    if generic:
        a = s.index('<aside aria-label="Mundarija"')
        b = s.index('</aside>', a) + len('</aside>')
        s = s[:a] + '<sc-if value="{{hasToc}}" hint-placeholder-val="{{ true }}">' + s[a:b] + '</sc-if>' + s[b:]
        a = s.index('<h2 id="nima" data-sec="0">')
        b = s.index('<div style="background: #111418; color: #EEF0F2; padding: 28px;', a)
        s = s[:a] + '<sc-html value="{{post.body}}"></sc-html>\n' + s[b:]
    return s


def video(s):
    """Video page: real player (YouTube embed or <video>) with clickable chapters; playlist and
    short clips from the videos table (VideosPageData). Without server data the design's demo stays."""
    s = _sub(s, "  renderVals() {", "  seek(sec) {\n    this.setState({ play: true, start: sec });\n"
             "    const el = document.querySelector('video[data-v]');\n    if (el) { try { el.currentTime = sec; el.play(); } catch (e) { } }\n  }\n  renderVals() {")
    s = _sub(s, "    const vids = [", "    const IR = window.__IR || null;\n    const vids = (IR && IR.videos) || [")
    s = _sub(s, "    const v = vids[st.i];", "    const v = vids[st.i] || vids[0] || { t: '', cat: '', len: '', d: '', sec: 1, chapters: [] };\n"
             "    const REAL = !!IR, YT = REAL && !!v.yt, FILE = REAL && !!v.file;")
    s = _sub(s, "chapters: v.chapters.map(([at, t, p]) => ({ at, t, mark:", "chapters: v.chapters.map(([at, t, p, sec]) => ({ at, t, go: () => this.seek(sec || 0), mark:")
    s = _sub(s, "this.setState({ i, play: true, flip: !st.flip })", "this.setState({ i, play: true, start: 0, flip: !st.flip })")
    s = _sub(s, "      shorts: [[", "      real: REAL, fake: !REAL, none: REAL && vids.length === 0, hasVideo: !REAL || vids.length > 0,\n"
             "      showPoster: REAL && !st.play, isYt: YT && st.play, isFile: FILE && st.play,\n"
             "      ytSrc: YT ? 'https://www.youtube-nocookie.com/embed/' + v.yt + '?autoplay=1&rel=0&start=' + (st.start || 0) : '',\n"
             "      posterBg: v.poster ? \"background-image:url('\" + v.poster + \"')\" : '',\n"
             "      hasShorts: !REAL || ((IR.shorts || []).length > 0),\n"
             "      shorts: REAL ? (IR.shorts || []) : [[")
    s = _sub(s, "list: vids.map((x, i) => ({ t: x.t, cat: x.cat, len: x.len,", "list: vids.map((x, i) => ({ t: x.t, cat: x.cat, len: x.len, poster: x.poster || '',")
    # player
    i = s.index('<div class="ph" style="position: relative; aspect-ratio: 16 / 9; display: flex; align-items: center; justify-content: center">')
    j = s.index('<div style="background: #111418; padding: 12px 16px; display: flex; flex-direction: column; gap: 10px">', i)
    k = s.index('<div style="background: #fff; border-top: 1px solid #111418; padding: 20px;', j)
    player = ('<sc-if value="{{real}}" hint-placeholder-val="{{ false }}"><div style="position: relative; aspect-ratio: 16 / 9; background: #111418">'
              '<sc-if value="{{showPoster}}" hint-placeholder-val="{{ true }}"><button onClick="{{toggle}}" class="playbtn-wrap" aria-label="Videoni boshlash" '
              'style="position: absolute; inset: 0; width: 100%; border: 0; padding: 0; cursor: pointer; display: flex; align-items: center; justify-content: center; background: #111418 center / cover no-repeat; {{posterBg}}">'
              '<span class="playbtn" style="width: 96px; height: 96px; border-radius: 50%; border: 2px solid #111418; background: {{accent}}; display: flex; align-items: center; justify-content: center">'
              '<svg aria-hidden="true" width="34" height="34" viewBox="0 0 24 24" fill="#111418"><path d="M7 4l14 8-14 8z"/></svg></span></button></sc-if>'
              '<sc-if value="{{isYt}}" hint-placeholder-val="{{ false }}"><iframe src="{{ytSrc}}" title="{{v.t}}" allow="autoplay; encrypted-media; picture-in-picture; fullscreen" allowfullscreen '
              'style="position: absolute; inset: 0; width: 100%; height: 100%; border: 0"></iframe></sc-if>'
              '<sc-if value="{{isFile}}" hint-placeholder-val="{{ false }}"><video data-v src="{{v.file}}" poster="{{v.poster}}" controls autoplay playsinline '
              'style="position: absolute; inset: 0; width: 100%; height: 100%; background: #111418"></video></sc-if>'
              '</div></sc-if>\n')
    s = s[:i] + player + '<sc-if value="{{fake}}" hint-placeholder-val="{{ true }}">' + s[i:k] + '</sc-if>\n' + s[k:]
    # chapters: click jumps to the time
    s = _sub(s, '<sc-for list="{{v.chapters}}" as="c" hint-placeholder-count="3"><span style="border: 1px solid #111418; padding: 6px 10px">{{c.at}} · {{c.t}}</span></sc-for>',
             '<sc-for list="{{v.chapters}}" as="c" hint-placeholder-count="3"><button type="button" onClick="{{c.go}}" class="mono" style="border: 1px solid #111418; background: #fff; padding: 6px 10px; font-size: 12px; cursor: pointer">{{c.at}} · {{c.t}}</button></sc-for>')
    # playlist thumbnails
    s = _sub(s, '<span class="ph" style="width: 112px; height: 64px; flex-shrink: 0; position: relative; display: block">',
             '<span class="ph" style="width: 112px; height: 64px; flex-shrink: 0; position: relative; display: block; overflow: hidden">'
             '<sc-if value="{{l.poster}}" hint-placeholder-val="{{ false }}"><img src="{{l.poster}}" alt="" loading="lazy" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover"></sc-if>')
    # short clips
    s = _sub(s, '<a href="#" class="card" style="background: #111418; border: 1px solid #111418; display: flex; flex-direction: column">',
             '<a href="{{s.url}}" target="_blank" rel="noopener" class="card" style="background: #111418; border: 1px solid #111418; display: flex; flex-direction: column">')
    s = _sub(s, '<span class="ph" style="aspect-ratio: 9 / 16; position: relative; display: flex; align-items: center; justify-content: center"><span style="width: 52px;',
             '<span class="ph" style="aspect-ratio: 9 / 16; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center">'
             '<sc-if value="{{s.poster}}" hint-placeholder-val="{{ false }}"><img src="{{s.poster}}" alt="" loading="lazy" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover"></sc-if>'
             '<span style="position: relative; width: 52px;')
    # sections without content are hidden
    a = s.index('<section style="padding: 0 0 64px">')
    b = s.index('</section>', a) + len('</section>')
    s = s[:a] + '<sc-if value="{{none}}" hint-placeholder-val="{{ false }}"><section style="padding: 0 0 64px"><div style="max-width: 1280px; margin: 0 auto; padding: 0 24px">'\
        '<p style="margin: 0; color: #3E4650; font-size: 17px">Tez orada bu yerda zavod videolari paydo bo\'ladi.</p></div></section></sc-if>\n'\
        '<sc-if value="{{hasVideo}}" hint-placeholder-val="{{ true }}">' + s[a:b] + '</sc-if>' + s[b:]
    a = s.index('<section style="padding: 64px 0 96px; background: rgba(255,255,255,0.8); border-top: 1px solid #111418">')
    b = s.index('</section>', a) + len('</section>')
    s = s[:a] + '<sc-if value="{{hasShorts}}" hint-placeholder-val="{{ true }}">' + s[a:b] + '</sc-if>' + s[b:]
    return s


def home(s):
    """Home page: «by industry» tabs with products and the catalog sections list from the database (HomePageData)."""
    s = _sub(s, "    const data = {\n      metal: [", "    const IR = window.__IR || null;\n"
             "    const data = (IR && IR.inds) ? Object.fromEntries(IR.inds.map((x) => [x.k, x.products])) : {\n      metal: [")
    s = _sub(s, "const labels = [['metal', 'Metallurgiya'], ", "const labels = (IR && IR.inds) ? IR.inds.map((x) => [x.k, x.label]) : [['metal', 'Metallurgiya'], ")
    s = _sub(s, "    const base = 'flex:1 1 180px;", "    const curInd = data[this.state.ind] ? this.state.ind : (labels[0] ? labels[0][0] : '');\n    const base = 'flex:1 1 180px;")
    s = _sub(s, "(this.state.ind === k ? 'background:#111418;color:#fff'", "(curInd === k ? 'background:#111418;color:#fff'")
    s = _sub(s, "shown: data[this.state.ind].map((p, i) => Object.assign({}, p, {", "hasInds: labels.length > 0,\n      shown: (data[curInd] || []).map((p, i) => Object.assign({ url: '#', img: '' }, p, {")
    s = _sub(s, "    const cats = [\n      { n: '/01',", "    const cats = (IR && IR.cats) || [\n      { n: '/01',")
    s = _sub(s, "cats: cats.map((c, i) => Object.assign({}, c, {", "cats: cats.map((c, i) => Object.assign({ url: '#' }, c, {")
    s = _sub(s, '<a href="#" class="card" style="{{p.anim}}">\n<div class="ph" style="height: 200px; border-bottom: 1px solid #111418">[{{p.name}}]</div>',
             '<a href="{{p.url}}" class="card" style="{{p.anim}}">\n<div class="ph" style="position: relative; overflow: hidden; height: 200px; border-bottom: 1px solid #111418">[{{p.name}}]'
             '<sc-if value="{{p.img}}" hint-placeholder-val="{{ false }}"><img src="{{p.img}}" alt="{{p.name}}" loading="lazy" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover"></sc-if></div>')
    s = _sub(s, '<a href="#" class="row rv" style="display: flex;', '<a href="{{c.url}}" class="row rv" style="display: flex;')
    return s


def zavod(s):
    """Factory page: client logos, reviews and certificates from the database (FactoryPageData); workshop photos from the site settings."""
    s = _sub(s, "    const num = (v) =>", "    const IR = window.__IR || null;\n    const num = (v) =>")
    s = _sub(s, "    const zone = zones[zi];", "    const zone = Object.assign({}, zones[zi], { img: ['⟦factory_zone_1⟧', '⟦factory_zone_2⟧', '⟦factory_zone_3⟧', '⟦factory_zone_4⟧', '⟦factory_zone_5⟧', '⟦factory_zone_6⟧'][zi] || '' });")
    s = _sub(s, '<div class="ph mono" style="height: 220px; display: flex; align-items: flex-end; padding: 14px; font-size: 12px; color: #3E4650; border-bottom: 1px solid #111418">[{{zone.photo}}]</div>',
             '<div class="ph mono" style="position: relative; overflow: hidden; height: 220px; display: flex; align-items: flex-end; padding: 14px; font-size: 12px; color: #3E4650; border-bottom: 1px solid #111418">[{{zone.photo}}]'
             '<sc-if value="{{zone.img}}" hint-placeholder-val="{{ false }}"><img src="{{zone.img}}" alt="{{zone.t}}" loading="lazy" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover"></sc-if></div>')
    s = _sub(s, "      logos: ['[Mijoz logotipi]',", "      hasLogos: !IR || (IR.logos || []).length > 0, hasQuotes: !IR || (IR.quotes || []).length > 0, hasCerts: !IR || (IR.certs || []).length > 0,\n"
             "      logos: ((IR && IR.logos) || ['[Mijoz logotipi]',")
    s = _sub(s, "'[Mijoz logotipi]', '[Mijoz logotipi]'],\n      quotes: [",
             "'[Mijoz logotipi]', '[Mijoz logotipi]']).map((l) => typeof l === 'string' ? { label: l, img: '' } : { label: l.img ? '' : l.name, img: l.img || '', name: l.name }),\n      quotes: ((IR && IR.quotes) || [")
    s = _sub(s, "      ].map((q, i) => Object.assign({}, q, { rv: 'margin:0;background:#fff;", "      ]).map((q, i) => Object.assign({ img: '' }, q, { rv: 'margin:0;background:#fff;")
    s = _sub(s, "      certs: [\n", "      certs: ((IR && IR.certs) || [\n")
    s = _sub(s, "        { t: '[Litsenziya / guvohnoma]', d: '[Raqam]' }\n      ],", "        { t: '[Litsenziya / guvohnoma]', d: '[Raqam]' }\n      ]).map((c) => Object.assign({ url: '', img: '' }, c)),")
    s = _sub(s, '<div class="mono" style="height: 110px; display: flex; align-items: center; justify-content: center; border-right: 1px solid #DDE1E5; border-bottom: 1px solid #DDE1E5; font-size: 12px; color: #8A939D; text-transform: uppercase">{{l}}</div>',
             '<div class="mono" style="height: 110px; display: flex; align-items: center; justify-content: center; border-right: 1px solid #DDE1E5; border-bottom: 1px solid #DDE1E5; font-size: 12px; color: #8A939D; text-transform: uppercase">'
             '<sc-if value="{{l.img}}" hint-placeholder-val="{{ false }}"><img src="{{l.img}}" alt="{{l.name}}" loading="lazy" style="max-width: 70%; max-height: 64px; object-fit: contain"></sc-if>{{l.label}}</div>')
    s = _sub(s, '<span class="ph" style="width: 44px; height: 44px; border: 1px solid #111418; display: block"></span>{{q.a}}',
             '<span class="ph" style="width: 44px; height: 44px; border: 1px solid #111418; display: block; overflow: hidden; flex-shrink: 0">'
             '<sc-if value="{{q.img}}" hint-placeholder-val="{{ false }}"><img src="{{q.img}}" alt="" style="width: 100%; height: 100%; object-fit: cover"></sc-if></span>{{q.a}}')
    s = _sub(s, '<div class="card" style="background: #fff; border: 1px solid #111418; padding: 16px; display: flex; gap: 14px; align-items: center; flex: 1 1 260px">\n'
                '<span class="ph" style="width: 56px; height: 72px; border: 1px solid #111418; display: block; flex-shrink: 0"></span>',
             '<a href="{{c.url}}" target="_blank" rel="noopener" class="card" style="background: #fff; border: 1px solid #111418; padding: 16px; display: flex; gap: 14px; align-items: center; flex: 1 1 260px">\n'
             '<span class="ph" style="width: 56px; height: 72px; border: 1px solid #111418; display: block; flex-shrink: 0; overflow: hidden">'
             '<sc-if value="{{c.img}}" hint-placeholder-val="{{ false }}"><img src="{{c.img}}" alt="" style="width: 100%; height: 100%; object-fit: cover"></sc-if></span>')
    i = s.index('<a href="{{c.url}}" target="_blank" rel="noopener" class="card"')
    j = s.index('</div>\n</sc-for>', i)
    s = s[:i] + s[i:j] + '</a>\n</sc-for>' + s[j + len('</div>\n</sc-for>'):]
    for flag, marker in (('hasLogos', '<sc-for list="{{logos}}"'), ('hasQuotes', '<sc-for list="{{quotes}}"'), ('hasCerts', '<sc-for list="{{certs}}"')):
        i = s.index(marker)
        j = s.index('</sc-for>', i) + len('</sc-for>')
        s = s[:i] + '<sc-if value="{{' + flag + '}}" hint-placeholder-val="{{ true }}">' + s[i:j] + '</sc-if>' + s[j:]
    return s


PATCHES = {'Katalog-G': katalog, 'Mahsulot-G': mahsulot, 'Qoplash-G': qoplash, 'Portfolio-G': portfolio, 'Blog-G': blog, 'Maqola-G': maqola, 'Video-G': video, 'Variant-Birlashgan': home, 'Zavod-G': zavod}
# Pages built twice from one design: patch by the output file.
PATCHES_BY_OUT = {'blog-post.html': lambda s: maqola(s, generic=True)}


def apply(src_name, s, out=None):
    fn = PATCHES_BY_OUT.get(out) or PATCHES.get(src_name)
    return fn(s) if fn else s
