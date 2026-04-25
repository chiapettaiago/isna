<?php
declare(strict_types=1);

require_once __DIR__ . '/AuthService.php';

class AccessLogger
{
    private static ?\PDO $pdo = null;

    public static function tableName(): string
    {
        $cfg = AuthService::dbConfig() ?? [];
        return (string)($cfg['access_table'] ?? 'accesses');
    }

    public static function ensureDb(): ?\PDO
    {
        if (self::$pdo !== null) return self::$pdo;

        try {
            $pdo = AuthService::getPdo();
            if (!$pdo) return null;

            $table = AuthService::quoteIdentifier(self::tableName());
            $pdo->exec("
CREATE TABLE IF NOT EXISTS {$table} (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  ts INT UNSIGNED NOT NULL,
  path VARCHAR(2048) NOT NULL,
  method VARCHAR(16) NOT NULL,
  ip VARCHAR(45) NULL,
  user_agent TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_accesses_ts (ts),
  KEY idx_accesses_path (path(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

            self::$pdo = $pdo;
            return self::$pdo;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function record(string $path, string $method = 'GET'): bool
    {
        $pdo = self::ensureDb();
        if (!$pdo) return false;

        try {
            $table = AuthService::quoteIdentifier(self::tableName());
            $stmt = $pdo->prepare("INSERT INTO {$table} (ts, path, method, ip, user_agent) VALUES (:ts, :path, :method, :ip, :ua)");
            $stmt->execute([
                ':ts' => time(),
                ':path' => $path,
                ':method' => $method,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Retorna array de [label=>count] entre duas datas (Y-m-d)
     * @return array<string,int>
     */
    public static function dailyCounts(string $fromYmd, string $toYmd): array
    {
        $pdo = self::ensureDb();
        if (!$pdo) return [];

        $fromTs = (new DateTimeImmutable($fromYmd))->setTime(0,0)->getTimestamp();
        $toTs = (new DateTimeImmutable($toYmd))->setTime(23,59,59)->getTimestamp();

        $table = AuthService::quoteIdentifier(self::tableName());
        $sql = "SELECT DATE(FROM_UNIXTIME(ts)) AS d, COUNT(*) AS c FROM {$table} WHERE ts BETWEEN :from AND :to GROUP BY d ORDER BY d ASC";
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':from' => $fromTs, ':to' => $toTs]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $out = [];
            foreach ($rows as $r) {
                $out[$r['d']] = (int)$r['c'];
            }
            return $out;
        } catch (\Exception $e) {
            return [];
        }
    }
}
