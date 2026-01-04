<?php
// Carregar configurações do ambiente
$config = include 'config.php';

// Bootstrap centralizado
require_once __DIR__ . '/app/bootstrap.php';

// Inicia a sessão para recursos de autenticação
AuthService::startSession();

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
    require_once __DIR__ . '/app/controllers/AuthController.php';
    $auth = new AuthController();
    $auth->logout();
}

if ($path === '/login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/app/controllers/AuthController.php';
    $auth = new AuthController();
    $auth->login();
}

// API: retorna estatísticas de acesso em JSON
if ($path === '/api/access-stats' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');

    if (!AuthService::check()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $logFile = __DIR__ . '/logs/access_log';
    $today = new DateTimeImmutable('today');
    $defaultStart = $today->modify('-29 days');

    $fromParam = isset($_GET['from']) ? trim((string) $_GET['from']) : '';
    $toParam = isset($_GET['to']) ? trim((string) $_GET['to']) : '';

    $fromDate = DateTimeImmutable::createFromFormat('Y-m-d', $fromParam) ?: $defaultStart;
    $toDate = DateTimeImmutable::createFromFormat('Y-m-d', $toParam) ?: $today;

    $fromDate = $fromDate->setTime(0, 0);
    $toDate = $toDate->setTime(0, 0);

    if ($fromDate > $toDate) {
        $temp = $fromDate; $fromDate = $toDate; $toDate = $temp;
    }

    $dailyCounts = [];
    if (is_readable($logFile)) {
        $handle = fopen($logFile, 'r');
        if ($handle !== false) {
            while (($line = fgets($handle)) !== false) {
                if (!preg_match('/"([A-Z]+) ([^\s]+) HTTP\/[0-9.]+" ([0-9]{3})/', $line, $requestMatches)) {
                    continue;
                }

                $method = $requestMatches[1];
                $resource = $requestMatches[2];
                $statusCode = (int) $requestMatches[3];

                if ($method !== 'GET' || $statusCode >= 500) {
                    continue;
                }

                $pathOnly = parse_url($resource, PHP_URL_PATH);
                if ($pathOnly === null) continue;
                if (preg_match('/\.(css|js|png|jpe?g|gif|svg|ico|webp|pdf|mp4|mp3|zip|json|txt|xml)$/i', $pathOnly)) continue;

                $excludedExact = ['/area-restrita', '/gestao-usuarios', '/gestao-galeria', '/login', '/logout'];
                $excludedPrefixes = ['/gestao-', '/admin'];
                if (in_array($pathOnly, $excludedExact, true)) continue;
                $skip = false;
                foreach ($excludedPrefixes as $prefix) {
                    if (strncmp($pathOnly, $prefix, strlen($prefix)) === 0) { $skip = true; break; }
                }
                if ($skip) continue;

                if (!preg_match('/\[(\d{2}\/[^\/]{3}\/\d{4}):(\d{2}:\d{2}:\d{2}) ([+\-]\d{4})\]/', $line, $dateMatches)) {
                    continue;
                }

                $dateTime = DateTimeImmutable::createFromFormat('d/M/Y H:i:s O', $dateMatches[1] . ' ' . $dateMatches[2] . ' ' . $dateMatches[3]);
                if (!$dateTime) continue;
                $dateTime = $dateTime->setTime(0, 0);
                if ($dateTime < $fromDate || $dateTime > $toDate) continue;

                $key = $dateTime->format('Y-m-d');
                $dailyCounts[$key] = ($dailyCounts[$key] ?? 0) + 1;
            }
            fclose($handle);
        }
    }

    $period = new DatePeriod($fromDate, new DateInterval('P1D'), $toDate->modify('+1 day'));
    $labels = [];
    $values = [];
    foreach ($period as $day) {
        $key = $day->format('Y-m-d');
        $labels[] = $day->format('d/m');
        $values[] = (int) ($dailyCounts[$key] ?? 0);
    }

    $total = array_sum($values);
    $count = count($values);
    $average = $count > 0 ? ($total / $count) : 0;
    $peakLabel = null; $peakValue = 0;
    if (!empty($values)) {
        $maxValue = max($values);
        if ($maxValue > 0) {
            $index = array_search($maxValue, $values, true);
            if ($index !== false) { $peakLabel = $labels[$index]; $peakValue = $maxValue; }
        }
    }

    echo json_encode([
        'labels' => $labels,
        'values' => $values,
        'total' => $total,
        'average' => $average,
        'peakLabel' => $peakLabel,
        'peakValue' => $peakValue,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (in_array($path, $protectedRoutes, true) && !AuthService::check()) {
    AuthService::flashMessage('warning', 'Faça login para acessar a página solicitada.');
    $intended = $path;
    $queryString = parse_url($requestUri, PHP_URL_QUERY);

    if ($queryString) {
        $intended .= '?' . $queryString;
    }

    AuthService::rememberIntended($intended);
    AuthService::redirect('login');
}

if ($path === '/gestao-usuarios' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/app/controllers/UserController.php';
    $uc = new UserController();
    $uc->handlePost();
}

if ($path === '/gestao-galeria' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/app/controllers/GalleryController.php';
    $gc = new GalleryController();
    $gc->handlePost();
}
if ($path === '/gestao-blog' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/app/controllers/BlogController.php';
    $bc = new BlogController();
    $bc->handlePost();
}



$currentUser = AuthService::user();

// Define routes using Router
$router = new Router();
$router->add('/', ['file' => 'home.php', 'title' => 'ISNA - Impacto Social']);
$router->add('/quem-somos', ['file' => 'quem-somos.php', 'title' => 'Quem Somos - ISNA']);
$router->add('/linha-atuacao', ['file' => 'linha-atuacao.php', 'title' => 'Linha de Atuação - ISNA']);
$router->add('/galeria', ['file' => 'galeria.php', 'title' => 'Projetos em Execução - ISNA']);
$router->add('/parceiros', ['file' => 'parceiros.php', 'title' => 'Parceiros - ISNA']);
$router->add('/transparencia', ['file' => 'transparencia.php', 'title' => 'Transparência - ISNA']);
$router->add('/titulos-documentos', ['file' => 'titulos-documentos.php', 'title' => 'Títulos e Documentos - ISNA']);
$router->add('/doe', ['file' => 'doe.php', 'title' => 'Doe - ISNA']);
$router->add('/doacoes-bancarias', ['file' => 'bank-donations.php', 'title' => 'Doações Bancárias - ISNA']);
$router->add('/sobre', ['file' => 'sobre.php', 'title' => 'Sobre o Site - ISNA']);
$router->add('/login', ['file' => 'login.php', 'title' => 'Entrar - ISNA']);
$router->add('/area-restrita', ['file' => 'area-restrita.php', 'title' => 'Área Restrita - ISNA']);
$router->add('/gestao-usuarios', ['file' => 'gestao-usuarios.php', 'title' => 'Gestão de Usuários - ISNA']);
$router->add('/gestao-galeria', ['file' => 'gestao-galeria.php', 'title' => 'Gestão da Galeria - ISNA']);
$router->add('/gestao-blog', ['file' => 'gestao-blog.php', 'title' => 'Gestão do Blog - ISNA']);

// Dispatch route
$meta = $router->dispatch($path);

// If route not found, dispatch returned status 404
if (isset($meta['status']) && $meta['status'] === 404) {
    http_response_code(404);
}

// Use PageController to render pages (keeps header/footer consistent)
require_once __DIR__ . '/app/controllers/PageController.php';
$controller = new PageController();
$controller->show($meta);

// Finaliza o buffer de saída e envia ao navegador
ob_end_flush();
?>
