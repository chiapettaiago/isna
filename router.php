<?php
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = realpath(__DIR__ . rawurldecode($uri));
$root = realpath(__DIR__);

if ($uri !== '/' && $file && $root && strpos($file, $root . DIRECTORY_SEPARATOR) === 0 && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
