
<?php
// Carregar configurações do ambiente
$config = include 'config.php';

// Detectar protocolo/host considerando proxy (Codespaces) e padronizar base URL
// 1) Tenta usar cabeçalho Forwarded: proto=..., host=...
$forwarded = isset($_SERVER['HTTP_FORWARDED']) ? $_SERVER['HTTP_FORWARDED'] : '';
$f_proto = null;
$f_host = null;
if ($forwarded) {
    if (preg_match('/proto=([^;\s]+)/i', $forwarded, $m)) $f_proto = $m[1];
    if (preg_match('/host=([^;\s]+)/i', $forwarded, $m)) $f_host = $m[1];
}

// 2) Fallback para X-Forwarded-Proto/Host (pode vir com múltiplos valores separados por vírgula)
$xf_proto = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'])[0]) : null;
$xf_host  = isset($_SERVER['HTTP_X_FORWARDED_HOST'])  ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])[0])  : null;

// 3) Caso não haja cabeçalhos de proxy, usa HTTPS/HTTP_HOST normais
$protocol = $f_proto ?: ($xf_proto ?: ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http'));
$domain   = $f_host  ?: ($xf_host  ?: (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'));

// 4) Fallback: se ainda vier localhost/0.0.0.0, tenta extrair do Origin/Referer (ex.: *.github.dev)
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

// 5) Fallback final: construir domínio via variáveis do Codespaces
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

// Detectar o diretório base automaticamente
$script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$base_path = dirname($script_name);

// Normalizar o base_path
if ($base_path === '/' || $base_path === '\\' || $base_path === '.' || $base_path === false) {
    $base_path = '';
}

// Garantir que o base_path não termine com /
$base_path = rtrim($base_path, '/');

// Montar base_url SEM barra final para evitar // ao concatenar
$base_url = $protocol . '://' . $domain . $base_path;

// Para uso em templates e links
$site_url = $base_url;

// Função para gerar URLs corretas
function url($path = '') {
    global $site_url;
    $path = ltrim($path, '/');
    return $site_url . ($path ? '/' . $path : '');
}

// Função para gerar URLs de recursos (imagens, CSS, JS)
function asset($path) {
    global $site_url;
    $path = ltrim($path, '/');
    return $site_url . '/' . $path;
}

// Função simples para substituir caminhos de recursos estáticos
function replaceStaticPaths($content) {
    global $site_url;
    
    // Apenas substitui se $site_url estiver definido e content não for null
    if (!empty($site_url) && $content !== null) {
        // Substituir caminhos de recursos de forma mais simples
        // Usar str_replace em vez de regex complexas
        
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
        
        // Aplicar substituições simples
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        
        // Tratar links de navegação de forma mais cuidadosa
        $content = preg_replace('/href="\/([^"\/][^"]*)"/', 'href="' . $site_url . '/$1"', $content);
    }
    
    return $content;
}

// Buffer de saída reativado com função mais simples
ob_start("replaceStaticPaths");

// Get request URI and parse path
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remover o diretório base do caminho para o roteamento
if (!empty($base_path) && strpos($path, $base_path) === 0) {
    $path = substr($path, strlen($base_path));
}

// Garantir que o caminho sempre comece com /
if (empty($path) || $path[0] !== '/') {
    $path = '/' . $path;
}

// Define routes
$routes = [
    '/' => ['file' => 'pages/home.php', 'title' => 'ISNA - Impacto Social'],
    '/quem-somos' => ['file' => 'pages/quem-somos.php', 'title' => 'Quem Somos - ISNA'],
    '/linha-atuacao' => ['file' => 'pages/linha-atuacao.php', 'title' => 'Linha de Atuação - ISNA'],
    '/galeria' => ['file' => 'pages/galeria.php', 'title' => 'Projetos em Execução - ISNA'],
    '/parceiros' => ['file' => 'pages/parceiros.php', 'title' => 'Parceiros - ISNA'],
    '/transparencia' => ['file' => 'pages/transparencia.php', 'title' => 'Transparência - ISNA'],
    '/titulos-documentos' => ['file' => 'pages/titulos-documentos.php', 'title' => 'Títulos e Documentos - ISNA'],
    '/doe' => ['file' => 'pages/doe.php', 'title' => 'Doe - ISNA'],
    '/doacoes-bancarias' => ['file' => 'pages/bank-donations.php', 'title' => 'Doações Bancárias - ISNA'],
    '/sobre' => ['file' => 'pages/sobre.php', 'title' => 'Sobre o Site - ISNA'],
];

// Default page title and content file
$pageTitle = 'Página Não Encontrada - ISNA';
$pageToInclude = null;
$is404 = true;

if (array_key_exists($path, $routes)) {
    $pageFile = $routes[$path]['file'];
    if (is_readable($pageFile)) {
        $pageTitle = $routes[$path]['title'];
        $pageToInclude = $pageFile;
        $is404 = false; // Page found and readable
    } else {
        // File not readable, server error
        http_response_code(500);
        $pageTitle = 'Erro Interno do Servidor - ISNA';
        // error_log("Error: Page file not readable: $pageFile for path: $path"); // Optional: Log this error
    }
} else {
    // Path not in routes, 404
    http_response_code(404);
}

// Include header
// Pass variables to be used by templates
include 'templates/header.php';

// Include page content
if ($pageToInclude) {
    // Disponibiliza a variável $site_url para todas as páginas
    include $pageToInclude;
} else {
    // Display 404 or error message
    echo '<div class="container py-5 text-center">';
    if (http_response_code() === 404) {
        echo '<h1 class="display-1">404</h1>';
        echo '<h2>Página Não Encontrada</h2>';
        echo '<p class="lead">Desculpe, a página que você está procurando não existe ou foi movida.</p>';
    } else { // Covers 500 or other errors where $pageToInclude is not set
        echo '<h1 class="display-1">Erro</h1>';
        echo '<h2>Ocorreu um problema</h2>';
        echo '<p class="lead">Não foi possível carregar o conteúdo desta página. Por favor, tente novamente mais tarde.</p>';
    }
    echo '<a href="' . url() . '" class="btn btn-primary mt-3">Voltar à Página Inicial</a>';
    echo '</div>';
}

// Include footer
include 'templates/footer.php';

// Finaliza o buffer de saída e envia ao navegador
ob_end_flush();
?>
