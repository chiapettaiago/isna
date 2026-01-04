<?php

declare(strict_types=1);

class AuthService
{
    public static function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function normalizeUsername(string $username): string
    {
        $username = trim($username);
        if ($username === '') return '';
        return strtolower($username);
    }

    public static function users(bool $refresh = false): array
    {
        static $users = null;

        if ($refresh) {
            $users = null;
        }

        if ($users === null) {
            $file = self::usersPath();
            $users = [];

            if (is_readable($file)) {
                $data = include $file;
                if (is_array($data)) {
                    foreach ($data as $username => $info) {
                        if (!is_array($info)) continue;
                        $normalized = self::normalizeUsername((string)$username);
                        if ($normalized === '') continue;
                        $name = isset($info['name']) && is_string($info['name']) && $info['name'] !== '' ? $info['name'] : $normalized;
                        $password = isset($info['password']) && is_string($info['password']) ? $info['password'] : '';
                        $roles = [];
                        if (isset($info['roles']) && is_array($info['roles'])) {
                            foreach ($info['roles'] as $role) {
                                if (!is_string($role)) continue;
                                $role = trim($role);
                                if ($role === '') continue;
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

    public static function usersPath(): string
    {
        return __DIR__ . '/../../config/users.php';
    }

    public static function renderUsersFile(array $users): string
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
                $escaped = array_map(static function ($role) { return "'" . addslashes($role) . "'"; }, $roles);
                $rolesString = '[' . implode(', ', $escaped) . ']';
            }

            $lines[] = "        'roles' => " . $rolesString . ',';
            $lines[] = '    ],';
        }

        $lines[] = '];';
        $lines[] = '';

        return implode("\n", $lines);
    }

    public static function usersSave(array $users): bool
    {
        $prepared = [];
        foreach ($users as $username => $info) {
            if (!is_array($info)) continue;
            $normalized = self::normalizeUsername((string)$username);
            if ($normalized === '') continue;
            $name = isset($info['name']) && is_string($info['name']) && $info['name'] !== '' ? trim($info['name']) : $normalized;
            $password = isset($info['password']) && is_string($info['password']) ? trim($info['password']) : '';
            if ($password === '') continue;
            $roles = [];
            if (isset($info['roles']) && is_array($info['roles'])) {
                foreach ($info['roles'] as $role) {
                    if (!is_string($role)) continue;
                    $role = strtolower(trim($role));
                    if ($role === '') continue;
                    $roles[] = $role;
                }
            }
            $prepared[$normalized] = [
                'name' => $name,
                'password' => $password,
                'roles' => array_values(array_unique($roles)),
            ];
        }

        if (empty($prepared)) return false;

        ksort($prepared);
        $content = self::renderUsersFile($prepared);
        $path = self::usersPath();
        $tempPath = $path . '.' . bin2hex(random_bytes(8));

        if (file_put_contents($tempPath, $content) === false) return false;
        if (!@rename($tempPath, $path)) { @unlink($tempPath); return false; }

        self::users(true);
        return true;
    }

    // Database helpers for remote MySQL authentication
    public static function dbConfigPath(): string
    {
        return __DIR__ . '/../../config/db.php';
    }

    public static function dbConfig(): ?array
    {
        $path = self::dbConfigPath();
        if (!is_readable($path)) return null;
        $data = include $path;
        if (!is_array($data)) return null;
        return $data;
    }

    public static function getPdo(): ?\PDO
    {
        $cfg = self::dbConfig();
        if (!$cfg) return null;
        $host = $cfg['host'] ?? null;
        $port = isset($cfg['port']) ? (int)$cfg['port'] : 3306;
        $user = $cfg['username'] ?? null;
        $pass = $cfg['password'] ?? null;
        $db = $cfg['database'] ?? null;
        if (!$host || !$user || $db === null) return null;
        $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $db . ';charset=utf8mb4';
        try {
            $pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            return $pdo;
        } catch (\PDOException $e) {
            return null;
        }
    }

    public static function userFromDb(string $username): ?array
    {
        $pdo = self::getPdo();
        if (!$pdo) return null;
        $cfg = self::dbConfig();
        $table = $cfg['table'] ?? 'users';
        $uCol = $cfg['username_column'] ?? 'username';
        $pCol = $cfg['password_column'] ?? 'password';
        $nCol = $cfg['name_column'] ?? 'name';
        $rCol = $cfg['roles_column'] ?? 'roles';

        $sql = 'SELECT * FROM `' . str_replace('`', '\\`', $table) . "` WHERE `$uCol` = :u LIMIT 1";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':u' => $username]);
            $row = $stmt->fetch();
            if (!$row || !is_array($row)) return null;

            $roles = [];
            if (isset($row[$rCol])) {
                if (is_array($row[$rCol])) {
                    $roles = $row[$rCol];
                } elseif (is_string($row[$rCol])) {
                    $parts = array_map('trim', explode(',', $row[$rCol]));
                    $roles = array_values(array_filter($parts, static function ($v) { return $v !== ''; }));
                }
            }

            $password = isset($row[$pCol]) ? (string)$row[$pCol] : '';
            $name = isset($row[$nCol]) && is_string($row[$nCol]) && $row[$nCol] !== '' ? $row[$nCol] : $username;

            return [
                'username' => $username,
                'password' => $password,
                'name' => $name,
                'roles' => $roles,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function createUserInDb(string $username, string $passwordHash, string $name = '', array $roles = []): bool
    {
        $pdo = self::getPdo();
        if (!$pdo) return false;
        $cfg = self::dbConfig();
        $table = $cfg['table'] ?? 'users';
        $uCol = $cfg['username_column'] ?? 'username';
        $pCol = $cfg['password_column'] ?? 'password';
        $nCol = $cfg['name_column'] ?? 'name';
        $rCol = $cfg['roles_column'] ?? 'roles';

        // Prepare roles value: if DB expects CSV string, join; if JSON, user can adapt later
        $rolesValue = '';
        if (!empty($roles)) {
            // default store as CSV
            $rolesValue = implode(',', $roles);
        }

        $columns = [$uCol, $pCol];
        $placeholders = [':u', ':p'];
        $params = [':u' => $username, ':p' => $passwordHash];

        if ($nCol) {
            $columns[] = $nCol;
            $placeholders[] = ':n';
            $params[':n'] = $name;
        }
        if ($rCol) {
            $columns[] = $rCol;
            $placeholders[] = ':r';
            $params[':r'] = $rolesValue;
        }

        $safeTable = str_replace('`', '\\`', $table);
        $colsSql = implode('`,`', array_map(static function ($c) { return $c; }, $columns));
        $sql = 'INSERT INTO `' . $safeTable . '` (`' . $colsSql . '`) VALUES (' . implode(', ', $placeholders) . ')';

        try {
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (\Exception $e) {
            return false;
        }
    }

    // Session / auth helpers
    public static function userUsername(): ?string
    {
        $user = self::user();
        if (!$user) return null;
        $username = $user['username'] ?? null;
        if (!is_string($username) || $username === '') return null;
        return self::normalizeUsername($username);
    }

    public static function userRoles(): array
    {
        $user = self::user();
        if (!$user) return [];
        $roles = $user['roles'] ?? [];
        if (!is_array($roles)) return [];
        $clean = [];
        foreach ($roles as $role) {
            if (!is_string($role)) continue;
            $role = strtolower(trim($role));
            if ($role === '') continue;
            $clean[] = $role;
        }
        return array_values(array_unique($clean));
    }

    public static function hasRole(string $role): bool
    {
        $role = strtolower(trim($role));
        if ($role === '') return false;
        foreach (self::userRoles() as $userRole) {
            if ($userRole === $role) return true;
        }
        return false;
    }

    public static function userIsAdmin(): bool
    {
        return self::hasRole('admin');
    }

    public static function attempt(string $username, string $password): bool
    {
        self::startSession();
        $username = self::normalizeUsername($username);
        if ($username === '' || $password === '') return false;

        // If DB config present, try remote MySQL authentication first
        $dbCfg = self::dbConfig();
        if ($dbCfg !== null) {
            $user = self::userFromDb($username);
            if (is_array($user) && isset($user['password']) && $user['password'] !== '') {
                $hash = $user['password'];
                // If stored as a hash, use password_verify. Otherwise compare directly.
                $verified = false;
                if (password_get_info($hash)['algo'] !== 0) {
                    $verified = password_verify($password, $hash);
                } else {
                    $verified = hash_equals($hash, $password);
                }
                if ($verified) {
                    $_SESSION['auth_user'] = [
                        'username' => $username,
                        'name' => isset($user['name']) && is_string($user['name']) && $user['name'] !== '' ? $user['name'] : $username,
                        'roles' => isset($user['roles']) && is_array($user['roles']) ? $user['roles'] : [],
                    ];
                    return true;
                }
            }
            // If DB auth configured but failed, do not immediately return: allow fallback to file users
        }

        // Fallback to local users file
        $users = self::users();
        if (!isset($users[$username])) return false;
        $user = $users[$username];
        $hash = $user['password'] ?? null;
        if (!is_string($hash) || $hash === '') return false;
        if (!password_verify($password, $hash)) return false;

        $_SESSION['auth_user'] = [
            'username' => $username,
            'name' => isset($user['name']) && is_string($user['name']) && $user['name'] !== '' ? $user['name'] : $username,
            'roles' => isset($user['roles']) && is_array($user['roles']) ? $user['roles'] : [],
        ];
        return true;
    }

    public static function check(): bool
    {
        self::startSession();
        return isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user']);
    }

    public static function user(): ?array
    {
        self::startSession();
        return self::check() ? $_SESSION['auth_user'] : null;
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], (bool)$params['httponly']);
        }
        session_destroy();
    }

    public static function flashMessage(string $type, string $message): void
    {
        self::startSession();
        if (!isset($_SESSION['auth_flash_messages'][$type]) || !is_array($_SESSION['auth_flash_messages'][$type])) {
            $_SESSION['auth_flash_messages'][$type] = [];
        }
        $_SESSION['auth_flash_messages'][$type][] = $message;
    }

    public static function flashMessages(string $type): array
    {
        self::startSession();
        if (!isset($_SESSION['auth_flash_messages'][$type]) || !is_array($_SESSION['auth_flash_messages'][$type])) return [];
        $messages = $_SESSION['auth_flash_messages'][$type];
        unset($_SESSION['auth_flash_messages'][$type]);
        return $messages;
    }

    public static function flashValue(string $key, $value): void
    {
        self::startSession();
        $_SESSION['auth_flash_values'][$key] = $value;
    }

    public static function flashPullValue(string $key, $default = null)
    {
        self::startSession();
        if (!isset($_SESSION['auth_flash_values'][$key])) return $default;
        $value = $_SESSION['auth_flash_values'][$key];
        unset($_SESSION['auth_flash_values'][$key]);
        return $value;
    }

    public static function rememberIntended(string $uri): void
    {
        self::startSession();
        $_SESSION['auth_intended'] = $uri;
    }

    public static function takeIntended(string $default): string
    {
        self::startSession();
        $target = $_SESSION['auth_intended'] ?? null;
        unset($_SESSION['auth_intended']);
        if (!is_string($target) || $target === '') return $default;
        if (filter_var($target, FILTER_VALIDATE_URL)) return $target;
        if ($target[0] === '/') {
            $base = $GLOBALS['site_url'] ?? '';
            return $base . $target;
        }
        $base = $GLOBALS['site_url'] ?? '';
        return $base . '/' . ltrim($target, '/');
    }

    public static function redirect(string $path = ''): void
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

    public static function requireAuth(): void
    {
        if (self::check()) return;
        self::flashMessage('warning', 'Faça login para acessar a página solicitada.');
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $intendedPath = parse_url($uri, PHP_URL_PATH) ?: '/';
        $query = parse_url($uri, PHP_URL_QUERY);
        if ($query) $intendedPath .= '?' . $query;
        self::rememberIntended($intendedPath);
        self::redirect('login');
    }

    public static function generateCsrfToken(string $form = 'default'): string
    {
        self::startSession();
        $token = bin2hex(random_bytes(32));
        $_SESSION['auth_csrf'][$form] = $token;
        return $token;
    }

    public static function validateCsrfToken(string $form, ?string $token): bool
    {
        self::startSession();
        if (!isset($_SESSION['auth_csrf'][$form])) return false;
        $expected = $_SESSION['auth_csrf'][$form];
        unset($_SESSION['auth_csrf'][$form]);
        if (!is_string($token) || $token === '') return false;
        return hash_equals($expected, $token);
    }
}
