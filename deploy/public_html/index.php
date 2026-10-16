<?php

/*
| Front controller for the hosting: this file lives in public_html,
| the project itself in ../laravel (one level up, out of the web root).
*/

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$project = __DIR__.'/../laravel';

if (file_exists($maintenance = $project.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $project.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $project.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
