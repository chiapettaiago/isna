<?php

declare(strict_types=1);

/**
 * Funções auxiliares de autenticação simples para o site ISNA.
 */

function auth_start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function auth_normalize_username(string $username): string
{
    $username = trim($username);

    if ($username === '') {
        return '';
    }

    return strtolower($username);
}

function auth_users(bool $refresh = false): array
{
    static $users = null;

    if ($refresh) {
        $users = null;
    }

    if ($users === null) {
        $file = auth_users_path();
        $users = [];

        if (is_readable($file)) {
            $data = include $file;

            if (is_array($data)) {
                foreach ($data as $username => $info) {
                    if (!is_array($info)) {
                        continue;
                    }

                    $normalized = auth_normalize_username((string) $username);

                    if ($normalized === '') {
                        continue;
                    }

                    $name = isset($info['name']) && is_string($info['name']) && $info['name'] !== '' ? $info['name'] : $normalized;
                    $password = isset($info['password']) && is_string($info['password']) ? $info['password'] : '';
                    $roles = [];

                    if (isset($info['roles']) && is_array($info['roles'])) {
                        foreach ($info['roles'] as $role) {
                            if (!is_string($role)) {
                                continue;
                            }

                            $role = trim($role);

                            if ($role === '') {
                                continue;
                            }

                            $roles[] = $role;
                        }
                    }

                    $users[$normalized] = [
                        'name' => $name,
                        'password' => $password,
                        'roles' => array_values(array_unique($roles)),
                    ];
                }
            }
        }
    }

    return $users;
}

function auth_users_path(): string
{
    return __DIR__ . '/config/users.php';
}

function auth_render_users_file(array $users): string
{
    $lines = [
        '<?php',
        '',
        'declare(strict_types=1);',
        '',
        'return [',
    ];

    foreach ($users as $username => $info) {
        $lines[] = "    '" . addslashes($username) . "' => [";
        $lines[] = "        'name' => '" . addslashes($info['name']) . "',";
        $lines[] = "        'password' => '" . addslashes($info['password']) . "',";

        $roles = $info['roles'] ?? [];
        $rolesString = '[]';

        if (!empty($roles)) {
            $escaped = array_map(static function ($role) {
                return "'" . addslashes($role) . "'";
            }, $roles);
            $rolesString = '[' . implode(', ', $escaped) . ']';
        }

        $lines[] = "        'roles' => " . $rolesString . ',';
        $lines[] = '    ],';
    }

    $lines[] = '];';
    $lines[] = '';

    return implode("\n", $lines);
}

function auth_users_save(array $users): bool
{
    $prepared = [];

    foreach ($users as $username => $info) {
        if (!is_array($info)) {
            continue;
        }

        $normalized = auth_normalize_username((string) $username);

        if ($normalized === '') {
            continue;
        }

        $name = isset($info['name']) && is_string($info['name']) && $info['name'] !== '' ? trim($info['name']) : $normalized;
        $password = isset($info['password']) && is_string($info['password']) ? trim($info['password']) : '';

        if ($password === '') {
            continue;
        }

        $roles = [];

        if (isset($info['roles']) && is_array($info['roles'])) {
            foreach ($info['roles'] as $role) {
                if (!is_string($role)) {
                    continue;
                }

                $role = strtolower(trim($role));

                if ($role === '') {
                    continue;
                }

                $roles[] = $role;
            }
        }

        $prepared[$normalized] = [
            'name' => $name,
            'password' => $password,
            'roles' => array_values(array_unique($roles)),
        ];
    }

    if (empty($prepared)) {
        return false;
    }

    ksort($prepared);

    $content = auth_render_users_file($prepared);
    $path = auth_users_path();
    $tempPath = $path . '.' . bin2hex(random_bytes(8));

    if (file_put_contents($tempPath, $content) === false) {
        return false;
    }

    if (!@rename($tempPath, $path)) {
        @unlink($tempPath);
        return false;
    }

    auth_users(true);

    return true;
}

function auth_user_username(): ?string
{
    $user = auth_user();

    if (!$user) {
        return null;
    }

    $username = $user['username'] ?? null;

    if (!is_string($username) || $username === '') {
        return null;
    }

    return auth_normalize_username($username);
}

function auth_user_roles(): array
{
    $user = auth_user();

    if (!$user) {
        return [];
    }

    $roles = $user['roles'] ?? [];

    if (!is_array($roles)) {
        return [];
    }

    $clean = [];

    foreach ($roles as $role) {
        if (!is_string($role)) {
            continue;
        }

        $role = strtolower(trim($role));

        if ($role === '') {
            continue;
        }

        $clean[] = $role;
    }

    return array_values(array_unique($clean));
}

function auth_has_role(string $role): bool
{
    $role = strtolower(trim($role));

    if ($role === '') {
        return false;
    }

    foreach (auth_user_roles() as $userRole) {
        if ($userRole === $role) {
            return true;
        }
    }

    return false;
}

function auth_user_is_admin(): bool
{
    return auth_has_role('admin');
}

function auth_attempt(string $username, string $password): bool
{
    auth_start_session();

    $username = auth_normalize_username($username);

    if ($username === '' || $password === '') {
        return false;
    }

    $users = auth_users();

    if (!isset($users[$username])) {
        return false;
    }

    $user = $users[$username];
    $hash = $user['password'] ?? null;

    if (!is_string($hash) || $hash === '') {
        return false;
    }

    if (!password_verify($password, $hash)) {
        return false;
    }

    $_SESSION['auth_user'] = [
        'username' => $username,
        'name' => isset($user['name']) && is_string($user['name']) && $user['name'] !== '' ? $user['name'] : $username,
        'roles' => isset($user['roles']) && is_array($user['roles']) ? $user['roles'] : [],
    ];

    return true;
}

function auth_check(): bool
{
    auth_start_session();

    return isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user']);
}

function auth_user(): ?array
{
    auth_start_session();

    return auth_check() ? $_SESSION['auth_user'] : null;
}

function auth_logout(): void
{
    auth_start_session();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }

    session_destroy();
}

function auth_flash_message(string $type, string $message): void
{
    auth_start_session();

    if (!isset($_SESSION['auth_flash_messages'][$type]) || !is_array($_SESSION['auth_flash_messages'][$type])) {
        $_SESSION['auth_flash_messages'][$type] = [];
    }

    $_SESSION['auth_flash_messages'][$type][] = $message;
}

function auth_flash_messages(string $type): array
{
    auth_start_session();

    if (!isset($_SESSION['auth_flash_messages'][$type]) || !is_array($_SESSION['auth_flash_messages'][$type])) {
        return [];
    }

    $messages = $_SESSION['auth_flash_messages'][$type];
    unset($_SESSION['auth_flash_messages'][$type]);

    return $messages;
}

function auth_flash_value(string $key, $value): void
{
    auth_start_session();
    $_SESSION['auth_flash_values'][$key] = $value;
}

function auth_flash_pull_value(string $key, $default = null)
{
    auth_start_session();

    if (!isset($_SESSION['auth_flash_values'][$key])) {
        return $default;
    }

    $value = $_SESSION['auth_flash_values'][$key];
    unset($_SESSION['auth_flash_values'][$key]);

    return $value;
}

function auth_remember_intended(string $uri): void
{
    auth_start_session();
    $_SESSION['auth_intended'] = $uri;
}

function auth_take_intended(string $default): string
{
    auth_start_session();

    $target = $_SESSION['auth_intended'] ?? null;
    unset($_SESSION['auth_intended']);

    if (!is_string($target) || $target === '') {
        return $default;
    }

    if (filter_var($target, FILTER_VALIDATE_URL)) {
        return $target;
    }

    if ($target[0] === '/') {
        $base = $GLOBALS['site_url'] ?? '';
        return $base . $target;
    }

    $base = $GLOBALS['site_url'] ?? '';

    return $base . '/' . ltrim($target, '/');
}

function auth_redirect(string $path = ''): void
{
    $location = $path;

    if ($path === '') {
        $location = $GLOBALS['site_url'] ?? '/';
    } elseif (!filter_var($path, FILTER_VALIDATE_URL)) {
        $base = $GLOBALS['site_url'] ?? '';
        $clean = '/' . ltrim($path, '/');
        $location = $base . $clean;
    }

    header('Location: ' . $location);
    exit;
}

function auth_require(): void
{
    if (auth_check()) {
        return;
    }

    auth_flash_message('warning', 'Faça login para acessar a página solicitada.');

    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $intendedPath = parse_url($uri, PHP_URL_PATH) ?: '/';
    $query = parse_url($uri, PHP_URL_QUERY);

    if ($query) {
        $intendedPath .= '?' . $query;
    }

    auth_remember_intended($intendedPath);

    auth_redirect('login');
}

function auth_generate_csrf_token(string $form = 'default'): string
{
    auth_start_session();
    $token = bin2hex(random_bytes(32));
    $_SESSION['auth_csrf'][$form] = $token;

    return $token;
}

function auth_validate_csrf_token(string $form, ?string $token): bool
{
    auth_start_session();

    if (!isset($_SESSION['auth_csrf'][$form])) {
        return false;
    }

    $expected = $_SESSION['auth_csrf'][$form];
    unset($_SESSION['auth_csrf'][$form]);

    if (!is_string($token) || $token === '') {
        return false;
    }

    return hash_equals($expected, $token);
}
