<?php

use App\Services\Site\PageData\BlogPageData;
use App\Services\Site\PageData\CatalogPageData;
use App\Services\Site\PageData\FactoryPageData;
use App\Services\Site\PageData\HomePageData;
use App\Services\Site\PageData\ProjectsPageData;
use App\Services\Site\PageData\VideosPageData;

/*
| Public site pages built from the approved design (see resources/site/{locale}/*.html).
| URI (main language, no prefix) => page file name. Other languages get a prefix,
| see App\Enums\Locale::path().
*/
return [

    'directory' => 'site',

    'pages' => [
        '/' => 'index',
        '/catalog' => 'catalog',
        '/recoating' => 'recoating',
        '/factory' => 'factory',
        '/contact' => 'contact',
        '/part-finder' => 'part-finder',
        // '/order-tracking' => 'order-tracking',   // switched off for now (also DISABLED in build_i18n.py)
        '/projects' => 'projects',
        '/video' => 'video',
        '/blog' => 'blog',
    ],

    // Pages that receive data from the database (page name => App\Services\Site\PageData\PageData).
    'data' => [
        'index' => HomePageData::class,
        'factory' => FactoryPageData::class,
        'catalog' => CatalogPageData::class,
        'projects' => ProjectsPageData::class,
        'blog' => BlogPageData::class,
        'video' => VideosPageData::class,
    ],

    // Blog articles: /blog/{slug} with the article design ("page"). Articles with their own
    // interactive page are listed in "custom" (slug => page) and keep it.
    'blog' => [
        'uri' => '/blog',
        'page' => 'blog-post',
        'custom' => ['shore-a-hardness' => 'blog-shore-a-hardness'],
    ],

    // Product pages: /product/{slug}; plain /product opens the default product.
    'product' => [
        'uri' => '/product',
        'page' => 'product',
        'default' => 'ir-v01',
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

    // Line icons per category slug (same drawings as the catalog design); others use "maxsus".
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
