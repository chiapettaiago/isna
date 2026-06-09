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

$protocol = strtolower((string)($f_proto ?: ($xf_proto ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http'))));
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

function normalize_public_path($path = ''): string {
    $path = trim((string)$path);
    if ($path === '') {
        return '';
    }

    if (preg_match('~^(?:[a-z][a-z0-9+.-]*:|//|data:|blob:|mailto:|tel:|#)~i', $path)) {
        return $path;
    }

    return '/' . ltrim($path, '/');
}

function url($path = '') {
    global $site_url;
    $path = normalize_public_path($path);
    if ($path === '' || $path === '/') {
        return $site_url . '/';
    }
    if (preg_match('~^(?:[a-z][a-z0-9+.-]*:|//|data:|blob:|mailto:|tel:|#)~i', $path)) {
        return $path;
    }
    return $site_url . $path;
}

function asset($path) {
    return url($path);
}

function public_path_url($path = ''): string {
    return url($path);
}

function app_request_path(string $basePath = ''): string {
    $candidates = [];

    foreach (['route', '_route_', 'path', 'page'] as $key) {
        if (isset($_GET[$key]) && is_string($_GET[$key]) && trim($_GET[$key]) !== '') {
            $candidates[] = $_GET[$key];
        }
    }

    foreach (['PATH_INFO', 'ORIG_PATH_INFO', 'REDIRECT_URL', 'REQUEST_URI'] as $key) {
        if (isset($_SERVER[$key]) && is_string($_SERVER[$key]) && trim($_SERVER[$key]) !== '') {
            $candidates[] = $_SERVER[$key];
        }
    }

    foreach ($candidates as $candidate) {
        $path = parse_url($candidate, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            continue;
        }

        $path = '/' . ltrim($path, '/');

        if ($basePath !== '' && strpos($path, $basePath . '/') === 0) {
            $path = substr($path, strlen($basePath));
        } elseif ($basePath !== '' && $path === $basePath) {
            $path = '/';
        }

        $path = preg_replace('#/index\.php/?#', '/', $path, 1);
        $path = '/' . ltrim($path, '/');
        $path = rtrim($path, '/') ?: '/';

        if ($path !== '/' || $candidate === '/' || $candidate === $basePath) {
            return $path;
        }
    }

    return '/';
}

function replaceStaticPaths($content) {
    global $site_url;
    if (!empty($site_url) && $content !== null) {
        $publicDirectories = '(?:css|js|images|videos|docs|thumbnails)';
        $content = preg_replace(
            '/\b(src|href|poster|action|data-[a-z0-9_-]+)=([\'"])\/(' . $publicDirectories . '\/[^\'"]*)\2/i',
            '$1=$2' . $site_url . '/$3$2',
            $content
        );
        $content = preg_replace(
            '/url\(([\'"])\/(' . $publicDirectories . '\/[^\'")]+)\1\)/i',
            'url($1' . $site_url . '/$2$1)',
            $content
        );
        $content = preg_replace(
            '/href="\/([^"\/][^"]*)"/',
            'href="' . $site_url . '/$1"',
            $content
        );
    }
    return $content;
}

// Load core classes
require_once __DIR__ . '/Core/Router.php';
require_once __DIR__ . '/Core/Controller.php';
require_once __DIR__ . '/Core/View.php';

// Load app services that depend on helpers (auth uses $site_url)
require_once __DIR__ . '/compat/auth.php';
require_once __DIR__ . '/compat/cms.php';
require_once __DIR__ . '/compat/gallery.php';
require_once __DIR__ . '/compat/blog.php';
