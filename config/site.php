<?php

use App\Services\Site\PageData\BlogPageData;
use App\Services\Site\PageData\CatalogPageData;
use App\Services\Site\PageData\FactoryPageData;
use App\Services\Site\PageData\HomePageData;
use App\Services\Site\PageData\PartFinderPageData;
use App\Services\Site\PageData\ProjectsPageData;
use App\Services\Site\PageData\VideosPageData;

/*
| Public site. Templates: resources/views/site/pages/{page}.blade.php, texts: lang/{locale}/*.php.
| URI (main language, no prefix) => page. Other languages get a prefix, see App\Enums\Locale::path().
*/
return [

    'pages' => [
        '/' => 'index',
        '/catalog' => 'catalog',
        '/recoating' => 'recoating',
        '/factory' => 'factory',
        '/contact' => 'contact',
        '/part-finder' => 'part-finder',
        '/projects' => 'projects',
        '/video' => 'video',
        '/blog' => 'blog',
    ],

    // Pages that receive data from the database (page => App\Services\Site\PageData\PageData).
    'data' => [
        'index' => HomePageData::class,
        'factory' => FactoryPageData::class,
        'catalog' => CatalogPageData::class,
        'part-finder' => PartFinderPageData::class,
        'projects' => ProjectsPageData::class,
        'blog' => BlogPageData::class,
        'video' => VideosPageData::class,
    ],

    // Menus: page names, labels come from lang/{locale}/nav.php.
    'navigation' => [
        'main' => ['index', 'catalog', 'recoating', 'projects', 'blog', 'factory', 'contact'],
        'more' => ['video', 'part-finder'],
        'footer' => ['projects', 'video', 'blog', 'part-finder'],
    ],

    // Sub-pages highlight their section in the menu: route page => menu page.
    'sections' => [
        'product' => 'catalog',
        'product.default' => 'catalog',
        'post' => 'blog',
    ],

    // Blog articles: /blog/{slug}. An article listed in "custom" has its own interactive body
    // (a Blade view) instead of the text from the admin; the title, author and cover still come from there.
    'blog' => [
        'uri' => '/blog',
        'custom' => ['shore-a-hardness' => 'site.posts.shore-a-hardness'],
    ],

    // Product pages: /product/{slug}; plain /product opens the default product.
    'product' => [
        'uri' => '/product',
        'default' => 'ir-v01',
    ],

    // Part finder: products suggested for each part type of the first step (by SKU).
    'part_finder' => [
        'products' => [
            'roller' => ['IR-V01', 'IR-K01'],
            'wheel' => ['IR-G01', 'IR-G02'],
            'plate' => ['IR-P01', 'IR-P03'],
            'coupling' => ['IR-M01', 'IR-S01'],
            'unknown' => ['IR-X01', 'IR-V01'],
        ],
    ],

    // Coating materials compared on the recoating page and by the part finder:
    // abrasion, oil, temperature, elasticity — 1 (low) … 4 (excellent); colour of the coating in the drawings.
    'materials' => [
        'pu' => ['levels' => [4, 3, 2, 3], 'color' => '#f5b700'],
        'nbr' => ['levels' => [2, 4, 2, 3], 'color' => '#2a3038'],
        'epdm' => ['levels' => [2, 1, 3, 4], 'color' => '#4a535e'],
        'sil' => ['levels' => [1, 2, 4, 3], 'color' => '#c9542e'],
    ],

    // Old (Uzbek) URLs → new ones, 301 in every language, so shared links and search results keep working.
    'redirects' => [
        '/katalog' => '/catalog',
        '/mahsulot' => '/product',
        '/mahsulot/{slug}' => '/product/{slug}',
        '/qayta-qoplash' => '/recoating',
        '/zavod' => '/factory',
        '/aloqa' => '/contact',
        '/tanlash-ustasi' => '/part-finder',
        '/ishlar' => '/projects',
        '/blog/shore-a' => '/blog/shore-a-hardness',
        '/ru' => '/',
    ],

    // Line icons (SVG path, 64×64) per category slug; other categories use "maxsus".
    'category_icons' => [
        'valik' => 'M10 22h44v20H10z M10 32h44 M4 30h6v4H4z M54 30h6v4h-6z M10 22c-3 0-4 4.5-4 10s1 10 4 10',
        'gildirak' => 'M32 6a26 26 0 1 0 0.01 0z M32 18a14 14 0 1 0 0.01 0z M32 28a4 4 0 1 0 0.01 0z M32 18v-12 M32 46v12 M18 32H6 M58 32H46',
        'qoplash' => 'M16 22h32v20H16z M16 32h32 M8 14l8 8 M56 50l-8-8 M8 14h6 M8 14v6 M56 50h-6 M56 50v-6',
        'plastina' => 'M6 42l40-22 12 8-40 22z M14 40l34-18 M6 42v6l12 8v-6',
        'mufta' => 'M32 6l5 13 13-5-5 13 13 5-13 5 5 13-13-5-5 13-5-13-13 5 5-13-13-5 13-5-5-13 13 5z M32 26a6 6 0 1 0 0.01 0z',
        'avto' => 'M20 12h24v40H20z M26 12v40 M38 12v40 M14 20h6 M14 44h6 M44 20h6 M44 44h6',
        'maxsus' => 'M12 54V10h28l12 12v32z M40 10v12h12 M20 40h24 M20 46h16 M24 30a6 6 0 1 0 0.01 0z',
    ],

];
