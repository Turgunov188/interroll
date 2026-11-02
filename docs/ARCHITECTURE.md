# Inter Roll — kod tuzilishi

Qoida: **controller faqat HTTP bilan ishlaydi** (so'rovni qabul qiladi, javob qaytaradi).
Biznes mantiq Action'larda, tekshiruv FormRequest'da, avtomatik ishlar Observer'da,
sahifa ma'lumoti PageData'da, ko'rinish Blade'da.

## Backend

| Qatlam | Papka | Vazifasi |
|---|---|---|
| Enum | `app/Enums` | `Locale` (ru asosiy), `LeadType`, `LeadStatus`, `OrderStatus`, `Material`, `UserRole`, `SettingKey`, `Configurator` — qiymat va yorliqlar bir joyda |
| DTO | `app/Data` | `LeadData` — HTTP'dan mustaqil, tekshirilgan ma'lumot |
| FormRequest | `app/Http/Requests` | `StoreLeadRequest` — qoidalar va `toData()`; `details[...]` (forma javoblari) menejer uchun rus tilidagi qatorlarga aylanadi (`lang/ru/leads.php`) |
| Middleware | `app/Http/Middleware` | `HoneypotTrap` (bot himoyasi), `SetAdminLocale` (admin — rus tilida) |
| Action | `app/Actions` | `CreateLead`, `CreateOrderFromLead`, `FindOrderForTracking` — bitta ish, bitta `handle()` |
| Observer | `app/Observers` | Lead raqami, buyurtma kodi/PIN, bosqichlar tarixi, keshni tozalash |
| Controller | `app/Http/Controllers` | `SiteController`, `ProductPageController`, `BlogPostController`, `LeadController` — 10–20 qator |
| Config | `config/site.php` | Sahifalar, menyular, qaysi sahifa bazadan ma'lumot oladi (`data`), materiallar xossalari, eski URL → yangi |
| Page data | `app/Services/Site/PageData` | Har sahifa uchun bitta klass: bazadan o'qiydi, keshlaydi, **view model**lar qaytaradi |
| View model | `app/Services/Site/ViewModels` | `ProductCard`, `ProductPage`, `ProjectCase`, `PostCard`, `Video`… — `final readonly`, Blade faqat shularni ko'radi |
| Presenter | `app/Services/Site/Presenters` | `ProductPresenter`, `PostPresenter` — modelni view modelga o'giradi |
| Sayt konteksti | `app/Services/Site/SiteContext` | Har sahifada `$site`: til, havolalar, hreflang, menyu, sozlamalar, tarjima |
| Cache | `CatalogCache`, `ContentCache` | Har til uchun; admin o'zgartirsa Observer tozalaydi |
| Filament | `app/Filament` | Admin panel; mantiq uchun o'sha Action'larni chaqiradi |

## Frontend (sayt)

Sahifalar serverda **Blade** bilan chiziladi (SEO uchun matn HTML'ning o'zida), interaktiv qismlar **Alpine.js**,
uslub — oddiy CSS (dizayn tokenlari). Yig'ish **Vite** (`npm run build` → `public/build`, gitga kiradi: hostingda Node yo'q).

| Nima | Qayerda |
|---|---|
| Layout, header, footer | `resources/views/components/site/layout.blade.php`, `header`, `footer`, `language-switch` |
| Umumiy komponentlar | `resources/views/components/site/*` — `<x-site.title>`, `<x-site.media>` (rasm yoki `[...]` joy), `<x-site.lead-form>`, `<x-site.field>`, `<x-site.dropzone>`, `<x-site.compare>`, `<x-site.faq>`, `<x-site.breadcrumbs>` |
| Sahifalar | `resources/views/site/pages/{page}.blade.php`; o'z interaktiv matni bor maqola — `resources/views/site/posts/` |
| Matnlar (4 til) | `lang/{ru,uz,en,tr}/*.php` — har sahifaga bitta fayl (`home`, `catalog`, `product`…), umumiylari `common`, `nav`, `meta`, `js` |
| Sozlamalar matnda | Matnda `:lead_days`, `:phone` kabi joylar — `$site->t()` ularni «Настройки сайта» qiymati bilan to'ldiradi; to'ldirilmagani `[__]` |
| CSS | `resources/css/site.css` → `site/base` (tokenlar, shriftlar, reset, layout, tipografiya), `site/components`, `site/pages` |
| JS | `resources/js/site.js` (Alpine komponentlarini ro'yxatdan o'tkazadi) → `site/core` (i18n, so'rov yuborish, toast), `site/components`, `site/pages`, `site/effects` (fon to'ri, zarrachalar, 3D valik), `site/shapes` (konfigurator: chizma + 3D) |
| JS testlari | `npm test` — masalan `site/pages/part-finder/recommend.test.js` (tanlash ustasi qoidalari) |

Formalar oddiy HTML forma (`name="phone"`, `name="items"`…): JavaScript bo'lsa fonda yuboriladi va natija joyida
ko'rinadi, bo'lmasa brauzer o'zi yuboradi va sahifaga qaytadi (`LeadController`).

## Oqimlar

Sayt formasi: `POST /leads` → `HoneypotTrap` → `throttle` → `StoreLeadRequest` → `toData()` → `CreateLead::handle()` → `Lead` → `LeadObserver` (L-000123) → JSON 201 (yoki JSsiz — redirect back).

Sahifa: `GET /catalog` → `SiteController` → `config('site.data.catalog')` = `CatalogPageData` (kesh) → `SiteResponse` → `site/pages/catalog.blade.php` (`$products`, `$categories`, `$industries` + `$site`).
Mahsulot: `GET /product/{slug}` → `ProductPageController` → `ProductPageData` → `site/pages/product.blade.php`.
Mahsulot ko'rinishlari fayllarga qarab chiqadi: «Чертёж» — konfigurator bo'lsa jonli chizma, bo'lmasa yuklangan chizmalar; «3D» — .glb bo'lsa `<model-viewer>` (kerak bo'lgandagina yuklanadi), bo'lmasa CSS-3D; «Фото» — yuklangan fotolar.

Konfigurator: `roller`, `wheel`, `bushing`, `plate`, `spider`. Slayderlar (kalit, chegaralar, 4 tildagi nomi) — `Configurator::parameters()`; chizish va 3D — `resources/js/site/shapes/{shape}.js`. Mijoz tanlagani `items` bo'lib leadga boradi, admin uni `Configurator::describeSelection()` orqali o'qiydigan qilib ko'radi.

Admin «Создать заказ»: `EditLead` → `CreateOrderFromLead::handle()` (tranzaksiya, ikki marta yaratib bo'lmaydi) → `OrderObserver` (IR-1001, PIN, tarix).

## Ishga tushirish

```
composer install
npm install
npm run build          # yoki ishlab chiqishda: npm run dev
php artisan migrate --seed
php artisan db:seed --class=CatalogSeeder    # boshlang'ich katalog (dublikat qilmaydi)
php artisan db:seed --class=ContentSeeder    # ishlar, maqolalar, videolar — yashirin qoralama
```

Testlar: `php artisan test` (PHP), `npm test` (JS). Kod uslubi: `./vendor/bin/pint`.
Frontendni o'zgartirgandan keyin `npm run build` qilib, `public/build` ni ham commit qiling.
