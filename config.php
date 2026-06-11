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
        'disable_update_check' => false,
        'git_binary' => '/usr/bin/git',
        'github_owner' => 'chiapettaiago',
        'github_repo' => 'isna',
        'github_branch' => 'master',
    ],
    
    // Configuração para ambiente beta
    'isna.org.br' => [
        'debug' => false,
        'force_https' => true,
        'disable_update_check' => false,
        'git_binary' => '/usr/bin/git',
        'github_owner' => 'chiapettaiago',
        'github_repo' => 'isna',
        'github_branch' => 'master',
    ],
    
    // Configuração padrão
    'default' => [
        'debug' => false,
        'force_https' => false,
        'disable_update_check' => false,
        'git_binary' => '/usr/bin/git',
        'github_owner' => 'chiapettaiago',
        'github_repo' => 'isna',
        'github_branch' => 'master',
    ]
];

// Detectar ambiente atual
$current_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$config = $environment_config['default'];

foreach ($environment_config as $host => $settings) {
    if (strpos($current_host, $host) !== false) {
        $config = $settings;
        break;
    }
}

// Aplicar configurações
$forwarded = $_SERVER['HTTP_FORWARDED'] ?? '';
$forwarded_proto = null;
if ($forwarded && preg_match('/proto=([^;\s]+)/i', $forwarded, $matches)) {
    $forwarded_proto = strtolower($matches[1]);
}
$xf_proto = isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
    ? strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]))
    : null;
$is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
    || $xf_proto === 'https'
    || $forwarded_proto === 'https';

if ($config['force_https'] && !$is_https) {
    // Verificar se não estamos em localhost antes de forçar HTTPS
    if (strpos($current_host, 'localhost') === false && strpos($current_host, '127.0.0.1') === false) {
        $redirect_url = 'https://' . $current_host . ($_SERVER['REQUEST_URI'] ?? '/');
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
