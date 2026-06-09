<?php 
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if (preg_match('#^/documento/(.+)\.pdf$#', $uri, $matches)) {
        $_GET['token'] = $matches[1];
        require __DIR__ . '/app/views/documento.php';
        exit;
    }

    $file = realpath(__DIR__ . rawurldecode($uri));
    $root = realpath(__DIR__);

    if ($uri !== '/' && $file && $root && strpos($file, $root . DIRECTORY_SEPARATOR) === 0 && is_file($file)) {
        return false; // Deixa o servidor web servir o arquivo estático
    }

    require __DIR__ . '/index.php';

?>
