<?php
declare(strict_types=1);

class AccessLogger
{
    private static ?\PDO $pdo = null;

    public static function dbPath(): string
    {
        return __DIR__ . '/../../data/accesses.sqlite3';
    }

    public static function ensureDb(): ?\PDO
    {
        if (self::$pdo !== null) return self::$pdo;
        $path = self::dbPath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        try {
            $pdo = new \PDO('sqlite:' . $path);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec('PRAGMA journal_mode = WAL');

            $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS accesses (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ts INTEGER NOT NULL,
  path TEXT NOT NULL,
  method TEXT NOT NULL,
  ip TEXT,
  user_agent TEXT
);
SQL
            );

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
            $stmt = $pdo->prepare('INSERT INTO accesses (ts, path, method, ip, user_agent) VALUES (:ts, :path, :method, :ip, :ua)');
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

        $sql = 'SELECT date(datetime(ts, "unixepoch")) as d, COUNT(*) as c FROM accesses WHERE ts BETWEEN :from AND :to GROUP BY d ORDER BY d ASC';
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
