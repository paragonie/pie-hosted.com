<?php
declare(strict_types=1);
use ParagonIE\Hosted\Hosted;

require_once \dirname(__DIR__) . '/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$dispatcher = Hosted::getRouteDispatcher();

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// PHP Built-in-Webserver: Show CSS/JS properly.
$file = $_SERVER['DOCUMENT_ROOT'] . '/' . $uri;
if (\file_exists($file) && !\is_dir($file)) {
    $realpath = \realpath($file);
    if (\strpos($realpath, $_SERVER['DOCUMENT_ROOT']) === 0) {
        if (\preg_match('/\.(js|css)$/', \strtolower($realpath), $matches)) {
            $ext = $matches[1];
            switch($ext) {
                case 'css':
                    \header('Content-Type: text/css');
                    break;
                case 'js':
                    \header('Content-Type: application/javascript');
                    break;
            }
            echo \file_get_contents($realpath);
            exit;
        }
    }
}

// Strip query string (?foo=bar) and decode URI
$pos = strpos($uri, '?');
if ($pos !== false) {
    $uri = \substr($uri, 0, $pos);
}
$uri = \rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        $response = Hosted::getErrorPage(404);
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $response = Hosted::getErrorPage(405);
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        if (\class_exists($handler)) {
            $obj = new $handler;
            $response = $obj(...$vars);
        } elseif (\function_exists($handler)) {
            $response = $handler(...$vars);
        } else {
            $response = Hosted::getErrorPage();
        }
        break;
    default:
        $response = Hosted::getErrorPage();
}
Hosted::finalizeOutput($response);
