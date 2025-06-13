<?php
/**
 * Arquivo de configuração para diferentes ambientes
 * 
 * Este arquivo pode ser personalizado para cada ambiente (desenvolvimento, beta, produção)
 */

// Configuração automática baseada no ambiente
$environment_config = [
    // Configuração para desenvolvimento local
    'localhost' => [
        'debug' => true,
        'force_https' => false,
    ],
    
    // Configuração para ambiente beta
    'isna.org.br' => [
        'debug' => false,
        'force_https' => true,
    ],
    
    // Configuração padrão
    'default' => [
        'debug' => false,
        'force_https' => false,
    ]
];

// Detectar ambiente atual
$current_host = $_SERVER['HTTP_HOST'];
$config = $environment_config['default'];

foreach ($environment_config as $host => $settings) {
    if (strpos($current_host, $host) !== false) {
        $config = $settings;
        break;
    }
}

// Aplicar configurações
if ($config['force_https'] && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
    // Verificar se não estamos em localhost antes de forçar HTTPS
    if (strpos($_SERVER['HTTP_HOST'], 'localhost') === false && strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false) {
        $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header("Location: $redirect_url", true, 301);
        exit;
    }
}

// Configuração de erro reporting
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

return $config;
