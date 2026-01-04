<?php
/**
 * Gera um arquivo JSON com os últimos commits do repositório.
 * Uso: php scripts/generate_changelog.php
 */

declare(strict_types=1);

$projectRoot = realpath(__DIR__ . '/..');
if ($projectRoot === false) {
    fwrite(STDERR, "Não foi possível determinar o diretório do projeto.\n");
    exit(1);
}

$gitDir = $projectRoot . '/.git';
if (!is_dir($gitDir)) {
    fwrite(STDERR, "Repositório Git não encontrado em $projectRoot/.git\n");
    exit(1);
}

$outFile = $projectRoot . '/data/last_changes.json';
if (!is_dir(dirname($outFile))) {
    mkdir(dirname($outFile), 0755, true);
}

$cmd = 'git -C ' . escapeshellarg($projectRoot) . ' log -n 20 --pretty=format:"%h|%an|%ad|%s" --date=short';
$raw = null;
exec($cmd, $raw, $rc);
if ($rc !== 0) {
    fwrite(STDERR, "Erro ao executar git. Código: $rc\n");
    exit(1);
}

$changes = [];
foreach ($raw as $line) {
    $parts = explode('|', $line, 4);
    if (count($parts) < 4) continue;
    $changes[] = [
        'hash' => $parts[0],
        'author' => $parts[1],
        'date' => $parts[2],
        'message' => $parts[3],
    ];
}

$json = json_encode($changes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
if ($json === false) {
    fwrite(STDERR, "Falha ao codificar JSON.\n");
    exit(1);
}

if (file_put_contents($outFile, $json) === false) {
    fwrite(STDERR, "Não foi possível gravar $outFile\n");
    exit(1);
}

echo "Gerado: $outFile (" . count($changes) . " entradas)\n";
exit(0);
