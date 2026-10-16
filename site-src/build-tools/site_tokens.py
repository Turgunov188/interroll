"""Site settings in the built pages.

The design has gaps such as "[__] soat ichida" or "+998 [__] ___-__-__". Each gap that the
factory fills in the admin («Настройки сайта», App\\Enums\\SettingKey) becomes a token ⟦key⟧
in the Uzbek source, before translation. i18n_apply keeps tokens in place while it swaps the
words around them, then build_i18n turns them into server placeholders:
    template  ⟦key⟧  → __S_key__    (HTML-escaped by Laravel)
    script    ⟦key⟧  → __SJ_key__   (JS-escaped)
    ⟦key:img⟧        → __SH_key__   (<img> tag or nothing)
⟦none⟧ marks a note for the copywriter ("[tasdiqlash]") that is simply removed.

Every replacement must match exactly the given number of times, so a design change fails the build.
"""
import re

TOKEN = re.compile(r'⟦([a-z0-9_]+)(?::img)?⟧')


def _sub(s, old, new, count=1, page=''):
    n = s.count(old)
    if n != count:
        raise SystemExit('site token (%s): expected %d match(es), found %d: %r' % (page, count, n, old[:90]))
    return s.replace(old, new)


PHONE = ('+998 [__] ___-__-__', '⟦phone⟧')
TEL = ('tel:+998000000000', 'tel:⟦phone_href⟧')
IMG = ('<span style="position: absolute; inset: 0">⟦%s:img⟧</span>')

RULES = {
    'Variant-Birlashgan': [
        ('Qattiqlik: [__]–[__] Shore A', 'Qattiqlik: ⟦hardness_min⟧–⟦hardness_max⟧ Shore A', 2),
        ('Diametr: Ø [__]–[___] mm</span>', 'Diametr: Ø ⟦diameter_min⟧–⟦diameter_max⟧ mm</span>', 2),
        ('Uzunlik: [____] mm gacha', 'Uzunlik: ⟦length_max⟧ mm gacha', 2),
        ('Muddat: [__] kundan</span>', 'Muddat: ⟦lead_days⟧ kundan</span>', 2),
        ('desc: "[Oziq-ovqatga ruxsat etilgan material]."', 'desc: "⟦food_material⟧."', 1),
        ("spec: 'Ø [__]–[___] mm · 70–95 Shore A'", "spec: 'Ø ⟦diameter_min⟧–⟦diameter_max⟧ mm · 70–95 Shore A'", 1),
        ("spec: 'Ø [__]–[___] mm · yuk [___] kg gacha'", "spec: 'Ø ⟦wheel_diameter_min⟧–⟦wheel_diameter_max⟧ mm · yuk ⟦wheel_load_max⟧ kg gacha'", 1),
        ("spec: '[__] ta standart o\\'lcham'", "spec: '⟦coupling_sizes⟧ ta standart o\\'lcham'", 1),
        ("spec: 'Ø [__]–[__] mm · 80–95 Shore A'", "spec: 'Ø ⟦spring_diameter_min⟧–⟦spring_diameter_max⟧ mm · 80–95 Shore A'", 1),
        ("spec: 'Muddat: [__] kundan'", "spec: 'Muddat: ⟦lead_days⟧ kundan'", 1),
        ('+998 [__] ___-__-__ · Telegram @[interroll]', '⟦phone⟧ · Telegram @⟦telegram⟧', 1),
        ('<div class="ph rv" style="height: 360px; border-right: 1px solid #111418; {{c6}}">[OLDIN — eskirgan valik]</div>',
         '<div class="ph rv" style="position: relative; overflow: hidden; height: 360px; border-right: 1px solid #111418; {{c6}}">[OLDIN — eskirgan valik]' + IMG % 'home_before' + '</div>', 1),
        ('<div class="ph rv" style="height: 360px; {{c7}}">[KEYIN — qayta qoplangan]</div>',
         '<div class="ph rv" style="position: relative; overflow: hidden; height: 360px; {{c7}}">[KEYIN — qayta qoplangan]' + IMG % 'home_after' + '</div>', 1),
    ],
    'Katalog-G': [
        ('<div style="font: 800 40px Archivo, sans-serif">[__]</div>kunda tayyor', '<div style="font: 800 40px Archivo, sans-serif">⟦lead_days⟧</div>kunda tayyor', 1),
        PHONE + (1,), TEL + (1,),
        ('va [__] soat ichida bog\'lanadi.', 'va ⟦reply_hours⟧ soat ichida bog\'lanadi.', 1),
    ],
    'Mahsulot-G': [
        ('<td>[__] °C … +[__] °C</td>', '<td>⟦temp_min⟧ °C … +⟦temp_max⟧ °C</td>', 1),
        ('<td>[__] ish kunidan</td>', '<td>⟦lead_days⟧ ish kunidan</td>', 1),
        ('Rezinadan [__] marta uzoq xizmat qiladi — <span class="mono" style="font-size: 12px">[raqamni tasdiqlash]</span>',
         'Rezinadan ⟦service_life⟧ marta uzoq xizmat qiladi', 1),
    ],
    'Qoplash-G': [
        ("Muhandis [__] soat ichida bog'lanadi.", "Muhandis ⟦reply_hours⟧ soat ichida bog'lanadi.", 1),
        ('"O\'zak va podshipnik joylari tekshiriladi, o\'lchanadi.", \'[__] soat\']', '"O\'zak va podshipnik joylari tekshiriladi, o\'lchanadi.", \'⟦recoat_diag_hours⟧ soat\']', 1),
        ('"Eski qoplama mexanik yoki termik usulda olib tashlanadi.", \'[__] soat\']', '"Eski qoplama mexanik yoki termik usulda olib tashlanadi.", \'⟦recoat_strip_hours⟧ soat\']', 1),
        ('"Tozalash, qumlash, yopishtiruvchi qatlam surtish.", \'[__] soat\']', '"Tozalash, qumlash, yopishtiruvchi qatlam surtish.", \'⟦recoat_prep_hours⟧ soat\']', 1),
        ('"Yangi poliuretan yoki rezina quyiladi va qotiriladi.", \'[__] kun\']', '"Yangi poliuretan yoki rezina quyiladi va qotiriladi.", \'⟦recoat_cast_days⟧ kun\']', 1),
        ('"Aniq o\'lchamga silliqlanadi, qattiqlik o\'lchanadi.", \'[__] soat\']', '"Aniq o\'lchamga silliqlanadi, qattiqlik o\'lchanadi.", \'⟦recoat_finish_hours⟧ soat\']', 1),
        ("[Yuk tashish kompaniyasi] orqali", "⟦carrier⟧ orqali", 1),
        ("olib ketish — [shartlar].", "olib ketish — ⟦pickup_terms⟧.", 1),
        ("bir necha marta. [Zavod tajribasidan aniq raqam]", "bir necha marta. ⟦recoat_times⟧", 1),
        ("nazoratdan o'tadi. [Kafolat shartlari]", "nazoratdan o'tadi. ⟦warranty_terms⟧", 1),
    ],
    'Zavod-G': [
        ('[Manzil, shahar]<br>Du–Sha · [08:00–18:00]<br>Smenalar: [__]', '⟦address⟧<br>Du–Sha · ⟦open_hour⟧:00–⟦close_hour⟧:00<br>Smenalar: ⟦shifts⟧', 1),
        ('<span>[Zavod hududi — dron fotosurati yoki 20 soniyalik video]</span>', '<span>[Zavod hududi — dron fotosurati yoki 20 soniyalik video]</span>' + IMG % 'factory_tour', 1),
        ('<div class="ph in mono" style="height: 420px;', '<div class="ph in mono" style="position: relative; overflow: hidden; height: 420px;', 1),
        ('<span class="mono" style="font-size: 12px; color: #8A939D; max-width: 340px">Raqamlar zavod tomonidan tasdiqlanishi kerak — [__] bilan belgilangan.</span>', '', 1),
        ('Sinov bayonnomasi № [____]', 'Sinov bayonnomasi № ⟦protocol_no⟧', 1),
        ('<span>[__.__.2026]</span>', '<span>⟦protocol_date⟧</span>', 1),
        ('<span>[____] · [__] dona</span>', '<span>⟦batch_no⟧ · ⟦batch_qty⟧ dona</span>', 1),
        ('<td style="padding: 10px 18px">[____]</td>', '<td style="padding: 10px 18px">⟦adhesion⟧</td>', 1),
        ('padding: 2px 6px">[__]%</b> gacha · muddat [__] kun</span>', 'padding: 2px 6px">⟦savings_percent⟧%</b> gacha · muddat ⟦recoat_days⟧ kun</span>', 1),
        ('color: #4A3F28">[KEYIN — qayta qoplangan valik]</div>', 'color: #4A3F28">[KEYIN — qayta qoplangan valik]' + IMG % 'factory_after' + '</div>', 1),
        ('<div class="mono" style="{{baClip}}">[OLDIN — eskirgan valik]</div>', '<div class="mono" style="{{baClip}}">[OLDIN — eskirgan valik]' + IMG % 'factory_before' + '</div>', 1),
        PHONE + (1,), TEL + (1,),
        ('this.props.yil ?? 0', "(parseInt('⟦years⟧', 10) || 0)", 1),
        ('this.props.maydon ?? 0', "(parseInt('⟦area⟧', 10) || 0)", 1),
        ('this.props.dastgoh ?? 0', "(parseInt('⟦machines⟧', 10) || 0)", 1),
        ('this.props.mijoz ?? 0', "(parseInt('⟦clients⟧', 10) || 0)", 1),
        ("'[Yetkazib beruvchilar]'", "'⟦suppliers⟧'", 1),
        ("'[Quyish mashinasi]'", "'⟦casting_machine⟧'", 1),
        ("'[Vulkanizatsiya pressi]'", "'⟦press⟧'", 1),
        ("'[__] ta tokarlik dastgohi'", "'⟦lathes⟧ ta tokarlik dastgohi'", 1),
        ("'CNC [bor/yo\\'q]'", "'CNC ⟦cnc⟧'", 1),
        ("proof: 'Muddat: [__] ish kunidan'", "proof: 'Muddat: ⟦lead_days⟧ ish kunidan'", 1),
        ("proof: 'Minimal buyurtma: [__] dona'", "proof: 'Minimal buyurtma: ⟦min_order⟧ dona'", 1),
        ("proof: 'Kafolat: [__] oy · [tasdiqlash]'", "proof: 'Kafolat: ⟦warranty_months⟧ oy'", 1),
        ("proof: 'Javob: [__] soat ichida'", "proof: 'Javob: ⟦reply_hours⟧ soat ichida'", 1),
        ("a: '[__]–[__] hafta + bojxona', b: '[__] ish kunidan'", "a: '⟦import_weeks_min⟧–⟦import_weeks_max⟧ hafta + bojxona', b: '⟦lead_days⟧ ish kunidan'", 1),
        ("a: 'Ko\\'pincha [__]+ dona'", "a: 'Ko\\'pincha ⟦import_min_batch⟧+ dona'", 1),
        ("b: '[__] oy · bepul qayta tayyorlash'", "b: '⟦warranty_months⟧ oy · bepul qayta tayyorlash'", 1),
        ('Seriyali buyurtmada narx kamayadi. [Aniq shartlarni tasdiqlash]', 'Seriyali buyurtmada narx kamayadi. ⟦min_order_terms⟧', 1),
        ('"Standart detallar — [__] ish kuni, murakkab qolipli detallar — [__] ish kunigacha.', '"Standart detallar — ⟦lead_days⟧ ish kuni, murakkab qolipli detallar — ⟦lead_days_complex⟧ ish kunigacha.', 1),
        ("QQS bilan hisob-faktura. [Oldindan to'lov foizi — tasdiqlash]", "QQS bilan hisob-faktura. ⟦prepayment_terms⟧", 1),
        ('a: "[Yetkazib berish shartlari — o\'z transporti yoki kuryer xizmati, tasdiqlash]"', 'a: "⟦delivery_terms⟧"', 1),
        ("bepul qayta tayyorlaymiz. [Kafolat shartlarini tasdiqlash]", "bepul qayta tayyorlaymiz. ⟦warranty_terms⟧", 1),
    ],
    'Blog-G': [
        ('<a href="#" class="btn" style="background: {{accent}}; color: #111418; border: 1px solid #111418; padding: 18px 26px; font-weight: 700">@[interroll] kanaliga qo\'shilish →</a>',
         '<a href="https://t.me/⟦telegram_channel⟧" target="_blank" rel="noopener" class="btn" style="background: {{accent}}; color: #111418; border: 1px solid #111418; padding: 18px 26px; font-weight: 700">@⟦telegram_channel⟧ kanaliga qo\'shilish →</a>', 1),
    ],
    'Usta-G': [
        ('Oziq-ovqat bilan aloqa — [oziq-ovqatga ruxsat etilgan material, sertifikat bilan] tanlanadi.', 'Oziq-ovqat bilan aloqa — ⟦food_material⟧ tanlanadi.', 1),
    ],
    'Aloqa-G': [
        ('<span>[Shahar, tuman]</span>', '<span>⟦city_district⟧</span>', 1),
        ('<text x="512" y="86" fill="#5B636D">[Ko\'cha, uy]</text>', '<text x="512" y="86" fill="#5B636D">⟦street⟧</text>', 1),
        ('<text x="14" y="288">[Asosiy ko\'cha / trassa]</text>', '<text x="14" y="288">⟦main_road⟧</text>', 1),
        ('<text x="476" y="390">[Ko\'cha nomi]</text>', '<text x="476" y="390">⟦side_street⟧</text>', 1),
        ('<text x="16" y="312" fill="#5B636D">● [Metro / avtobus bekati]</text>', '<text x="16" y="312" fill="#5B636D">● ⟦transit_stop⟧</text>', 1),
        ('<a href="#" class="mono" style="flex: 1 1 200px; padding: 16px; border-right: 1px solid #111418; font-size: 13px; font-weight: 600; text-transform: uppercase">Yandex Xaritada',
         '<a href="⟦map_yandex⟧" target="_blank" rel="noopener" class="mono" style="flex: 1 1 200px; padding: 16px; border-right: 1px solid #111418; font-size: 13px; font-weight: 600; text-transform: uppercase">Yandex Xaritada', 1),
        ('<a href="#" class="mono" style="flex: 1 1 200px; padding: 16px; border-right: 1px solid #111418; font-size: 13px; font-weight: 600; text-transform: uppercase">Google Maps',
         '<a href="⟦map_google⟧" target="_blank" rel="noopener" class="mono" style="flex: 1 1 200px; padding: 16px; border-right: 1px solid #111418; font-size: 13px; font-weight: 600; text-transform: uppercase">Google Maps', 1),
        ('<a href="#" class="mono" style="flex: 1 1 200px; padding: 16px; font-size: 13px; font-weight: 600; text-transform: uppercase">Yandex Go',
         '<a href="⟦map_route⟧" target="_blank" rel="noopener" class="mono" style="flex: 1 1 200px; padding: 16px; font-size: 13px; font-weight: 600; text-transform: uppercase">Yandex Go', 1),
        ("[Viloyat, shahar,<br>ko'cha, uy]", '⟦address⟧', 1),
        ("Mo'ljal: [mo'ljal]<br>Yuk mashinasi kirishi: [darvoza]", "Mo'ljal: ⟦landmark⟧<br>Yuk mashinasi kirishi: ⟦truck_gate⟧", 1),
        ("Jamoat transporti: <b>[Metro / avtobus bekati]</b>, [__] daqiqa piyoda.<br>Avtomobilda: [mo'ljal], bepul to'xtash joyi [bor/yo'q].",
         "Jamoat transporti: <b>⟦transit_stop⟧</b>, ⟦walk_minutes⟧ daqiqa piyoda.<br>Avtomobilda: ⟦landmark⟧, bepul to'xtash joyi ⟦parking⟧.", 1),
        ('Ish vaqtida [__] daqiqa ichida javob beramiz.', 'Ish vaqtida ⟦reply_minutes⟧ daqiqa ichida javob beramiz.', 1),
        ('<a href="tel:+998000000000" class="mono" style="font-weight: 600; font-size: 15px">{{p.tel}}</a>',
         '<a href="{{p.href}}" class="mono" style="font-weight: 600; font-size: 15px">{{p.tel}}</a>', 1),
        ("v: '+998 [__] ___-__-__', href: 'tel:+998000000000'", "v: '⟦phone⟧', href: 'tel:⟦phone_href⟧'", 1),
        ("v: '@[interroll]', href: '#'", "v: '@⟦telegram⟧', href: 'https://t.me/⟦telegram⟧'", 1),
        ("v: '[info@interroll.uz]', href: 'mailto:info@example.com'", "v: '⟦email⟧', href: 'mailto:⟦email⟧'", 1),
        ("v: '[Shahar, ko\\'cha, uy]', href: '#'", "v: '⟦address⟧', href: '⟦map_yandex⟧'", 1),
        ("{ k: 'STIR (INN)', v: '[_________]' }, { k: 'Bank', v: '[Bank nomi, filial]' }", "{ k: 'STIR (INN)', v: '⟦inn⟧' }, { k: 'Bank', v: '⟦bank⟧' }", 1),
        ("{ k: 'H/r', v: '[____ ____ ____ ____ ____]' }, { k: 'MFO', v: '[_____]' }, { k: 'OKED', v: '[_____]' }, { k: 'Yuridik manzil', v: '[Manzil]' }",
         "{ k: 'H/r', v: '⟦account⟧' }, { k: 'MFO', v: '⟦mfo⟧' }, { k: 'OKED', v: '⟦oked⟧' }, { k: 'Yuridik manzil', v: '⟦legal_address⟧' }", 1),
        ('this.props.ochilish ?? 8', "(parseInt('⟦open_hour⟧', 10) || 8)", 1),
        ('this.props.yopilish ?? 18', "(parseInt('⟦close_hour⟧', 10) || 18)", 1),
        ("':00 – [__]:00'", "':00 – ⟦saturday_close_hour⟧:00'", 1),
        ("{ name: '[Ism Familiya]', role: 'Sotuv bo\\'limi', d: 'Narxlar, tijoriy taklif, shartnoma va yetkazib berish.', tel: '+998 [__] ___-__-__',",
         "{ name: '⟦sales_name⟧', role: 'Sotuv bo\\'limi', d: 'Narxlar, tijoriy taklif, shartnoma va yetkazib berish.', tel: '⟦sales_phone⟧', href: 'tel:⟦sales_phone_href⟧',", 1),
        ("{ name: '[Ism Familiya]', role: 'Bosh muhandis', d: 'Material tanlash, chizmalar, ish sharoiti bo\\'yicha maslahat.', tel: '+998 [__] ___-__-__',",
         "{ name: '⟦engineer_name⟧', role: 'Bosh muhandis', d: 'Material tanlash, chizmalar, ish sharoiti bo\\'yicha maslahat.', tel: '⟦engineer_phone⟧', href: 'tel:⟦engineer_phone_href⟧',", 1),
        ("{ name: '[Ism Familiya]', role: 'Buxgalteriya', d: 'Hisob-faktura, solishtirma dalolatnoma, to\\'lovlar.', tel: '+998 [__] ___-__-__',",
         "{ name: '⟦accounting_name⟧', role: 'Buxgalteriya', d: 'Hisob-faktura, solishtirma dalolatnoma, to\\'lovlar.', tel: '⟦accounting_phone⟧', href: 'tel:⟦accounting_phone_href⟧',", 1),
        # request number from the server answer (runtime-laravel sets window.__irLead and re-renders)
        ('Ariza raqami: <span class="mono">IR-[____]</span>', 'Ariza raqami: <span class="mono">{{leadNo}}</span>', 1),
        ('      sent: st.sent, notSent: !st.sent,', "      sent: st.sent, notSent: !st.sent, leadNo: window.__irLead || '…',", 1),
    ],
}


def apply(src_name, s):
    for old, new, count in RULES.get(src_name, []):
        s = _sub(s, old, new, count, src_name)
    return s


def to_placeholders(part, script):
    """⟦key⟧ → server placeholder; script=True for JS code (JS-escaped values)."""
    def rep(m):
        key = m.group(1)
        if key == 'none':
            return ''
        if m.group(0).endswith(':img⟧'):
            return '__SH_%s__' % key
        return ('__SJ_%s__' if script else '__S_%s__') % key
    return TOKEN.sub(rep, part)
