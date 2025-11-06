#!/usr/bin/env php
<?php
/**
 * Script automático para gerar thumbnails dos vídeos da API
 * (sem interação do usuário - sobrescreve arquivos existentes)
 * 
 * Uso: php generate_video_thumbnails_auto.php
 */

// Configuração dos vídeos
$videos = [
    [
        'url' => 'https://api.chiapetta.dev/v/4N4uRF1EkEjnpxgx',
        'name' => 'ISNA - Doações',
        'thumbnail' => __DIR__ . '/images/donation-thumbnail.jpg',
        'time' => 2, // segundos
    ],
    [
        'url' => 'https://api.chiapetta.dev/v/ItV-Nx6UsanFr8DH',
        'name' => 'outubro_rosa-horizontal',
        'thumbnail' => __DIR__ . '/videos/outubro_rosa_poster.jpg',
        'time' => 2,
    ],
    [
        'url' => 'https://api.chiapetta.dev/v/KfyfXHINHWwqZ_Bk',
        'name' => 'outubro_rosa-vertical',
        'thumbnail' => __DIR__ . '/videos/outubro_rosa_poster_vertical.jpg',
        'time' => 2,
    ],
    [
        'url' => 'https://api.chiapetta.dev/v/boKogI2kIyY6fieR',
        'name' => 'realizacao-1-horizontal',
        'thumbnail' => __DIR__ . '/images/realizacoes/realizacao-1-horizontal.jpg',
        'time' => 2,
    ],
    [
        'url' => 'https://api.chiapetta.dev/v/DU_q-YUklTb57i2Y',
        'name' => 'realizacao-1-vertical',
        'thumbnail' => __DIR__ . '/images/realizacoes/realizacao-1-vertical.jpg',
        'time' => 2,
    ],
];

function generateThumbnail($videoUrl, $outputPath, $timeInSeconds = 2) {
    $dir = dirname($outputPath);
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            return ['success' => false, 'error' => "Erro ao criar diretório: $dir"];
        }
    }
    
    $tempVideo = sys_get_temp_dir() . '/' . uniqid('video_') . '.mp4';
    
    // Baixa o vídeo
    $videoContent = @file_get_contents($videoUrl);
    if ($videoContent === false) {
        return ['success' => false, 'error' => 'Erro ao baixar vídeo'];
    }
    
    file_put_contents($tempVideo, $videoContent);
    $videoSize = filesize($tempVideo);
    
    // Gera thumbnail
    $cmd = sprintf(
        'ffmpeg -y -ss %d -i %s -vframes 1 -q:v 2 -vf "scale=1280:-1" %s 2>&1',
        $timeInSeconds,
        escapeshellarg($tempVideo),
        escapeshellarg($outputPath)
    );
    
    exec($cmd, $output, $returnVar);
    @unlink($tempVideo);
    
    if ($returnVar === 0 && file_exists($outputPath)) {
        return [
            'success' => true,
            'size' => filesize($outputPath),
            'video_size' => $videoSize
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Erro ao gerar thumbnail',
            'details' => implode("\n", array_slice($output, -3))
        ];
    }
}

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

// Verificar FFmpeg
exec('which ffmpeg', $ffmpegPath, $returnVar);
if ($returnVar !== 0) {
    echo json_encode(['error' => 'FFmpeg não encontrado']) . "\n";
    exit(1);
}

echo "🎥 Gerando thumbnails automaticamente...\n\n";

$results = [];
foreach ($videos as $video) {
    echo "📹 {$video['name']}... ";
    $result = generateThumbnail($video['url'], $video['thumbnail'], $video['time']);
    
    if ($result['success']) {
        echo "✅ " . formatBytes($result['size']) . "\n";
        $results[] = [
            'name' => $video['name'],
            'status' => 'success',
            'path' => $video['thumbnail'],
            'size' => $result['size']
        ];
    } else {
        echo "❌ {$result['error']}\n";
        $results[] = [
            'name' => $video['name'],
            'status' => 'error',
            'error' => $result['error']
        ];
    }
}

$success = count(array_filter($results, fn($r) => $r['status'] === 'success'));
$failed = count($results) - $success;

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ Sucesso: $success | ❌ Falhas: $failed\n";

exit($failed > 0 ? 1 : 0);
