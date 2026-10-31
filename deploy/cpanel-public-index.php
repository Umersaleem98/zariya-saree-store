<?php
// Only this entry point and static public assets belong in public_html.
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
define('LARAVEL_START', microtime(true));
$applicationRoot = dirname(__DIR__).'/apps/rangmahal';
if (file_exists($maintenance = $applicationRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}
require $applicationRoot.'/vendor/autoload.php';
/** @var Application $app */
$app = require_once $applicationRoot.'/bootstrap/app.php';
$app->usePublicPath(__DIR__);
$app->handleRequest(Request::capture());
