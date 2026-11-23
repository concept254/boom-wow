<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
 * cPanel layout: the Laravel app lives OUTSIDE public_html, e.g. /home/ACCOUNT/boom-wow
 * Change $appPath if you used a different folder name.
 */
$appPath = dirname(__DIR__).'/boom-wow';

if (file_exists($maintenance = $appPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $appPath.'/vendor/autoload.php';

$app = require_once $appPath.'/bootstrap/app.php';

// Make public_path() point at public_html so images, uploads and Vite builds resolve.
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
