#!/usr/bin/env php
<?php
declare(strict_types=1);

// Script legado: importa entradas de `logs/access_log` para o banco usado por AccessLogger.
// O AccessLogger atual usa MySQL remoto.

require_once __DIR__ . '/../app/services/AccessLogger.php';

function e(string $msg): void
{
    fwrite(STDERR, $msg . PHP_EOL);
}

$logFile = __DIR__ . '/../logs/access_log';
if (!is_readable($logFile)) {
    e("Arquivo de log não encontrado ou sem permissão de leitura: $logFile");
    exit(2);
}

$pdo = AccessLogger::ensureDb();
if (!$pdo) {
    e('Não foi possível abrir/criar a tabela de acessos no banco configurado.');
    exit(3);
}

$in = fopen($logFile, 'r');
if ($in === false) {
    e('Falha ao abrir o arquivo de log para leitura.');
    exit(4);
}

$count = 0;
$skipped = 0;
$inserted = 0;

$checkStmt = $pdo->prepare('SELECT COUNT(1) as c FROM accesses WHERE ts = :ts AND path = :path AND ip = :ip LIMIT 1');
$insStmt = $pdo->prepare('INSERT INTO accesses (ts, path, method, ip, user_agent) VALUES (:ts, :path, :method, :ip, :ua)');

while (!feof($in)) {
    $line = trim((string) fgets($in));
    if ($line === '') continue;
    $count++;

    // IP
    $ip = null;
    if (preg_match('/^(\S+)/', $line, $m)) {
        $ip = $m[1];
    }

    // Date/time between brackets
    if (!preg_match('/\[(.*?)\]/', $line, $m)) {
        $skipped++;
        continue;
    }

    $dateString = $m[1]; // e.g. 10/Oct/2000:13:55:36 -0700
    $dt = DateTimeImmutable::createFromFormat('d/M/Y:H:i:s O', $dateString);
    if (!$dt) {
        $skipped++;
        continue;
    }
    $ts = $dt->getTimestamp();

    // Request and status
    if (!preg_match('/"([A-Z]+) ([^\s\"]+) HTTP\/[0-9.]+"\s+(\d{3})/', $line, $mReq)) {
        $skipped++;
        continue;
    }
    $method = $mReq[1];
    $resource = $mReq[2];
    $status = (int) $mReq[3];

    if ($method !== 'GET' || $status >= 500) {
        $skipped++;
        continue;
    }

    $path = parse_url($resource, PHP_URL_PATH) ?: $resource;

    // Excluir assets e rotas administrativas
    if (preg_match('/\.(css|js|png|jpe?g|gif|svg|ico|webp|pdf|mp4|mp3|zip|json|txt|xml)$/i', $path)) {
        $skipped++;
        continue;
    }

    $excludedExact = ['/area-restrita', '/gestao-usuarios', '/gestao-galeria', '/login', '/logout'];
    $excludedPrefixes = ['/gestao-', '/admin'];
    if (in_array($path, $excludedExact, true)) {
        $skipped++;
        continue;
    }
    $skip = false;
    foreach ($excludedPrefixes as $prefix) {
        if (strncmp($path, $prefix, strlen($prefix)) === 0) { $skip = true; break; }
    }
    if ($skip) { $skipped++; continue; }

    // User agent: última string entre aspas
    $ua = null;
    if (preg_match('/"([^\"]*)"\s*$/', $line, $mUa)) {
        $ua = $mUa[1];
    }

    // Evitar duplicatas
    try {
        $checkStmt->execute([':ts' => $ts, ':path' => $path, ':ip' => $ip]);
        $r = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($r && ((int)$r['c']) > 0) {
            $skipped++;
            continue;
        }

        $insStmt->execute([':ts' => $ts, ':path' => $path, ':method' => $method, ':ip' => $ip, ':ua' => $ua]);
        $inserted++;
    } catch (Exception $e) {
        e('Erro ao inserir: ' . $e->getMessage());
    }
}

fclose($in);

echo "Lido: $count linhas\n";
echo "Inseridos: $inserted\n";
echo "Pulados: $skipped\n";

exit(0);
