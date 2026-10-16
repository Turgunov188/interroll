# Sayt sahifalarining manbasi

`resources/site/{ru,uz,en,tr}/*.html` qo'lda tahrir qilinmaydi — ular shu yerdan yig'iladi.

| Papka | Ichida |
|---|---|
| `design/` | Tasdiqlangan dizayn sahifalari (`*.dc.html`, o'zbekcha matn bilan) |
| `i18n/` | Tarjimalar: `ru.json`, `en.json`, `tr.json` (asosiy), `extra.tsv` (qo'shimcha matnlar), `tokens.tsv` (sozlama joylari bor matnlar) |
| `build-tools/build_i18n.py` | Yig'uvchi: sahifalar ro'yxati, URL'lar, menyu, til almashtirgich |
| `build-tools/i18n_apply.py` | Tarjima (matn, atributlar, JS satrlari) |
| `build-tools/dynamic_patches.py` | Sahifalarni bazaga ulash (`window.__IR`): katalog, mahsulot, ishlar, blog, video, bosh sahifa, zavod |
| `build-tools/site_tokens.py` | Dizayndagi `[__]` joylar → «Настройки сайта» qiymatlari (`__S_key__`) |
| `build-tools/runtime-laravel.js` | Sahifalarni brauzerda chizadigan kichik runtime + formalarni `/leads` ga yuborish |
| `build-tools/shapes.js` | Konfigurator: jonli chizma (SVG) va 3D |
| `build-tools/content_seed.py` | `database/seeders/data/content.php` ni dizayndan yasaydi |

## Qayta yig'ish

Python 3.10+ kerak (Node faqat tekshiruv uchun):

```
cd site-src/build-tools
python build_i18n.py ../design ../..
python content_seed.py ../design ../..      # faqat dizayndagi boshlang'ich kontent o'zgarsa
npm install && node smoke_i18n.js ../..      # tekshiruv: har sahifa 4 tilda ochiladimi
```

Yangi matn tarjimasiz qolsa — `i18n/extra.tsv` ga qator qo'shing (`uz<TAB>ru<TAB>en<TAB>tr`).
