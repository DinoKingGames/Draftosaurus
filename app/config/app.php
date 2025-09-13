<?php

define('BASE_PATH', realpath(__DIR__ . '/../../'));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');


$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

$script = $_SERVER['SCRIPT_NAME'] ?? '';
$prefix = '';
if (($pos = strpos($script, '/public/')) !== false) {
    $prefix = substr($script, 0, $pos) . '/public';
}
define('BASE_URL', ($host ? $scheme . '://' . $host : '') . rtrim($prefix, '/'));

function view_partial(string $name): void {
    require APP_PATH . '/Client/parciales/' . $name . '.php';
}

function asset(string $path): string {
    return BASE_URL . '/assets/' . ltrim($path, '/');
}