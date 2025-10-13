
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

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/gallery.php';
require_once __DIR__ . '/blog.php';

// Inicia a sessão para recursos de autenticação
auth_start_session();

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

$currentUser = null;
$protectedRoutes = ['/area-restrita', '/gestao-usuarios', '/gestao-galeria', '/gestao-blog', '/sobre'];

if ($path === '/logout') {
    auth_logout();
    auth_flash_message('success', 'Sessão encerrada com sucesso.');
    auth_redirect('login');
}

if ($path === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? (string) $_POST['username'] : '';
    $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!auth_validate_csrf_token('login', $csrfToken)) {
        auth_flash_message('error', 'Token de segurança inválido. Por favor, tente novamente.');
        auth_flash_value('old_username', $username);
        auth_redirect('login');
    }

    if (auth_attempt($username, $password)) {
        $redirectTo = auth_take_intended(url('area-restrita'));
        auth_flash_message('success', 'Login realizado com sucesso.');
        header('Location: ' . $redirectTo);
        exit;
    }

    auth_flash_message('error', 'Usuário ou senha inválidos.');
    auth_flash_value('old_username', $username);
    auth_redirect('login');
}

if (in_array($path, $protectedRoutes, true) && !auth_check()) {
    auth_flash_message('warning', 'Faça login para acessar a página solicitada.');
    $intended = $path;
    $queryString = parse_url($requestUri, PHP_URL_QUERY);

    if ($queryString) {
        $intended .= '?' . $queryString;
    }

    auth_remember_intended($intended);
    auth_redirect('login');
}

if ($path === '/gestao-usuarios' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!auth_check()) {
        auth_redirect('login');
    }

    $action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

    if ($action === 'update_password') {
        $token = $_POST['csrf_token'] ?? '';

        if (!auth_validate_csrf_token('update_password', $token)) {
            auth_flash_message('error', 'Token de segurança inválido. Por favor, tente novamente.');
            auth_redirect('gestao-usuarios');
        }

        $currentPassword = isset($_POST['current_password']) ? (string) $_POST['current_password'] : '';
        $newPassword = isset($_POST['new_password']) ? (string) $_POST['new_password'] : '';
        $confirmPassword = isset($_POST['new_password_confirmation']) ? (string) $_POST['new_password_confirmation'] : '';

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            auth_flash_message('error', 'Preencha todos os campos para alterar a senha.');
            auth_redirect('gestao-usuarios');
        }

        if ($newPassword !== $confirmPassword) {
            auth_flash_message('error', 'A confirmação da nova senha não confere.');
            auth_redirect('gestao-usuarios');
        }

        if (strlen($newPassword) < 8) {
            auth_flash_message('error', 'A nova senha deve conter pelo menos 8 caracteres.');
            auth_redirect('gestao-usuarios');
        }

        $username = auth_user_username();

        if ($username === null) {
            auth_flash_message('error', 'Não foi possível identificar o usuário autenticado.');
            auth_redirect('gestao-usuarios');
        }

        $users = auth_users();

        if (!isset($users[$username])) {
            auth_flash_message('error', 'Registro de usuário não encontrado.');
            auth_redirect('gestao-usuarios');
        }

        $currentHash = $users[$username]['password'] ?? '';

        if (!is_string($currentHash) || $currentHash === '' || !password_verify($currentPassword, $currentHash)) {
            auth_flash_message('error', 'Senha atual incorreta.');
            auth_redirect('gestao-usuarios');
        }

        $users[$username]['password'] = password_hash($newPassword, PASSWORD_DEFAULT);

        if (!auth_users_save($users)) {
            auth_flash_message('error', 'Não foi possível atualizar a senha. Verifique as permissões do arquivo.');
            auth_redirect('gestao-usuarios');
        }

        auth_flash_message('success', 'Senha atualizada com sucesso.');
        auth_redirect('gestao-usuarios');
    } elseif ($action === 'create_user') {
        if (!auth_user_is_admin()) {
            auth_flash_message('error', 'Você não tem permissão para criar usuários.');
            auth_redirect('gestao-usuarios');
        }

        $token = $_POST['csrf_token'] ?? '';

        if (!auth_validate_csrf_token('create_user', $token)) {
            auth_flash_message('error', 'Token de segurança inválido. Por favor, tente novamente.');
            auth_redirect('gestao-usuarios');
        }

        $usernameInput = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
        $displayName = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
        $password = isset($_POST['password']) ? (string) $_POST['password'] : '';
        $passwordConfirmation = isset($_POST['password_confirmation']) ? (string) $_POST['password_confirmation'] : '';
        $isAdminRequested = isset($_POST['is_admin']) && $_POST['is_admin'] === '1';

        $preserveFormState = static function () use ($usernameInput, $displayName, $isAdminRequested): void {
            auth_flash_value('create_user_username', $usernameInput);
            auth_flash_value('create_user_name', $displayName);
            auth_flash_value('create_user_is_admin', $isAdminRequested ? '1' : '0');
        };

        if ($usernameInput === '') {
            $preserveFormState();
            auth_flash_message('error', 'Informe um nome de usuário.');
            auth_redirect('gestao-usuarios');
        }

        $usernameNormalized = auth_normalize_username($usernameInput);

        if ($usernameNormalized === '') {
            $preserveFormState();
            auth_flash_message('error', 'O nome de usuário informado é inválido.');
            auth_redirect('gestao-usuarios');
        }

        if (!preg_match('/^[a-z0-9._-]{4,}$/', $usernameNormalized)) {
            $preserveFormState();
            auth_flash_message('error', 'Use pelo menos 4 caracteres (letras minúsculas, números, ponto, hífen ou sublinhado).');
            auth_redirect('gestao-usuarios');
        }

        if ($password === '' || $passwordConfirmation === '') {
            $preserveFormState();
            auth_flash_message('error', 'Defina uma senha e confirme-a.');
            auth_redirect('gestao-usuarios');
        }

        if ($password !== $passwordConfirmation) {
            $preserveFormState();
            auth_flash_message('error', 'A confirmação da senha não confere.');
            auth_redirect('gestao-usuarios');
        }

        if (strlen($password) < 8) {
            $preserveFormState();
            auth_flash_message('error', 'A senha deve conter pelo menos 8 caracteres.');
            auth_redirect('gestao-usuarios');
        }

        $users = auth_users();

        if (isset($users[$usernameNormalized])) {
            $preserveFormState();
            auth_flash_message('error', 'Já existe um usuário com esse login.');
            auth_redirect('gestao-usuarios');
        }

        $users[$usernameNormalized] = [
            'name' => $displayName !== '' ? $displayName : $usernameInput,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'roles' => $isAdminRequested ? ['admin'] : [],
        ];

        if (!auth_users_save($users)) {
            $preserveFormState();
            auth_flash_message('error', 'Não foi possível criar o usuário. Verifique as permissões do arquivo.');
            auth_redirect('gestao-usuarios');
        }

        auth_flash_message('success', 'Usuário criado com sucesso.');
        auth_redirect('gestao-usuarios');
    }
}

if ($path === '/gestao-galeria' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!auth_check()) {
        auth_redirect('login');
    }

    if (!auth_user_is_admin()) {
        auth_flash_message('error', 'Apenas administradores podem alterar a galeria.');
        auth_redirect('area-restrita');
    }

    $action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

    if ($action === 'create_section') {
        $token = $_POST['csrf_token'] ?? '';

        if (!auth_validate_csrf_token('gallery_create_section', $token)) {
            auth_flash_message('error', 'Token de segurança inválido. Por favor, tente novamente.');
            auth_redirect('gestao-galeria');
        }

        $title = isset($_POST['title']) ? trim((string) $_POST['title']) : '';
        $background = isset($_POST['background']) ? trim((string) $_POST['background']) : '';
        $description = isset($_POST['description']) ? trim((string) $_POST['description']) : '';

        auth_flash_value('gallery_create_title', $title);
        auth_flash_value('gallery_create_background', $background);
        auth_flash_value('gallery_create_description', $description);

        if ($title === '') {
            auth_flash_message('error', 'Informe um título para a nova seção.');
            auth_redirect('gestao-galeria');
        }

        $allowedBackgrounds = ['', 'bg-light', 'bg-white'];
        if (!in_array($background, $allowedBackgrounds, true)) {
            $background = '';
        }

        $config = gallery_load();
        $existingIds = array_map(static function ($section) {
            return $section['id'];
        }, $config['sections']);

        $baseSlug = gallery_slug($title);
        $slug = $baseSlug;
        $suffix = 1;
        while (in_array($slug, $existingIds, true)) {
            $suffix++;
            $slug = $baseSlug . '-' . $suffix;
        }

        $config['sections'][] = [
            'id' => $slug,
            'title' => $title,
            'type' => 'grid',
            'background' => $background,
            'description' => $description,
            'items' => [],
        ];

        if (!gallery_save($config)) {
            auth_flash_message('error', 'Não foi possível salvar a nova seção. Verifique as permissões do arquivo.');
            auth_redirect('gestao-galeria');
        }

        auth_flash_message('success', 'Seção criada com sucesso. Agora adicione itens para populá-la.');
        auth_redirect('gestao-galeria');
    } elseif ($action === 'add_item') {
        $token = $_POST['csrf_token'] ?? '';

        if (!auth_validate_csrf_token('gallery_add_item', $token)) {
            auth_flash_message('error', 'Token de segurança inválido. Por favor, tente novamente.');
            auth_redirect('gestao-galeria');
        }

        $sectionId = isset($_POST['section_id']) ? trim((string) $_POST['section_id']) : '';
        $src = isset($_POST['src']) ? trim((string) $_POST['src']) : '';
        $alt = isset($_POST['alt']) ? trim((string) $_POST['alt']) : '';
        $caption = isset($_POST['caption']) ? trim((string) $_POST['caption']) : '';

        auth_flash_value('gallery_item_section', $sectionId);
        auth_flash_value('gallery_item_src', $src);
        auth_flash_value('gallery_item_alt', $alt);
        auth_flash_value('gallery_item_caption', $caption);

        if ($sectionId === '') {
            auth_flash_message('error', 'Escolha uma seção para adicionar o item.');
            auth_redirect('gestao-galeria');
        }

        if ($src === '') {
            auth_flash_message('error', 'Informe o caminho ou URL da imagem.');
            auth_redirect('gestao-galeria');
        }

        $config = gallery_load();
        $found = false;

        foreach ($config['sections'] as &$section) {
            if ($section['id'] !== $sectionId) {
                continue;
            }

            $found = true;

            if ($section['type'] !== 'grid') {
                auth_flash_message('error', 'Não é possível adicionar itens diretamente em seções automáticas.');
                auth_redirect('gestao-galeria');
            }

            if (!isset($section['items']) || !is_array($section['items'])) {
                $section['items'] = [];
            }

            $section['items'][] = [
                'type' => 'image',
                'src' => $src,
                'alt' => $alt !== '' ? $alt : $section['title'],
                'caption' => $caption,
            ];
            break;
        }
        unset($section);

        if (!$found) {
            auth_flash_message('error', 'Seção selecionada não encontrada.');
            auth_redirect('gestao-galeria');
        }

        if (!gallery_save($config)) {
            auth_flash_message('error', 'Não foi possível salvar o item. Verifique as permissões do arquivo.');
            auth_redirect('gestao-galeria');
        }

        auth_flash_message('success', 'Item adicionado à galeria com sucesso.');
        auth_redirect('gestao-galeria');
    } else {
        auth_flash_message('error', 'Ação da galeria desconhecida.');
        auth_redirect('gestao-galeria');
    }
}
if ($path === '/gestao-blog' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!auth_check()) {
        auth_redirect('login');
    }

    if (!auth_user_is_admin()) {
        auth_flash_message('error', 'Apenas administradores podem gerenciar o blog.');
        auth_redirect('area-restrita');
    }

    $action = isset($_POST['action']) ? trim((string) $_POST['action']) : '';

    if ($action === 'create_post') {
        $token = $_POST['csrf_token'] ?? '';

        if (!auth_validate_csrf_token('blog_create_post', $token)) {
            auth_flash_message('error', 'Token de segurança inválido. Por favor, tente novamente.');
            auth_redirect('gestao-blog');
        }

        $title = isset($_POST['title']) ? trim((string) $_POST['title']) : '';
        $summary = isset($_POST['summary']) ? trim((string) $_POST['summary']) : '';
        $content = isset($_POST['content']) ? trim((string) $_POST['content']) : '';
        $author = isset($_POST['author']) ? trim((string) $_POST['author']) : '';

        auth_flash_value('blog_post_title', $title);
        auth_flash_value('blog_post_summary', $summary);
        auth_flash_value('blog_post_content', $content);
        auth_flash_value('blog_post_author', $author);

        if ($title === '' || $content === '') {
            auth_flash_message('error', 'Informe pelo menos o título e o conteúdo do post.');
            auth_redirect('gestao-blog');
        }

        $config = blog_load();
        $posts = $config['posts'];

        $baseSlug = blog_slug($title);
        $slug = $baseSlug;
        $suffix = 1;

        $existingIds = array_map(static function ($post) {
            return $post['id'];
        }, $posts);

        while (in_array($slug, $existingIds, true)) {
            $suffix++;
            $slug = $baseSlug . '-' . $suffix;
        }

        $posts[] = [
            'id' => $slug,
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'author' => $author !== '' ? $author : 'Equipe ISNA',
            'published_at' => date('c'),
        ];

        $savePayload = ['posts' => $posts];

        if (!blog_save($savePayload)) {
            auth_flash_message('error', 'Não foi possível salvar o post. Verifique as permissões do arquivo.');
            auth_redirect('gestao-blog');
        }

        auth_flash_message('success', 'Post publicado com sucesso!');
        auth_redirect('gestao-blog');
    } else {
        auth_flash_message('error', 'Ação do blog desconhecida.');
        auth_redirect('gestao-blog');
    }
}



$currentUser = auth_user();

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
    '/login' => ['file' => 'pages/login.php', 'title' => 'Entrar - ISNA'],
    '/area-restrita' => ['file' => 'pages/area-restrita.php', 'title' => 'Área Restrita - ISNA'],
    '/gestao-usuarios' => ['file' => 'pages/gestao-usuarios.php', 'title' => 'Gestão de Usuários - ISNA'],
    '/gestao-galeria' => ['file' => 'pages/gestao-galeria.php', 'title' => 'Gestão da Galeria - ISNA'],
    '/gestao-blog' => ['file' => 'pages/gestao-blog.php', 'title' => 'Gestão do Blog - ISNA'],
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
