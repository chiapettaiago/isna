<?php

declare(strict_types=1);

class SystemUpdateService
{
    private string $projectRoot;
    private ?string $gitBinary = null;

    public function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?: dirname(__DIR__, 2);
    }

    public function checkForUpdates(): array
    {
        if (!is_dir($this->projectRoot . '/.git')) {
            return $this->checkGithubArchiveUpdates();
        }

        $this->gitBinary = $this->resolveGitBinary();

        if ($this->gitBinary === null) {
            return $this->checkGithubArchiveUpdates();
        }

        $upstream = $this->run($this->gitCommand(['-C', $this->projectRoot, 'rev-parse', '--abbrev-ref', '--symbolic-full-name', '@{u}']));
        if ($upstream['code'] !== 0 || trim($upstream['stdout']) === '') {
            return [
                'ok' => false,
                'available' => false,
                'canInstall' => false,
                'message' => 'Nao ha um branch remoto configurado para verificar novas versoes.',
                'changes' => [],
            ];
        }

        $fetch = $this->run($this->gitCommand(['-C', $this->projectRoot, 'fetch', '--prune']));
        if ($fetch['code'] !== 0) {
            return [
                'ok' => false,
                'available' => false,
                'canInstall' => false,
                'message' => 'Nao foi possivel consultar o GitHub. Verifique a conexao e a chave de acesso do servidor.',
                'changes' => [],
                'details' => $this->formatOutput($fetch),
            ];
        }

        $local = $this->revParse('HEAD');
        $remote = $this->revParse('@{u}');

        if ($local === null || $remote === null) {
            return [
                'ok' => false,
                'available' => false,
                'canInstall' => false,
                'message' => 'Nao foi possivel identificar a versao local ou remota do sistema.',
                'changes' => [],
            ];
        }

        if ($local === $remote) {
            return [
                'ok' => true,
                'available' => false,
                'canInstall' => false,
                'currentVersion' => substr($local, 0, 7),
                'latestVersion' => substr($remote, 0, 7),
                'message' => 'O sistema ja esta atualizado.',
                'changes' => [],
            ];
        }

        $ancestor = $this->run($this->gitCommand(['-C', $this->projectRoot, 'merge-base', '--is-ancestor', 'HEAD', '@{u}']));
        $changes = $this->gitChangeSummary();

        return [
            'ok' => $ancestor['code'] === 0,
            'available' => true,
            'canInstall' => $ancestor['code'] === 0,
            'currentVersion' => substr($local, 0, 7),
            'latestVersion' => substr($remote, 0, 7),
            'message' => $ancestor['code'] === 0
                ? 'Nova versao disponivel para instalacao.'
                : 'Existe divergencia entre a versao local e a versao do GitHub. A instalacao automatica esta bloqueada.',
            'changes' => $changes,
        ];
    }

    public function updateFromGithub(): array
    {
        if (!is_dir($this->projectRoot . '/.git')) {
            return $this->updateFromGithubArchive();
        }

        $this->gitBinary = $this->resolveGitBinary();

        if ($this->gitBinary === null) {
            return $this->updateFromGithubArchive();
        }

        $upstream = $this->run($this->gitCommand(['-C', $this->projectRoot, 'rev-parse', '--abbrev-ref', '--symbolic-full-name', '@{u}']));
        if ($upstream['code'] !== 0 || trim($upstream['stdout']) === '') {
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'Nao ha um branch remoto configurado para verificar novas versoes.',
            ];
        }

        $fetch = $this->run($this->gitCommand(['-C', $this->projectRoot, 'fetch', '--prune']));
        if ($fetch['code'] !== 0) {
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'Nao foi possivel consultar o GitHub. Verifique a conexao e a chave de acesso do servidor.',
                'details' => $this->formatOutput($fetch),
            ];
        }

        $local = $this->revParse('HEAD');
        $remote = $this->revParse('@{u}');

        if ($local === null || $remote === null) {
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'Nao foi possivel identificar a versao local ou remota do sistema.',
            ];
        }

        if ($local === $remote) {
            return [
                'ok' => true,
                'updated' => false,
                'message' => 'O sistema ja esta atualizado. Nenhuma nova versao foi encontrada no GitHub.',
            ];
        }

        $ancestor = $this->run($this->gitCommand(['-C', $this->projectRoot, 'merge-base', '--is-ancestor', 'HEAD', '@{u}']));
        if ($ancestor['code'] !== 0) {
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'Existe divergencia entre a versao local e a versao do GitHub. Atualizacao automatica cancelada.',
            ];
        }

        $pull = $this->run($this->gitCommand(['-C', $this->projectRoot, 'pull', '--ff-only']));
        if ($pull['code'] !== 0) {
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'Uma nova versao foi encontrada, mas nao foi possivel aplica-la automaticamente.',
                'details' => $this->formatOutput($pull),
            ];
        }

        $newLocal = $this->revParse('HEAD') ?: $remote;
        $this->writeAppliedVersion($newLocal);

        return [
            'ok' => true,
            'updated' => true,
            'message' => 'Sistema atualizado com sucesso para a versao ' . substr($newLocal, 0, 7) . '.',
            'details' => $this->formatOutput($pull),
        ];
    }

    private function revParse(string $ref): ?string
    {
        $result = $this->run($this->gitCommand(['-C', $this->projectRoot, 'rev-parse', $ref]));
        if ($result['code'] !== 0) {
            return null;
        }

        $hash = trim($result['stdout']);
        return preg_match('/^[a-f0-9]{40}$/', $hash) ? $hash : null;
    }

    private function updateFromGithubArchive(): array
    {
        if (!class_exists('ZipArchive')) {
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'Atualizacao indisponivel: a extensao ZIP do PHP nao esta habilitada.',
            ];
        }

        $repository = $this->githubRepositoryConfig();
        if ($repository === null) {
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'Atualizacao indisponivel: configure github_owner, github_repo e github_branch no config.php.',
            ];
        }

        $remoteSha = $this->fetchRemoteGithubSha($repository);
        if ($remoteSha === null) {
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'Nao foi possivel consultar o GitHub pelo PHP. Verifique conexao, permissao do repositorio e token de acesso.',
            ];
        }

        $localSha = $this->readAppliedVersion() ?: $this->readLocalGitSha();
        if ($localSha !== null && hash_equals($localSha, $remoteSha)) {
            return [
                'ok' => true,
                'updated' => false,
                'message' => 'O sistema ja esta atualizado. Nenhuma nova versao foi encontrada no GitHub.',
            ];
        }

        $zipPath = $this->downloadGithubZip($repository);
        if ($zipPath === null) {
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'Nao foi possivel baixar a nova versao do GitHub.',
            ];
        }

        $extractDir = $this->makeTempDir('isna-update-');
        if ($extractDir === null) {
            @unlink($zipPath);
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'Nao foi possivel preparar a pasta temporaria de atualizacao.',
            ];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true || !$zip->extractTo($extractDir)) {
            $zip->close();
            @unlink($zipPath);
            $this->removeDirectory($extractDir);
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'Nao foi possivel extrair o pacote baixado do GitHub.',
            ];
        }
        $zip->close();
        @unlink($zipPath);

        $sourceDir = $this->firstDirectory($extractDir);
        if ($sourceDir === null) {
            $this->removeDirectory($extractDir);
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'O pacote baixado do GitHub nao contem arquivos validos.',
            ];
        }

        $copied = $this->copyDirectoryContents($sourceDir, $this->projectRoot);
        $this->removeDirectory($extractDir);

        if (!$copied) {
            return [
                'ok' => false,
                'updated' => false,
                'message' => 'A nova versao foi baixada, mas nao foi possivel copiar todos os arquivos.',
            ];
        }

        $this->writeAppliedVersion($remoteSha);

        return [
            'ok' => true,
            'updated' => true,
            'message' => 'Sistema atualizado com sucesso para a versao ' . substr($remoteSha, 0, 7) . ' usando pacote ZIP do GitHub.',
        ];
    }

    private function githubRepositoryConfig(): ?array
    {
        $config = isset($GLOBALS['config']) && is_array($GLOBALS['config']) ? $GLOBALS['config'] : [];

        $owner = trim((string)($config['github_owner'] ?? getenv('ISNA_GITHUB_OWNER') ?: ''));
        $repo = trim((string)($config['github_repo'] ?? getenv('ISNA_GITHUB_REPO') ?: ''));
        $branch = trim((string)($config['github_branch'] ?? getenv('ISNA_GITHUB_BRANCH') ?: 'master'));
        $token = trim((string)($config['github_token'] ?? getenv('ISNA_GITHUB_TOKEN') ?: getenv('GITHUB_TOKEN') ?: ''));

        if ($owner === '' || $repo === '' || $branch === '') {
            return null;
        }

        return [
            'owner' => $owner,
            'repo' => $repo,
            'branch' => $branch,
            'token' => $token,
        ];
    }

    private function checkGithubArchiveUpdates(): array
    {
        $repository = $this->githubRepositoryConfig();
        if ($repository === null) {
            return [
                'ok' => false,
                'available' => false,
                'canInstall' => false,
                'message' => 'Atualizacao indisponivel: configure github_owner, github_repo e github_branch no config.php.',
                'changes' => [],
            ];
        }

        $remoteCommit = $this->fetchRemoteGithubCommit($repository);
        if ($remoteCommit === null) {
            return [
                'ok' => false,
                'available' => false,
                'canInstall' => false,
                'message' => 'Nao foi possivel consultar o GitHub pelo PHP. Verifique conexao, permissao do repositorio e token de acesso.',
                'changes' => [],
            ];
        }

        $remoteSha = $remoteCommit['sha'];
        $localSha = $this->readAppliedVersion() ?: $this->readLocalGitSha();

        if ($localSha !== null && hash_equals($localSha, $remoteSha)) {
            return [
                'ok' => true,
                'available' => false,
                'canInstall' => false,
                'currentVersion' => substr($localSha, 0, 7),
                'latestVersion' => substr($remoteSha, 0, 7),
                'message' => 'O sistema ja esta atualizado.',
                'changes' => [],
            ];
        }

        $changes = $localSha !== null
            ? $this->fetchGithubCompareChanges($repository, $localSha, $remoteSha)
            : [];

        if (empty($changes) && $remoteCommit['message'] !== '') {
            $changes[] = $remoteCommit['message'];
        }

        return [
            'ok' => true,
            'available' => true,
            'canInstall' => true,
            'currentVersion' => $localSha !== null ? substr($localSha, 0, 7) : null,
            'latestVersion' => substr($remoteSha, 0, 7),
            'message' => 'Nova versao disponivel para instalacao.',
            'changes' => $changes,
        ];
    }

    private function fetchRemoteGithubSha(array $repository): ?string
    {
        $commit = $this->fetchRemoteGithubCommit($repository);
        return $commit['sha'] ?? null;
    }

    private function fetchRemoteGithubCommit(array $repository): ?array
    {
        $url = 'https://api.github.com/repos/' . rawurlencode($repository['owner']) . '/' . rawurlencode($repository['repo']) . '/commits/' . rawurlencode($repository['branch']);
        $response = $this->httpRequest($url, $repository['token']);

        if ($response === null || $response['status'] < 200 || $response['status'] >= 300) {
            return null;
        }

        $data = json_decode($response['body'], true);
        $sha = is_array($data) && isset($data['sha']) ? (string)$data['sha'] : '';
        $message = is_array($data) && isset($data['commit']['message']) ? (string)$data['commit']['message'] : '';

        if (!preg_match('/^[a-f0-9]{40}$/', $sha)) {
            return null;
        }

        return [
            'sha' => $sha,
            'message' => $this->firstCommitMessageLine($message),
        ];
    }

    private function fetchGithubCompareChanges(array $repository, string $baseSha, string $headSha): array
    {
        if (!preg_match('/^[a-f0-9]{40}$/', $baseSha) || !preg_match('/^[a-f0-9]{40}$/', $headSha)) {
            return [];
        }

        $url = 'https://api.github.com/repos/' . rawurlencode($repository['owner']) . '/' . rawurlencode($repository['repo']) . '/compare/' . rawurlencode($baseSha) . '...' . rawurlencode($headSha);
        $response = $this->httpRequest($url, $repository['token']);

        if ($response === null || $response['status'] < 200 || $response['status'] >= 300) {
            return [];
        }

        $data = json_decode($response['body'], true);
        $commits = is_array($data) && isset($data['commits']) && is_array($data['commits'])
            ? $data['commits']
            : [];

        $changes = [];
        foreach (array_slice(array_reverse($commits), 0, 8) as $commit) {
            $message = is_array($commit) && isset($commit['commit']['message']) ? (string)$commit['commit']['message'] : '';
            $line = $this->firstCommitMessageLine($message);
            if ($line !== '') {
                $changes[] = $line;
            }
        }

        return array_values(array_unique($changes));
    }

    private function downloadGithubZip(array $repository): ?string
    {
        $url = 'https://api.github.com/repos/' . rawurlencode($repository['owner']) . '/' . rawurlencode($repository['repo']) . '/zipball/' . rawurlencode($repository['branch']);
        $response = $this->httpRequest($url, $repository['token']);

        if ($response === null || $response['status'] < 200 || $response['status'] >= 300 || $response['body'] === '') {
            return null;
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'isna-update-');
        if (!is_string($zipPath) || file_put_contents($zipPath, $response['body']) === false) {
            return null;
        }

        return $zipPath;
    }

    private function httpRequest(string $url, string $token = ''): ?array
    {
        $headers = [
            'User-Agent: ISNA-System-Updater',
            'Accept: application/vnd.github+json',
            'X-GitHub-Api-Version: 2022-11-28',
        ];

        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch === false) {
                return null;
            }

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_TIMEOUT => 120,
                CURLOPT_HTTPHEADER => $headers,
            ]);

            $body = curl_exec($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!is_string($body)) {
                return null;
            }

            return ['status' => $status, 'body' => $body];
        }

        if (!ini_get('allow_url_fopen')) {
            return null;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => 120,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        if (!is_string($body)) {
            return null;
        }

        $status = 0;
        $responseHeaders = function_exists('http_get_last_response_headers') ? http_get_last_response_headers() : [];
        if (is_array($responseHeaders)) {
            foreach ($responseHeaders as $header) {
                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $matches)) {
                    $status = (int)$matches[1];
                }
            }
        }

        return ['status' => $status, 'body' => $body];
    }

    private function readLocalGitSha(): ?string
    {
        $headPath = $this->projectRoot . '/.git/HEAD';
        if (!is_readable($headPath)) {
            return null;
        }

        $head = trim((string)file_get_contents($headPath));
        if (preg_match('/^[a-f0-9]{40}$/', $head)) {
            return $head;
        }

        if (!$this->startsWith($head, 'ref: ')) {
            return null;
        }

        $ref = trim(substr($head, 5));
        if ($ref === '' || $this->contains($ref, '..')) {
            return null;
        }

        $refPath = $this->projectRoot . '/.git/' . $ref;
        if (is_readable($refPath)) {
            $sha = trim((string)file_get_contents($refPath));
            return preg_match('/^[a-f0-9]{40}$/', $sha) ? $sha : null;
        }

        $packedRefsPath = $this->projectRoot . '/.git/packed-refs';
        if (!is_readable($packedRefsPath)) {
            return null;
        }

        foreach (file($packedRefsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if ($line[0] === '#' || $line[0] === '^') {
                continue;
            }

            [$sha, $packedRef] = array_pad(explode(' ', $line, 2), 2, '');
            if ($packedRef === $ref && preg_match('/^[a-f0-9]{40}$/', $sha)) {
                return $sha;
            }
        }

        return null;
    }

    private function appliedVersionPath(): string
    {
        return $this->projectRoot . '/data/system_update.json';
    }

    private function readAppliedVersion(): ?string
    {
        $path = $this->appliedVersionPath();
        if (!is_readable($path)) {
            return null;
        }

        $data = json_decode((string)file_get_contents($path), true);
        $sha = is_array($data) && isset($data['sha']) ? (string)$data['sha'] : '';

        return preg_match('/^[a-f0-9]{40}$/', $sha) ? $sha : null;
    }

    private function writeAppliedVersion(string $sha): void
    {
        if (!preg_match('/^[a-f0-9]{40}$/', $sha)) {
            return;
        }

        $dir = dirname($this->appliedVersionPath());
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        @file_put_contents($this->appliedVersionPath(), json_encode([
            'sha' => $sha,
            'updated_at' => date(DATE_ATOM),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function makeTempDir(string $prefix): ?string
    {
        $base = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
        $dir = $base . DIRECTORY_SEPARATOR . $prefix . bin2hex(random_bytes(8));

        return @mkdir($dir, 0775, true) ? $dir : null;
    }

    private function firstDirectory(string $dir): ?string
    {
        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                return $path;
            }
        }

        return null;
    }

    private function copyDirectoryContents(string $source, string $destination): bool
    {
        $ok = true;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $sourcePath = $item->getPathname();
            $relativePath = str_replace('\\', '/', substr($sourcePath, strlen($source) + 1));

            if ($this->shouldSkipArchivePath($relativePath)) {
                continue;
            }

            $targetPath = $destination . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if ($item->isDir()) {
                if (!is_dir($targetPath) && !@mkdir($targetPath, 0775, true)) {
                    $ok = false;
                }
                continue;
            }

            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir) && !@mkdir($targetDir, 0775, true)) {
                $ok = false;
                continue;
            }

            if (!@copy($sourcePath, $targetPath)) {
                $ok = false;
            }
        }

        return $ok;
    }

    private function shouldSkipArchivePath(string $relativePath): bool
    {
        $relativePath = trim(str_replace('\\', '/', $relativePath), '/');
        $firstSegment = explode('/', $relativePath, 2)[0] ?? '';

        if (in_array($firstSegment, ['.git', 'logs', 'storage', 'legacy_archive'], true)) {
            return true;
        }

        return in_array($relativePath, [
            'config/db.php',
            'config/users.php',
            'data/system_update.json',
        ], true) || $this->startsWith($relativePath, 'images/cms/');
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }

        @rmdir($dir);
    }

    private function resolveGitBinary(): ?string
    {
        $configured = getenv('ISNA_GIT_BINARY') ?: getenv('GIT_BINARY') ?: '';

        if ($configured === '' && isset($GLOBALS['config']) && is_array($GLOBALS['config'])) {
            $configured = isset($GLOBALS['config']['git_binary']) ? (string)$GLOBALS['config']['git_binary'] : '';
        }

        $candidates = array_filter([
            $configured,
            'git',
            '/usr/bin/git',
            '/usr/local/bin/git',
            '/bin/git',
        ]);

        foreach (array_unique($candidates) as $candidate) {
            $result = $this->run([$candidate, '--version']);
            if ($result['code'] === 0) {
                return $candidate;
            }
        }

        return null;
    }

    private function gitChangeSummary(): array
    {
        $result = $this->run($this->gitCommand(['-C', $this->projectRoot, 'log', '--format=%s', '--max-count=8', 'HEAD..@{u}']));
        if ($result['code'] !== 0) {
            return [];
        }

        $changes = [];
        foreach (preg_split('/\r\n|\r|\n/', trim($result['stdout'])) ?: [] as $line) {
            $line = $this->firstCommitMessageLine($line);
            if ($line !== '') {
                $changes[] = $line;
            }
        }

        return array_values(array_unique($changes));
    }

    private function firstCommitMessageLine(string $message): string
    {
        $line = trim((string)preg_split('/\r\n|\r|\n/', $message)[0]);
        $line = preg_replace('/\s+/', ' ', $line) ?: $line;
        return function_exists('mb_substr') ? mb_substr($line, 0, 160) : substr($line, 0, 160);
    }

    private function gitCommand(array $arguments): array
    {
        return array_merge([$this->gitBinary ?: 'git'], $arguments);
    }

    private function run(array $command, ?string $cwd = null): array
    {
        if (function_exists('proc_open')) {
            return $this->runWithProcOpen($command, $cwd);
        }

        if (function_exists('exec')) {
            return $this->runWithExec($command, $cwd);
        }

        return [
            'code' => 1,
            'stdout' => '',
            'stderr' => 'O PHP do servidor bloqueia proc_open() e exec().',
        ];
    }

    private function runWithProcOpen(array $command, ?string $cwd = null): array
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($command, $descriptorSpec, $pipes, $cwd ?: $this->projectRoot);

        if (!is_resource($process)) {
            return ['code' => 1, 'stdout' => '', 'stderr' => 'Nao foi possivel iniciar o processo.'];
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [
            'code' => proc_close($process),
            'stdout' => is_string($stdout) ? $stdout : '',
            'stderr' => is_string($stderr) ? $stderr : '',
        ];
    }

    private function runWithExec(array $command, ?string $cwd = null): array
    {
        $previousCwd = getcwd();
        $targetCwd = $cwd ?: $this->projectRoot;

        if (!is_string($previousCwd) || !@chdir($targetCwd)) {
            return ['code' => 1, 'stdout' => '', 'stderr' => 'Nao foi possivel acessar a pasta do projeto.'];
        }

        $shellCommand = implode(' ', array_map('escapeshellarg', $command)) . ' 2>&1';
        $lines = [];
        $code = 1;
        @exec($shellCommand, $lines, $code);
        @chdir($previousCwd);

        return [
            'code' => (int)$code,
            'stdout' => implode("\n", $lines),
            'stderr' => '',
        ];
    }

    private function formatOutput(array $result): string
    {
        $output = trim((string)($result['stdout'] ?? '') . "\n" . (string)($result['stderr'] ?? ''));
        if ($output === '') {
            return '';
        }

        $output = preg_replace('/\s+/', ' ', $output) ?: $output;
        return substr($output, 0, 500);
    }

    private function startsWith(string $value, string $prefix): bool
    {
        return $prefix === '' || strncmp($value, $prefix, strlen($prefix)) === 0;
    }

    private function contains(string $value, string $needle): bool
    {
        return $needle === '' || strpos($value, $needle) !== false;
    }
}
