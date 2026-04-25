<?php
/**
 * Script de migração simples para criar tabelas necessárias no banco MySQL
 * Uso: php scripts/create_tables.php
 */

declare(strict_types=1);

// Carrega a configuração do DB
$cfgPath = __DIR__ . '/../config/db.php';
if (!is_readable($cfgPath)) {
    echo "Arquivo de configuração '$cfgPath' não encontrado ou não legível.\n";
    exit(1);
}

$cfg = include $cfgPath;
if (!is_array($cfg)) {
    echo "Configuração inválida em $cfgPath. Deve retornar um array.\n";
    exit(1);
}

$host = $cfg['host'] ?? '';
$port = isset($cfg['port']) && $cfg['port'] !== '' ? (int)$cfg['port'] : 3306;
$user = $cfg['username'] ?? '';
$pass = $cfg['password'] ?? '';
$db   = $cfg['database'] ?? '';

if ($host === '' || $user === '' || $db === '') {
    echo "As chaves 'host', 'username' e 'database' devem estar preenchidas em config/db.php.\n";
    exit(1);
}

$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $db);

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    echo "Falha ao conectar no banco de dados: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Conectado em $host:$port (banco: $db)\n";

// SQL para criar tabela de usuários
$table = $cfg['table'] ?? 'users';
$accessTable = $cfg['access_table'] ?? 'accesses';
$cmsTable = $cfg['cms_table'] ?? 'cms_blocks';
$cmsSectionsTable = $cfg['cms_sections_table'] ?? 'cms_sections';
$usernameCol = $cfg['username_column'] ?? 'username';
$passwordCol = $cfg['password_column'] ?? 'password';
$nameCol = $cfg['name_column'] ?? 'name';
$rolesCol = $cfg['roles_column'] ?? 'roles';

$quoteIdentifier = static function (string $identifier): string {
    $identifier = trim($identifier);
    if ($identifier === '') {
        throw new InvalidArgumentException('Identificador SQL vazio.');
    }
    return '`' . str_replace('`', '``', $identifier) . '`';
};

$qTable = $quoteIdentifier((string)$table);
$qAccessTable = $quoteIdentifier((string)$accessTable);
$qCmsTable = $quoteIdentifier((string)$cmsTable);
$qCmsSectionsTable = $quoteIdentifier((string)$cmsSectionsTable);
$qUsernameCol = $quoteIdentifier((string)$usernameCol);
$qPasswordCol = $quoteIdentifier((string)$passwordCol);
$qNameCol = $quoteIdentifier((string)$nameCol);
$qRolesCol = $quoteIdentifier((string)$rolesCol);

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS {$qTable} (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  {$qUsernameCol} VARCHAR(191) NOT NULL,
  {$qPasswordCol} VARCHAR(255) NOT NULL,
  {$qNameCol} VARCHAR(255) NULL,
  {$qRolesCol} TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` ({$qUsernameCol})
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

try {
    $pdo->exec($sql);
    echo "Tabela '$table' criada ou já existente.\n";
} catch (PDOException $e) {
    echo "Erro ao criar tabela '$table': " . $e->getMessage() . "\n";
    exit(1);
}

// SQL para criar tabela de acessos da dashboard
$accessSql = <<<SQL
CREATE TABLE IF NOT EXISTS {$qAccessTable} (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ts` INT UNSIGNED NOT NULL,
  `path` VARCHAR(2048) NOT NULL,
  `method` VARCHAR(16) NOT NULL,
  `ip` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_accesses_ts` (`ts`),
  KEY `idx_accesses_path` (`path`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

try {
    $pdo->exec($accessSql);
    echo "Tabela '$accessTable' criada ou já existente.\n";
} catch (PDOException $e) {
    echo "Erro ao criar tabela '$accessTable': " . $e->getMessage() . "\n";
    exit(1);
}

$cmsSql = <<<SQL
CREATE TABLE IF NOT EXISTS {$qCmsTable} (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_slug` VARCHAR(191) NOT NULL,
  `block_key` VARCHAR(191) NOT NULL,
  `block_type` VARCHAR(32) NOT NULL DEFAULT 'text',
  `label` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NULL,
  `updated_by` VARCHAR(191) NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cms_page_block` (`page_slug`, `block_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

try {
    $pdo->exec($cmsSql);
    echo "Tabela '$cmsTable' criada ou já existente.\n";
} catch (PDOException $e) {
    echo "Erro ao criar tabela '$cmsTable': " . $e->getMessage() . "\n";
    exit(1);
}

$cmsSectionsSql = <<<SQL
CREATE TABLE IF NOT EXISTS {$qCmsSectionsTable} (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `page_slug` VARCHAR(191) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `position` INT NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_by` VARCHAR(191) NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cms_sections_page_position` (`page_slug`, `is_active`, `position`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

try {
    $pdo->exec($cmsSectionsSql);
    echo "Tabela '$cmsSectionsTable' criada ou já existente.\n";
} catch (PDOException $e) {
    echo "Erro ao criar tabela '$cmsSectionsTable': " . $e->getMessage() . "\n";
    exit(1);
}

// Importa usuários locais legados que ainda não existem na tabela remota
try {
    $legacyUsersPath = __DIR__ . '/../config/users.php';

    if (is_readable($legacyUsersPath)) {
        $legacyUsers = include $legacyUsersPath;
        if (is_array($legacyUsers) && !empty($legacyUsers)) {
            $exists = $pdo->prepare("SELECT COUNT(*) FROM {$qTable} WHERE {$qUsernameCol} = :username LIMIT 1");
            $insertSql = "INSERT INTO {$qTable} ({$qUsernameCol}, {$qPasswordCol}, {$qNameCol}, {$qRolesCol}) VALUES (:username, :password, :name, :roles)";
            $insert = $pdo->prepare($insertSql);
            $imported = 0;

            foreach ($legacyUsers as $username => $info) {
                if (!is_array($info)) continue;
                $username = strtolower(trim((string)$username));
                $passwordHash = isset($info['password']) ? trim((string)$info['password']) : '';
                if ($username === '' || $passwordHash === '') continue;

                $exists->execute([':username' => $username]);
                if ((int)$exists->fetchColumn() > 0) continue;

                $roles = [];
                if (isset($info['roles']) && is_array($info['roles'])) {
                    foreach ($info['roles'] as $role) {
                        if (!is_string($role)) continue;
                        $role = strtolower(trim($role));
                        if ($role !== '') $roles[] = $role;
                    }
                }

                $insert->execute([
                    ':username' => $username,
                    ':password' => $passwordHash,
                    ':name' => isset($info['name']) && is_string($info['name']) && $info['name'] !== '' ? $info['name'] : $username,
                    ':roles' => implode(',', array_values(array_unique($roles))),
                ]);
                $imported++;
            }

            echo "Usuários legados importados para MySQL: $imported.\n";
        }
    }
} catch (Exception $e) {
    echo "Aviso: não foi possível importar usuários legados: " . $e->getMessage() . "\n";
}

// Importa acessos locais legados do SQLite, evitando duplicatas simples
try {
    $sqlitePath = __DIR__ . '/../data/accesses.sqlite3';
    if (is_readable($sqlitePath) && class_exists('SQLite3')) {
        $sqlite = new PDO('sqlite:' . $sqlitePath);
        $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $rows = $sqlite->query('SELECT ts, path, method, ip, user_agent FROM accesses ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $check = $pdo->prepare("SELECT COUNT(*) FROM {$qAccessTable} WHERE ts = :ts AND path = :path AND method = :method AND (ip <=> :ip) LIMIT 1");
            $insertAccess = $pdo->prepare("INSERT INTO {$qAccessTable} (ts, path, method, ip, user_agent) VALUES (:ts, :path, :method, :ip, :ua)");
            $imported = 0;

            foreach ($rows as $row) {
                $params = [
                    ':ts' => (int)$row['ts'],
                    ':path' => (string)$row['path'],
                    ':method' => (string)$row['method'],
                    ':ip' => $row['ip'] !== null ? (string)$row['ip'] : null,
                ];
                $check->execute($params);
                if ((int)$check->fetchColumn() > 0) continue;

                $insertAccess->execute($params + [
                    ':ua' => $row['user_agent'] !== null ? (string)$row['user_agent'] : null,
                ]);
                $imported++;
            }

            echo "Acessos legados importados para MySQL: $imported.\n";
        }
    }
} catch (Exception $e) {
    echo "Aviso: não foi possível importar acessos legados: " . $e->getMessage() . "\n";
}

// Mostrar tabelas existentes (verificação simples)
try {
    $stmt = $pdo->query("SHOW TABLES LIKE '" . addslashes($table) . "'");
    $found = $stmt->fetch();
    if ($found) {
        echo "Verificação: a tabela '$table' existe no banco.\n";
        // listar colunas
        $stmt2 = $pdo->query("SHOW COLUMNS FROM {$qTable}");
        $cols = $stmt2->fetchAll();
        echo "Colunas em $table:\n";
        foreach ($cols as $c) {
            echo " - " . ($c['Field'] ?? $c[0]) . " (" . ($c['Type'] ?? '') . ")" . "\n";
        }
    } else {
        echo "Verificação falhou: tabela '$table' não encontrada após criação.\n";
    }
} catch (PDOException $e) {
    echo "Erro durante verificação: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migração finalizada com sucesso.\n";
exit(0);
