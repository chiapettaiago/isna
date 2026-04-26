<?php

declare(strict_types=1);

class PdfRenderer
{
    private const DOCUMENTS = [
        ['key' => 'certificado-cebas', 'title' => 'CERTIFICADO CEBAS', 'file' => 'CERTIFICADO CEBAS.pdf', 'thumbnail' => 'all_documents.png'],
        ['key' => 'certificado-siconv', 'title' => 'Certificado Siconv', 'file' => 'CertificadoSiconv.pdf', 'thumbnail' => 'CertificadoSiconv_thumbnail.png'],
        ['key' => 'cmas', 'title' => 'CMAS', 'file' => 'cmas.pdf', 'thumbnail' => 'cmas_thumbnail.png'],
        ['key' => 'declaracao-isna', 'title' => 'Declaração ISNA', 'file' => 'ISNA_Declaracao.pdf', 'thumbnail' => 'all_documents.png'],
        ['key' => 'oscip', 'title' => 'OSCIP', 'file' => 'OSCIP.pdf', 'thumbnail' => 'all_documents.png'],
        ['key' => 'utilidade-publica-municipal', 'title' => 'Título de Utilidade Pública Municipal', 'file' => 'Titulo de Utilidade publica municipal.pdf', 'thumbnail' => 'Titulo de Utilidade publica_thumbnail.png'],
        ['key' => 'utilidade-publica', 'title' => 'Título de Utilidade Pública', 'file' => 'Titulo de Utilidade publica.pdf', 'thumbnail' => 'Titulo de Utilidade publica_thumbnail.png'],
        ['key' => 'utilidade-publica-extra', 'title' => 'Utilidade Pública', 'file' => 'utilidade publica.pdf', 'thumbnail' => 'all_documents.png'],
    ];

    public static function documents(): array
    {
        $documents = [];

        foreach (self::DOCUMENTS as $document) {
            $path = self::documentPath($document['file']);

            if (!is_file($path)) {
                continue;
            }

            $document['path'] = $path;
            $document['pages'] = self::pageCount($document['key']);
            $documents[] = $document;
        }

        return $documents;
    }

    public static function findDocument(string $key): ?array
    {
        foreach (self::DOCUMENTS as $document) {
            if ($document['key'] !== $key) {
                continue;
            }

            $path = self::documentPath($document['file']);
            if (!is_file($path)) {
                return null;
            }

            $document['path'] = $path;
            $document['pages'] = self::pageCount($key);
            return $document;
        }

        return null;
    }

    public static function supportsRendering(): bool
    {
        return self::canRunExternalCommands() && self::binaryUsable('pdftoppm', '/usr/bin/pdftoppm');
    }

    public static function pageCount(string $key): int
    {
        $document = self::documentDefinition($key);
        if ($document === null) {
            return 1;
        }

        $path = self::documentPath($document['file']);
        if (!is_file($path)) {
            return 1;
        }

        $mtime = (string) filemtime($path);
        $metaPath = self::cacheDir() . '/' . $key . '-' . $mtime . '.json';
        if (is_file($metaPath)) {
            $meta = json_decode((string) file_get_contents($metaPath), true);
            if (isset($meta['pages']) && (int) $meta['pages'] > 0) {
                return (int) $meta['pages'];
            }
        }

        $pages = self::readPageCount($path);
        @file_put_contents($metaPath, json_encode(['pages' => $pages], JSON_UNESCAPED_UNICODE));

        return $pages;
    }

    public static function renderPage(string $key, int $page = 1, string $size = 'full'): string
    {
        if (!self::supportsRendering()) {
            throw new RuntimeException('Renderização de PDF indisponível neste servidor.');
        }

        $document = self::findDocument($key);
        if ($document === null) {
            throw new InvalidArgumentException('Documento não encontrado.');
        }

        $pageCount = max(1, (int) $document['pages']);
        $page = max(1, min($page, $pageCount));
        $size = $size === 'thumb' ? 'thumb' : 'full';
        $dpi = $size === 'thumb' ? 32 : 150;

        $sourcePath = $document['path'];
        $cacheKey = sha1($key . '|' . filemtime($sourcePath) . '|' . $page . '|' . $size . '|' . $dpi);
        $targetPath = self::cacheDir() . '/' . $cacheKey . '.png';

        if (is_file($targetPath) && filesize($targetPath) > 0) {
            return $targetPath;
        }

        $tempPrefix = self::cacheDir() . '/render-' . $cacheKey . '-' . bin2hex(random_bytes(4));
        $tempPath = $tempPrefix . '.png';
        if (is_file($tempPath)) {
            @unlink($tempPath);
        }

        $pdftoppm = self::binaryPath('pdftoppm', '/usr/bin/pdftoppm');
        $command = escapeshellcmd($pdftoppm)
            . ' -f ' . $page
            . ' -l ' . $page
            . ' -singlefile -png'
            . ' -r ' . $dpi
            . ' ' . escapeshellarg($sourcePath)
            . ' ' . escapeshellarg($tempPrefix)
            . ' 2>&1';

        $output = [];
        $exitCode = 1;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !is_file($tempPath)) {
            throw new RuntimeException('Falha ao renderizar PDF: ' . implode("\n", $output));
        }

        @rename($tempPath, $targetPath);
        if (!is_file($targetPath)) {
            @copy($tempPath, $targetPath);
            @unlink($tempPath);
        }

        if (!is_file($targetPath)) {
            throw new RuntimeException('Falha ao salvar imagem renderizada.');
        }

        return $targetPath;
    }

    private static function documentDefinition(string $key): ?array
    {
        foreach (self::DOCUMENTS as $document) {
            if ($document['key'] === $key) {
                return $document;
            }
        }

        return null;
    }

    private static function readPageCount(string $path): int
    {
        if (!self::canRunExternalCommands() || !self::binaryUsable('pdfinfo', '/usr/bin/pdfinfo')) {
            return 1;
        }

        $pdfinfo = self::binaryPath('pdfinfo', '/usr/bin/pdfinfo');
        $command = escapeshellcmd($pdfinfo) . ' ' . escapeshellarg($path) . ' 2>&1';
        $output = [];
        $exitCode = 1;

        exec($command, $output, $exitCode);

        if ($exitCode === 0) {
            foreach ($output as $line) {
                if (preg_match('/^Pages:\s+(\d+)/i', $line, $matches)) {
                    return max(1, (int) $matches[1]);
                }
            }
        }

        return 1;
    }

    private static function documentPath(string $file): string
    {
        return self::rootDir() . '/docs/' . $file;
    }

    private static function rootDir(): string
    {
        return dirname(__DIR__, 2);
    }

    private static function cacheDir(): string
    {
        $dir = self::rootDir() . '/data/pdf-cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
    }

    private static function binaryPath(string $name, string $preferred): string
    {
        return is_file($preferred) && is_executable($preferred) ? $preferred : $name;
    }

    private static function canRunExternalCommands(): bool
    {
        return function_exists('exec') && is_callable('exec');
    }

    private static function binaryUsable(string $name, string $preferred): bool
    {
        if (is_file($preferred) && is_executable($preferred)) {
            return true;
        }

        if (!self::canRunExternalCommands()) {
            return false;
        }

        $output = [];
        $exitCode = 1;
        @exec('command -v ' . escapeshellarg($name) . ' 2>/dev/null', $output, $exitCode);

        return $exitCode === 0 && !empty($output);
    }
}
