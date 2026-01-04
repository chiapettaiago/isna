<?php

// Bootstrap: define site URL helpers and load core dependencies

// Load config if not already loaded
if (!isset($config)) {
    $config = include __DIR__ . '/../config.php';
}

// Detect protocol/host considering proxy headers
$forwarded = isset($_SERVER['HTTP_FORWARDED']) ? $_SERVER['HTTP_FORWARDED'] : '';
$f_proto = null;
$f_host = null;
if ($forwarded) {
    if (preg_match('/proto=([^;\s]+)/i', $forwarded, $m)) $f_proto = $m[1];
    if (preg_match('/host=([^;\s]+)/i', $forwarded, $m)) $f_host = $m[1];
}

$xf_proto = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]) : null;
$xf_host  = isset($_SERVER['HTTP_X_FORWARDED_HOST'])  ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])[0])  : null;

$protocol = $f_proto ?: ($xf_proto ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http'));
$domain   = $f_host  ?: ($xf_host  ?: (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'));

if (preg_match('/^(localhost|127\.0\.0\.1|0\.0\.0\.0)(:\d+)?$/', $domain)) {
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $cand = $origin ?: $referer;
    if ($cand) {
        $h = parse_url($cand, PHP_URL_HOST);
        $p = parse_url($cand, PHP_URL_SCHEME);
        if ($h && preg_match('/(github\.dev|app\.github\.dev|githubpreview\.dev)$/', $h)) {
            $domain = $h;
            if ($p) $protocol = $p; else $protocol = 'https';
        }
    }
}

if ((preg_match('/^(localhost|127\.0\.0\.1|0\.0\.0\.0)(:\d+)?$/', $domain))) {
    $is_codespaces = getenv('CODESPACES');
    $cs_name = getenv('CODESPACE_NAME');
    $pf_domain = getenv('GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN');
    $fwd_port = isset($_SERVER['HTTP_X_FORWARDED_PORT']) ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PORT'])[0]) : null;
    $srv_port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null;
    if ($is_codespaces && $cs_name) {
        $protocol = 'https';
        $pf_domain = $pf_domain ?: 'github.dev';
        $port = $fwd_port ?: ($srv_port ?: '8080');
        $domain = $cs_name . '-' . $port . '.' . $pf_domain;
    }
}

// Detect base path
$script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$base_path = dirname($script_name);
if ($base_path === '/' || $base_path === '\\' || $base_path === '.' || $base_path === false) {
    $base_path = '';
}
$base_path = rtrim($base_path, '/');

$base_url = $protocol . '://' . $domain . $base_path;

$site_url = $base_url;

function url($path = '') {
    global $site_url;
    $path = ltrim($path, '/');
    return $site_url . ($path ? '/' . $path : '');
}

function asset($path) {
    global $site_url;
    $path = ltrim($path, '/');
    return $site_url . '/' . $path;
}

function replaceStaticPaths($content) {
    global $site_url;
    if (!empty($site_url) && $content !== null) {
        $replacements = [
            'src="/images/' => 'src="' . $site_url . '/images/',
            'href="/css/' => 'href="' . $site_url . '/css/',
            'src="/js/' => 'src="' . $site_url . '/js/',
            'href="/js/' => 'href="' . $site_url . '/js/',
            'src="/videos/' => 'src="' . $site_url . '/videos/',
            'href="/docs/' => 'href="' . $site_url . '/docs/',
            'href="/images/favicon.ico"' => 'href="' . $site_url . '/images/favicon.ico"',
            "background-image: url('/images/" => "background-image: url('" . $site_url . "/images/",
            'background-image: url("/images/' => 'background-image: url("' . $site_url . '/images/',
        ];

        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        $content = preg_replace('/href="\/([^"\/][^"]*)"/', 'href="' . $site_url . '/$1"', $content);
    }
    return $content;
}

// Load core classes
require_once __DIR__ . '/Core/Router.php';
require_once __DIR__ . '/Core/Controller.php';
require_once __DIR__ . '/Core/View.php';

// Load app services that depend on helpers (auth uses $site_url)
require_once __DIR__ . '/compat/auth.php';
require_once __DIR__ . '/compat/gallery.php';
require_once __DIR__ . '/compat/blog.php';
