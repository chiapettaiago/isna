<?php
declare(strict_types=1);

require_once __DIR__ . '/AuthService.php';

class AccessLogger
{
    private static ?\PDO $pdo = null;
    private const EXCLUDED_IPS = ['179.151.230.30'];

    public static function tableName(): string
    {
        $cfg = AuthService::dbConfig() ?? [];
        return (string)($cfg['access_table'] ?? 'accesses');
    }

    public static function isReportablePath(string $path): bool
    {
        $path = parse_url($path, PHP_URL_PATH) ?: '/';

        $excludedExact = [
            '/area-restrita',
            '/relatorios-acesso',
            '/relatorios-acesso/pdf',
            '/gestao-usuarios',
            '/gestao-galeria',
            '/gestao-blog',
            '/gestao-cms',
            '/sobre',
            '/login',
            '/logout',
            '/api/access-stats',
            '/api/pdf-documents',
            '/api/pdf-page',
        ];

        if (in_array($path, $excludedExact, true)) {
            return false;
        }

        $excludedPrefixes = ['/gestao-', '/admin', '/api/'];
        foreach ($excludedPrefixes as $prefix) {
            if (strncmp($path, $prefix, strlen($prefix)) === 0) {
                return false;
            }
        }

        return !preg_match('/\.(css|js|png|jpe?g|gif|svg|ico|webp|pdf|mp4|mp3|zip|json|txt|xml)$/i', $path);
    }

    public static function isReportableIp(?string $ip): bool
    {
        $ip = trim((string)$ip);
        return $ip === '' || !in_array($ip, self::EXCLUDED_IPS, true);
    }

    private static function reportableSqlCondition(): string
    {
        $excludedIpList = implode(', ', array_map(static function (string $ip): string {
            return "'" . str_replace("'", "''", $ip) . "'";
        }, self::EXCLUDED_IPS));

        return "
            path NOT IN (
                '/area-restrita',
                '/relatorios-acesso',
                '/relatorios-acesso/pdf',
                '/gestao-usuarios',
                '/gestao-galeria',
                '/gestao-blog',
                '/gestao-cms',
                '/sobre',
                '/login',
                '/logout',
                '/api/access-stats',
                '/api/pdf-documents',
                '/api/pdf-page'
            )
            AND path NOT LIKE '/gestao-%'
            AND path NOT LIKE '/admin%'
            AND path NOT LIKE '/api/%'
            AND path NOT REGEXP '\\\\.(css|js|png|jpe?g|gif|svg|ico|webp|pdf|mp4|mp3|zip|json|txt|xml)$'
            AND (ip IS NULL OR ip NOT IN ({$excludedIpList}))
        ";
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
        if (!self::isReportablePath($path) || !self::isReportableIp($_SERVER['REMOTE_ADDR'] ?? null)) {
            return false;
        }

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
        $reportableWhere = self::reportableSqlCondition();
        $sql = "SELECT DATE(FROM_UNIXTIME(ts)) AS d, COUNT(*) AS c FROM {$table} WHERE ts BETWEEN :from AND :to AND {$reportableWhere} GROUP BY d ORDER BY d ASC";
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

    public static function report(string $fromYmd, string $toYmd, int $recentLimit = 50): array
    {
        $empty = [
            'totals' => [
                'accesses' => 0,
                'unique_ips' => 0,
                'unique_paths' => 0,
            ],
            'daily' => [],
            'top_paths' => [],
            'recent' => [],
        ];

        $pdo = self::ensureDb();
        if (!$pdo) return $empty;

        try {
            $fromTs = (new DateTimeImmutable($fromYmd))->setTime(0, 0)->getTimestamp();
            $toTs = (new DateTimeImmutable($toYmd))->setTime(23, 59, 59)->getTimestamp();
        } catch (\Exception $e) {
            return $empty;
        }

        $recentLimit = max(1, min(200, $recentLimit));
        $table = AuthService::quoteIdentifier(self::tableName());

        try {
            $totalsStmt = $pdo->prepare("
                SELECT
                    COUNT(*) AS accesses,
                    COUNT(DISTINCT ip) AS unique_ips,
                    COUNT(DISTINCT path) AS unique_paths
                FROM {$table}
                WHERE ts BETWEEN :from AND :to
                    AND " . self::reportableSqlCondition() . "
            ");
            $totalsStmt->execute([':from' => $fromTs, ':to' => $toTs]);
            $totals = $totalsStmt->fetch(\PDO::FETCH_ASSOC) ?: [];

            $topStmt = $pdo->prepare("
                SELECT path, COUNT(*) AS accesses, MAX(ts) AS last_ts
                FROM {$table}
                WHERE ts BETWEEN :from AND :to
                    AND " . self::reportableSqlCondition() . "
                GROUP BY path
                ORDER BY accesses DESC, path ASC
                LIMIT 20
            ");
            $topStmt->execute([':from' => $fromTs, ':to' => $toTs]);

            $recentStmt = $pdo->prepare("
                SELECT ts, path, method, ip, user_agent
                FROM {$table}
                WHERE ts BETWEEN :from AND :to
                    AND " . self::reportableSqlCondition() . "
                ORDER BY ts DESC
                LIMIT {$recentLimit}
            ");
            $recentStmt->execute([':from' => $fromTs, ':to' => $toTs]);

            return [
                'totals' => [
                    'accesses' => (int)($totals['accesses'] ?? 0),
                    'unique_ips' => (int)($totals['unique_ips'] ?? 0),
                    'unique_paths' => (int)($totals['unique_paths'] ?? 0),
                ],
                'daily' => self::dailyCounts($fromYmd, $toYmd),
                'top_paths' => array_map(static function (array $row): array {
                    return [
                        'path' => (string)($row['path'] ?? ''),
                        'accesses' => (int)($row['accesses'] ?? 0),
                        'last_ts' => (int)($row['last_ts'] ?? 0),
                    ];
                }, $topStmt->fetchAll(\PDO::FETCH_ASSOC) ?: []),
                'recent' => array_map(static function (array $row): array {
                    return [
                        'ts' => (int)($row['ts'] ?? 0),
                        'path' => (string)($row['path'] ?? ''),
                        'method' => (string)($row['method'] ?? ''),
                        'ip' => (string)($row['ip'] ?? ''),
                        'user_agent' => (string)($row['user_agent'] ?? ''),
                    ];
                }, $recentStmt->fetchAll(\PDO::FETCH_ASSOC) ?: []),
            ];
        } catch (\Exception $e) {
            return $empty;
        }
    }
}
