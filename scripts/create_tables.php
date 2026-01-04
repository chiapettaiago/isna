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
$usernameCol = $cfg['username_column'] ?? 'username';
$passwordCol = $cfg['password_column'] ?? 'password';
$nameCol = $cfg['name_column'] ?? 'name';
$rolesCol = $cfg['roles_column'] ?? 'roles';

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS `$table` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `$usernameCol` VARCHAR(191) NOT NULL,
  `$passwordCol` VARCHAR(255) NOT NULL,
  `$nameCol` VARCHAR(255) NULL,
  `$rolesCol` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_{$table}_{$usernameCol}` (`$usernameCol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

try {
    $pdo->exec($sql);
    echo "Tabela '$table' criada ou já existente.\n";
} catch (PDOException $e) {
    echo "Erro ao criar tabela '$table': " . $e->getMessage() . "\n";
    exit(1);
}

// Mostrar tabelas existentes (verificação simples)
try {
    $stmt = $pdo->query("SHOW TABLES LIKE '" . addslashes($table) . "'");
    $found = $stmt->fetch();
    if ($found) {
        echo "Verificação: a tabela '$table' existe no banco.\n";
        // listar colunas
        $stmt2 = $pdo->query("SHOW COLUMNS FROM `" . addslashes($table) . "`");
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
