# Inter Roll — kod tuzilishi

Qoida: **controller faqat HTTP bilan ishlaydi** (so'rovni qabul qiladi, javob qaytaradi).
Biznes mantiq Action'larda, tekshiruv FormRequest'da, avtomatik ishlar Observer'da.

| Qatlam | Papka | Vazifasi |
|---|---|---|
| Enum | `app/Enums` | `Locale` (ru asosiy), `LeadType`, `LeadStatus`, `OrderStatus`, `Material`, `UserRole` — qiymat va yorliqlar bir joyda |
| DTO | `app/Data` | `LeadData` — HTTP'dan mustaqil, tekshirilgan ma'lumot |
| FormRequest | `app/Http/Requests` | `StoreLeadRequest` — qoidalar va `toData()` |
| Middleware | `app/Http/Middleware` | `HoneypotTrap` (bot himoyasi), `SetAdminLocale` (admin — rus tilida) |
| Action | `app/Actions` | `CreateLead`, `CreateOrderFromLead`, `FindOrderForTracking` — bitta ish, bitta `handle()` |
| Observer | `app/Observers` | Lead raqami, buyurtma kodi/PIN, bosqichlar tarixi, kim o'zgartirgani |
| Service | `app/Services/Site` | `SitePageRenderer` — tayyor sahifani o'qib, token va manzilni qo'yadi |
| Config | `config/site.php` | Sayt sahifalari ro'yxati, qaysi sahifa bazadan ma'lumot olishi (`data`), mahsulot sahifasi sozlamalari |
| Page data | `app/Services/Site/PageData` | `CatalogPageData` (keshlanadi), `ProductPageData` — sahifaga `window.__IR` bo'lib boradi |
| Presenter | `app/Services/Site/Presenters` | `ProductPresenter` — Product modelini front kutgan shaklga o'giradi |
| Media | `product_media` jadvali, `ProductMedia`, `MediaType` | Mahsulot fayllari turi bilan: `photo` → «Фото», `drawing` → «Чертёж», `model3d` (.glb) → «3D», `document` → «Документы». Fayl o'chirilsa yoki almashtirilsa diskdan ham o'chadi (`ProductMediaObserver`) |
| Configurator | `products.configurator`, `Configurator` enum, `build-tools/shapes.js` | `roller`, `wheel`, `bushing`, `plate`, `spider`: tashrif buyuruvchi o'lchamlarni suradi, jonli chizma (SVG) va 3D (CSS) shunga qarab chiziladi. Parametrlar va 4 tildagi nomlari `Configurator::parameters()` da; shakllarni chizish `shapes.js` da (har shakl = ko'pburchak prizma). Konfiguratorsiz mahsulotda yuklangan fayllar ko'rinadi |
| Cache | `app/Services/Catalog/CatalogCache` | Katalog har til uchun keshlanadi; `CatalogObserver` admin o'zgarishida tozalaydi |
| Controller | `app/Http/Controllers` | `SiteController`, `LeadController` — 5–10 qatordan oshmaydi |
| Filament | `app/Filament` | Admin panel; mantiq uchun o'sha Action'larni chaqiradi |

| Sayt sozlamalari | `site_settings`, `SettingKey` enum, `SiteSettings`, admin «Настройки → Настройки сайта» | Telefon, manzil, ish vaqti, raqamlar (Shore A, Ø, muddatlar), rekvizitlar, fotolar. Sahifada `__S_key__` (HTML), `__SJ_key__` (JS ichida), `__SH_key__` (rasm) joylari bor — `SiteResponse` ularni qiymat bilan almashtiradi. To'ldirilmagan qiymat saytda `[__]` bo'lib ko'rinadi. Yangi sozlama = `SettingKey` ga bitta case + `build-tools/site_tokens.py` da joyi |
| Kontent | `projects`, `posts` + `post_categories`, `videos`, `clients`, `testimonials`, `certificates` | Admin «Контент» guruhi. Sahifaga `PageData` orqali (`ProjectsPageData`, `BlogPageData`, `VideosPageData`, `FactoryPageData`, `HomePageData`), `ContentCache` da keshlanadi, `ContentObserver` o'zgarishda yangilaydi |
| Blog maqola | `/blog/{slug}` → `BlogPostController` | Matn admin rich-editoridan, `SafeHtml` tozalaydi (script, onclick, javascript: o'chadi), H2 lardan mundarija yasaladi. `config/site.php` → `blog.custom`: o'z interaktiv sahifasi bor maqolalar (Shore A) |
| Video | `videos` | YouTube havola (pleyerda embed) yoki .mp4 fayl; boblar ustiga bosilsa o'sha joydan qo'yiladi |
| Boshlang'ich kontent | `ContentSeeder` | Dizayndagi ishlar, maqolalar, videolar 4 tilda — **yashirin qoralama** bo'lib qo'shiladi. `[__]` larni to'ldirib, «Показывать на сайте» ni yoqish kerak |

Oqim (sayt formasi):
`POST /leads` → `HoneypotTrap` → `throttle` → `StoreLeadRequest` (validatsiya) → `toData()` → `CreateLead::handle()` → `Lead` → `LeadObserver` (L-000123) → JSON 201.

Katalog: `GET /catalog` → `SiteController` → `CatalogPageData` (kesh) → `SiteResponse` sahifaga JSON qo'yadi → front `window.__IR` dan chizadi.
Mahsulot: `GET /product/{slug}` → `ProductPageController` → `ProductPageData` → o'sha `product` sahifa dizayni.
Mahsulot sahifasidagi ko'rinishlar fayllarga qarab chiqadi: «Чертёж» — konfigurator bo'lsa jonli chizma, bo'lmasa yuklangan chizmalar; «3D» — .glb bo'lsa `<model-viewer>`, bo'lmasa konfiguratorli valik uchun CSS-3D; «Фото» — yuklangan fotolar. Fayli yo'q ko'rinish tugmasi umuman chiqmaydi.

Admin «Создать заказ»: `EditLead` → `CreateOrderFromLead::handle()` (tranzaksiya, ikki marta yaratib bo'lmaydi) → `OrderObserver` (IR-1001, PIN, tarix).

Boshlang'ich katalog: `php artisan db:seed --class=CatalogSeeder` (qayta ishga tushirsa ham dublikat qilmaydi).
Boshlang'ich kontent: `php artisan db:seed --class=ContentSeeder` (mavjud yozuvlarga tegmaydi).

Testlar: `php artisan test` (`tests/Feature`, `tests/Unit`). Kod uslubi: `./vendor/bin/pint`.
