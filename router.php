<?php
// Router para PHP embutido: serve arquivos estáticos diretamente e encaminha o resto ao index.php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$path = __DIR__ . $uri;

if ($uri !== '/' && file_exists($path) && !is_dir($path)) {
    return false; // Deixa o PHP embutido servir o arquivo
}

require __DIR__ . '/index.php';
